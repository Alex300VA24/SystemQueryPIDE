<?php

namespace App\Services\Pide;

use Illuminate\Support\Facades\Session;

/**
 * Único punto de acceso a la credencial PIDE del usuario autenticado.
 *
 * La credencial se guarda en la sesión del servidor (cifrada cuando
 * `SESSION_ENCRYPT` está activo, ver config/session.php) junto con su propia
 * marca de expiración, independiente del tiempo de vida de la sesión.
 * Ningún componente Livewire ni evento debe leer/escribir la clave de
 * sesión directamente: todo pasa por esta clase.
 */
class PideCredentialStore
{
    private const SESSION_KEY = 'pide_credential';

    public function store(string $password): void
    {
        Session::put(self::SESSION_KEY, [
            'password' => $password,
            'expires_at' => now()->addMinutes($this->ttlMinutes())->timestamp,
        ]);
    }

    public function get(): ?string
    {
        $entry = Session::get(self::SESSION_KEY);

        if (! is_array($entry) || ! isset($entry['password'], $entry['expires_at'])) {
            return null;
        }

        if (now()->timestamp > (int) $entry['expires_at']) {
            $this->forget();

            return null;
        }

        return (string) $entry['password'];
    }

    public function has(): bool
    {
        return $this->get() !== null;
    }

    public function forget(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    private function ttlMinutes(): int
    {
        return (int) config('pide.credential_ttl_minutes', 15);
    }
}
