<div
    class="dashboard-container"
    x-data="{
        navigationOpen: $wire.entangle('navigationOpen').live,
        logoutOpen: false,
        logoutSubmitting: false,
        sidebarCollapsed: (window.innerWidth > 768 && localStorage.getItem('sidebar_collapsed') === 'true'),
        toggleCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
        },
        openLogout() {
            if (this.logoutSubmitting) {
                return;
            }

            this.logoutOpen = true;
        },
        closeLogout() {
            this.logoutOpen = false;
            this.logoutSubmitting = false;
        },
        submitLogout() {
            if (this.logoutSubmitting) {
                return;
            }

            this.logoutSubmitting = true;
            this.$nextTick(() => {
                const form = this.$root.querySelector('[data-logout-form]');
                if (form) {
                    form.submit();
                }
            });
        },
    }"
    x-init="$watch('navigationOpen', value => document.body.classList.toggle('overflow-y-hidden', value))"
    @close-dashboard-navigation.window="navigationOpen = false"
    @keydown.escape.window="navigationOpen = false; logoutOpen = false"
>
    <button type="button" class="mobile-menu-btn" @click="navigationOpen = true" aria-label="Abrir menú" :aria-expanded="navigationOpen.toString()">
        <x-icon name="menu" />
    </button>

    <div class="sidebar-overlay" x-cloak x-show="navigationOpen" x-transition.opacity @click="navigationOpen = false" aria-hidden="true"></div>

    <aside class="sideBar" :class="{ 'mobile-open': navigationOpen, 'collapsed': sidebarCollapsed }" aria-label="Navegación principal">
        <header class="sidebar-header">
            <button type="button" class="sidebar-close-btn" @click="navigationOpen = false" aria-label="Cerrar menú"><x-icon name="close" /></button>
            <img class="sidebar-logo" src="{{ asset('assets/images/muni2.png') }}" alt="Municipalidad Distrital de El Tambo">
            <div class="sidebar-brand-text">
                <div class="sidebar-title">MDE</div>
                <div class="sidebar-subtitle">Sistema PIDE</div>
            </div>
        </header>

        <nav class="sidebar-nav">
            @forelse ($sections as $module)
                @if (empty($module['children']))
                    <div class="option-wrap">
                        <button
                            type="button"
                            wire:click="selectSection('{{ $module['key'] }}')"
                            @click="navigationOpen = false"
                            class="option {{ $activeSection === $module['key'] ? 'active' : '' }}"
                            @if ($activeSection === $module['key']) aria-current="page" @endif
                        >
                            <span class="containerIconOption"><x-icon :name="$module['icon']" /></span>
                            <span class="sidebar-label">{{ $module['label'] }}</span>
                        </button>
                        <span class="option-tooltip">{{ $module['label'] }}</span>
                    </div>
                @else
                    <div x-data="{ open: {{ collect($module['children'])->contains('key', $activeSection) ? 'true' : 'false' }} }" class="sidebar-branch option-wrap">
                        <button
                            type="button"
                            class="option has-submenu"
                            :class="{ 'open': open }"
                            @click="if (sidebarCollapsed) { sidebarCollapsed = false; localStorage.setItem('sidebar_collapsed', false); open = true; } else { open = !open; }"
                            :aria-expanded="open.toString()"
                        >
                            <span class="containerIconOption"><x-icon :name="$module['icon']" /></span>
                            <span class="sidebar-label">{{ $module['label'] }}</span>
                            <span class="submenu-icon" aria-hidden="true"><x-icon name="chevron" /></span>
                        </button>
                        <span class="option-tooltip">{{ $module['label'] }}</span>
                        <div class="submenu" x-cloak x-show="open" x-transition.origin.top>
                            @foreach ($module['children'] as $child)
                                <button
                                    type="button"
                                    wire:click="selectSection('{{ $child['key'] }}')"
                                    @click="navigationOpen = false"
                                    class="suboption {{ $activeSection === $child['key'] ? 'active' : '' }}"
                                    @if ($activeSection === $child['key']) aria-current="page" @endif
                                >
                                    <span><x-icon :name="$child['icon']" /></span>
                                    <span>{{ $child['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <p class="navigation-empty">No existen módulos asignados.</p>
            @endforelse
        </nav>

        <div class="user-section">
            <div class="user-info">
                <span class="user-avatar">{{ mb_substr(auth()->user()->username, 0, 1) }}</span>
                <span class="user-details">
                    <strong class="user-name">{{ auth()->user()->username }}</strong>
                    <small class="user-role">{{ auth()->user()->roles->pluck('nombre')->join(', ') ?: 'Sin rol' }}</small>
                </span>
            </div>
            <button type="button" class="logout-btn" @click="openLogout()"><x-icon name="logout" /><span class="logout-text">Cerrar Sesión</span></button>
        </div>
    </aside>

    <button type="button" class="sidebar-toggle-btn" x-show="!navigationOpen" @click="toggleCollapse()" :style="{ left: (sidebarCollapsed ? 62 : 258) + 'px' }" aria-label="Contraer/Expandir menú">
        <span :class="{ 'is-collapsed': sidebarCollapsed }" class="toggle-icon"><x-icon name="collapse" /></span>
    </button>

    <main id="contenido" class="main-content" :class="{ 'collapsed': sidebarCollapsed }" tabindex="-1" aria-live="polite">
        <header class="dashboard-header glass">
            <div class="dashboard-header-brand">
                <img src="{{ asset('assets/images/logo_pide.png') }}" alt="Logo" class="dashboard-header-logo">
                <div>
                    <h1>Sistema de Consultas PIDE</h1>
                    <p>Plataforma de Interoperabilidad del Estado Peruano</p>
                </div>
            </div>
            <div class="dashboard-header-meta">
                <span class="header-date"><x-icon name="calendar" />{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </header>

        <div class="spa-status" wire:loading.flex wire:target="selectSection" role="status" aria-live="polite">
            <div class="spa-status-card">
                <img class="spa-status-logo" src="{{ asset('assets/images/logo_pide.png') }}" alt="Logo PIDE">
                <h2 class="spa-status-title">Cargando sección</h2>
                <p class="spa-status-subtitle">Preparando el módulo de {{ $this->sectionTitle() }}…</p>
                <div class="spa-status-progress" aria-hidden="true"><span></span></div>
            </div>
        </div>
        <div wire:loading.remove wire:target="selectSection" class="page-content active" wire:key="section-{{ $activeSection }}">
            @switch($activeSection)
                @case('dni') <livewire:consulta-dni wire:key="consulta-dni" /> @break
                @case('ruc') <livewire:consulta-ruc wire:key="consulta-ruc" /> @break
                @case('ccoactiva') <livewire:consulta-coactiva wire:key="consulta-coactiva" /> @break
                @case('cert-ambientales') <livewire:consulta-cambientales wire:key="consulta-cambientales" /> @break
                @case('partidas') <livewire:consulta-partidas wire:key="consulta-partidas" /> @break
                @case('papeletas') <livewire:consulta-papeletas wire:key="consulta-papeletas" /> @break
                @case('mtc') <livewire:consulta-mtc wire:key="consulta-mtc" /> @break
                @case('usuarios') <livewire:gestion-usuarios wire:key="gestion-usuarios" /> @break
                @case('roles') <livewire:gestion-roles wire:key="gestion-roles" /> @break
                @case('modulos') <livewire:gestion-modulos wire:key="gestion-modulos" /> @break
                @case('password') @include('livewire.sections.password') @break
                @case('ayuda') <livewire:ayuda wire:key="ayuda" /> @break
                @case('inicio') @include('livewire.sections.inicio') @break
                @default
                    @include('livewire.sections.construccion')
            @endswitch
        </div>
    </main>

    <div data-ui-modal class="modal-overlay" x-cloak x-show="logoutOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="presentation" @click.self="closeLogout()">
        <section class="modal-content" role="dialog" aria-modal="true" aria-labelledby="logout-title" x-show="logoutOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95">
            <div class="modal-symbol danger"><x-icon name="logout" /></div>
            <h2 id="logout-title">¿Cerrar sesión?</h2>
            <p>¿Deseas cerrar la sesión actual?</p>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" @click="closeLogout()">Cancelar</button>
                <form method="POST" action="{{ route('logout') }}" data-logout-form @submit.prevent="submitLogout()">
                    @csrf
                    <button type="submit" class="btn-logout" :disabled="logoutSubmitting" x-text="logoutSubmitting ? 'Cerrando...' : 'Cerrar sesión'"></button>
                </form>
            </div>
        </section>
    </div>

    <livewire:pide-password-modal wire:key="pide-password-modal" />
    <livewire:pide-credential-modal wire:key="pide-credential-modal" />
</div>
