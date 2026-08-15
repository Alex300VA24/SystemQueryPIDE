<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DocumentoNumeroRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    private const NOMBRE_REGEX = '/^[\pL\s]+$/u';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::buildRules($this->input('documentoTipoId'), $this->input('personaId'), $this->input('usuarioId'));
    }

    public static function buildRules(int|string|null $documentoTipoId, int|string|null $personaId = null, int|string|null $usuarioId = null): array
    {
        return [
            'tipoPersona' => ['required', Rule::in(['1', '2'])],
            'documentoTipoId' => ['required', 'integer', 'exists:tipo_documento,id'],
            'documentoNumero' => [
                ...DocumentoNumeroRule::rules($documentoTipoId),
                Rule::unique('personas', 'documento_numero')->where('documento_tipo_id', $documentoTipoId)->ignore($personaId),
            ],
            'apellidoPaterno' => ['required', 'string', 'max:20', 'regex:'.self::NOMBRE_REGEX],
            'apellidoMaterno' => ['nullable', 'string', 'max:20', 'regex:'.self::NOMBRE_REGEX],
            'nombres' => ['required', 'string', 'max:40', 'regex:'.self::NOMBRE_REGEX],
            'sexo' => ['required', Rule::in(['M', 'F'])],
            'username' => ['required', 'string', 'min:3', 'max:15', Rule::unique('usuarios', 'username')->ignore($usuarioId)],
            'email' => ['nullable', 'email:rfc', 'max:50', Rule::unique('usuarios', 'email')->ignore($usuarioId)],
            'password' => [$usuarioId ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'roleId' => ['required', 'integer', 'exists:roles,id'],
            'estadoId' => ['required', 'integer', 'exists:cat_estado,id'],
            'cui' => ['required', 'numeric', 'digits:1'],
            'telefonoCodigo' => ['nullable', 'digits_between:1,4', 'required_with:telefonoNumero'],
            'telefonoNumero' => ['nullable', 'digits_between:6,9'],
        ];
    }

    public function messages(): array
    {
        return self::validationMessages();
    }

    public static function validationMessages(): array
    {
        return [
            'tipoPersona.required' => 'Seleccione el tipo de persona.',
            'tipoPersona.in' => 'El tipo de persona seleccionado no es válido.',
            'documentoTipoId.required' => 'Seleccione el tipo de documento.',
            'documentoTipoId.exists' => 'El tipo de documento seleccionado no es válido.',
            'documentoNumero.required' => 'Ingrese el número de documento.',
            'documentoNumero.digits' => 'El número de documento debe tener exactamente :digits dígitos numéricos, sin letras ni símbolos.',
            'documentoNumero.unique' => 'Ya existe una persona registrada con este número de documento.',
            'apellidoPaterno.required' => 'Ingrese el apellido paterno.',
            'apellidoPaterno.regex' => 'El apellido paterno solo debe contener letras.',
            'apellidoPaterno.max' => 'El apellido paterno no debe exceder :max caracteres.',
            'apellidoMaterno.regex' => 'El apellido materno solo debe contener letras.',
            'apellidoMaterno.max' => 'El apellido materno no debe exceder :max caracteres.',
            'nombres.required' => 'Ingrese los nombres.',
            'nombres.regex' => 'Los nombres solo deben contener letras.',
            'nombres.max' => 'Los nombres no deben exceder :max caracteres.',
            'sexo.required' => 'Seleccione el sexo.',
            'sexo.in' => 'El sexo seleccionado no es válido.',
            'username.required' => 'Ingrese el nombre de usuario.',
            'username.min' => 'El nombre de usuario debe tener al menos :min caracteres.',
            'username.max' => 'El nombre de usuario no debe exceder :max caracteres.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.max' => 'El correo no debe exceder :max caracteres.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'Ingrese la contraseña.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'roleId.required' => 'Seleccione un rol.',
            'roleId.exists' => 'El rol seleccionado no es válido.',
            'estadoId.required' => 'Seleccione el estado.',
            'estadoId.exists' => 'El estado seleccionado no es válido.',
            'cui.required' => 'Ingrese el CUI.',
            'cui.numeric' => 'El CUI debe ser numérico.',
            'cui.digits' => 'El CUI debe contener un solo dígito.',
            'telefonoCodigo.digits_between' => 'El código de país debe tener entre :min y :max dígitos numéricos.',
            'telefonoCodigo.required_with' => 'Ingrese el código de país del teléfono.',
            'telefonoNumero.digits_between' => 'El número de teléfono debe tener entre :min y :max dígitos numéricos.',
        ];
    }

    public function attributes(): array
    {
        return self::validationAttributes();
    }

    public static function validationAttributes(): array
    {
        return [
            'documentoNumero' => 'número de documento', 'apellidoPaterno' => 'apellido paterno',
            'apellidoMaterno' => 'apellido materno', 'tipoPersona' => 'tipo de persona',
            'roleId' => 'rol', 'password' => 'contraseña', 'estadoId' => 'estado', 'cui' => 'CUI',
            'telefonoCodigo' => 'código de teléfono', 'telefonoNumero' => 'número de teléfono',
        ];
    }
}
