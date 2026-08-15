<div
    id="modalValidarCUI"
    class="modalCUI modal fade"
    style="{{ $showModal ? '' : 'display: none !important;' }}"
    tabindex="-1"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cui-modal-title"
    aria-describedby="cui-modal-description"
    :class="{ 'active': open }"
    :aria-hidden="(!open).toString()"
    x-cloak
    x-show.important="open"
    @click.self="closeOptimistically()"
    x-data="{
        open: @entangle('showModal'),
        confirmPending: false,
        closeOptimistically() {
            if (!this.open) return;

            this.open = false;
            this.$nextTick(() => this.$wire.closeModal());
        },
        focusables() {
            return [...$el.querySelectorAll('button:not([disabled]), input:not([disabled])')];
        },
        moveFocus(backwards = false) {
            const items = this.focusables();
            if (!items.length) return;

            const current = items.indexOf(document.activeElement);
            const next = backwards
                ? (current <= 0 ? items.length - 1 : current - 1)
                : (current + 1) % items.length;

            items[next].focus();
        }
    }"
    x-effect="if (open) { confirmPending = false; $nextTick(() => $refs.cui.focus()) } else { confirmPending = false }"
    @cui-action-finished.window="confirmPending = false"
    @keydown.escape.window="closeOptimistically()"
    @keydown.tab.prevent="if (open) moveFocus($event.shiftKey)"
>
    <div class="modal-dialog modal-dialog-centered cui-modal-dialog" role="document" @click.self="closeOptimistically()">
        <div class="modal-content cui-modal-content">
            <div class="modal-header cui-modal-header">
                <div class="cui-modal-title-row">
                    <i class="fas fa-shield-halved" aria-hidden="true"></i>
                    <h2 class="modal-title" id="cui-modal-title">Autenticación de Doble Factor — CUI</h2>
                </div>
            </div>

            <form
                class="formValidarCUI cui-modal-form"
                id="validarCUIForm"
                wire:submit="confirmCui"
                novalidate
                @submit="confirmPending = true"
            >
                <div class="modal-body">
                    <p id="cui-modal-description" class="cui-modal-description">
                        Busca tu Código Único de Identificación (CUI) en tu DNI e ingrésalo a continuación.
                    </p>

                    <div class="containerGuiaCUI">
                        <img src="{{ asset('assets/images/dniGuiCUI.svg') }}" alt="Guía ubicación CUI en DNI">
                    </div>

                    <div class="containerCUI">
                        <label for="cui">Código único de Identificación (CUI):</label>
                        <input
                            x-ref="cui"
                            wire:model="cui"
                            class="cui-input"
                            type="text"
                            id="cui"
                            name="cui"
                            maxlength="1"
                            inputmode="numeric"
                            autocomplete="off"
                            pattern="[0-9]"
                            required
                            aria-describedby="cui-error"
                            @input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 1)"
                        >
                        @error('cui')
                            <p id="cui-error" class="legacy-field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="containerButtonsModals">
                        <button
                            type="button"
                            id="btnCancelarCUI"
                            class="btn btn-cancel"
                            @click="closeOptimistically()"
                            :disabled="confirmPending"
                        >Cancelar</button>
                        <button
                            type="submit"
                            id="btnConfirmarCUI"
                            class="btn btn-submit"
                            :disabled="confirmPending"
                            wire:loading.attr="disabled"
                            wire:target="confirmCui"
                        >
                            <span x-show="!confirmPending">Confirmar</span>
                            <span x-cloak x-show="confirmPending">
                                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Validando...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
