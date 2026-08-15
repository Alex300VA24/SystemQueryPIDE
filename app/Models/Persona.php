<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';

    protected $fillable = [
        'tipo_persona', 'documento_tipo_id', 'documento_numero',
        'apellido_paterno', 'apellido_materno', 'nombres', 'fecha_nacimiento', 'sexo',
        'telefono_fijo', 'telefono_movil', 'direccion',
        'via_nombre', 'via_numero', 'via_mz', 'via_lote', 'foto_url', 'estado_id',
    ];

    protected $casts = ['fecha_nacimiento' => 'date'];

    public function documentoTipo()
    {
        return $this->belongsTo(TipoDocumento::class, 'documento_tipo_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatEstado::class, 'estado_id');
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'persona_id');
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
