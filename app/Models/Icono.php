<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icono extends Model
{
    protected $table = 'iconos';

    protected $fillable = [
        'clase', 'nombre', 'grupo', 'orden', 'activo',
    ];

    protected $casts = ['activo' => 'boolean'];
}
