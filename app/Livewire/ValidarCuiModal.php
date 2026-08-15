<?php

namespace App\Livewire;

use App\Auth\PendingCuiAuthentication;
use App\Http\Requests\ValidarCuiRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class ValidarCuiModal extends Component
{
    public bool $showModal = false;

    public string $cui = '';

    protected function rules(): array
    {
        return ValidarCuiRequest::buildRules();
    }

    protected function messages(): array
    {
        return ValidarCuiRequest::validationMessages();
    }

    #[On('open-validar-cui-modal')]
    public function openModal(): void
    {
        $this->resetValidation();
        $this->reset('cui');
        $this->showModal = true;

        $this->dispatch('login-transition-complete');
    }

    #[On('close-validar-cui-modal')]
    public function closeModal(): void
    {
        $this->resetValidation();
        $this->reset('cui');
        $this->showModal = false;
        app(PendingCuiAuthentication::class)->clear();

        $this->dispatch('cuiModalClosed');
    }

    public function confirmCui(): void
    {
        try {
            $validated = $this->validate();
            app(PendingCuiAuthentication::class)->authenticatePending((string) $validated['cui']);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            $this->dispatch('cui-action-finished');

            return;
        }

        $this->dispatch('cuiValidated', cui: (string) $validated['cui']);

        Session::regenerate();

        $this->redirectIntended(default: url(RouteServiceProvider::HOME));
    }

    public function render()
    {
        return view('livewire.validar-cui-modal');
    }
}
