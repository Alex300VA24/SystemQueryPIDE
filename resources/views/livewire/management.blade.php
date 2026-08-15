<div class="page" x-data @demo-saved.window="$refs.notice.focus()">
    <header class="page-head"><div><h1>{{ $page['title'] }}</h1><p>{{ $page['description'] }}</p></div><span class="demo-notice">Estado temporal</span></header>
    <section class="surface surface-pad" aria-label="Listado de {{ $page['singular'] }}s">
        <div class="toolbar">
            <label class="sr-only" for="table-search">Buscar</label><input id="table-search" wire:model.live.debounce.250ms="search" class="search-input" type="search" placeholder="Buscar por nombre, código o detalle">
            <button type="button" wire:click="openCreate" class="button button-primary">Crear {{ $page['singular'] }}</button>
        </div>
        <p x-ref="notice" tabindex="-1" class="sr-only" aria-live="polite">Registro demo creado.</p>
        <div class="table-wrap">
            <table class="data-table"><thead><tr><th>Código</th><th>Nombre</th><th>Detalle</th><th>Estado</th></tr></thead><tbody>
            @forelse($filtered as $item)<tr wire:key="{{ $item['code'] }}"><td data-label="Código"><strong>{{ $item['code'] }}</strong></td><td data-label="Nombre">{{ $item['name'] }}</td><td data-label="Detalle">{{ $item['detail'] }}</td><td data-label="Estado"><span class="badge">{{ $item['status'] }}</span></td></tr>
            @empty<tr><td colspan="4" class="empty">No hay coincidencias. Prueba otro término.</td></tr>@endforelse
            </tbody></table>
        </div>
    </section>

    @if($modalOpen)
        <div data-ui-modal class="modal-backdrop" role="presentation" @keydown.escape.window="$el.querySelector('[data-ui-close]')?.click()">
            <section class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                <h2 id="modal-title">Nuevo {{ $page['singular'] }}</h2><p>Se guardará solo durante esta sesión Livewire.</p>
                <form wire:submit="save" class="form-grid">
                    <div class="field"><label for="code">Código</label><input id="code" wire:model="code" autocomplete="off">@error('code')<span class="field-error" role="alert">{{ $message }}</span>@enderror</div>
                    <div class="field"><label for="name">Nombre</label><input id="name" wire:model="name" autocomplete="off">@error('name')<span class="field-error" role="alert">{{ $message }}</span>@enderror</div>
                    <div class="actions full"><button type="button" class="button button-secondary" data-ui-close="closeModal">Cancelar</button><button type="submit" class="button button-primary" wire:loading.attr="disabled">Guardar demo</button></div>
                </form>
            </section>
        </div>
    @endif
</div>
