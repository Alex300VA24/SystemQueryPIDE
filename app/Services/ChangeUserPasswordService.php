<?php

namespace App\Services;

use App\Models\HistorialAuditoria;
use App\Models\Usuario;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\PideCredentialStore;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Cambia la contraseña local de un Usuario y, cuando corresponde, sincroniza
 * la misma contraseña como credencial PIDE ante RENIEC.
 *
 * Orden intencional: si el usuario tiene acceso PIDE, la contraseña remota
 * se cambia PRIMERO. Solo si RENIEC confirma el cambio se actualiza el hash
 * local. Así, si PIDE falla, ambos sistemas quedan consistentes con la
 * contraseña anterior; si se invirtiera el orden, un fallo de PIDE dejaría
 * el sistema local con una contraseña que el operador ya no podría usar
 * para autenticarse ante RENIEC.
 *
 * Solo DNI (RENIEC) y PAR (SUNARP, que internamente consulta RENIEC) usan
 * credencial personal ante RENIEC. RUC (SUNAT) consulta con URL fija de
 * config, sin credencial de usuario, así que no dispara este sync: forzarlo
 * rompía a usuarios con convenio solo en SUNAT ("Usuario no registrado en
 * ningún convenio" al cambiar password).
 */
class ChangeUserPasswordService
{
    private const PIDE_MODULE_CODES = ['DNI', 'PAR'];

    public function __construct(
        private readonly ReniecServiceInterface $reniecService,
        private readonly PideCredentialStore $credentialStore,
    ) {
    }

    public function change(Usuario $usuario, string $currentPassword, string $newPassword): void
    {
        if (! $this->tieneAccesoPide($usuario)) {
            $this->actualizarHashLocal($usuario, $newPassword);
            $this->auditar($usuario, 'CAMBIO_PASSWORD');

            return;
        }

        $dniUsuario = (string) ($usuario->persona?->documento_numero ?? '');

        $resultado = $this->reniecService->actualizarPasswordRENIEC($currentPassword, $newPassword, $dniUsuario);

        if (! ($resultado['success'] ?? false)) {
            $this->auditar($usuario, 'CAMBIO_PASSWORD_PIDE', 'Falló la actualización remota, no se modificó ninguna contraseña.');

            throw ValidationException::withMessages([
                'currentPassword' => $resultado['message'] ?? 'No se pudo actualizar la contraseña PIDE. La contraseña no fue modificada.',
            ]);
        }

        $this->actualizarHashLocal($usuario, $newPassword);
        $this->credentialStore->store($newPassword);
        $this->auditar($usuario, 'CAMBIO_PASSWORD_PIDE');
    }

    private function actualizarHashLocal(Usuario $usuario, string $newPassword): void
    {
        $usuario->forceFill([
            'password_hash' => Hash::make($newPassword),
            'requiere_cambio_password' => false,
            'fecha_actualizacion_password' => now(),
        ])->save();
    }

    private function tieneAccesoPide(Usuario $usuario): bool
    {
        foreach (self::PIDE_MODULE_CODES as $codigo) {
            if ($usuario->tieneAccesoModulo($codigo)) {
                return true;
            }
        }

        return false;
    }

    private function auditar(Usuario $usuario, string $operacion, ?string $observacion = null): void
    {
        HistorialAuditoria::create([
            'tabla' => 'usuarios',
            'registro_id' => $usuario->id,
            'operacion' => $operacion,
            'usuario_id' => $usuario->id,
            'fecha' => now(),
            'ip' => request()?->ip(),
            'observacion' => $observacion,
        ]);

        Log::info('Cambio de contraseña registrado', [
            'operation' => $operacion,
            'user_id' => $usuario->id,
        ]);
    }
}
