<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use App\Support\DashboardNavigation;
use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
