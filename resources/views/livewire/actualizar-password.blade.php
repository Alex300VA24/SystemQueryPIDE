<div class="modulo-legacy password-legacy" @notify.window="$refs.notice.textContent = $event.detail.message; $refs.notice.focus()">
    <div class="page-title">
        <h1><i class="fas fa-pen-to-square"></i> Actualizar Mi Contraseña</h1>
    </div>
    <p x-ref="notice" tabindex="-1" class="sr-only" aria-live="polite"></p>

    <div class="content-wrapper">
        <div class="form-section">
            <div class="current-user-card">
                <p><i class="fas fa-user"></i><span><strong>Usuario:</strong> {{ auth()->user()->name() }}</span></p>
                <p><i class="fas fa-id-card"></i><span><strong>DNI:</strong> {{ auth()->user()->persona?->documento_numero ?? 'Sin documento' }}</span></p>
                <p><i class="fas fa-right-to-bracket"></i><span><strong>Login:</strong> {{ auth()->user()->username }}</span></p>
            </div>

            <form wire:submit="update" wire:key="password-form" novalidate>
                <div class="section-header"><i class="fas fa-lock"></i> Actualizar Contraseña</div>

                <div class="form-grid password-grid">
                    <div class="form-group password-container" x-data="{ visible: false }">
                        <label for="usuPassActualPassword">Contraseña Actual <span class="required">*</span></label>
                        <div class="password-input-wrap">
                            <input :type="visible ? 'text' : 'password'" id="usuPassActualPassword" wire:model="currentPassword" maxlength="100" placeholder="Ingrese su contraseña actual" autocomplete="current-password" autofocus>
                            <button type="button" class="toggle-password" @click="visible = !visible" :aria-label="visible ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'">
                                <i class="fas" :class="visible ? 'fa-eye' : 'fa-eye-slash'"></i>
                            </button>
                        </div>
                        @error('currentPassword')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group password-container" x-data="{ visible: false }">
                        <label for="usu-passPassword">Nueva Contraseña <span class="required">*</span></label>
                        <div class="password-input-wrap">
                            <input :type="visible ? 'text' : 'password'" id="usu-passPassword" wire:model="password" maxlength="100" placeholder="Ingrese su nueva contraseña" autocomplete="new-password">
                            <button type="button" class="toggle-password" @click="visible = !visible" :aria-label="visible ? 'Ocultar nueva contraseña' : 'Mostrar nueva contraseña'">
                                <i class="fas" :class="visible ? 'fa-eye' : 'fa-eye-slash'"></i>
                            </button>
                        </div>
                        <small class="password-hint">Mínimo 8 caracteres</small>
                        @error('password')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group password-container" x-data="{ visible: false }">
                        <label for="usu-passConfirmPassword">Confirmar Nueva Contraseña <span class="required">*</span></label>
                        <div class="password-input-wrap">
                            <input :type="visible ? 'text' : 'password'" id="usu-passConfirmPassword" wire:model="password_confirmation" maxlength="100" placeholder="Confirme su nueva contraseña" autocomplete="new-password">
                            <button type="button" class="toggle-password" @click="visible = !visible" :aria-label="visible ? 'Ocultar confirmación' : 'Mostrar confirmación'">
                                <i class="fas" :class="visible ? 'fa-eye' : 'fa-eye-slash'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="update">
                        <span wire:loading.remove wire:target="update"><i class="fas fa-save"></i> Actualizar Contraseña</span>
                        <span wire:loading wire:target="update"><span class="loading-spinner"></span> Actualizando...</span>
                    </button>
                    <button type="button" wire:click="clearForm" class="btn btn-secondary"><i class="fas fa-broom"></i> <span>Limpiar</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
