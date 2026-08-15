<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'sistema_id', 'padre_id', 'codigo', 'nombre', 'descripcion',
        'url', 'icono', 'orden', 'nivel', 'es_menu', 'activo',
    ];

    protected $casts = ['es_menu' => 'boolean', 'activo' => 'boolean'];

    public function sistema()
    {
        return $this->belongsTo(Sistema::class, 'sistema_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'padre_id')->orderBy('orden');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_modulo', 'modulo_id', 'rol_id')
            ->withPivot(['sistema_id', 'fecha_asignacion']);
    }
}
