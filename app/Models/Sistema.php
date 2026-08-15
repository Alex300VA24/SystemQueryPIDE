<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    protected $table = 'sistemas';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'url', 'icono', 'version', 'orden', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function modulos()
    {
        return $this->hasMany(Modulo::class, 'sistema_id');
    }
}
