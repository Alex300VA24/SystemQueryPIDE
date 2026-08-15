<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidarCuiRequest extends FormRequest
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
        return ['cui' => ['required', 'numeric', 'digits:1']];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return [
            'cui.required' => 'Ingrese el último dígito de su CUI.',
            'cui.numeric' => 'El CUI debe ser numérico.',
            'cui.digits' => 'El CUI debe contener un solo dígito.',
        ];
    }
}
