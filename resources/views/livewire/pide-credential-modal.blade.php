<div
    data-ui-modal
    class="modal-overlay"
    x-cloak
    x-show="open"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    role="presentation"
    x-data="{ open: @entangle('showModal') }"
    @click.self="open = false; $wire.closeModal()"
    @keydown.escape.window="if (open) { open = false; $wire.closeModal() }"
>
    <section
        class="modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="pide-credential-title"
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    >
        <div class="modal-symbol"><i class="fas fa-key"></i></div>
        <h2 id="pide-credential-title">Contraseña PIDE</h2>
        <p>Ingresa tu contraseña PIDE para continuar con la consulta. Se guardará solo mientras dure tu sesión.</p>

        <form wire:submit="save" novalidate class="modal-form">
            <div class="form-group password-container" x-data="{ visible: false }">
                <label for="pide-credential-password">Contraseña PIDE</label>
                <div class="password-input-wrap">
                    <input :type="visible ? 'text' : 'password'" id="pide-credential-password" wire:model="password" autocomplete="current-password" autofocus>
                    <button type="button" class="toggle-password" @click="visible = !visible" :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                        <i class="fas" :class="visible ? 'fa-eye' : 'fa-eye-slash'"></i>
                    </button>
                </div>
                @error('password')<span class="field-error" role="alert">{{ $message }}</span>@enderror
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" @click="open = false" wire:click="closeModal">Cancelar</button>
                <button type="submit" class="btn-logout" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Continuar</span>
                    <span wire:loading wire:target="save"><span class="loading-spinner"></span> Guardando...</span>
                </button>
            </div>
        </form>
    </section>
</div>
