<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PideCredentialRequest extends FormRequest
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
        return ['password' => ['required', 'string']];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return ['password.required' => 'Ingrese la contraseña PIDE.'];
    }

    public function attributes(): array
    {
        return self::validationAttributes();
    }

    public static function validationAttributes(): array
    {
        return ['password' => 'contraseña PIDE'];
    }
}
