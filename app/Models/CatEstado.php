<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatEstado extends Model
{
    protected $table = 'cat_estado';

    public $timestamps = true;

    protected $fillable = ['codigo', 'descripcion', 'aplicable_a'];

    public const ACTIVO = 1;
    public const INACTIVO = 2;
    public const BLOQUEADO = 3;
    public const SUSPENDIDO = 4;
    public const PENDIENTE = 5;
    public const ELIMINADO = 6;
}
