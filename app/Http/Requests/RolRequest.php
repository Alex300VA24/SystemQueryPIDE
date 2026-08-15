<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::buildRules($this->input('rolId'));
    }

    public static function buildRules(int|string|null $rolId = null): array
    {
        return [
            'codigo' => ['required', 'alpha_dash', 'max:50', Rule::unique('roles')->ignore($rolId)],
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'nivel' => ['required', 'integer', 'between:1,10'],
            'activo' => ['boolean'],
            'selectedModuleIds' => ['required', 'array', 'min:1'],
            'selectedModuleIds.*' => ['integer', 'exists:modulos,id'],
        ];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return [
            'codigo.required' => 'Ingrese el código del rol.',
            'codigo.alpha_dash' => 'El código solo puede contener letras, números, guiones y guiones bajos.',
            'codigo.max' => 'El código no debe exceder :max caracteres.',
            'codigo.unique' => 'Ya existe un rol con este código.',
            'nombre.required' => 'Ingrese el nombre del rol.',
            'nombre.min' => 'El nombre debe tener al menos :min caracteres.',
            'nombre.max' => 'El nombre no debe exceder :max caracteres.',
            'descripcion.max' => 'La descripción no debe exceder :max caracteres.',
            'nivel.required' => 'Ingrese el nivel del rol.',
            'nivel.between' => 'El nivel debe estar entre :min y :max.',
            'selectedModuleIds.required' => 'Seleccione al menos un módulo.',
            'selectedModuleIds.min' => 'Seleccione al menos un módulo.',
            'selectedModuleIds.*.exists' => 'Uno de los módulos seleccionados no es válido.',
        ];
    }

    public function attributes(): array
    {
        return self::validationAttributes();
    }

    public static function validationAttributes(): array
    {
        return ['selectedModuleIds' => 'módulos', 'selectedModuleIds.*' => 'módulo'];
    }
}
