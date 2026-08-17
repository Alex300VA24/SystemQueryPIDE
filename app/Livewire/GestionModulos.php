<?php

namespace App\Livewire;

use App\Http\Requests\ModuloRequest;
use App\Models\Icono;
use App\Models\Modulo;
use App\Models\Rol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

final class GestionModulos extends Component
{
    use WithPagination;

    public function boot(): void
    {
        abort_unless(auth()->user()?->tieneAccesoModuloId(Modulo::ID_MODULOS), 403);
    }

    public string $activeTab = 'create';

    public string $search = '';

    public int $perPage = 10;

    public ?int $editingId = null;

    public bool $deleteModalOpen = false;

    public ?int $deletingId = null;

    public string $sistemaId = '2';

    public string $codigo = '';

    public string $nombre = '';

    public string $descripcion = '';

    public string $url = '';

    public string $icono = '';

    public string $parentId = '';

    public string $orden = '';

    public string $nivel = '';

    public bool $activo = true;

    public function updatedSearch(): void
    {
        $this->resetPage('modulosPage');
    }

    public function updatedPerPage(): void
    {
        $this->resetPage('modulosPage');
    }

    public function mount(): void
    {
        $this->nivel = '1';
        $this->parentId = '';
        $this->orden = (string) $this->nextOrder();
    }

    public function showCreateTab(): void
    {
        $this->resetForm();
        $this->activeTab = 'create';
    }

    public function updatedNivel(): void
    {
        $nivel = (int) $this->nivel;

        if ($nivel <= 1) {
            $this->parentId = '';
        } elseif ($this->parentId === '' || $this->parentId === null) {
            $firstParent = Modulo::query()->where('nivel', $nivel - 1)->where('activo', true)->orderBy('orden')->orderBy('id')->value('id');
            $this->parentId = $firstParent ? (string) $firstParent : '';
        }

        $this->orden = (string) $this->nextOrder();
    }

    public function updatedParentId(): void
    {
        $this->orden = (string) $this->nextOrder();
    }

    public function openEdit(int $id): void
    {
        $module = Modulo::findOrFail($id);
        $this->editingId = $module->id;
        $this->sistemaId = (string) $module->sistema_id;
        $this->codigo = $module->codigo;
        $this->nombre = $module->nombre;
        $this->descripcion = $module->descripcion ?? '';
        $this->url = $module->url ?? '';
        $this->icono = $module->icono ?? '';
        $this->parentId = (string) ($module->padre_id ?? '');
        $this->orden = (string) $module->orden;
        $this->nivel = (string) $module->nivel;
        $this->activo = $module->activo;
        $this->resetValidation();
        $this->activeTab = 'create';
    }

    public function toggleEstado(int $id): void
    {
        $module = Modulo::findOrFail($id);
        $module->activo = ! $module->activo;
        $module->save();

        $this->dispatch('pide-alert', message: $module->activo ? 'Módulo activado exitosamente.' : 'Módulo desactivado exitosamente.', type: 'success');
        $this->dispatch('modulos-updated');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->deleteModalOpen = true;
    }

