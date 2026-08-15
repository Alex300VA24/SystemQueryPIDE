<?php

namespace Tests\Feature;

use App\Livewire\ConsultaDni;
use App\Livewire\ConsultaPartidas;
use App\Livewire\ConsultaRuc;
use App\Livewire\Dashboard;
use App\Livewire\ActualizarPassword;
use App\Livewire\GestionModulos;
use App\Livewire\GestionRoles;
use App\Livewire\GestionUsuarios;
use App\Livewire\Ayuda;
use App\Models\Modulo;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Pide\Contracts\SunarpServiceInterface;
use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_changes_section_without_redirect(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Livewire::actingAs($usuario)
            ->test(Dashboard::class)
            ->call('selectSection', 'dni')
            ->assertSet('activeSection', 'dni')
            ->assertNoRedirect();
    }

    public function test_dni_requires_eight_digits(): void
    {
        Livewire::test(ConsultaDni::class)->set('busqueda', '123')->call('search')->assertHasErrors(['busqueda']);
    }

    public function test_dni_search_falls_back_to_demo_without_pide_access(): void
    {
        Livewire::test(ConsultaDni::class)
            ->set('busqueda', '74251836')
            ->set('dniUsuario', '12345678')
            ->set('pidePassword', 'secret')
            ->call('search')
            ->assertHasNoErrors()
            ->assertSet('searched', true)
            ->assertSet('real', false);
    }

    public function test_ruc_requires_eleven_digits(): void
    {
        Livewire::test(ConsultaRuc::class)->set('busqueda', '201')->call('search')->assertHasErrors(['busqueda']);
    }

    public function test_sunarp_juridical_flow_selects_person_and_loads_registry_detail(): void
    {
        Cache::forget('sunarp_oficinas');

        $service = \Mockery::mock(SunarpServiceInterface::class);
        $service->shouldReceive('consultarGOficina')->zeroOrMoreTimes()->andReturn([
            'success' => true,
            'data' => [['codZona' => '01', 'codOficina' => '01', 'descripcion' => 'Lima']],
        ]);
        $service->shouldReceive('buscarPersonaJuridica')->once()->andReturn([
            'success' => true,
            'data' => [['ruc' => '20123456789', 'razon_social' => 'Empresa Demo S.A.C.']],
        ]);
        $service->shouldReceive('consultarTSIRSARPJuridica')->once()->with('Empresa Demo S.A.C.')->andReturn([
            'success' => true,
            'data' => [[
                'numero_partida' => '11234567',
                'codigo_zona' => '01',
                'codigo_oficina' => '01',
                'oficina' => 'Lima',
            ]],
        ]);
        $service->shouldReceive('cargarDetallePartida')->once()->with('11234567', '01', '01', '')->andReturn([
            'success' => true,
            'data' => ['asientos' => [['tipo' => 'ASIENTO']], 'imagenes' => [], 'datos_vehiculo' => []],
        ]);
        $this->app->instance(SunarpServiceInterface::class, $service);

        Livewire::test(ConsultaPartidas::class)
            ->call('setTab', 'juridica')
            ->call('openSearchModal')
            ->set('juridicaMode', 'ruc')
            ->set('juridicaQuery', '20123456789')
            ->call('searchJuridica')
            ->assertHasNoErrors()
            ->assertSet('people.0.razon_social', 'Empresa Demo S.A.C.')
            ->call('selectPerson', 0)
            ->call('searchSunarp')
            ->assertHasNoErrors()
            ->assertSet('searched', true)
            ->assertSet('selectedPartida.numero_partida', '11234567')
            ->assertSet('detail.asientos.0.tipo', 'ASIENTO');
    }

    public function test_authenticated_user_can_update_password_in_place(): void
    {
        $usuario = Usuario::factory()->create();

        Livewire::actingAs($usuario)
            ->test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'nueva-clave')
            ->set('password_confirmation', 'nueva-clave')
            ->call('update')
            ->assertHasNoErrors()
            ->assertDispatched('password-updated');

        $this->assertTrue(Hash::check('nueva-clave', $usuario->fresh()->password_hash));
    }

    public function test_user_management_filters_sorts_and_persists(): void
    {
        $rol = Rol::create(['codigo' => 'OPERADOR', 'nombre' => 'Operador', 'nivel' => 1, 'activo' => true]);
        Usuario::factory()->create(['username' => 'mquispe']);

        Livewire::test(GestionUsuarios::class)
            ->call('showListTab')->set('search', 'mquispe')->assertSee('mquispe')
            ->set('perPage', 5)->assertViewHas('users', fn ($users) => $users->perPage() === 5)
            ->call('sort', 'email')->assertSet('sortBy', 'email')
            ->call('showCreateTab')
            ->set('documentoNumero', '87654321')
            ->set('apellidoPaterno', 'Nuevo')
            ->set('nombres', 'Operador')
            ->set('sexo', 'M')
            ->set('username', 'nuevo.operador')
            ->set('email', 'nuevo@example.test')
            ->set('roleId', (string) $rol->id)
            ->set('cui', '4')
            ->set('password', 'password')->set('password_confirmation', 'password')
            ->call('save')
            ->assertHasNoErrors()->assertSet('activeTab', 'create');

        $this->assertDatabaseHas('usuarios', ['username' => 'nuevo.operador', 'email' => 'nuevo@example.test', 'cui' => '4']);
        $this->assertDatabaseHas('personas', ['documento_numero' => '87654321', 'tipo_persona' => 1, 'sexo' => 'M']);
    }

    public function test_role_management_filters_and_persists(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $module = Modulo::query()->where('activo', true)->firstOrFail();

        Livewire::test(GestionRoles::class)
            ->set('perPage', 5)->assertViewHas('roles', fn ($roles) => $roles->perPage() === 5)
            ->call('showCreateTab')->set('codigo', 'SUPERVISOR_TEST')->set('nombre', 'Supervisor de área')
            ->set('nivel', 5)->set('selectedModuleIds', [(string) $module->id])
            ->call('save')->assertHasNoErrors()->assertSet('activeTab', 'create')
            ->call('showListTab')->set('search', 'SUPERVISOR')->assertSee('Supervisor de área');

        $role = Rol::where('codigo', 'SUPERVISOR_TEST')->firstOrFail();
        $this->assertDatabaseHas('rol_modulo', ['rol_id' => $role->id, 'modulo_id' => $module->id]);
    }

    public function test_module_management_persists_hierarchy(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $parent = Modulo::where('codigo', 'CON')->firstOrFail();

        Livewire::test(GestionModulos::class)
            ->call('showCreateTab')
            ->set('sistemaId', (string) $parent->sistema_id)
            ->set('codigo', 'NUEVO')
            ->set('nombre', 'Nueva consulta')
            ->set('descripcion', 'Descripción de la nueva consulta')
            ->set('url', '/pide/consultas/nueva')
            ->set('icono', 'fa-solid fa-file-lines')
            ->set('parentId', (string) $parent->id)
            ->set('orden', 9)
            ->set('nivel', 2)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('activeTab', 'create');

        $this->assertDatabaseHas('modulos', ['codigo' => 'NUEVO', 'padre_id' => $parent->id]);
    }

    public function test_ayuda_filters_guides_by_role_modules(): void
    {
        $this->seed(PideProductionDataSeeder::class);

        $admin = Usuario::factory()->create();
        $admin->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $emp = Usuario::factory()->create();
        $emp->roles()->attach(Rol::where('codigo', 'EMP')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $vis = Usuario::factory()->create();
        $vis->roles()->attach(Rol::where('codigo', 'VIS')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Livewire::actingAs($admin)
            ->test(Ayuda::class)
            ->assertSee('Gestión de roles')
            ->assertSee('Gestión de módulos')
            ->assertSee('Registro de usuarios')
            ->assertSee('RENIEC · Consulta por DNI');

        Livewire::actingAs($emp)
            ->test(Ayuda::class)
            ->assertSee('RENIEC · Consulta por DNI')
            ->assertSee('SUNAT · Consulta por RUC')
            ->assertSee('SUNARP · Consulta de partidas')
            ->assertDontSee('Gestión de roles')
            ->assertDontSee('Registro de usuarios');

        Livewire::actingAs($vis)
            ->test(Ayuda::class)
            ->assertSee('Ingreso al sistema')
            ->assertDontSee('RENIEC · Consulta por DNI')
            ->assertDontSee('Gestión de roles');
    }
}
