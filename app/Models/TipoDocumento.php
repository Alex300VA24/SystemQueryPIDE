<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = 'tipo_documento';

    protected $fillable = ['codigo', 'nombre', 'abreviatura', 'formato_validacion', 'longitud_min', 'longitud_max', 'activo'];

    protected $casts = ['activo' => 'boolean'];
}