    public function delete(): void
    {
        $module = Modulo::findOrFail($this->deletingId);

        if ($module->children()->exists()) {
            $this->addError('module', 'No se puede eliminar el módulo porque tiene módulos hijos asociados.');

            return;
        }

        $module->delete();
        $this->deleteModalOpen = false;
        $this->deletingId = null;
        $this->dispatch('pide-alert', message: 'Módulo eliminado exitosamente.', type: 'success');
        $this->dispatch('modulos-updated');
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalOpen = false;
        $this->deletingId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate(
            ModuloRequest::buildRules($this->sistemaId, $this->nivel, $this->editingId),
            ModuloRequest::validationMessages(),
            ModuloRequest::validationAttributes(),
        );

        DB::transaction(function () use ($data): void {
            $module = Modulo::query()->updateOrCreate(
                ['id' => $this->editingId],
                [
                    'sistema_id' => $data['sistemaId'],
                    'codigo' => mb_strtoupper($data['codigo']),
                    'nombre' => $data['nombre'],
                    'descripcion' => $data['descripcion'],
                    'url' => $data['url'],
                    'icono' => $data['icono'],
                    'padre_id' => $data['parentId'] ?: null,
                    'orden' => $data['orden'],
                    'nivel' => $data['nivel'],
                    'es_menu' => true,
                    'activo' => $data['activo'],
                ],
            );

            $this->reorderGroup($module, (int) $data['orden']);

            if (! $this->editingId) {
                $adminRole = Rol::query()->where('codigo', 'ADMIN')->first();
                if ($adminRole) {
                    $module->roles()->syncWithoutDetaching([$adminRole->id => ['sistema_id' => $data['sistemaId'], 'fecha_asignacion' => now()]]);
                }
            }
        });

        $this->dispatch('pide-alert', message: $this->editingId ? 'Módulo actualizado exitosamente.' : 'Módulo creado exitosamente.', type: 'success');
        $this->dispatch('modulos-updated');
        $this->resetForm();
    }

    public function render()
    {
        $search = trim($this->search);

        return view('livewire.gestion-modulos', [
            'modules' => Modulo::query()
                ->with('parent:id,nombre')
                ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                    ->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")))
                ->orderBy('nivel')
                ->orderBy('orden')
                ->orderBy('id')
                ->paginate($this->perPage, pageName: 'modulosPage'),
            'icons' => Icono::query()->where('activo', true)->orderBy('id')->get(['id', 'clase', 'nombre']),
            'parentOptions' => $this->parentOptions(),
            'orderOptions' => $this->orderOptions(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'codigo', 'nombre', 'descripcion', 'url', 'icono', 'parentId', 'orden', 'nivel']);
        $this->sistemaId = '2';
        $this->nivel = '1';
        $this->parentId = '';
        $this->orden = (string) $this->nextOrder();
        $this->activo = true;
        $this->resetValidation();
    }

    private function siblingModules(): Collection
    {
        $nivel = (int) $this->nivel;

        return Modulo::query()
            ->where('nivel', $nivel)
            ->where('padre_id', $nivel <= 1 ? null : ($this->parentId !== '' ? (int) $this->parentId : null))
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'codigo', 'nombre', 'orden']);
    }

    private function nextOrder(): int
    {
        return $this->siblingModules()->count() + 1;
    }

    /** @return array<string, string> */
    private function orderOptions(): array
    {
        $options = [];
        $position = 1;
        foreach ($this->siblingModules() as $sibling) {
            $options[(string) $position] = $position.' — '.$sibling->codigo.' '.$sibling->nombre;
            $position++;
        }
        $options[(string) $position] = $position.' — (Nuevo módulo)';

        return $options;
    }

    /** @return array<int, array{id: int, label: string}> */
    private function parentOptions(): array
    {
        $nivel = (int) $this->nivel;

        if ($nivel <= 1) {
            return [];
        }

        return Modulo::query()
            ->where('nivel', $nivel - 1)
            ->where('activo', true)
            ->whereKeyNot($this->editingId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'codigo', 'nombre'])
            ->map(fn (Modulo $module): array => ['id' => $module->id, 'label' => $module->codigo.' | '.$module->nombre])
            ->all();
    }

    private function reorderGroup(Modulo $module, int $position): void
    {
        $others = Modulo::query()
            ->where('nivel', $module->nivel)
            ->where('padre_id', $module->padre_id)
            ->whereKeyNot($module->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->pluck('id');

        $position = min(max($position, 1), $others->count() + 1);

        $sequence = [];
        $inserted = false;
        foreach ($others as $index => $id) {
            if (! $inserted && $index === $position - 1) {
                $sequence[] = $module->id;
                $inserted = true;
            }
            $sequence[] = $id;
        }
        if (! $inserted) {
            $sequence[] = $module->id;
        }

        foreach ($sequence as $index => $id) {
            Modulo::query()->whereKey($id)->update(['orden' => $index + 1]);
        }
    }
}
