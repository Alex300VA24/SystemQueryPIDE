<?php

namespace App\Livewire;

use App\Http\Requests\PideCredentialRequest;
use App\Services\Pide\PideCredentialStore;
use Livewire\Attributes\On;
use Livewire\Component;

final class PideCredentialModal extends Component
{
    public bool $showModal = false;

    public string $password = '';

    #[On('open-pide-credential-modal')]
    public function openModal(): void
    {
        $this->resetValidation();
        $this->reset('password');
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->resetValidation();
        $this->reset('password');
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate(
            PideCredentialRequest::buildRules(),
            PideCredentialRequest::validationMessages(),
            PideCredentialRequest::validationAttributes(),
        );

        app(PideCredentialStore::class)->store($this->password);

        $this->closeModal();

        $this->dispatch('pide-credential-saved');
    }

    public function render()
    {
        return view('livewire.pide-credential-modal');
    }
}
