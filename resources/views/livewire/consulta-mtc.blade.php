<div class="consulta-legacy page" style="--source:#1d4ed8">
    <section class="glass consulta-legacy-heading">
        <span class="consulta-source-icon"><x-icon name="car" /></span>
        <div>
            <h1>Récord de Conductor - MTC</h1>
            <p>Ministerio de Transportes y Comunicaciones · Licencias, papeletas y sanciones vigentes</p>
        </div>
    </section>

    <section class="glass consulta-search-card">
        <form wire:submit="consultar" class="consulta-search-form mtc-form" novalidate>
            <div class="mtc-operaciones" role="group" aria-label="Operación a consultar">
                @foreach ($this->operaciones() as $clave => $op)
                    <button
                        type="button"
                        wire:click="$set('operacion', '{{ $clave }}')"
                        class="mtc-op {{ $operacion === $clave ? 'active' : '' }}"
                        wire:key="op-{{ $clave }}"
                    >
                        <x-icon :name="$op['icon']" /> {{ $op['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="field consulta-main-field">
                <label for="tipo-documento"><x-icon name="id" /> Tipo de documento</label>
                <select id="tipo-documento" wire:model="tipoDocumento" class="mtc-select" aria-describedby="tipo-error">
                    <option value="1">DNI</option>
                    <option value="2">Carné de Extranjería</option>
                </select>
                @error('tipoDocumento') <span id="tipo-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field consulta-main-field">
                <label for="numero-documento"><x-icon name="document" /> Número de documento</label>
                <input
                    id="numero-documento"
                    type="text"
                    wire:model="numeroDocumento"
                    maxlength="15"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="Ingrese el número del documento"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 15)"
                    aria-describedby="numero-hint numero-error"
                >
                <span id="numero-hint" class="consulta-hint">Ingresa solo dígitos del documento del conductor.</span>
                @error('numeroDocumento') <span id="numero-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="consultar">
                        <span wire:loading.remove wire:target="consultar"><x-icon name="search" /> Consultar</span>
                        <span wire:loading.flex wire:target="consultar"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="consultar" aria-label="Limpiar consulta">
                        <x-icon name="eraser" /><span class="sr-only">Limpiar</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    @if($successMessage || $errorMessage)
        <div class="consulta-alert {{ $successMessage ? 'success' : 'danger' }}" role="status">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @if($searched && $result !== [])
        <section class="glass consulta-legacy-results" aria-live="polite">
            @if($operacion === 'licencia')
                <article class="glass consulta-info-card">
                    <h2><x-icon name="id" /> Última Licencia de Conducir</h2>
                    <div class="consulta-info-grid">
                        @foreach($this->columnas() as $clave => $etiqueta)
                            @php($valor = $result[0][$clave] ?? '')
                            <div class="consulta-info-item">
                                <span>{{ $etiqueta }}</span>
                                <strong>{{ $valor !== '' ? $valor : '-' }}</strong>
                            </div>
                        @endforeach
                    </div>
                </article>
            @else
                <article class="glass consulta-info-card">
                    <h2><x-icon name="document" /> {{ $operacion === 'sanciones' ? 'Últimas Sanciones' : 'Papeletas Aplicadas' }}</h2>
                    <div class="table-wrap mtc-table">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    @foreach($this->columnas() as $etiqueta)
                                        <th>{{ $etiqueta }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result as $item)
                                    <tr>
                                        @foreach(array_keys($this->columnas()) as $clave)
                                            @php($valor = $item[$clave] ?? '')
                                            <td>{{ $valor !== '' ? $valor : '-' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif
        </section>
    @endif
</div>