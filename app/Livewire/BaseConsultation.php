<?php

namespace App\Livewire;

use App\Http\Requests\ConsultaRequest;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

abstract class BaseConsultation extends Component
{
    public string $busqueda = '';

    public string $dniUsuario = '';

    public string $pidePassword = '';

    public bool $searched = false;

    public bool $real = false;

    public string $oficina = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $result = [];

    public ?string $photo = null;

    public bool $pideCredentialExpired = false;

    public function mount(): void
    {
        $this->dniUsuario = (string) (auth()->user()?->persona?->documento_numero ?? '');
        $this->pidePassword = (string) session('pide_password', '');
    }

    abstract protected function page(): array;

    /**
     * Intenta la consulta real contra PIDE. Devuelve null si el componente
     * no tiene integración real o si la consulta no fue exitosa.
     */
    protected function attemptReal(): ?array
    {
        return null;
    }

    #[On('pide-credential-saved')]
    public function onPideCredentialSaved(string $pidePassword): void
    {
        $this->pidePassword = $pidePassword;
        $this->search();
    }

    public function search(): void
    {
        $needsCredentials = $this->page()['needsCredentials'] ?? false;
        $needsOficina = $this->page()['needsOficina'] ?? false;

        $this->validate(
            ConsultaRequest::buildRules($this->page()['rules'], $needsCredentials, $needsOficina),
            ConsultaRequest::validationMessages(),
            ConsultaRequest::validationAttributes($this->page()['field']),
        );

        if (($this->page()['needsCredentials'] ?? false) && trim($this->pidePassword) === '') {
            $this->dispatch('open-pide-credential-modal');

            return;
        }

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->photo = null;
        $this->pideCredentialExpired = false;
        $real = null;

        try {
            $real = $this->attemptReal();
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo conectar con el servicio PIDE. Inténtalo nuevamente.';
        }

        if ($real !== null) {
            $this->result = $real;
            $this->real = true;
            $this->successMessage = 'Consulta realizada exitosamente.';
        } else {
            $this->errorMessage ??= 'Servicio PIDE no disponible en este momento.';
            $this->result = $this->page()['result'];
            $this->real = false;
        }

        if ($this->pideCredentialExpired) {
            session()->forget('pide_password');
            $this->pidePassword = '';
            $this->dispatch('open-pide-password-modal', dniUsuario: $this->dniUsuario, message: $this->errorMessage);
        }

        $this->searched = true;
    }

    public function resetSearch(): void
    {
        $this->reset('busqueda', 'searched', 'result', 'real', 'errorMessage', 'successMessage', 'photo', 'oficina');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.consulta', ['page' => $this->page()]);
    }
}
