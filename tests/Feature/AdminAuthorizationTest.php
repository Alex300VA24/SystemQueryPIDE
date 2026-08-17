<?php

namespace Tests\Feature;

use App\Livewire\GestionModulos;
use App\Livewire\GestionRoles;
use App\Livewire\GestionUsuarios;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_module_access_cannot_open_gestion_usuarios(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $sinAcceso = Usuario::factory()->create();
        $sinAcceso->roles()->attach(Rol::where('codigo', 'VIS')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Livewire::actingAs($sinAcceso)->test(GestionUsuarios::class)->assertForbidden();
    }

    public function test_user_without_module_access_cannot_open_gestion_roles(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $sinAcceso = Usuario::factory()->create();
        $sinAcceso->roles()->attach(Rol::where('codigo', 'VIS')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Livewire::actingAs($sinAcceso)->test(GestionRoles::class)->assertForbidden();
    }

    public function test_user_without_module_access_cannot_open_gestion_modulos(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $sinAcceso = Usuario::factory()->create();
        $sinAcceso->roles()->attach(Rol::where('codigo', 'VIS')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Livewire::actingAs($sinAcceso)->test(GestionModulos::class)->assertForbidden();
    }

    public function test_guest_cannot_manipulate_gestion_usuarios(): void
    {
        Livewire::test(GestionUsuarios::class)->assertForbidden();
    }

    public function test_authorized_admin_can_open_gestion_modulos(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $admin = Usuario::factory()->create();
        $admin->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Livewire::actingAs($admin)->test(GestionModulos::class)->assertOk();
    }
}
