<?php

namespace App\Livewire;

use Livewire\Component;

abstract class BaseManagement extends Component
{
    public string $search = '';

    public bool $modalOpen = false;

    public string $name = '';

    public string $code = '';

    public array $items = [];

    abstract protected function page(): array;

    public function mount(): void
    {
        $this->items = $this->page()['items'];
    }

    public function openCreate(): void
    {
        $this->reset('name', 'code');
        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|min:3|max:80', 'code' => 'required|alpha_dash|max:20'], [], ['name' => 'nombre', 'code' => 'código']);
        $this->items[] = ['code' => strtoupper($this->code), 'name' => $this->name, 'detail' => 'Creado en esta sesión', 'status' => 'Activo'];
        $this->modalOpen = false;
        $this->dispatch('demo-saved');
    }

    public function getFilteredItemsProperty(): array
    {
        if ($this->search === '') {
            return $this->items;
        }

        return array_values(array_filter($this->items, fn ($item) => str_contains(mb_strtolower(implode(' ', $item)), mb_strtolower($this->search))));
    }

    public function render()
    {
        return view('livewire.management', ['page' => $this->page(), 'filtered' => $this->filteredItems]);
    }
}
