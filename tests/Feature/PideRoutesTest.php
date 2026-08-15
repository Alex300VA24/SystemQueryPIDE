<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PideRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_render_dashboard(): void
    {
        $this->actingAs(Usuario::factory()->create());
        $this->get('/dashboard')->assertOk();
    }

    public function test_legacy_section_routes_are_not_spa_endpoints(): void
    {
        $this->actingAs(Usuario::factory()->create());
        $this->get('/consultas/dni')->assertNotFound();
        $this->get('/sistema/usuarios')->assertNotFound();
    }
}
