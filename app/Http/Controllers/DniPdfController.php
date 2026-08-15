<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class DniPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'dni' => ['required', 'digits:8'],
            'nombres' => ['nullable', 'string'],
            'apellido_paterno' => ['nullable', 'string'],
            'apellido_materno' => ['nullable', 'string'],
            'estado_civil' => ['nullable', 'string'],
            'direccion' => ['nullable', 'string'],
        ]);

        $pdf = Pdf::loadView('pdf.dni', [
            'dni' => $data['dni'],
            'nombres' => $data['nombres'] ?? '',
            'apellido_paterno' => $data['apellido_paterno'] ?? '',
            'apellido_materno' => $data['apellido_materno'] ?? '',
            'estado_civil' => $data['estado_civil'] ?? '',
            'direccion' => $data['direccion'] ?? '',
        ]);

        return $pdf->download("dni-{$data['dni']}.pdf");
    }
}
