<div class="consulta-legacy page" style="--source:#0f766e">
    <section class="glass consulta-legacy-heading">
        <span class="consulta-source-icon"><x-icon name="calculator" /></span>
        <div>
            <h1>Cobranza Coactiva - SUNAT</h1>
            <p>Consulta de deudas en cobranza coactiva administradas por SUNAT</p>
        </div>
    </section>

    <section class="glass consulta-search-card">
        <form wire:submit="buscar" class="consulta-search-form" novalidate>
            <div class="field">
                <label for="tipoDocumento"><x-icon name="id" /> Tipo de documento</label>
                <select id="tipoDocumento" wire:model.live="tipoDocumento">
                    <option value="01">DNI</option>
                    <option value="06">RUC</option>
                </select>
                @error('tipoDocumento') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field consulta-main-field">
                <label for="numeroDocumento"><x-icon name="document" /> Número de documento</label>
                <input
                    id="numeroDocumento"
                    type="text"
                    wire:model="numeroDocumento"
                    maxlength="{{ $tipoDocumento === '01' ? 8 : 11 }}"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="{{ $tipoDocumento === '01' ? 'Ingrese 8 dígitos' : 'Ingrese 11 dígitos' }}"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, {{ $tipoDocumento === '01' ? 8 : 11 }})"
                    aria-describedby="numeroDocumento-error"
                >
                @error('numeroDocumento') <span id="numeroDocumento-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="buscar">
                        <span wire:loading.remove wire:target="buscar"><x-icon name="search" /> Buscar</span>
                        <span wire:loading.flex wire:target="buscar"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="buscar" aria-label="Limpiar consulta">
                        <x-icon name="eraser" /><span class="sr-only">Limpiar</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    @if($successMessage || $errorMessage)
        <div class="consulta-alert {{ $successMessage ? 'success' : ($real ? 'danger' : 'warning') }}" role="status">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @php
        $totalDeudas = count($deudas);
        $deuda = $deudas[$deudaActual] ?? [];
    @endphp

    <section class="consulta-legacy-results" aria-live="polite" aria-busy="{{ $searched && !$real ? 'true' : 'false' }}">
        <article class="glass consulta-info-card coactiva-detail-card">
            <header class="coactiva-detail-header">
                <div>
                    <h2><x-icon name="calculator" /> Detalle de deuda coactiva</h2>
                    <p>Los datos obtenidos se muestran en campos de solo lectura.</p>
                </div>
                <span class="coactiva-counter">
                    {{ $totalDeudas > 0 ? 'Deuda '.($deudaActual + 1).' de '.$totalDeudas : 'Sin deuda seleccionada' }}
                </span>
            </header>

            @if($totalDeudas > 1)
                <nav class="coactiva-debt-nav" aria-label="Navegar entre deudas encontradas">
                    <button
                        type="button"
                        class="coactiva-nav-arrow"
                        wire:click="deudaAnterior"
                        @disabled($deudaActual === 0)
                        aria-label="Ver deuda anterior"
                    >
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </button>

                    <div class="coactiva-debt-pages" aria-label="{{ $totalDeudas }} deudas encontradas">
                        @foreach($deudas as $indice => $item)
                            <button
                                type="button"
                                wire:key="deuda-nav-{{ $indice }}"
                                wire:click="seleccionarDeuda({{ $indice }})"
                                class="coactiva-debt-page {{ $deudaActual === $indice ? 'active' : '' }}"
                                aria-label="Ver deuda {{ $indice + 1 }} de {{ $totalDeudas }}"
                                @if($deudaActual === $indice) aria-current="true" @endif
                            >
                                <span>{{ $indice + 1 }}</span>
                                <small>{{ $item['perDoc'] ?: 'Sin periodo' }}</small>
                            </button>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        class="coactiva-nav-arrow"
                        wire:click="deudaSiguiente"
                        @disabled($deudaActual === $totalDeudas - 1)
                        aria-label="Ver deuda siguiente"
                    >
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </nav>
            @endif

            <div class="coactiva-fields">
                <div class="field coactiva-field-wide">
                    <label for="coactiva-contribuyente"><x-icon name="user" /> Contribuyente</label>
                    <input id="coactiva-contribuyente" type="text" readonly value="{{ $deuda['nomRuc'] ?? '' }}" placeholder="Se completará al consultar">
                </div>

                <div class="field">
                    <label for="coactiva-ruc"><x-icon name="id" /> RUC</label>
                    <input id="coactiva-ruc" type="text" readonly value="{{ $deuda['numRuc'] ?? '' }}" placeholder="Se completará al consultar">
                </div>

                <div class="field coactiva-field-wide">
                    <label for="coactiva-entidad"><x-icon name="building" /> Entidad</label>
                    <input id="coactiva-entidad" type="text" readonly value="{{ $deuda['desEntidad'] ?? '' }}" placeholder="Se completará al consultar">
                </div>

                <div class="field">
                    <label for="coactiva-periodo"><x-icon name="calendar" /> Periodo</label>
                    <input id="coactiva-periodo" type="text" readonly value="{{ $deuda['perDoc'] ?? '' }}" placeholder="Se completará al consultar">
                </div>

                <div class="field">
                    <label for="coactiva-monto"><x-icon name="calculator" /> Monto de deuda</label>
                    <div class="coactiva-money-input">
                        <span aria-hidden="true">S/</span>
                        <input id="coactiva-monto" type="text" readonly value="{{ $totalDeudas > 0 ? number_format((float) ($deuda['mtoDeuda'] ?? 0), 2) : '' }}" placeholder="0.00">
                    </div>
                </div>

                <div class="field">
                    <label for="coactiva-transferencia"><x-icon name="calendar" /> Fecha de transferencia</label>
                    <input id="coactiva-transferencia" type="text" readonly value="{{ $deuda['fecTraCoa'] ?? '' }}" placeholder="Se completará al consultar">
                </div>

                <div class="field">
                    <label for="coactiva-actualizacion"><x-icon name="clock" /> Fecha de actualización</label>
                    <input id="coactiva-actualizacion" type="text" readonly value="{{ $deuda['fecAct'] ?? '' }}" placeholder="Se completará al consultar">
                </div>
            </div>
        </article>
    </section>
</div>
