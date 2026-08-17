<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'nivel', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    protected static function booted(): void
    {
        static::deleting(function (Rol $rol) {
            DB::table('usuario_rol')->where('rol_id', $rol->id)->delete();
            DB::table('rol_modulo')->where('rol_id', $rol->id)->delete();
        });
    }

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
