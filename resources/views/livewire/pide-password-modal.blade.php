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
        aria-labelledby="pide-password-title"
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
    >
        <div class="modal-symbol danger"><i class="fas fa-key"></i></div>
        <h2 id="pide-password-title">Actualizar contraseña PIDE</h2>
        <p>Tu contraseña del servicio PIDE (RENIEC) ha caducado o no es válida. Debes actualizarla para poder seguir realizando consultas.</p>

        <form wire:submit="update" novalidate class="modal-form">
            <div class="form-group">
                <label for="pide-dni-usuario">DNI del usuario PIDE</label>
                <input id="pide-dni-usuario" wire:model="dniUsuario" maxlength="8" inputmode="numeric" autocomplete="username">
                @error('dniUsuario')<span class="field-error" role="alert">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="pide-credencial-anterior">Contraseña PIDE actual</label>
                <input id="pide-credencial-anterior" type="password" wire:model="credencialAnterior" autocomplete="current-password">
                @error('credencialAnterior')<span class="field-error" role="alert">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="pide-credencial-nueva">Nueva contraseña PIDE</label>
                <input id="pide-credencial-nueva" type="password" wire:model="credencialNueva" autocomplete="new-password">
                @error('credencialNueva')<span class="field-error" role="alert">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="pide-credencial-nueva-confirmation">Confirmar nueva contraseña PIDE</label>
                <input id="pide-credencial-nueva-confirmation" type="password" wire:model="credencialNueva_confirmation" autocomplete="new-password">
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" @click="open = false" wire:click="closeModal">Cancelar</button>
                <button type="submit" class="btn-logout" wire:loading.attr="disabled" wire:target="update">
                    <span wire:loading.remove wire:target="update">Actualizar</span>
                    <span wire:loading wire:target="update"><span class="loading-spinner"></span> Actualizando...</span>
                </button>
            </div>
        </form>
    </section>
</div>
