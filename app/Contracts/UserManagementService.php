<?php

namespace App\Contracts;

use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserManagementService
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function save(?Usuario $usuario, array $data): Usuario;

    public function delete(Usuario $usuario): void;
}
