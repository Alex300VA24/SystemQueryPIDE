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
        $logo = $this->imageDataUri(base64_encode((string) file_get_contents(public_path('assets/images/muni2.png'))));
        $photo = $this->imageDataUri($entry['photo'] ?? null);

        $pdf = Pdf::loadView('pdf.dni', [
            'result' => $result,
            'logo' => $logo,
            'photo' => $photo,
        ])->setPaper('a4');

        return $pdf->download('dni-'.($result['DNI'] ?? 'reniec').'.pdf');
    }

    private function imageDataUri(mixed $image): ?string
    {
        if (! is_string($image) || trim($image) === '') {
            return null;
        }

        $mime = null;
        $payload = $image;

        if (preg_match('/^data:(image\/(?:png|jpe?g));base64,(.*)$/is', $image, $matches) === 1) {
            $mime = strtolower($matches[1]);
            $payload = $matches[2];
        }

        $binary = base64_decode((string) preg_replace('/\s+/', '', $payload), true);

        if ($binary === false) {
            return null;
        }

        $detected = @getimagesizefromstring($binary);
        $mime ??= is_array($detected) ? ($detected['mime'] ?? null) : null;

        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }
}
