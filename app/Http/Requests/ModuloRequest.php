<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::buildRules($this->input('sistemaId'), $this->input('nivel'), $this->input('moduloId'));
    }

    public static function buildRules(int|string|null $sistemaId, int|string|null $nivel, int|string|null $moduloId = null): array
    {
        return [
            'sistemaId' => ['required', 'integer', 'exists:sistemas,id'],
            'codigo' => ['required', 'regex:/^[A-Za-z0-9_-]+$/', 'max:10', Rule::unique('modulos')->where('sistema_id', $sistemaId)->ignore($moduloId)],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:1000'],
            'url' => ['required', 'string', 'max:255'],
            'icono' => ['required', 'string', 'max:100', 'exists:iconos,clase'],
            'parentId' => ['nullable', Rule::requiredIf((int) $nivel > 1), 'integer', 'exists:modulos,id', Rule::notIn(array_filter([$moduloId]))],
            'orden' => ['required', 'integer', 'min:1'],
            'nivel' => ['required', 'integer', 'between:1,4'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return [
            'sistemaId.required' => 'Seleccione el sistema.',
            'sistemaId.exists' => 'El sistema seleccionado no es válido.',
            'codigo.required' => 'Ingrese el código del módulo.',
            'codigo.regex' => 'El código solo puede contener letras, números, guiones y guiones bajos.',
            'codigo.max' => 'El código no debe exceder :max caracteres.',
            'codigo.unique' => 'Ya existe un módulo con este código en el sistema seleccionado.',
            'nombre.required' => 'Ingrese el nombre del módulo.',
            'nombre.max' => 'El nombre no debe exceder :max caracteres.',
            'descripcion.required' => 'Ingrese la descripción del módulo.',
            'descripcion.max' => 'La descripción no debe exceder :max caracteres.',
            'url.required' => 'Ingrese la ruta del módulo.',
            'url.max' => 'La ruta no debe exceder :max caracteres.',
            'icono.required' => 'Seleccione un ícono.',
            'icono.exists' => 'El ícono seleccionado no es válido.',
            'parentId.required' => 'Seleccione el módulo padre.',
            'parentId.exists' => 'El módulo padre seleccionado no es válido.',
            'parentId.not_in' => 'Un módulo no puede ser padre de sí mismo.',
            'orden.required' => 'Ingrese el orden del módulo.',
            'orden.min' => 'El orden debe ser al menos :min.',
            'nivel.required' => 'Seleccione el nivel del módulo.',
            'nivel.between' => 'El nivel debe estar entre :min y :max.',
        ];
    }

    public function attributes(): array
    {
        return self::validationAttributes();
    }

    public static function validationAttributes(): array
    {
        return ['codigo' => 'código', 'url' => 'ruta', 'icono' => 'ícono', 'parentId' => 'módulo padre', 'descripcion' => 'descripción', 'nivel' => 'nivel', 'orden' => 'orden'];
    }
}
