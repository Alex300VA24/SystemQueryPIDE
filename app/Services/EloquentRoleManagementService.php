<?php

namespace App\Services;

use App\Contracts\RoleManagementService;
use App\Models\Modulo;
use App\Models\Rol;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EloquentRoleManagementService implements RoleManagementService
{
    private const SORTABLE = ['codigo', 'nombre', 'nivel', 'created_at'];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $sort = in_array($filters['sort'] ?? '', self::SORTABLE, true) ? $filters['sort'] : 'nombre';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) ($filters['perPage'] ?? 10), [5, 10, 25], true) ? (int) $filters['perPage'] : 10;

        return Rol::query()->with('modulos:id,nombre')->withCount(['usuarios', 'modulos'])
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('codigo', 'like', "%{$search}%")
                ->orWhere('nombre', 'like', "%{$search}%")
                ->orWhere('descripcion', 'like', "%{$search}%")))
            ->when(($filters['status'] ?? '') !== '', fn ($query) => $query->where('activo', $filters['status'] === 'active'))
            ->when(($filters['level'] ?? '') !== '', fn ($query) => $query->where('nivel', $filters['level']))
            ->orderBy($sort, $direction)
            ->paginate($perPage, pageName: 'rolesPage');
    }

    public function save(?Rol $role, array $data): Rol
    {
        return DB::transaction(function () use ($role, $data): Rol {
            $role ??= new Rol;
            $role->fill(Arr::only($data, ['codigo', 'nombre', 'descripcion', 'nivel', 'activo']))->save();
            $modules = collect($data['module_ids'])->mapWithKeys(fn ($moduleId) => [
                $moduleId => [
                    'sistema_id' => Modulo::query()->whereKey($moduleId)->value('sistema_id'),
                    'fecha_asignacion' => now(),
                ],
            ])->all();
            $role->modulos()->sync($modules);

            return $role->refresh()->load('modulos');
        });
    }

    public function delete(Rol $role): void
    {
        if ($role->usuarios()->exists()) {
            throw ValidationException::withMessages(['role' => 'Rol tiene usuarios asignados. Reasígnalos antes de eliminar.']);
        }

        DB::transaction(fn () => $role->delete());
    }
}
