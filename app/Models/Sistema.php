<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sistema extends Model
{
    protected $table = 'sistemas';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'url', 'icono', 'version', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    protected static function booted(): void
    {
        static::deleting(function (Sistema $sistema) {
            $sistema->modulos()->each(fn (Modulo $modulo) => $modulo->delete());

            DB::table('rol_modulo')->where('sistema_id', $sistema->id)->delete();
        });
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class, 'sistema_id');
    }
}
