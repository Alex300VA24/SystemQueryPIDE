<?php

namespace Tests\Feature\Auth;

use App\Livewire\ValidarCuiModal;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Pide\PideCredentialStore;
use Database\Seeders\PideProductionDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PideLoginLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_pide_access_gets_a_temporary_credential_on_login(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create(['cui' => '7']);
        $usuario->roles()->attach(Rol::where('codigo', 'ADMIN')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Volt::test('pages.auth.login')
            ->set('form.username', $usuario->username)
            ->set('form.password', 'password')
            ->call('login');

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '7')
            ->call('confirmCui')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
        $this->assertTrue(app(PideCredentialStore::class)->has());
        $this->assertSame('password', app(PideCredentialStore::class)->get());
    }

    public function test_user_without_pide_access_does_not_get_a_stored_credential(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create(['cui' => '7']);
        $usuario->roles()->attach(Rol::where('codigo', 'VIS')->value('id'), ['fecha_asignacion' => now(), 'activo' => true]);

        Volt::test('pages.auth.login')
            ->set('form.username', $usuario->username)
            ->set('form.password', 'password')
            ->call('login');

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '7')
            ->call('confirmCui')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
        $this->assertFalse(app(PideCredentialStore::class)->has());
    }

    public function test_login_form_password_never_reaches_the_livewire_response_after_the_first_step(): void
    {
        $this->seed(PideProductionDataSeeder::class);
        $usuario = Usuario::factory()->create(['cui' => '7']);

        $component = Volt::test('pages.auth.login')
            ->set('form.username', $usuario->username)
            ->set('form.password', 'password')
            ->call('login');

        $component->assertSet('form.password', '');
    }

    public function test_logout_clears_the_pide_credential(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);
        app(PideCredentialStore::class)->store('clave-pide');

        $this->post('/logout')->assertRedirect('/login');

        $this->assertFalse(app(PideCredentialStore::class)->has());
        $this->assertGuest();
    }
}
