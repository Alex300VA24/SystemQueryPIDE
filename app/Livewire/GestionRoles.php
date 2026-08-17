<?php

namespace App\Livewire;

use App\Contracts\RoleManagementService;
use App\Http\Requests\RolRequest;
use App\Models\Modulo;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;

final class GestionRoles extends Component
{
    use WithPagination;

    public function boot(): void
    {
        abort_unless(auth()->user()?->tieneAccesoModuloId(Modulo::ID_ROLES), 403);
    }

    public string $activeTab = 'create';

    public string $search = '';

    public string $statusFilter = '';

    public string $levelFilter = '';

    public string $sortBy = 'nombre';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public bool $deleteModalOpen = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $codigo = '';

    public string $nombre = '';

    public string $descripcion = '';

    public int $nivel = 1;

    public bool $activo = true;

    /** @var array<int, int|string> */
    public array $selectedModuleIds = [];

    public function updated($property): void
    {
        if (in_array($property, ['search', 'statusFilter', 'levelFilter', 'perPage'], true)) {
            $this->resetPage('rolesPage');
        }
    }

    public function sort(string $column): void
    {
        abort_unless(in_array($column, ['codigo', 'nombre', 'nivel', 'created_at'], true), 404);
        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
        $this->resetPage('rolesPage');
    }

    public function mount(): void
    {
        $this->selectedModuleIds = $this->defaultModuleIds();
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
        $role = Rol::query()->with('modulos:id')->findOrFail($id);
        $this->resetValidation();
        $this->editingId = $role->id;
        $this->codigo = $role->codigo;
        $this->nombre = $role->nombre;
        $this->descripcion = $role->descripcion ?? '';
        $this->nivel = $role->nivel ?? 1;
        $this->activo = $role->activo;
        $this->selectedModuleIds = $role->modulos->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->activeTab = 'create';
    }

    public function save(RoleManagementService $service): void
    {
        $data = $this->validate(
            RolRequest::buildRules($this->editingId),
            RolRequest::validationMessages(),
            RolRequest::validationAttributes(),
        );
        $data['codigo'] = mb_strtoupper($data['codigo']);
        $data['module_ids'] = array_map('intval', $data['selectedModuleIds']);
        $service->save($this->editingId ? Rol::findOrFail($this->editingId) : null, $data);
        $this->dispatch('pide-alert', message: $this->editingId ? 'Rol actualizado.' : 'Rol creado.', type: 'success');
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = Rol::findOrFail($id)->id;
        $this->deleteModalOpen = true;
    }

    public function delete(RoleManagementService $service): void
    {
        $service->delete(Rol::findOrFail($this->deletingId));
        $this->deleteModalOpen = false;
        $this->deletingId = null;
        $this->dispatch('pide-alert', message: 'Rol eliminado.', type: 'success');
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalOpen = false;
        $this->deletingId = null;
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'codigo', 'nombre', 'descripcion', 'selectedModuleIds']);
        $this->nivel = 1;
        $this->activo = true;
        $this->selectedModuleIds = $this->defaultModuleIds();
        $this->resetValidation();
    }

    /** @return array<int, string> */
    private function defaultModuleIds(): array
    {
        $iniId = Modulo::query()->where('codigo', 'INI')->whereNull('padre_id')->value('id');

        return $iniId ? [(string) $iniId] : [];
    }

    public function render(RoleManagementService $service)
    {
        return view('livewire.gestion-roles', [
            'roles' => $service->paginate(['search' => $this->search, 'status' => $this->statusFilter, 'level' => $this->levelFilter, 'sort' => $this->sortBy, 'direction' => $this->sortDirection, 'perPage' => $this->perPage]),
            'moduleGroups' => $this->moduleGroups(),
        ]);
    }

    /** @return array<int, array{id: int, codigo: string, nombre: string, icono: string, children: array<int, array{id: int, codigo: string, nombre: string, icono: string, descripcion: ?string}>}> */
    private function moduleGroups(): array
    {
        return Modulo::query()
            ->with(['children' => fn ($query) => $query->where('activo', true)->orderBy('orden')->orderBy('id')])
            ->whereNull('padre_id')
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->map(fn (Modulo $module): array => [
                'id' => $module->id,
                'codigo' => $module->codigo,
                'nombre' => $module->nombre,
                'icono' => $module->icono ?: 'fas fa-cube',
                'children' => $module->children->map(fn (Modulo $child): array => [
                    'id' => $child->id,
                    'codigo' => $child->codigo,
                    'nombre' => $child->nombre,
                    'icono' => $child->icono ?: 'fas fa-circle',
                    'descripcion' => $child->descripcion,
                ])->values()->all(),
            ])
            ->all();
    }
}
