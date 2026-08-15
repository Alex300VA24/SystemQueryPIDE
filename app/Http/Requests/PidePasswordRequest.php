<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\PideOperadorRules;
use Illuminate\Foundation\Http\FormRequest;

class PidePasswordRequest extends FormRequest
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
            'dniUsuario' => PideOperadorRules::dniUsuarioRules(),
            'credencialAnterior' => ['required', 'string'],
            'credencialNueva' => ['required', 'string', 'min:6', 'different:credencialAnterior', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return array_merge(PideOperadorRules::messages(), [
            'credencialAnterior.required' => 'Ingrese la contraseña PIDE actual.',
            'credencialNueva.required' => 'Ingrese la nueva contraseña PIDE.',
            'credencialNueva.min' => 'La nueva contraseña PIDE debe tener al menos :min caracteres.',
            'credencialNueva.different' => 'La nueva contraseña PIDE debe ser diferente a la actual.',
            'credencialNueva.confirmed' => 'La confirmación de la nueva contraseña PIDE no coincide.',
        ]);
    }

    public function attributes(): array
    {
        return self::validationAttributes();
    }

    public static function validationAttributes(): array
    {
        return array_merge(PideOperadorRules::attributes(), [
            'credencialAnterior' => 'contraseña PIDE actual',
            'credencialNueva' => 'nueva contraseña PIDE',
        ]);
    }
}
