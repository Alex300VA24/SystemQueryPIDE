<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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

    protected static function booted(): void
    {
        static::deleting(function (Usuario $usuario) {
            $usuario->sesiones()->each(fn (SesionUsuario $sesion) => $sesion->delete());

            DB::table('usuario_rol')->where('usuario_id', $usuario->id)->delete();
            DB::table('usuario_rol')->where('asignado_por', $usuario->id)->update(['asignado_por' => null]);
            DB::table('historial_auditoria')->where('usuario_id', $usuario->id)->update(['usuario_id' => null]);
        });
    }

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

    /**
     * Indica si el usuario tiene, mediante alguno de sus roles activos,
     * acceso al módulo indicado (por código, p. ej. "DNI", "RUC", "PAR").
     */
    public function tieneAccesoModulo(string $codigo): bool
    {
        return $this->rolesActivos()
            ->whereHas('modulos', fn ($query) => $query->where('modulos.codigo', $codigo))
            ->exists();
    }

    /**
     * Igual que tieneAccesoModulo(), pero por ID de módulo (estable aunque
     * el admin renombre el código desde Gestión de Módulos).
     */
    public function tieneAccesoModuloId(int $moduloId): bool
    {
        return $this->rolesActivos()
            ->whereHas('modulos', fn ($query) => $query->where('modulos.id', $moduloId))
            ->exists();
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
