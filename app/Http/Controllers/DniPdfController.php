<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Genera el PDF de una consulta RENIEC ya realizada por el usuario.
 *
 * El navegador solo envía el token de la consulta (ver
 * App\Livewire\BaseConsultation::cacheResultForPdf); los datos de la
 * persona nunca vienen del cliente, se recuperan de la caché del servidor
 * y se verifica que la consulta pertenezca al usuario autenticado.
 */
final class DniPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $entry = Cache::get("reniec_result:{$data['token']}");

        abort_if($entry === null, 404, 'La consulta expiró o no existe. Vuelva a realizarla.');
        abort_unless(($entry['user_id'] ?? null) === $request->user()->id, 403);

        $result = $entry['result'] ?? [];

        $pdf = Pdf::loadView('pdf.dni', [
            'dni' => $result['DNI'] ?? '',
            'nombres' => $result['Nombres'] ?? '',
            'apellido_paterno' => $result['Apellido paterno'] ?? '',
            'apellido_materno' => $result['Apellido materno'] ?? '',
            'estado_civil' => $result['Estado civil'] ?? '',
            'direccion' => $result['Dirección'] ?? '',
        ]);

        return $pdf->download('dni-'.($result['DNI'] ?? 'reniec').'.pdf');
    }
}
