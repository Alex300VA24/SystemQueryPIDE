<?php

namespace App\Livewire;

use App\Contracts\UserManagementService;
use App\Http\Requests\UsuarioRequest;
use App\Models\CatEstado;
use App\Models\Rol;
use App\Models\TipoDocumento;
use App\Models\Usuario;
use Livewire\Component;
use Livewire\WithPagination;

final class GestionUsuarios extends Component
{
    use WithPagination;

    public string $activeTab = 'create';

    public string $search = '';

    public string $statusFilter = '';

    public string $roleFilter = '';

    public string $sortBy = 'username';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public bool $deleteModalOpen = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $tipoPersona = '1';

    public string $documentoTipoId = '1';

    public string $documentoNumero = '';

    public string $apellidoPaterno = '';

    public string $apellidoMaterno = '';

    public string $nombres = '';

    public string $sexo = '';

    public string $username = '';

    public string $email = '';

    public string $telefonoCodigo = '51';

    public string $telefonoNumero = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $showPassword = false;

    public bool $showPasswordConfirm = false;

    public string $roleId = '';

    public string $estadoId = '';

    public string $cui = '';

    public function updated($property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'roleFilter', 'perPage'], true)) {
            $this->resetPage('usersPage');
        }
    }

    public function sort(string $column): void
    {
        abort_unless(in_array($column, ['username', 'email', 'created_at'], true), 404);
        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage('usersPage');
    }

    public function togglePassword(): void
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function togglePasswordConfirm(): void
    {
        $this->showPasswordConfirm = ! $this->showPasswordConfirm;
    }

    public function showCreateTab(): void
    {
        $this->resetForm();
        $this->activeTab = 'create';
    }

    public function showListTab(): void
    {
        $this->activeTab = 'list';
    }

    public function openEdit(int $id): void
    {
        $usuario = Usuario::query()->with(['persona', 'roles:id'])->findOrFail($id);
        $this->resetValidation();
        $this->editingId = $usuario->id;
        $this->tipoPersona = (string) ($usuario->persona->tipo_persona ?? 1);
        $this->documentoTipoId = (string) $usuario->persona->documento_tipo_id;
        $this->documentoNumero = $usuario->persona->documento_numero;
        $this->apellidoPaterno = $usuario->persona->apellido_paterno;
        $this->apellidoMaterno = $usuario->persona->apellido_materno ?? '';
        $this->nombres = $usuario->persona->nombres;
        $this->sexo = $usuario->persona->sexo ?? '';
        $this->username = $usuario->username;
        $this->email = $usuario->email ?? '';
        $this->roleId = (string) ($usuario->roles->first()?->id ?? '');
        $this->estadoId = (string) $usuario->estado_id;
        $this->cui = $usuario->cui ?? '';
        [$this->telefonoCodigo, $this->telefonoNumero] = $this->splitTelefono($usuario->telefono);
        $this->password = $this->password_confirmation = '';
        $this->showPassword = $this->showPasswordConfirm = false;
        $this->activeTab = 'create';
    }

    public function save(UserManagementService $service): void
    {
        $usuario = $this->editingId ? Usuario::findOrFail($this->editingId) : null;

        $data = $this->validate(
            UsuarioRequest::buildRules($this->documentoTipoId, $usuario?->persona_id, $this->editingId),
            UsuarioRequest::validationMessages(),
            UsuarioRequest::validationAttributes(),
        );

        $service->save($usuario, [
            'tipo_persona' => $data['tipoPersona'],
            'documento_tipo_id' => $data['documentoTipoId'],
            'documento_numero' => $data['documentoNumero'],
            'apellido_paterno' => $data['apellidoPaterno'],
            'apellido_materno' => $data['apellidoMaterno'],
            'nombres' => $data['nombres'],
            'sexo' => $data['sexo'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'telefono' => $data['telefonoNumero'] ? '+'.$data['telefonoCodigo'].'-'.$data['telefonoNumero'] : null,
            'password' => $data['password'],
            'role_id' => $data['roleId'] ?: null,
            'estado_id' => $data['estadoId'],
            'cui' => $data['cui'],
        ]);
        $this->dispatch('pide-alert', message: $this->editingId ? 'Usuario actualizado.' : 'Usuario creado.', type: 'success');
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = Usuario::findOrFail($id)->id;
        $this->deleteModalOpen = true;
    }

    public function delete(UserManagementService $service): void
    {
        $service->delete(Usuario::findOrFail($this->deletingId));
        $this->deleteModalOpen = false;
        $this->deletingId = null;
        $this->dispatch('pide-alert', message: 'Usuario eliminado.', type: 'success');
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalOpen = false;
        $this->deletingId = null;
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'documentoNumero', 'apellidoPaterno', 'apellidoMaterno', 'nombres', 'sexo',
            'username', 'email', 'telefonoCodigo', 'telefonoNumero', 'password', 'password_confirmation',
            'roleId', 'cui', 'showPassword', 'showPasswordConfirm',
        ]);
        $this->tipoPersona = '1';
        $this->documentoTipoId = '1';
        $this->estadoId = (string) CatEstado::ACTIVO;
        $this->resetValidation();
    }

    /** @return array{0: string, 1: string} */
    private function splitTelefono(?string $telefono): array
    {
        if (! $telefono) {
            return ['51', ''];
        }

        [$codigo, $numero] = array_pad(explode('-', ltrim($telefono, '+'), 2), 2, '');

        return [$codigo !== '' ? $codigo : '51', $numero];
    }

    public function render(UserManagementService $service)
    {
        return view('livewire.gestion-usuarios', [
            'users' => $service->paginate(['search' => $this->search, 'status' => $this->statusFilter, 'role' => $this->roleFilter, 'sort' => $this->sortBy, 'direction' => $this->sortDirection, 'perPage' => $this->perPage]),
            'roles' => Rol::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']),
            'tiposDocumento' => TipoDocumento::query()->where('activo', true)->get(['id', 'nombre']),
            'estados' => CatEstado::query()->orderBy('id')->get(['id', 'codigo', 'descripcion']),
        ]);
    }
}
