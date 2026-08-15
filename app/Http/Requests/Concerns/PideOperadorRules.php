<?php

namespace App\Http\Requests\Concerns;

class PideOperadorRules
{
    public static function dniUsuarioRules(): array
    {
        return ['required', 'digits:8'];
    }

    public static function messages(): array
    {
        return [
            'dniUsuario.required' => 'Ingrese el DNI del usuario PIDE.',
            'dniUsuario.digits' => 'El DNI del usuario PIDE debe tener exactamente 8 dígitos numéricos.',
        ];
    }

    public static function attributes(): array
    {
        return ['dniUsuario' => 'DNI de usuario PIDE'];
    }
}
