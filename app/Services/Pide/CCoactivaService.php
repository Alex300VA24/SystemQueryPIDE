<?php

namespace App\Services\Pide;

use App\Services\Pide\Contracts\CCoactivaServiceInterface;
use App\Services\Pide\Contracts\PideHttpClientInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de consulta de deudas en cobranza coactiva (SUNAT WS3).
 *
 * Endpoint: GET {url_ccoactiva}?docIdentidad=&numDocIdentidad=&out=json
 *
 * POLÍTICA OBLIGATORIA: este servicio debe consumirse bajo demanda
 * transaccional (una consulta por invocación). Prohibido su uso para
 * procesos batch/masivos; infringirlo implica revocación inmediata de
 * credenciales por parte de SUNAT. Los datos obtenidos son confidenciales:
 * no exponer públicamente ni almacenar fuera del propósito autorizado
 * (DS 067-2017-PCM, DS 121-2017-PCM, DL 1246).
 */
class CCoactivaService implements CCoactivaServiceInterface
{
    private const DOC_DNI = '01';
    private const DOC_RUC = '06';

    /** Matriz oficial de códigos de error del servicio WS3. */
    private const ERROR_MESSAGES = [
        '50000' => 'Faltan los parámetros de entrada.',
        '50001' => 'El DNI no es válido.',
        '50002' => 'El número de DNI no tiene un RUC asociado.',
        '50003' => 'El RUC no es válido.',
        '50004' => 'No se encuentra el número de RUC.',
        '50005' => 'El tipo de documento enviado no es válido.',
        '50006' => 'Error inesperado al obtener la deuda en coactiva notificada a centrales de riesgo.',
        '50007' => 'Los valores de los parámetros no deben ser vacíos.',
    ];

    /** @var PideHttpClientInterface */
    private $httpClient;

    /** @var string */
    private $urlCCoactiva;

    public function __construct(PideHttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->urlCCoactiva = config('pide.url_ccoactiva');
    }

    /**
     * {@inheritdoc}
     */
    public function consultarDeudaCoactiva(string $tipoDocumento, string $numeroDocumento): array
    {
        $validacion = $this->validarParametros($tipoDocumento, $numeroDocumento);
        if ($validacion !== null) {
            return $validacion;
        }

        try {
            $url = $this->urlCCoactiva
                . '?docIdentidad=' . urlencode($tipoDocumento)
                . '&numDocIdentidad=' . urlencode($numeroDocumento)
                . '&out=json';

            $curlResult = $this->httpClient->execute($url, null, 'GET', 'SUNAT-CCoactiva');

            if (!$curlResult['success']) {
                return $this->fallo($curlResult['error'], null);
            }

            Log::info('Consulta cobranza coactiva (SUNAT WS3)', [
                'service' => 'SUNAT-CCoactiva',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
                'tipo_documento' => $tipoDocumento,
            ]);

            if ($curlResult['httpCode'] !== 200) {
                return $this->fallo("Error HTTP {$curlResult['httpCode']} en el servicio SUNAT-CCoactiva", null);
            }

            return $this->procesarRespuesta($curlResult['response']);
        } catch (\Exception $e) {
            Log::error('Excepción al consultar cobranza coactiva (SUNAT WS3)', [
                'service' => 'SUNAT-CCoactiva',
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return $this->fallo('Error al consultar deuda coactiva: ' . $e->getMessage(), null);
        }
    }

    // ========================================
    // VALIDACIÓN DE ENTRADA
    // ========================================

    private function validarParametros(string $tipoDocumento, string $numeroDocumento): ?array
    {
        if ($tipoDocumento === '' || $numeroDocumento === '') {
            return $this->fallo(self::ERROR_MESSAGES['50007'], '50007');
        }

        if (!in_array($tipoDocumento, [self::DOC_DNI, self::DOC_RUC], true)) {
            return $this->fallo(self::ERROR_MESSAGES['50005'], '50005');
        }

        if ($tipoDocumento === self::DOC_DNI && !preg_match('/^\d{8}$/', $numeroDocumento)) {
            return $this->fallo(self::ERROR_MESSAGES['50001'], '50001');
        }

        if ($tipoDocumento === self::DOC_RUC && !preg_match('/^\d{11}$/', $numeroDocumento)) {
            return $this->fallo(self::ERROR_MESSAGES['50003'], '50003');
        }

        return null;
    }

    // ========================================
    // PROCESAMIENTO DE RESPUESTA
    // ========================================

    private function procesarRespuesta(string $jsonResponse): array
    {
        $respuesta = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fallo('Error al decodificar respuesta JSON de SUNAT WS3: ' . json_last_error_msg(), null);
        }

        $status = filter_var($respuesta['status'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$status) {
            $errorCode = isset($respuesta['errorCode']) ? (string) $respuesta['errorCode'] : null;
            $mensaje = $errorCode !== null && isset(self::ERROR_MESSAGES[$errorCode])
                ? self::ERROR_MESSAGES[$errorCode]
                : 'Error desconocido al consultar deuda coactiva en SUNAT WS3.';

            return $this->fallo($mensaje, $errorCode);
        }

        $items = $respuesta['data'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        return [
            'success' => true,
            'message' => empty($items) ? 'No se encontraron deudas en cobranza coactiva.' : 'Consulta exitosa',
            'errorCode' => null,
            'data' => array_map([$this, 'mapearDeuda'], array_values($items)),
        ];
    }

    private function mapearDeuda(array $item): array
    {
        return [
            'nomRuc' => trim((string) ($item['nomRuc'] ?? '')),
            'numRuc' => trim((string) ($item['numRuc'] ?? '')),
            'mtoDeuda' => (float) ($item['mtoDeuda'] ?? 0),
            'fecAct' => (string) ($item['fecAct'] ?? ''),
            'fecTraCoa' => (string) ($item['fecTraCoa'] ?? ''),
            'desEntidad' => trim((string) ($item['desEntidad'] ?? '')),
            'perDoc' => (string) ($item['perDoc'] ?? ''),
        ];
    }

    private function fallo(string $mensaje, ?string $errorCode): array
    {
        return [
            'success' => false,
            'message' => $mensaje,
            'errorCode' => $errorCode,
            'data' => [],
        ];
    }
}
