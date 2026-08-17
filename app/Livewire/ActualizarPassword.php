<?php

namespace App\Livewire;

use App\Http\Requests\ActualizarPasswordRequest;
use App\Services\ChangeUserPasswordService;
use Livewire\Component;

final class ActualizarPassword extends Component
{
    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function clearForm(): void
    {
        $this->reset('currentPassword', 'password', 'password_confirmation');
        $this->resetValidation();
    }

    public function update(ChangeUserPasswordService $service)
    {
        $usuario = auth()->user();

        $this->validate(
            ActualizarPasswordRequest::buildRules(),
            ActualizarPasswordRequest::validationMessages(),
            ActualizarPasswordRequest::validationAttributes(),
        );

        $currentPassword = $this->currentPassword;
        $newPassword = $this->password;

        $service->change($usuario, $currentPassword, $newPassword);

        $this->clearForm();

        $this->dispatch('pide-alert', message: 'Contraseña actualizada correctamente.', type: 'success');
        $this->dispatch('password-updated');
    }

    public function render()
    {
        return view('livewire.actualizar-password');
    }
}
