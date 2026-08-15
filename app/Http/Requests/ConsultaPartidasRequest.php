<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\PideOperadorRules;
use Illuminate\Foundation\Http\FormRequest;

class ConsultaPartidasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public static function naturalRules(): array
    {
        return [
            'naturalDni' => ['required', 'digits:8'],
            'dniUsuario' => PideOperadorRules::dniUsuarioRules(),
        ];
    }

    public static function juridicaRules(string $mode): array
    {
        return $mode === 'ruc'
            ? ['juridicaQuery' => ['required', 'digits:11']]
            : ['juridicaQuery' => ['required', 'string', 'min:3', 'max:255']];
    }

    public static function partidaRules(): array
    {
        return [
            'busqueda' => ['required', 'string', 'max:20'],
            'oficina' => ['required', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return array_merge(PideOperadorRules::messages(), [
            'naturalDni.required' => 'Ingrese el DNI de la persona.',
            'naturalDni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'juridicaQuery.required' => 'Ingrese el dato de búsqueda.',
            'juridicaQuery.digits' => 'El RUC debe tener exactamente 11 dígitos numéricos.',
            'juridicaQuery.min' => 'La razón social debe tener al menos :min caracteres.',
            'juridicaQuery.max' => 'La razón social no debe exceder :max caracteres.',
            'busqueda.required' => 'Ingrese el número de partida.',
            'busqueda.max' => 'El número de partida no debe exceder :max caracteres.',
            'oficina.required' => 'Seleccione la oficina registral.',
        ]);
    }

    public static function validationAttributes(string $juridicaMode = 'ruc'): array
    {
        return array_merge(PideOperadorRules::attributes(), [
            'naturalDni' => 'DNI',
            'juridicaQuery' => $juridicaMode === 'ruc' ? 'RUC' : 'razón social',
            'busqueda' => 'número de partida',
            'oficina' => 'oficina registral',
        ]);
    }
}
