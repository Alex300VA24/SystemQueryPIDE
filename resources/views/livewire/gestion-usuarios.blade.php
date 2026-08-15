<div class="modulo-legacy usuario-legacy" @notify.window="$refs.notice.textContent = $event.detail.message; $refs.notice.focus()">
    <div class="page-title">
        <h1><i class="fas fa-user-plus"></i> Gestión de Usuarios</h1>
    </div>
    <p x-ref="notice" tabindex="-1" class="sr-only" aria-live="polite"></p>

    <div class="content-wrapper">
        <div class="tabs">
            <button type="button" wire:click="showCreateTab" class="tab-btn {{ $activeTab === 'create' ? 'active' : '' }}">
                <i class="fa-solid fa-plus-circle"></i> {{ $editingId ? 'Editar Usuario' : 'Crear Usuario' }}
            </button>
            <button type="button" wire:click="showListTab" class="tab-btn {{ $activeTab === 'list' ? 'active' : '' }}">
                <i class="fa-solid fa-list"></i> Listado de Usuarios
            </button>
        </div>

        @if ($activeTab === 'create')
            <div class="form-section">
                <form wire:submit="save" wire:key="user-form" novalidate>
                    <div class="section-header"><i class="fas fa-user"></i> Datos Personales</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="perTipo">Tipo de Persona <span class="required">*</span></label>
                            <select id="perTipo" wire:model="tipoPersona">
                                <option value="1">Natural</option>
                                <option value="2">Jurídica</option>
                            </select>
                            @error('tipoPersona')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perDocumentoTipo">Tipo de Documento <span class="required">*</span></label>
                            <select id="perDocumentoTipo" wire:model="documentoTipoId">
                                @foreach ($tiposDocumento as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            @error('documentoTipoId')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perDocumentoNum">Número de Documento <span class="required">*</span></label>
                            <input type="text" id="perDocumentoNum" wire:model="documentoNumero" maxlength="12" placeholder="Ej: 12345678" inputmode="numeric" x-on:input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '')">
                            @error('documentoNumero')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perNombre">Nombres <span class="required">*</span></label>
                            <input type="text" id="perNombre" wire:model="nombres" maxlength="40" placeholder="Nombres completos" x-on:input="$event.target.value = $event.target.value.replace(/[^\p{L}\s]/gu, '')">
                            @error('nombres')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perApellidoPat">Apellido Paterno <span class="required">*</span></label>
                            <input type="text" id="perApellidoPat" wire:model="apellidoPaterno" maxlength="20" placeholder="Apellido paterno" x-on:input="$event.target.value = $event.target.value.replace(/[^\p{L}\s]/gu, '')">
                            @error('apellidoPaterno')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perApellidoMat">Apellido Materno</label>
                            <input type="text" id="perApellidoMat" wire:model="apellidoMaterno" maxlength="20" placeholder="Apellido materno" x-on:input="$event.target.value = $event.target.value.replace(/[^\p{L}\s]/gu, '')">
                            @error('apellidoMaterno')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perSexo">Sexo <span class="required">*</span></label>
                            <select id="perSexo" wire:model="sexo">
                                <option value="">Seleccionar...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                            @error('sexo')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="perEmail">Email <small>(opcional)</small></label>
                            <input type="email" id="perEmail" wire:model="email" maxlength="50" placeholder="correo@ejemplo.com" autocomplete="email">
                            @error('email')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group phone-group">
                            <label for="perTelefonoNumero">Teléfono <small>(opcional)</small></label>
                            <div class="phone-input-wrap">
                                <span class="phone-prefix">+</span>
                                <input type="text" id="perTelefonoCodigo" wire:model="telefonoCodigo" maxlength="4" inputmode="numeric" placeholder="51" aria-label="Código de país" class="phone-code" x-on:input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '')">
                                <input type="text" id="perTelefonoNumero" wire:model="telefonoNumero" maxlength="9" inputmode="numeric" placeholder="987654321" aria-label="Número de teléfono" class="phone-number" x-on:input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '')">
                            </div>
                            @error('telefonoCodigo')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                            @error('telefonoNumero')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="section-header"><i class="fas fa-lock"></i> Datos de Usuario</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="usuLogin">Login/Usuario <span class="required">*</span></label>
                            <input type="text" id="usuLogin" wire:model="username" maxlength="15" placeholder="Nombre de usuario" autocomplete="username">
                            @error('username')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group password-container">
                            <label for="usuPass">Contraseña @unless($editingId)<span class="required">*</span>@endunless</label>
                            <div class="password-input-wrap">
                                <input type="{{ $showPassword ? 'text' : 'password' }}" id="usuPass" wire:model="password" maxlength="100" placeholder="{{ $editingId ? 'Dejar vacío para conservar' : 'Contraseña segura' }}" autocomplete="new-password">
                                <button type="button" class="toggle-password" wire:click="togglePassword" aria-label="{{ $showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña' }}">
                                    <i class="fas {{ $showPassword ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </div>
                            @error('password')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group password-container">
                            <label for="usuPassConfirm">Confirmar Contraseña @unless($editingId)<span class="required">*</span>@endunless</label>
                            <div class="password-input-wrap">
                                <input type="{{ $showPasswordConfirm ? 'text' : 'password' }}" id="usuPassConfirm" wire:model="password_confirmation" maxlength="100" placeholder="Repita la contraseña" autocomplete="new-password">
                                <button type="button" class="toggle-password" wire:click="togglePasswordConfirm" aria-label="{{ $showPasswordConfirm ? 'Ocultar contraseña' : 'Mostrar contraseña' }}">
                                    <i class="fas {{ $showPasswordConfirm ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="usuPermiso">Rol de Usuario <span class="required">*</span></label>
                            <select id="usuPermiso" wire:model="roleId">
                                <option value="">Seleccionar...</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->nombre }}</option>
                                @endforeach
                            </select>
                            @error('roleId')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="usuEstado">Estado <span class="required">*</span></label>
                            <select id="usuEstado" wire:model="estadoId">
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado->id }}">{{ $estado->descripcion }}</option>
                                @endforeach
                            </select>
                            @error('estadoId')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label for="cui">CUI <span class="required">*</span></label>
                            <input type="text" id="cui" wire:model="cui" maxlength="1" inputmode="numeric" pattern="[0-9]" placeholder="Código único" @disabled($editingId)>
                            @error('cui')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save"><i class="fas fa-save"></i> {{ $editingId ? 'Actualizar Usuario' : 'Guardar Usuario' }}</span>
                            <span wire:loading wire:target="save"><span class="loading-spinner"></span> Guardando...</span>
                        </button>
                        <button type="button" wire:click="showCreateTab" class="btn btn-secondary"><i class="fas fa-broom"></i> <span>Limpiar</span></button>
                    </div>
                </form>
            </div>
        @else
            <div class="toolbar-row">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por usuario, nombre, correo o documento...">
                </div>
                <select wire:model.live="perPage" class="per-page-select" aria-label="Usuarios por página">
                    <option value="5">5 por página</option>
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                </select>
            </div>

            <div class="table-container" wire:loading.class="is-loading">
                <table id="tablaUsuarios">
                    <thead><tr><th>Usuario</th><th>Nombre Completo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td><strong>{{ $user->username }}</strong></td>
                                <td>{{ $user->persona?->nombreCompleto() }}</td>
                                <td>{{ $user->roles->first()?->nombre ?? 'Sin rol' }}</td>
                                <td><span class="badge {{ $user->estado?->codigo === 'ACTIVO' ? 'badge-success' : 'badge-danger' }}">{{ $user->estado?->descripcion }}</span></td>
                                <td><div class="action-btns">
                                    <button type="button" class="btn-icon btn-edit" wire:click="openEdit({{ $user->id }})" aria-label="Editar {{ $user->username }}" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn-icon btn-delete" wire:click="confirmDelete({{ $user->id }})" aria-label="Eliminar {{ $user->username }}" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state"><i class="fas fa-inbox"></i><h3>No hay usuarios registrados</h3><p>Crea tu primer usuario desde la pestaña "Crear Usuario"</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        @endif
    </div>

    @if ($deleteModalOpen)
        <div data-ui-modal class="modal-backdrop" role="presentation" wire:click.self="closeDeleteModal" @keydown.escape.window="$wire.closeDeleteModal()">
            <section class="modal-panel confirm-modal" role="alertdialog" aria-modal="true" aria-labelledby="delete-user-title">
                <div class="modal-symbol danger"><i class="fas fa-trash"></i></div>
                <h2 id="delete-user-title">Eliminar usuario</h2>
                <p>¿Está seguro que desea eliminar este usuario? Esta acción no se puede deshacer.</p>
                @error('user')<span class="field-error" role="alert">{{ $message }}</span>@enderror
                <div class="action-buttons">
                    <button type="button" wire:click="closeDeleteModal" class="btn btn-secondary">Cancelar</button>
                    <button type="button" wire:click="delete" wire:loading.attr="disabled" wire:target="delete" class="btn btn-primary danger-button">Eliminar</button>
                </div>
            </section>
        </div>
    @endif
</div>
