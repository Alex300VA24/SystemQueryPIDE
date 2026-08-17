<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Modulo extends Model
{
    /**
     * IDs fijos de módulos de sistema (no cambian aunque el admin edite
     * el código/nombre desde Gestión de Módulos). Usados para gating
     * de acceso donde el código no debe romperse al renombrarlo.
     */
    public const ID_USUARIOS = 8;

    public const ID_ROLES = 10;

    public const ID_MODULOS = 11;

    protected $table = 'modulos';

    protected $fillable = [
        'sistema_id', 'padre_id', 'codigo', 'nombre', 'descripcion',
        'url', 'icono', 'orden', 'nivel', 'es_menu', 'activo',
    ];

    protected $casts = ['es_menu' => 'boolean', 'activo' => 'boolean'];

    protected static function booted(): void
    {
        static::deleting(function (Modulo $modulo) {
            $modulo->children()->update(['padre_id' => null]);

            DB::table('rol_modulo')->where('modulo_id', $modulo->id)->delete();
        });
    }

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
