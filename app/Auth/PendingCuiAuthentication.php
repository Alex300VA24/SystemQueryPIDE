<?php

namespace App\Auth;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

final class PendingCuiAuthentication
{
    private const SESSION_KEY = 'auth.pending_cui';

    private const TTL_SECONDS = 300;

    public function start(int $usuarioId, bool $remember): void
    {
        Session::put(self::SESSION_KEY, [
            'usuario_id' => $usuarioId,
            'remember' => $remember,
            'created_at' => now()->timestamp,
        ]);
    }

    public function authenticatePending(string $cui): Usuario
    {
        $pending = Session::get(self::SESSION_KEY);

        if (! is_array($pending)
            || ! isset($pending['usuario_id'], $pending['created_at'])
            || now()->timestamp - (int) $pending['created_at'] > self::TTL_SECONDS) {
            $this->clear();

            throw ValidationException::withMessages([
                'cui' => 'La validación expiró. Ingrese nuevamente sus credenciales.',
            ]);
        }

        return $this->authenticate(
            (int) $pending['usuario_id'],
            $cui,
            (bool) ($pending['remember'] ?? false),
        );
    }

    public function authenticate(int $usuarioId, string $cui, bool $remember = false): Usuario
    {
        $usuario = Usuario::find($usuarioId);

        if (! $usuario || ! hash_equals((string) $usuario->cui, $cui)) {
            throw ValidationException::withMessages([
                'cui' => 'Código CUI incorrecto.',
            ]);
        }

        $usuario->forceFill([
            'intentos_fallidos' => 0,
            'fecha_ultimo_acceso' => now(),
        ])->save();

        Auth::login($usuario, $remember);
        $this->clear();

        return $usuario;
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
