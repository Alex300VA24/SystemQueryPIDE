<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAuditoria extends Model
{
    protected $table = 'historial_auditoria';

    public $timestamps = false;

    protected $fillable = ['tabla', 'registro_id', 'operacion', 'usuario_id', 'fecha', 'ip', 'datos_anteriores', 'datos_nuevos', 'observacion'];

    protected $casts = [
        'fecha' => 'datetime',
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
