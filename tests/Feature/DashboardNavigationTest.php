<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\DashboardNavigation;
use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_is_built_from_database_as_hierarchy(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        $navigation = app(DashboardNavigation::class)->forUser($usuario);
        $consultations = collect($navigation)->firstWhere('code', 'CON');

        $this->assertNotNull($consultations);
        $this->assertContains('DNI', array_column($consultations['children'], 'code'));
        $this->assertContains('RUC', array_column($consultations['children'], 'code'));
        $this->assertContains('PAR', array_column($consultations['children'], 'code'));
    }

    public function test_level_three_modules_become_tabs_inside_parent_entity(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        $navigation = app(DashboardNavigation::class)->forUser($usuario);
        $consultations = collect($navigation)->firstWhere('code', 'CON');
        $sunat = collect($consultations['children'])->firstWhere('code', 'RUC');

        $this->assertNotNull($sunat);
        $this->assertSame([], $sunat['children'], 'Los niveles 3 no deben aparecer en el sidebar.');
        $this->assertSame(['contribuyente', 'ccoactiva'], array_column($sunat['tabs'], 'key'));
        $this->assertContains('Consulta Contribuyente', array_column($sunat['tabs'], 'label'));
        $this->assertContains('Consulta Cobranza Coactiva', array_column($sunat['tabs'], 'label'));
    }

    public function test_practicante_role_does_not_receive_administration_modules(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'PRAC')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        $codes = collect(app(DashboardNavigation::class)->forUser($usuario))
            ->flatMap(fn (array $module) => [$module['code'], ...array_column($module['children'], 'code')])
            ->all();

        $this->assertContains('RUC', $codes);
        $this->assertNotContains('CROL', $codes);
        $this->assertNotContains('CMOD', $codes);
    }

    public function test_practicante_receives_only_granted_tab_of_sunat(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'PRAC')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        $navigation = app(DashboardNavigation::class)->forUser($usuario);
        $consultations = collect($navigation)->firstWhere('code', 'CON');
        $sunat = collect($consultations['children'])->firstWhere('code', 'RUC');

        $this->assertNotNull($sunat);
        $this->assertSame(['contribuyente'], array_column($sunat['tabs'], 'key'));
    }

    public function test_select_section_defaults_to_first_tab_of_entity(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $this->actingAs($usuario);

        Livewire::test(Dashboard::class)
            ->call('selectSection', 'ruc')
            ->assertSet('activeSection', 'ruc')
            ->assertSet('activeTab', 'contribuyente');
    }

    public function test_select_section_with_tab_opens_requested_tab(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $this->actingAs($usuario);

        Livewire::test(Dashboard::class)
            ->call('selectSection', 'ruc', 'ccoactiva')
            ->assertSet('activeSection', 'ruc')
            ->assertSet('activeTab', 'ccoactiva');
    }

    public function test_select_tab_switches_tab_within_active_entity(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $this->actingAs($usuario);

        Livewire::test(Dashboard::class)
            ->call('selectSection', 'ruc')
            ->call('selectTab', 'ccoactiva')
            ->assertSet('activeTab', 'ccoactiva')
            ->call('selectTab', 'contribuyente')
            ->assertSet('activeTab', 'contribuyente');
    }

    public function test_select_tab_is_rejected_when_not_granted(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create();
        $usuario->roles()->attach(Rol::where('codigo', 'PRAC')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);
        $this->actingAs($usuario);

        Livewire::test(Dashboard::class)
            ->call('selectSection', 'ruc')
            ->call('selectTab', 'ccoactiva')
            ->assertSet('activeTab', 'contribuyente');
    }
}
