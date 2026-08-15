<?php

namespace App\Contracts;

use App\Models\Rol;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleManagementService
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function save(?Rol $role, array $data): Rol;

    public function delete(Rol $role): void;
}
