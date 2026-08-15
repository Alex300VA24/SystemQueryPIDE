<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\PideOperadorRules;
use Illuminate\Foundation\Http\FormRequest;

class ConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public static function buildRules(array|string $queryRules, bool $needsCredentials, bool $needsOficina): array
    {
        $rules = ['busqueda' => $queryRules];
        if ($needsCredentials) {
            $rules['dniUsuario'] = PideOperadorRules::dniUsuarioRules();
        }
        if ($needsOficina) {
            $rules['oficina'] = ['required', 'string'];
        }

        return $rules;
    }

    public static function validationMessages(): array
    {
        return array_merge(PideOperadorRules::messages(), [
            'busqueda.required' => 'Este campo es obligatorio.',
            'busqueda.digits' => 'Debe ingresar exactamente :digits dígitos numéricos, sin letras ni símbolos.',
            'busqueda.regex' => 'El formato ingresado no es válido.',
            'oficina.required' => 'Seleccione la oficina registral.',
        ]);
    }

    public static function validationAttributes(string $fieldLabel = 'valor'): array
    {
        return array_merge(PideOperadorRules::attributes(), [
            'busqueda' => $fieldLabel,
            'oficina' => 'oficina registral',
        ]);
    }
}
