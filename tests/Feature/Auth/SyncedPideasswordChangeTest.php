<?php

namespace Tests\Feature\Auth;

use App\Livewire\ActualizarPassword;
use App\Models\HistorialAuditoria;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\PideCredentialStore;
use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SyncedPideasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConAccesoPide(): Usuario
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        return $usuario;
    }

    public function test_local_password_is_not_changed_when_pide_update_fails(): void
    {
        $usuario = $this->usuarioConAccesoPide();
        $this->actingAs($usuario);

        $service = \Mockery::mock(ReniecServiceInterface::class);
        $service->shouldReceive('actualizarPasswordRENIEC')->once()->andReturn([
            'success' => false,
            'message' => 'Credencial PIDE actual incorrecta.',
        ]);
        $this->app->instance(ReniecServiceInterface::class, $service);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'NuevaClave#26')
            ->set('password_confirmation', 'NuevaClave#26')
            ->call('update')
            ->assertHasErrors(['currentPassword']);

        $this->assertTrue(Hash::check('password', $usuario->fresh()->password_hash));
        $this->assertFalse(app(PideCredentialStore::class)->has());
    }

    public function test_local_password_changes_when_pide_update_succeeds(): void
    {
        $usuario = $this->usuarioConAccesoPide();
        $this->actingAs($usuario);

        $service = \Mockery::mock(ReniecServiceInterface::class);
        $service->shouldReceive('actualizarPasswordRENIEC')
            ->once()
            ->with('password', 'NuevaClave#26', $usuario->persona->documento_numero)
            ->andReturn(['success' => true, 'message' => 'OK']);
        $this->app->instance(ReniecServiceInterface::class, $service);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'NuevaClave#26')
            ->set('password_confirmation', 'NuevaClave#26')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClave#26', $usuario->fresh()->password_hash));
        $this->assertSame('NuevaClave#26', app(PideCredentialStore::class)->get());
    }

    public function test_audit_trail_never_contains_a_password(): void
    {
        $usuario = $this->usuarioConAccesoPide();
        $this->actingAs($usuario);

        $service = \Mockery::mock(ReniecServiceInterface::class);
        $service->shouldReceive('actualizarPasswordRENIEC')->once()->andReturn(['success' => true, 'message' => 'OK']);
        $this->app->instance(ReniecServiceInterface::class, $service);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'NuevaClave#26')
            ->set('password_confirmation', 'NuevaClave#26')
            ->call('update');

        $registro = HistorialAuditoria::where('usuario_id', $usuario->id)->where('operacion', 'CAMBIO_PASSWORD_PIDE')->firstOrFail();

        // El tipo de evento (operacion) legítimamente incluye la palabra
        // "password" en su nombre; lo que no debe aparecer es el valor de
        // ninguna contraseña en los campos libres del registro.
        $payload = json_encode([
            'observacion' => $registro->observacion,
            'datos_anteriores' => $registro->datos_anteriores,
            'datos_nuevos' => $registro->datos_nuevos,
        ]);

        $this->assertStringNotContainsString('password', strtolower((string) $payload));
        $this->assertStringNotContainsString('nuevaclave', strtolower((string) $payload));
    }

    public function test_user_without_pide_access_only_changes_local_password(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'NuevaClave#26')
            ->set('password_confirmation', 'NuevaClave#26')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClave#26', $usuario->fresh()->password_hash));
        $this->assertFalse(app(PideCredentialStore::class)->has());
    }

    public function test_user_with_only_sunat_access_does_not_sync_against_reniec(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'PRAC')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $this->actingAs($usuario);

        $service = \Mockery::mock(ReniecServiceInterface::class);
        $service->shouldNotReceive('actualizarPasswordRENIEC');
        $this->app->instance(ReniecServiceInterface::class, $service);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'NuevaClave#26')
            ->set('password_confirmation', 'NuevaClave#26')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClave#26', $usuario->fresh()->password_hash));
        $this->assertFalse(app(PideCredentialStore::class)->has());
    }
}
