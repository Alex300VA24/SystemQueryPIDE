<?php

namespace App\Livewire;

use App\Support\DashboardNavigation;
use Livewire\Attributes\On;
use Livewire\Component;

class Dashboard extends Component
{
    private const COMPONENTS = [
        'dni' => ConsultaDni::class,
        'ruc' => ConsultaRuc::class,
        'contribuyente' => ConsultaRuc::class,
        'ccoactiva' => ConsultaCoactiva::class,
        'cert-ambientales' => ConsultaCambientales::class,
        'partidas' => ConsultaPartidas::class,
        'papeletas' => ConsultaPapeletas::class,
        'mtc' => ConsultaMtc::class,
        'usuarios' => GestionUsuarios::class,
        'roles' => GestionRoles::class,
        'modulos' => GestionModulos::class,
        'ayuda' => Ayuda::class,
    ];

    public array $sections = [];

    public string $activeSection = 'inicio';

    public string $activeTab = '';

    public bool $navigationOpen = false;

    public function mount(array $sections = []): void
    {
        $this->sections = $sections ?: app(DashboardNavigation::class)->forUser(auth()->user());

        if (auth()->user()->requiere_cambio_password) {
            $this->activeSection = 'password';
            $this->activeTab = '';

            return;
        }

        $keys = $this->navigationKeys();
        $stored = session('dashboard_active_section');

        $this->activeSection = ($stored && in_array($stored, $keys, true))
            ? $stored
            : (in_array('inicio', $keys, true) ? 'inicio' : ($keys[0] ?? 'inicio'));
        $this->activeTab = (string) session('dashboard_active_tab', '');

        $this->validateActiveTab();
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

        $this->validateActiveTab();
    }

    #[On('password-updated')]
    public function onPasswordUpdated(): void
    {
        $keys = $this->navigationKeys();
        $this->activeSection = in_array('inicio', $keys, true) ? 'inicio' : ($keys[0] ?? 'inicio');
        $this->activeTab = '';
        session(['dashboard_active_section' => $this->activeSection]);
        session()->forget('dashboard_active_tab');

        $this->validateActiveTab();
    }

    public function selectSection(string $section, ?string $tab = null): void
    {
        if (auth()->user()->requiere_cambio_password) {
            if ($section !== 'password') {
                $this->dispatch('pide-alert', message: 'Debes actualizar tu contraseña antes de continuar navegando.', type: 'warning');
            }
            $this->activeSection = 'password';
            $this->activeTab = '';
            session(['dashboard_active_section' => 'password']);
            session()->forget('dashboard_active_tab');

            return;
        }

        if (! in_array($section, $this->navigationKeys(), true)) {
            $this->dispatch('pide-alert', message: 'No tienes acceso a este módulo.', type: 'warning');

            return;
        }

        $this->activeSection = $section;
        session(['dashboard_active_section' => $section]);

        $module = $this->activeModule();
        $tabKeys = array_column($module['tabs'] ?? [], 'key');

        $this->activeTab = $tabKeys !== []
            ? (($tab !== null && $tab !== '' && in_array($tab, $tabKeys, true)) ? $tab : $tabKeys[0])
            : '';
        session(['dashboard_active_tab' => $this->activeTab]);

        $this->navigationOpen = false;
        $this->dispatch('dashboard-section-changed', title: $this->sectionTitle());
        $this->dispatch('close-dashboard-navigation');
    }

    public function selectTab(string $tab): void
    {
        $module = $this->activeModule();
        $tabKeys = array_column($module['tabs'] ?? [], 'key');

        if (! in_array($tab, $tabKeys, true)) {
            $this->dispatch('pide-alert', message: 'No tienes acceso a esta opción.', type: 'warning');

            return;
        }

        $this->activeTab = $tab;
        session(['dashboard_active_tab' => $tab]);
        $this->dispatch('dashboard-section-changed', title: $this->sectionTitle());
    }

    public function sectionTitle(): string
    {
        $module = $this->activeModule();

        if ($module === []) {
            return 'Inicio';
        }

        $label = $module['label'] ?? '';

        if ($this->activeTab !== '') {
            foreach ($module['tabs'] ?? [] as $tab) {
                if ($tab['key'] === $this->activeTab) {
                    return trim($label.' · '.($tab['label'] ?? ''));
                }
            }
        }

        return $label;
    }

    public function renderKey(): string
    {
        return $this->activeTab !== '' ? $this->activeTab : $this->activeSection;
    }

    public function componentFor(string $key): ?string
    {
        return self::COMPONENTS[$key] ?? null;
    }

    public function canReach(string $key): bool
    {
        return in_array($key, $this->navigationKeys(), true) || in_array($key, $this->allTabKeys(), true);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    public function activeModule(): array
    {
        foreach ($this->sections as $module) {
            if ($module['key'] === $this->activeSection) {
                return $module;
            }

            foreach ($module['children'] as $child) {
                if ($child['key'] === $this->activeSection) {
                    return $child;
                }
            }
        }

        return [];
    }

    private function validateActiveTab(): void
    {
        $module = $this->activeModule();
        $tabKeys = array_column($module['tabs'] ?? [], 'key');

        if ($tabKeys === []) {
            $this->activeTab = '';
            session()->forget('dashboard_active_tab');

            return;
        }

        if (! in_array($this->activeTab, $tabKeys, true)) {
            $this->activeTab = $tabKeys[0];
        }

        session(['dashboard_active_tab' => $this->activeTab]);
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

    private function allTabKeys(): array
    {
        return collect($this->sections)
            ->flatMap(fn (array $module) => [
                ...array_column($module['tabs'] ?? [], 'key'),
                ...collect($module['children'] ?? [])->flatMap(fn (array $child) => array_column($child['tabs'] ?? [], 'key'))->all(),
            ])
            ->filter()
            ->values()
            ->all();
    }
}
