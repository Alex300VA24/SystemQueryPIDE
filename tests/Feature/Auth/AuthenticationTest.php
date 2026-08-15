<?php

namespace Tests\Feature\Auth;

use App\Livewire\ValidarCuiModal;
use App\Models\Usuario;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen_and_cui(): void
    {
        $usuario = Usuario::factory()->create(['username' => 'jperez', 'cui' => '7']);

        $component = Volt::test('pages.auth.login')
            ->set('form.username', $usuario->username)
            ->set('form.password', 'password');

        $component
            ->call('login')
            ->assertHasNoErrors()
            ->assertDispatched('open-validar-cui-modal');

        $this->assertGuest();
        $this->assertSame($usuario->id, session('auth.pending_cui.usuario_id'));

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '7')
            ->call('confirmCui')
            ->assertHasNoErrors()
            ->assertDispatched('cuiValidated', cui: '7')
            ->assertRedirect(RouteServiceProvider::HOME);

        $this->assertAuthenticated();
    }

    public function test_cui_verification_is_presented_as_a_modal_over_the_login_form(): void
    {
        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->assertSet('showModal', true)
            ->assertDispatched('login-transition-complete')
            ->assertSeeHtml('id="modalValidarCUI"')
            ->assertSeeHtml('class="modalCUI modal fade"')
            ->assertSeeHtml('x-show.important="open"')
            ->assertSeeHtml('x-show="confirmPending"')
            ->assertSeeHtml('confirmPending = false')
            ->assertSeeHtml('role="dialog"')
            ->assertSee('Autenticación de Doble Factor — CUI')
            ->assertSee('Busca tu Código Único de Identificación')
            ->assertSeeHtml('fa-shield-halved')
            ->assertSee('dniGuiCUI.svg');
    }

    public function test_cui_modal_can_be_cancelled_without_authenticating(): void
    {
        $usuario = Usuario::factory()->create();
        app(\App\Auth\PendingCuiAuthentication::class)->start($usuario->id, false);

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '7')
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('cui', '')
            ->assertSeeHtml('style="display: none !important;"')
            ->assertDispatched('cuiModalClosed');

        $this->assertGuest();
        $this->assertNull(session('auth.pending_cui'));
    }

    public function test_cui_must_be_a_single_numeric_digit(): void
    {
        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', 'x')
            ->call('confirmCui')
            ->assertHasErrors(['cui'])
            ->assertSet('showModal', true)
            ->assertDispatched('cui-action-finished')
            ->assertNotDispatched('cuiValidated');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $usuario = Usuario::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.username', $usuario->username)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertDispatched('login-action-finished')
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_wrong_cui_blocks_login(): void
    {
        $usuario = Usuario::factory()->create(['cui' => '9']);

        $component = Volt::test('pages.auth.login')
            ->set('form.username', $usuario->username)
            ->set('form.password', 'password');

        $component->call('login')->assertDispatched('open-validar-cui-modal');

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '1')
            ->call('confirmCui')
            ->assertHasErrors(['cui'])
            ->assertDispatched('cui-action-finished')
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_cui_modal_dispatches_validated_digit(): void
    {
        $usuario = Usuario::factory()->create(['cui' => '7']);
        app(\App\Auth\PendingCuiAuthentication::class)->start($usuario->id, false);

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '7')
            ->call('confirmCui')
            ->assertHasNoErrors()
            ->assertDispatched('cuiValidated', cui: '7');
    }

    public function test_cui_loading_finishes_when_digit_is_rejected(): void
    {
        $usuario = Usuario::factory()->create(['cui' => '7']);
        app(\App\Auth\PendingCuiAuthentication::class)->start($usuario->id, false);

        Livewire::test(ValidarCuiModal::class)
            ->call('openModal')
            ->set('cui', '1')
            ->call('confirmCui')
            ->assertHasErrors(['cui'])
            ->assertDispatched('cui-action-finished');
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario);

        $response = $this->get('/dashboard');

        $response
            ->assertOk()
            ->assertSeeLivewire('dashboard');
    }

    public function test_users_can_logout(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario);

        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }
}
