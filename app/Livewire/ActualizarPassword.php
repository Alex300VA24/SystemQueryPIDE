<?php

namespace App\Livewire;

use App\Http\Requests\ActualizarPasswordRequest;
use Illuminate\Support\Facades\Hash;
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

    public function update()
    {
        $usuario = auth()->user();

        $this->validate(
            ActualizarPasswordRequest::buildRules(),
            ActualizarPasswordRequest::validationMessages(),
            ActualizarPasswordRequest::validationAttributes(),
        );

        $usuario->forceFill([
            'password_hash' => Hash::make($this->password),
            'requiere_cambio_password' => false,
            'fecha_actualizacion_password' => now(),
        ])->save();

        $this->clearForm();

        $this->dispatch('pide-alert', message: 'Contraseña actualizada correctamente.', type: 'success');
        $this->dispatch('password-updated');
    }

    public function render()
    {
        return view('livewire.actualizar-password');
    }
}
