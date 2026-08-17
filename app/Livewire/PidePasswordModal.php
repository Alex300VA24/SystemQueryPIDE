<?php

namespace App\Livewire;

use App\Http\Requests\PidePasswordRequest;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\PideCredentialStore;
use Livewire\Attributes\On;
use Livewire\Component;

final class PidePasswordModal extends Component
{
    public bool $showModal = false;

    public string $dniUsuario = '';

    public string $credencialAnterior = '';

    public string $credencialNueva = '';

    public string $credencialNueva_confirmation = '';

    #[On('open-pide-password-modal')]
    public function openModal(string $dniUsuario = '', ?string $message = null): void
    {
        $this->resetValidation();
        $this->reset('credencialAnterior', 'credencialNueva', 'credencialNueva_confirmation');
        $this->dniUsuario = $dniUsuario;
        $this->showModal = true;

        $this->dispatch(
            'pide-alert',
            message: $message ?: 'Tu contraseña PIDE ha caducado o no es válida. Actualízala para continuar realizando consultas.',
            type: 'warning',
        );
    }

    public function closeModal(): void
    {
        $this->resetValidation();
        $this->reset('credencialAnterior', 'credencialNueva', 'credencialNueva_confirmation');
        $this->showModal = false;
    }

    public function update(ReniecServiceInterface $reniecService): void
    {
        $this->validate(
            PidePasswordRequest::buildRules(),
            PidePasswordRequest::validationMessages(),
            PidePasswordRequest::validationAttributes(),
        );

        $resultado = $reniecService->actualizarPasswordRENIEC(
            $this->credencialAnterior,
            $this->credencialNueva,
            $this->dniUsuario,
        );

        if (! ($resultado['success'] ?? false)) {
            $this->dispatch('pide-alert', message: $resultado['message'] ?? 'No se pudo actualizar la contraseña PIDE.', type: 'danger');

            return;
        }

        app(PideCredentialStore::class)->store($this->credencialNueva);

        $this->dispatch('pide-alert', message: 'Contraseña PIDE actualizada correctamente.', type: 'success');
        $this->closeModal();
        $this->dispatch('pide-credential-saved');
    }

    public function render()
    {
        return view('livewire.pide-password-modal');
    }
}
