<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::buildRules();
    }

    public static function buildRules(): array
    {
        return [
            'currentPassword' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'different:currentPassword'],
        ];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return [
            'currentPassword.required' => 'Ingrese su contraseña actual.',
            'currentPassword.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Ingrese la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.different' => 'La nueva contraseña debe ser diferente a la actual.',
        ];
    }

    public function attributes(): array
    {
        return self::validationAttributes();
    }

    public static function validationAttributes(): array
    {
        return ['currentPassword' => 'contraseña actual', 'password' => 'nueva contraseña'];
    }
}
