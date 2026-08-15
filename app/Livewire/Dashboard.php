<?php

namespace App\Livewire;

use App\Support\DashboardNavigation;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    public array $sections = [];

    public string $activeSection = 'inicio';

    public bool $navigationOpen = false;

    public function mount(array $sections = []): void
    {
        $this->sections = $sections ?: app(DashboardNavigation::class)->forUser(auth()->user());

        if (auth()->user()->requiere_cambio_password) {
            $this->activeSection = 'password';

            return;
        }

        $keys = $this->navigationKeys();
        $stored = session('dashboard_active_section');

        $this->activeSection = ($stored && in_array($stored, $keys, true))
            ? $stored
            : (in_array('inicio', $keys, true) ? 'inicio' : ($keys[0] ?? 'inicio'));
    }

    #[On('modulos-updated')]
    public function refreshSections(): void
    {
        $this->sections = app(DashboardNavigation::class)->forUser(auth()->user());

        if (! in_array($this->activeSection, $this->navigationKeys(), true) && $this->activeSection !== 'password') {
            $keys = $this->navigationKeys();
            $this->activeSection = in_array('inicio', $keys, true) ? 'inicio' : ($keys[0] ?? 'inicio');
            session(['dashboard_active_section' => $this->activeSection]);
        }
    }

    #[On('password-updated')]
    public function onPasswordUpdated(): void
    {
        $keys = $this->navigationKeys();
        $this->activeSection = in_array('inicio', $keys, true) ? 'inicio' : ($keys[0] ?? 'inicio');
        session(['dashboard_active_section' => $this->activeSection]);
    }

    public function selectSection(string $section): void
    {
        if (auth()->user()->requiere_cambio_password) {
            if ($section !== 'password') {
                $this->dispatch('pide-alert', message: 'Debes actualizar tu contraseña antes de continuar navegando.', type: 'warning');
            }
            $this->activeSection = 'password';

            return;
        }

        if (! in_array($section, $this->navigationKeys(), true)) {
            $this->dispatch('pide-alert', message: 'No tienes acceso a este módulo.', type: 'warning');

            return;
        }

        $this->activeSection = $section;
        session(['dashboard_active_section' => $section]);
        $this->navigationOpen = false;
        $this->dispatch('dashboard-section-changed', title: $this->sectionTitle());
        $this->dispatch('close-dashboard-navigation');
    }

    public function sectionTitle(): string
    {
        foreach ($this->sections as $module) {
            if ($module['key'] === $this->activeSection) {
                return $module['label'];
            }

            foreach ($module['children'] as $child) {
                if ($child['key'] === $this->activeSection) {
                    return $child['label'];
                }
            }
        }

        return 'Inicio';
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    private function navigationKeys(): array
    {
        return collect($this->sections)
            ->flatMap(fn (array $module) => [
                ...($module['key'] ? [$module['key']] : []),
                ...array_column($module['children'], 'key'),
            ])
            ->filter()
            ->values()
            ->all();
    }
}
