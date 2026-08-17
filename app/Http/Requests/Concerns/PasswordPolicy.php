<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rules\Password;

/**
 * Política de contraseñas del sistema local (SCPIDE), reutilizada por todos
 * los formularios que crean o cambian la contraseña de un Usuario.
 *
 * No se aplica a la credencial PIDE (ver PidePasswordRequest): esa contraseña
 * la valida el servicio externo RENIEC, cuyas propias reglas de formato no
 * están documentadas en este proyecto ni deben inventarse aquí.
 */
class PasswordPolicy
{
    /**
     * Reglas de robustez de la contraseña. No incluye 'required'/'nullable':
     * cada formulario decide esa parte según si la contraseña es obligatoria
     * en ese contexto (p. ej. opcional al editar un usuario existente).
     *
     * @return array<int, mixed>
     */
    public static function rules(): array
    {
        return [
            'string',
            Password::min(12)->mixedCase()->numbers()->symbols(),
        ];
    }
}
