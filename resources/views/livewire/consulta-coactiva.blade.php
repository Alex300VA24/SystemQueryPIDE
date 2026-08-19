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

    @if($searched && $real && !empty($deudas))
        <section class="consulta-legacy-results" aria-live="polite">
            <article class="glass consulta-info-card" style="width: 100%;">
                <h2><x-icon name="calculator" /> Deudas en Cobranza Coactiva</h2>
                <div style="overflow-x: auto;">
                    <table class="consulta-table">
                        <thead>
                            <tr>
                                <th>Contribuyente</th>
                                <th>RUC</th>
                                <th>Entidad</th>
                                <th>Periodo</th>
                                <th>Monto Deuda</th>
                                <th>Fecha Transferencia</th>
                                <th>Fecha Actualización</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deudas as $deuda)
                                <tr>
                                    <td>{{ $deuda['nomRuc'] ?: '-' }}</td>
                                    <td>{{ $deuda['numRuc'] ?: '-' }}</td>
                                    <td>{{ $deuda['desEntidad'] ?: '-' }}</td>
                                    <td>{{ $deuda['perDoc'] ?: '-' }}</td>
                                    <td>{{ number_format((float) $deuda['mtoDeuda'], 2) }}</td>
                                    <td>{{ $deuda['fecTraCoa'] ?: '-' }}</td>
                                    <td>{{ $deuda['fecAct'] ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    @endif
</div>
