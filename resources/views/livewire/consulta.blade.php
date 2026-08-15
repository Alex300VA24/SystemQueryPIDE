<div class="consulta-legacy page" style="--source:{{ $page['accent'] }}">
    <section class="glass consulta-legacy-heading">
        <span class="consulta-source-icon"><x-icon name="search" /></span>
        <div>
            <h1>{{ $page['title'] }} - {{ $page['source'] }}</h1>
            <p>{{ $page['description'] }}</p>
        </div>
    </section>

    <section class="glass consulta-search-card">
        <form wire:submit="search" class="consulta-search-form" novalidate>
            <div class="field consulta-main-field">
                <label for="query"><x-icon :name="$page['source'] === 'RENIEC' ? 'id' : 'document'" /> {{ $page['field'] }}</label>
                <input
                    id="query"
                    type="text"
                    wire:model="busqueda"
                    maxlength="{{ $page['source'] === 'RENIEC' ? 8 : 11 }}"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="{{ $page['placeholder'] }}"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, {{ $page['source'] === 'RENIEC' ? 8 : 11 }})"
                    aria-describedby="query-hint query-error"
                >
                <span id="query-hint" class="consulta-hint">{{ $page['hint'] }}</span>
                @error('busqueda') <span id="query-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            @if($page['needsCredentials'] ?? false)
                <input type="hidden" wire:model="dniUsuario">
            @endif

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="search">
                        <span wire:loading.remove wire:target="search"><x-icon name="search" /> Buscar</span>
                        <span wire:loading.flex wire:target="search"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="search" aria-label="Limpiar consulta">
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

    <section class="consulta-legacy-results {{ ($page['hasPhoto'] ?? false) ? 'with-photo' : '' }}" aria-live="polite">
        @if($page['hasPhoto'] ?? false)
            <article class="glass consulta-photo-card">
                <h2><x-icon name="camera" /> Fotografía</h2>
                <div class="consulta-photo-frame">
                    @if($photo)
                        <img src="{{ $photo }}" alt="Fotografía de la persona consultada">
                    @else
                        <x-icon name="user" />
                        <span>Sin fotografía</span>
                    @endif
                </div>
            </article>
        @endif

        <article class="glass consulta-info-card">
            <h2><x-icon :name="$page['source'] === 'RENIEC' ? 'user' : 'building'" /> {{ $page['resultTitle'] ?? 'Información' }}</h2>
            <div class="consulta-info-grid {{ $page['source'] === 'SUNAT' ? 'sunat-grid' : '' }}">
                @foreach(array_keys($page['result']) as $label)
                    @php($featured = $page['featuredFields'][$label] ?? null)
                    <div class="consulta-info-item {{ in_array($label, $page['fullWidthFields'] ?? [], true) ? 'full' : '' }} {{ $featured ? 'featured '.$featured : '' }}">
                        <span>{{ $label }}</span>
                        <strong>{{ $searched ? (($result[$label] ?? '') !== '' ? $result[$label] : '-') : '-' }}</strong>
                    </div>
                @endforeach
            </div>

            @if($page['source'] === 'RENIEC')
                <div class="consulta-result-actions">
                    <form method="POST" action="{{ route('consulta.dni.pdf') }}">
                        @csrf
                        <input type="hidden" name="dni" value="{{ $result['DNI'] ?? '' }}">
                        <input type="hidden" name="nombres" value="{{ $result['Nombres'] ?? '' }}">
                        <input type="hidden" name="apellido_paterno" value="{{ $result['Apellido paterno'] ?? '' }}">
                        <input type="hidden" name="apellido_materno" value="{{ $result['Apellido materno'] ?? '' }}">
                        <input type="hidden" name="estado_civil" value="{{ $result['Estado civil'] ?? '' }}">
                        <input type="hidden" name="direccion" value="{{ $result['Dirección'] ?? '' }}">
                        <input type="hidden" name="restriccion" value="{{ $result['Restricción'] ?? '' }}">
                        <input type="hidden" name="ubigeo" value="{{ $result['Ubigeo'] ?? '' }}">
                        <input type="hidden" name="foto" value="{{ $photo ?? '' }}">
                        <button type="submit" class="consulta-button consulta-button-pdf" @disabled(!($searched && $real))><x-icon name="pdf" /> Exportar PDF</button>
                    </form>
                    <button type="button" class="consulta-button consulta-button-print" onclick="window.print()" @disabled(!$searched)><x-icon name="print" /> Imprimir</button>
                </div>
            @endif
        </article>
    </section>
</div>
