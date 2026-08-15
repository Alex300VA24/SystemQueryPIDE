<div
    class="modulo-legacy ayuda-legacy"
    x-data="{
        modalOpen: false,
        activeGroup: null,
        activeItem: null,
        guides: @js($groups),
        active() { return this.activeGroup === null ? null : this.guides[this.activeGroup]?.items[this.activeItem] ?? null; },
        openGuide(g, i) { this.activeGroup = g; this.activeItem = i; this.modalOpen = true; },
        closeGuide() { this.modalOpen = false; },
    }"
    @keydown.escape.window="if (modalOpen) closeGuide()"
>
    <div class="page-title">
        <h1><i class="fas fa-circle-question"></i> Centro de Ayuda</h1>
    </div>

    <div class="ayuda-intro">
        <div>
            <h2>¿Cómo usar el sistema?</h2>
            <p>Guías paso a paso preparadas para todos los usuarios.</p>
        </div>
        <span class="badge badge-info ayuda-version"><i class="fa-solid fa-book-open"></i> Manual v2.0</span>
    </div>

    <div class="content-wrapper ayuda-content">
        @foreach ($groups as $gi => $group)
            <h2 class="section-header"><i class="{{ $group['icon'] }}"></i> {{ $group['label'] }}</h2>

            <div class="ayuda-grid">
                @foreach ($group['items'] as $ii => $item)
                    <button
                        type="button"
                        class="ayuda-card"
                        @click="openGuide({{ $gi }}, {{ $ii }})"
                        aria-haspopup="dialog"
                    >
                        <span class="ayuda-chip {{ $item['chip'] }}"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                        <span class="ayuda-card-title">{{ $item['title'] }}</span>
                        <p class="ayuda-card-desc">{{ $item['desc'] }}</p>
                        <span class="ayuda-card-cta">Ver guía <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>

    <div
        data-ui-modal
        class="modal-backdrop"
        role="presentation"
        x-cloak
        x-show="modalOpen"
        x-transition:enter="ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-effect="if (modalOpen) $nextTick(() => $refs.ayudaModalClose?.focus())"
        @click.self="closeGuide()"
    >
        <section
            class="modal-panel ayuda-modal-panel"
            role="dialog"
            aria-modal="true"
            aria-label="Detalle de la guía"
            x-show="modalOpen"
            x-transition:enter="ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        >
            <template x-if="active()">
                <div>
                    <div class="modal-heading">
                        <div class="ayuda-modal-head">
                            <span class="ayuda-chip" :class="active().chip"><i :class="active().icon" aria-hidden="true"></i></span>
                            <h2 x-text="active().title"></h2>
                        </div>
                        <button type="button" class="modal-close" x-ref="ayudaModalClose" @click="closeGuide()" aria-label="Cerrar guía">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="modal-panel-body ayuda-body">
                        <p x-text="active().desc"></p>

                        <ol class="ayuda-steps">
                            <template x-for="(step, index) in active().steps" :key="index">
                                <li x-text="step"></li>
                            </template>
                        </ol>

                        <ul class="ayuda-tips" x-show="active().tips.length">
                            <template x-for="(tip, index) in active().tips" :key="index">
                                <li><strong>Consejo:</strong> <span x-text="tip"></span></li>
                            </template>
                        </ul>

                        <p class="ayuda-note" x-show="active().note">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> <span x-text="active().note"></span>
                        </p>
                    </div>
                </div>
            </template>
        </section>
    </div>
</div>
