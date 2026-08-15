<?php

namespace App\Livewire\Forms;

use App\Auth\PendingCuiAuthentication;
use App\Models\CatEstado;
use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string', message: ['username.required' => 'Ingrese su usuario.', 'username.string' => 'El usuario no es válido.'])]
    public string $username = '';

    #[Validate('required|string', message: ['password.required' => 'Ingrese su contraseña.', 'password.string' => 'La contraseña no es válida.'])]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Paso 1: valida usuario/contraseña y reglas de bloqueo, sin iniciar sesión.
     * Devuelve el id del usuario pendiente de verificación CUI.
     *
     * @throws ValidationException
     */
    public function verifyCredentials(): int
    {
        $this->ensureIsNotRateLimited();

        $usuario = Usuario::query()->where('username', $this->username)->first();

        if (! $usuario || ! Hash::check($this->password, $usuario->password_hash)) {
            if ($usuario && ! $usuario->isBloqueado()) {
                $usuario->increment('intentos_fallidos');
                if ($usuario->intentos_fallidos >= 5) {
                    $usuario->update(['estado_id' => CatEstado::BLOQUEADO]);
                }
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.username' => 'Estas credenciales no coinciden con nuestros registros.',
            ]);
        }

        if ($usuario->isBloqueado()) {
            throw ValidationException::withMessages([
                'form.username' => 'Usuario bloqueado por intentos fallidos. Contacte al administrador.',
            ]);
        }

        if (! $usuario->isActivo()) {
            throw ValidationException::withMessages([
                'form.username' => 'Usuario inactivo o sin acceso al sistema.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $usuario->id;
    }

    /**
     * Paso 2: verifica el CUI y autentica al usuario.
     *
     * @throws ValidationException
     */
    public function verifyCuiAndLogin(int $usuarioId, string $cui): Usuario
    {
        return app(PendingCuiAuthentication::class)->authenticate(
            $usuarioId,
            $cui,
            $this->remember,
        );
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.username' => "Demasiados intentos de inicio de sesión. Inténtelo nuevamente en {$seconds} segundos.",
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username).'|'.request()->ip());
    }
}
