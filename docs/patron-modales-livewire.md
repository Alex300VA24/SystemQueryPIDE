# Patrón de modales con Livewire 3 y Alpine.js

## Modal CUI implementado

Archivos:

- `app/Livewire/ValidarCuiModal.php`: estado, validación y eventos.
- `resources/views/livewire/validar-cui-modal.blade.php`: HTML legacy adaptado.
- `resources/views/livewire/pages/auth/login.blade.php`: componente padre que abre el modal y valida la identidad.

Flujo:

1. Login valida usuario y contraseña.
2. Padre crea un desafío CUI temporal en sesión con duración máxima de cinco minutos; el identificador no se pasa al modal ni al navegador.
3. Padre emite `open-validar-cui-modal` dirigido a `ValidarCuiModal`.
4. Modal valida formato y comprueba el CUI usando el desafío guardado en sesión.
5. Si falla, conserva el modal y muestra el error. Si pasa, inicia sesión, elimina el desafío y redirige al dashboard desde la misma petición.

## Plantilla reutilizable

Estado mínimo:

```php
public bool $showModal = false;
public ?int $recordId = null;

#[On('open-record-modal')]
public function openModal(?int $recordId = null): void
{
    $this->resetValidation();
    $this->recordId = $recordId;
    $this->showModal = true;
}

public function closeModal(): void
{
    $this->resetValidation();
    $this->reset('recordId');
    $this->showModal = false;
}

public function confirm(): void
{
    $this->validate();
    $this->dispatch('recordConfirmed', recordId: $this->recordId);
    $this->closeModal();
}
```

Para datos sensibles o identificadores que autorizan acciones, no confíe en parámetros enviados por un evento del navegador. Use una propiedad `#[Locked]`, vuelva a autorizar el registro en `confirm()`, o mantenga el identificador en el componente padre como hace el login CUI.

Base Blade:

```blade
<div
    class="modalCUI {{ $showModal ? 'active' : '' }}"
    style="{{ $showModal ? '' : 'display: none !important;' }}"
    wire:click.self="closeModal"
    x-data
    x-effect="if ($wire.showModal) $nextTick(() => $refs.firstField.focus())"
    @keydown.escape.window="if ($wire.showModal) $wire.closeModal()"
>
    <div class="modal-dialog" wire:click.self="closeModal">
        <div class="modal-content">
            <form wire:submit="confirm">
                <input x-ref="firstField" wire:model="value">
                @error('value') <p role="alert">{{ $message }}</p> @enderror

                <div class="containerButtonsModals">
                    <button type="button" class="btn btn-cancel" wire:click="closeModal">Cancelar</button>
                    <button type="submit" class="btn btn-submit" wire:loading.attr="disabled">
                        <span wire:loading.remove>Confirmar</span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

Abrir desde componente padre:

```php
$this->dispatch('open-record-modal', recordId: $id)
    ->to(RecordModal::class);
```

Escuchar respuesta en padre:

```php
#[On('recordConfirmed')]
public function recordConfirmed(int $recordId): void
{
    // Autorizar y ejecutar acción.
}
```

## Ejemplo: modal de confirmación simple

Clase:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ConfirmacionModal extends Component
{
    public bool $showModal = false;
    public ?int $recordId = null;
    public string $message = '¿Desea continuar?';

    #[On('open-confirmacion-modal')]
    public function openModal(int $recordId, string $message = '¿Desea continuar?'): void
    {
        $this->recordId = $recordId;
        $this->message = $message;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset('showModal', 'recordId');
    }

    public function confirm(): void
    {
        $this->dispatch('confirmed', recordId: $this->recordId);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.confirmacion-modal');
    }
}
```

Vista:

```blade
<div
    class="modalCUI {{ $showModal ? 'active' : '' }}"
    style="{{ $showModal ? '' : 'display: none !important;' }}"
    wire:click.self="closeModal"
    x-data
    x-effect="if ($wire.showModal) $nextTick(() => $refs.confirm.focus())"
    @keydown.escape.window="if ($wire.showModal) $wire.closeModal()"
>
    <div class="modal-dialog" wire:click.self="closeModal">
        <section class="modal-content" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <div class="modal-header">
                <h2 id="confirm-title" class="modal-title">Confirmación</h2>
            </div>
            <div class="modal-body"><p>{{ $message }}</p></div>
            <div class="containerButtonsModals">
                <button type="button" class="btn btn-cancel" wire:click="closeModal">Cancelar</button>
                <button x-ref="confirm" type="button" class="btn btn-submit" wire:click="confirm" wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirmar</span>
                    <span wire:loading>Procesando...</span>
                </button>
            </div>
        </section>
    </div>
</div>
```

Mantenga nombres `modalCUI`, `modal-dialog`, `modal-content`, `modal-header`, `modal-body`, `containerButtonsModals`, `btn`, `btn-submit` y `btn-cancel` para reutilizar CSS actual sin cambios.

## Modal de contraseña PIDE (flujo dividido en dos componentes)

Archivos:

- `app/Livewire/PideCredentialModal.php` + `resources/views/livewire/pide-credential-modal.blade.php`: pide la contraseña PIDE vigente cuando la consulta aún no la tiene en sesión.
- `app/Livewire/PidePasswordModal.php` + `resources/views/livewire/pide-password-modal.blade.php`: renueva la contraseña PIDE contra RENIEC cuando la credencial guardada caducó o fue rechazada.
- `app/Livewire/BaseConsultation.php`: componente base de las consultas (RENIEC, SUNAT, SUNARP); dispara y escucha ambos modales.

Por qué son dos componentes y no uno: `PideCredentialModal` solo captura una contraseña y la guarda en sesión (`session(['pide_password' => $password])`); no valida nada contra RENIEC. `PidePasswordModal` sí ejecuta `ReniecServiceInterface::actualizarPasswordRENIEC()` con DNI + contraseña actual + contraseña nueva, y solo actualiza la sesión si RENIEC confirma el cambio. Mezclar ambos casos en un componente obligaría a validar campos que no siempre aplican (contraseña actual/nueva/confirmación cuando solo hace falta capturar una contraseña existente).

Flujo:

1. `BaseConsultation::search()` valida el formulario de consulta. Si el `page()` de la consulta declara `needsCredentials` y `pidePassword` está vacío en el componente, dispara `open-pide-credential-modal` (sin argumentos) y detiene la búsqueda.
2. `PideCredentialModal::save()` valida la contraseña, la guarda en `session('pide_password')` y emite `pide-credential-saved` con la contraseña en el payload.
3. `BaseConsultation::onPideCredentialSaved()` escucha ese evento (`#[On('pide-credential-saved')]`), actualiza `$this->pidePassword` y reintenta `search()` automáticamente.
4. Si la consulta real (`attemptReal()`) marca `pideCredentialExpired = true` (la contraseña guardada fue rechazada por RENIEC), `search()` olvida `session('pide_password')`, limpia `$this->pidePassword` y dispara `open-pide-password-modal` con el `dniUsuario` y el mensaje de error como argumentos.
5. `PidePasswordModal::update()` valida DNI + credencial anterior + credencial nueva (confirmada, distinta de la anterior), llama a `ReniecServiceInterface::actualizarPasswordRENIEC()`. Si RENIEC rechaza el cambio, muestra el error y mantiene el modal abierto. Si acepta, guarda la nueva contraseña en sesión y vuelve a emitir `pide-credential-saved`, que dispara el mismo reintento automático del paso 3.

Los dos modales terminan en el mismo evento (`pide-credential-saved`) para que `BaseConsultation` no necesite saber cuál de los dos lo resolvió.
