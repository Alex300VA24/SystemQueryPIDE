<?php

use App\Auth\PendingCuiAuthentication;
use App\Livewire\Forms\LoginForm;
use App\Livewire\ValidarCuiModal;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        try {
            $this->validate();
            $usuarioId = $this->form->verifyCredentials();
        } catch (ValidationException $exception) {
            app(PendingCuiAuthentication::class)->clear();

            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->dispatch('login-action-finished');

            return;
        }

        app(PendingCuiAuthentication::class)->start($usuarioId, $this->form->remember, $this->form->password);
        $this->form->reset('password');
        $this->dispatch('open-validar-cui-modal')->to(ValidarCuiModal::class);
    }
}; ?>

<div
    class="login-container"
    x-data="{ showPassword: false, loginPending: false }"
    @login-transition-complete.window="loginPending = false"
    @login-action-finished.window="loginPending = false"
>
    <section class="login-left" aria-labelledby="system-name">
        <div class="header-section">
            <img class="muni-logo" src="{{ asset('assets/images/muni2.png') }}" alt="Municipalidad Distrital de La Esperanza">
            <h1 id="system-name">Sistema PIDE</h1>
            <p>Municipalidad Distrital de La Esperanza</p>
        </div>

        <div class="divider-bar" aria-hidden="true"></div>

        <div class="entity-cards" aria-label="Entidades disponibles">
            <article class="entity-card reniec">
                <div class="entity-icon" aria-hidden="true"><i class="fas fa-id-card"></i></div>
                <div class="entity-info"><h2>RENIEC</h2><p>Registro Nacional de Identificación y Estado Civil</p></div>
            </article>
            <article class="entity-card sunat">
                <div class="entity-icon" aria-hidden="true"><i class="fas fa-building"></i></div>
                <div class="entity-info"><h2>SUNAT</h2><p>Superintendencia Nacional de Aduanas</p></div>
            </article>
            <article class="entity-card sunarp">
                <div class="entity-icon" aria-hidden="true"><i class="fas fa-book"></i></div>
                <div class="entity-info"><h2>SUNARP</h2><p>Superintendencia de Registros Públicos</p></div>
            </article>
            <article class="entity-card senace">
                <div class="entity-icon" aria-hidden="true"><i class="fas fa-leaf"></i></div>
                <div class="entity-info"><h2>SENACE</h2><p>Servicio Nacional de Certificaciones Ambientales</p></div>
            </article>
            <article class="entity-card mtc">
                <div class="entity-icon" aria-hidden="true"><i class="fas fa-road"></i></div>
                <div class="entity-info"><h2>MTC</h2><p>Ministerio de Transportes y Comunicaciones</p></div>
            </article>
        </div>
    </section>

    <section class="login-right" aria-labelledby="login-title">
        <div class="login-header">
            <div class="login-icon"><img src="{{ asset('assets/images/logo_pide.png') }}" class="logo" alt="Plataforma de Interoperabilidad del Estado"></div>
            <h2 class="login-title" id="login-title">Bienvenido</h2>
            <p class="login-subtitle">Ingrese sus credenciales para acceder</p>
        </div>

        <x-auth-session-status class="legacy-auth-status" :status="session('status')" />

        <form id="formLogin" wire:submit="login" class="login-form" novalidate @submit="loginPending = true">
            <div class="form-group">
                <label for="username"><i class="fas fa-user" aria-hidden="true"></i>Usuario</label>
                <div class="input-wrapper has-icon">
                    <input wire:model="form.username" type="text" id="username" name="username" autocomplete="username" placeholder="Ingrese su usuario" required autofocus aria-describedby="username-error">
                    <i class="fas fa-user icon-left" aria-hidden="true"></i>
                </div>
                @error('form.username') <p id="username-error" class="legacy-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock" aria-hidden="true"></i>Contraseña</label>
                <div class="input-wrapper password-container has-icon">
                    <input wire:model="form.password" :type="showPassword ? 'text' : 'password'" id="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password" aria-describedby="password-error">
                    <i class="fas fa-lock icon-left" aria-hidden="true"></i>
                    <button type="button" class="toggle-password" @click="showPassword = !showPassword" :aria-pressed="showPassword.toString()" :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                        <i class="fas" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'" aria-hidden="true"></i>
                    </button>
                </div>
                @error('form.password') <p id="password-error" class="legacy-field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <button type="submit" id="btnLogin" :disabled="loginPending" wire:loading.attr="disabled" wire:target="login">
                <span x-show="!loginPending"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Ingresar al Sistema</span>
                <span x-cloak x-show="loginPending"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Ingresando...</span>
            </button>
        </form>
    </section>

    <livewire:validar-cui-modal />
</div>
