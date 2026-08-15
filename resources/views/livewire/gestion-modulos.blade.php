<div class="modulo-legacy" @notify.window="$refs.notice.textContent = $event.detail.message; $refs.notice.focus()">
    <div class="page-title">
        <h1><i class="fa-solid fa-puzzle-piece"></i> Gestión de Módulos</h1>
    </div>
    <p x-ref="notice" tabindex="-1" class="sr-only" aria-live="polite"></p>

    <div class="content-wrapper">
        <div class="tabs">
            <button type="button" wire:click="showCreateTab" class="tab-btn {{ $activeTab === 'create' ? 'active' : '' }}"><i class="fa-solid fa-plus-circle"></i> {{ $editingId ? 'Editar Módulo' : 'Crear Módulo' }}</button>
            <button type="button" wire:click="$set('activeTab', 'list')" class="tab-btn {{ $activeTab === 'list' ? 'active' : '' }}"><i class="fa-solid fa-list"></i> Listado de Módulos</button>
        </div>

        @if ($activeTab === 'create')
            <div class="form-section">
                <form wire:submit="save" wire:key="module-form">
                    <div class="section-header"><i class="fa-solid fa-info-circle"></i> Información del Módulo</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="codigoModulo">Código del Módulo <span class="required">*</span></label>
                            <input type="text" id="codigoModulo" wire:model="codigo" placeholder="Ej: CON, MAN, SIS" maxlength="10" style="text-transform:uppercase;">
                            @error('codigo')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="nombreModulo">Nombre del Módulo <span class="required">*</span></label>
                            <input type="text" id="nombreModulo" wire:model="nombre" placeholder="Ej: CONSULTAS, MANTENIMIENTO" maxlength="100">
                            @error('nombre')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="descripcionModulo">Descripción <span class="required">*</span></label>
                            <textarea id="descripcionModulo" wire:model="descripcion" placeholder="Descripción detallada del módulo" rows="3"></textarea>
                            @error('descripcion')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="section-header"><i class="fa-solid fa-cog"></i> Configuración de Visualización</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nivelModulo">Nivel <span class="required">*</span></label>
                            <select id="nivelModulo" wire:model.live="nivel">
                                <option value="1">Nivel 1 - Principal</option>
                                <option value="2">Nivel 2 - Secundario</option>
                                <option value="3">Nivel 3 - Terciario</option>
                                <option value="4">Nivel 4 - Cuaternario</option>
                            </select>
                            <small class="field-hint">Al cambiar el nivel se muestran los módulos existentes del grupo.</small>
                            @error('nivel')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="moduloPadre">Módulo Padre @if((int) $nivel > 1)<span class="required">*</span>@endif</label>
                            @if ((int) $nivel <= 1)
                                <input type="text" id="moduloPadre" value="Módulo Principal (sin padre)" disabled>
                            @else
                                <select id="moduloPadre" wire:model.live="parentId">
                                    <option value="">Seleccione un módulo padre</option>
                                    @foreach ($parentOptions as $parent)
                                        <option value="{{ $parent['id'] }}">{{ $parent['label'] }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('parentId')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="ordenModulo">Posición <span class="required">*</span></label>
                            <select id="ordenModulo" wire:model="orden">
                                @foreach ($orderOptions as $position => $label)
                                    <option value="{{ $position }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <small class="field-hint">Indica dónde se insertará el módulo dentro de su grupo.</small>
                            @error('orden')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="urlModulo">URL del Módulo <span class="required">*</span></label>
                            <input type="text" id="urlModulo" wire:model="url" placeholder="/pide/consultas">
                            @error('url')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="iconoModulo">Ícono <span class="required">*</span></label>
                            <div class="icon-select" x-data="{ open: false, q: '' }" @click.outside="open = false">
                                <button type="button" id="iconoModulo" class="icon-select-trigger" @click="open = !open; if (open) { q = ''; $nextTick(() => $refs.iconSearch?.focus()); }" :aria-expanded="open.toString()" aria-haspopup="listbox">
                                    <span class="icon-preview" aria-hidden="true"><i class="{{ $icono ?: 'fa-solid fa-icons' }}"></i></span>
                                    <span class="icon-select-label">{{ $icono ? ($icons->firstWhere('clase', $icono)->nombre ?? $icono) : 'Seleccione un ícono' }}</span>
                                    <i class="fa-solid fa-chevron-down icon-select-caret" aria-hidden="true"></i>
                                </button>
                                <div class="icon-select-list" x-cloak x-show="open" x-transition.origin.top role="listbox" aria-label="Lista de íconos">
                                    <div class="icon-select-search">
                                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                        <input type="search" x-ref="iconSearch" x-model="q" placeholder="Buscar ícono..." aria-label="Buscar ícono">
                                    </div>
                                    <ul class="icon-select-options">
                                        @foreach ($icons as $icon)
                                            <li role="option" aria-selected="{{ $icono === $icon->clase ? 'true' : 'false' }}" x-show="!q || '{{ $icon->nombre }}'.toLowerCase().includes(q.toLowerCase())">
                                                <button type="button" class="icon-select-option {{ $icono === $icon->clase ? 'is-selected' : '' }}" wire:click="$set('icono', '{{ $icon->clase }}')" @click="open = false">
                                                    <i class="{{ $icon->clase }}" aria-hidden="true"></i>
                                                    <span>{{ $icon->nombre }}</span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            @error('icono')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save"><i class="fas fa-save"></i> Guardar Módulo</span>
                            <span wire:loading wire:target="save"><span class="loading-spinner"></span> Guardando...</span>
                        </button>
                        <button type="button" wire:click="showCreateTab" class="btn btn-secondary"><i class="fas fa-eraser"></i> <span>Limpiar</span></button>
                    </div>
                </form>
            </div>
        @else
            <div class="toolbar-row">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por código, nombre o URL...">
                </div>
                <select wire:model.live="perPage" class="per-page-select" aria-label="Módulos por página">
                    <option value="5">5 por página</option>
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>

            <div class="table-container" wire:loading.class="is-loading">
                <table id="tablaModulos">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Padre</th>
                            <th>Nivel</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modules as $module)
                            <tr wire:key="module-{{ $module->id }}">
                                <td><strong>{{ $module->codigo }}</strong></td>
                                <td>{{ $module->nombre }}</td>
                                <td>{{ $module->parent?->nombre ?? 'SIN PADRE' }}</td>
                                <td><span class="badge badge-info">Nivel {{ $module->nivel }}</span></td>
                                <td>{{ $module->orden }}</td>
                                <td><span class="badge {{ $module->activo ? 'badge-success' : 'badge-danger' }}">{{ $module->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                <td>
                                    <div class="action-btns">
                                        <button type="button" class="btn-icon btn-edit" wire:click="openEdit({{ $module->id }})" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn-icon btn-toggle {{ $module->activo ? 'is-on' : 'is-off' }}" wire:click="toggleEstado({{ $module->id }})" title="{{ $module->activo ? 'Desactivar' : 'Activar' }}" aria-label="{{ $module->activo ? 'Desactivar' : 'Activar' }} {{ $module->nombre }}"><i class="fas fa-power-off"></i></button>
                                        <button type="button" class="btn-icon btn-delete" wire:click="confirmDelete({{ $module->id }})" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h3>No hay módulos registrados</h3>
                                    <p>Crea tu primer módulo desde la pestaña "Crear Módulo"</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $modules->links() }}</div>
        @endif
    </div>

    @if ($deleteModalOpen)
        <div data-ui-modal class="modal-backdrop" role="presentation" @keydown.escape.window="$el.querySelector('[data-ui-close]')?.click()">
            <section class="modal-panel confirm-modal" role="alertdialog" aria-modal="true" aria-labelledby="delete-module-title">
                <div class="modal-symbol danger"><i class="fas fa-trash"></i></div>
                <h2 id="delete-module-title">Eliminar módulo</h2>
                <p>¿Está seguro que desea eliminar este módulo? Esta acción no se puede deshacer.</p>
                @error('module')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                <div class="action-buttons">
                    <button type="button" wire:click="closeDeleteModal" data-ui-close="closeDeleteModal" class="btn btn-secondary">Cancelar</button>
                    <button type="button" wire:click="delete" class="btn btn-primary" style="background:linear-gradient(135deg,#ef4444,#dc2626);">Eliminar</button>
                </div>
            </section>
        </div>
    @endif
</div>
