<?php

namespace App\Http\Requests\Concerns;

use App\Models\TipoDocumento;

class DocumentoNumeroRule
{
    public static function digitsFor(int|string|null $documentoTipoId): int
    {
        $codigo = TipoDocumento::find($documentoTipoId)?->codigo;

        return match ($codigo) {
            'DNI' => 8,
            'RUC' => 11,
            default => 9,
        };
    }

    public static function rules(int|string|null $documentoTipoId): array
    {
        return ['required', 'digits:'.self::digitsFor($documentoTipoId)];
    }
}
