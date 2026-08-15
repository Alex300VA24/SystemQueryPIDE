<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'nivel', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'rol_id', 'usuario_id')
            ->withPivot(['fecha_asignacion', 'fecha_expiracion', 'asignado_por', 'activo']);
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'rol_modulo', 'rol_id', 'modulo_id')
            ->withPivot(['sistema_id', 'fecha_asignacion']);
    }
}
