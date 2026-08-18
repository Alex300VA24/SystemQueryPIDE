<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

final class PartidaRegistralPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $entry = Cache::get("sunarp_partida_pdf:{$data['token']}");

        abort_if($entry === null, 404, 'Las imágenes expiraron. Vuelva a consultar la partida.');
        abort_unless(($entry['user_id'] ?? null) === $request->user()->id, 403);

        $numero = (string) ($entry['numero'] ?? 'registral');
        $images = array_values(array_filter($entry['images'] ?? [], 'is_string'));

        abort_if($images === [], 404, 'No hay imágenes disponibles para generar el PDF.');

        return Pdf::loadView('pdf.partida-registral', [
            'images' => $images,
        ])->setPaper('a4')->download("partida-{$numero}.pdf");
    }
}
