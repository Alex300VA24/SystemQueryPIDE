<?php

namespace App\Services;

use App\Contracts\UserManagementService;
use App\Models\CatEstado;
use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class EloquentUserManagementService implements UserManagementService
{
    private const SORTABLE = ['username', 'email', 'created_at'];

    public function paginate(array $filters): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $sort = in_array($filters['sort'] ?? '', self::SORTABLE, true) ? $filters['sort'] : 'username';
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) ($filters['perPage'] ?? 10), [5, 10, 25], true) ? (int) $filters['perPage'] : 10;

        return Usuario::query()
            ->with(['persona', 'roles', 'estado'])
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('persona', fn ($p) => $p
                    ->where('nombres', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('documento_numero', 'like', "%{$search}%"))))
            ->when(($filters['status'] ?? '') !== '', fn ($query) => $query->where('estado_id', $filters['status'] === 'active' ? CatEstado::ACTIVO : CatEstado::INACTIVO))
            ->when(($filters['role'] ?? '') !== '', fn ($query) => $query->whereHas('roles', fn ($role) => $role->whereKey($filters['role'])))
            ->orderBy($sort, $direction)
            ->paginate($perPage, pageName: 'usersPage');
    }

    public function save(?Usuario $usuario, array $data): Usuario
    {
        return DB::transaction(function () use ($usuario, $data): Usuario {
            $persona = $usuario?->persona ?? new Persona;
            $persona->fill([
                'tipo_persona' => $data['tipo_persona'],
                'documento_tipo_id' => $data['documento_tipo_id'],
                'documento_numero' => $data['documento_numero'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'] ?: null,
                'nombres' => $data['nombres'],
                'sexo' => $data['sexo'],
                'estado_id' => CatEstado::ACTIVO,
            ])->save();

            $usuario ??= new Usuario;
            $usuario->persona_id = $persona->id;
            $usuario->username = $data['username'];
            $usuario->email = $data['email'];
            $usuario->telefono = $data['telefono'];
            $usuario->estado_id = $data['estado_id'];
            $usuario->cui = $data['cui'];
            if (! empty($data['password'])) {
                $usuario->password_hash = Hash::make($data['password']);
                $usuario->requiere_cambio_password = true;
            }
            $usuario->save();
            $usuario->roles()->sync(! empty($data['role_id']) ? [(int) $data['role_id'] => ['fecha_asignacion' => now(), 'activo' => true]] : []);

            return $usuario->refresh();
        });
    }

    public function delete(Usuario $usuario): void
    {
        if ($usuario->is(auth()->user())) {
            throw ValidationException::withMessages(['user' => 'No puedes eliminar tu propia cuenta.']);
        }

        DB::transaction(fn () => $usuario->delete());
    }
}
