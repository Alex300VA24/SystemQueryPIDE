<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'persona_id', 'username', 'password_hash', 'email', 'telefono',
        'requiere_cambio_password', 'intentos_fallidos', 'fecha_ultimo_acceso',
        'estado_id', 'fecha_actualizacion_password', 'cui',
    ];

    protected $hidden = ['password_hash', 'remember_token', 'cui'];

    protected $casts = [
        'requiere_cambio_password' => 'boolean',
        'fecha_ultimo_acceso' => 'datetime',
        'fecha_actualizacion_password' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatEstado::class, 'estado_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
            ->withPivot(['fecha_asignacion', 'fecha_expiracion', 'asignado_por', 'activo']);
    }

    public function rolesActivos()
    {
        return $this->roles()->wherePivot('activo', true)->where('roles.activo', true);
    }

    public function sesiones()
    {
        return $this->hasMany(SesionUsuario::class, 'usuario_id');
    }

    public function isActivo(): bool
    {
        return (int) $this->estado_id === CatEstado::ACTIVO;
    }

    public function isBloqueado(): bool
    {
        return (int) $this->estado_id === CatEstado::BLOQUEADO;
    }

    public function name(): string
    {
        return $this->persona?->nombreCompleto() ?? $this->username;
    }
}
