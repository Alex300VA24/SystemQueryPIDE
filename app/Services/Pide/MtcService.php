<?php

namespace App\Services\Pide;

use App\Services\Pide\Contracts\MtcServiceInterface;
use App\Services\Pide\Contracts\PideHttpClientInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de consultas del récord de conductor del MTC (WS3 PIDE).
 *
 * Endpoints REST (GET) derivados de la base configurable `pide.url_mtc`:
 *  - /DatosPapeletas   → papeletas de tránsito aplicadas
 *  - /UltimaLicencia   → última licencia de conducir emitida
 *  - /UltimasSanciones → sanciones vigentes
 *
 * Parámetros comunes: iTipoDocumento, sNumDocumento y out=json.
 *
 * POLÍTICA OBLIGATORIA: este servicio debe consumirse bajo demanda
 * transaccional (una consulta por invocación). Prohibido su uso para
 * procesos batch/masivos; infringirlo implica revocación inmediata de
 * accesos por parte del MTC. Los datos obtenidos son confidenciales:
 * no exponerlos públicamente ni almacenarlos fuera del propósito autorizado
 * (DS 083-2017-PCM, DL 1246).
 */
class MtcService implements MtcServiceInterface
{
    private const TIPOS_DOCUMENTO = ['1', '2'];

    /** @var PideHttpClientInterface */
    private $httpClient;

    /** @var string */
    private $urlMtc;

    public function __construct(PideHttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->urlMtc = (string) config('pide.url_mtc');
    }

    /**
     * {@inheritdoc}
     */
    public function consultarPapeletas(string $tipoDocumento, string $numeroDocumento): array
    {
        $validacion = $this->validarParametros($tipoDocumento, $numeroDocumento);
        if ($validacion !== null) {
            return $validacion;
        }

        try {
            $curlResult = $this->httpClient->execute(
                $this->endpoint('DatosPapeletas', $tipoDocumento, $numeroDocumento),
                null,
                'GET',
                'MTC-Papeletas'
            );

            if (! $curlResult['success']) {
                return $this->fallo($curlResult['error'], null);
            }

            Log::info('Consulta de papeletas (MTC WS3)', [
                'service' => 'MTC-Papeletas',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
                'tipo_documento' => $tipoDocumento,
            ]);

            if ($curlResult['httpCode'] !== 200) {
                return $this->fallo("Error HTTP {$curlResult['httpCode']} en el servicio MTC", null);
            }

            $json = $this->decodificar($curlResult['response'], 'MTC-Papeletas');
            if ($json === null) {
                return $this->fallo('Error al decodificar respuesta JSON de MTC WS3.', null);
            }

            $mensaje = $this->buscar($json, ['dc'], '');
            $lista = $this->extraerLista($json, [$this, 'esPapeleta']);

            if ($mensaje !== '' && $lista === []) {
                return $this->exito($mensaje, []);
            }

            return $this->exito(
                $lista === [] ? 'No se encontraron papeletas para el administrado.' : 'Consulta de papeletas exitosa',
                array_map([$this, 'mapearPapeleta'], array_values($lista))
            );
        } catch (\Exception $e) {
            return $this->excepcion('consultar papeletas', $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function consultarUltimaLicencia(string $tipoDocumento, string $numeroDocumento): array
    {
        $validacion = $this->validarParametros($tipoDocumento, $numeroDocumento);
        if ($validacion !== null) {
            return $validacion;
        }

        try {
            $curlResult = $this->httpClient->execute(
                $this->endpoint('UltimaLicencia', $tipoDocumento, $numeroDocumento),
                null,
                'GET',
                'MTC-UltimaLicencia'
            );

            if (! $curlResult['success']) {
                return $this->fallo($curlResult['error'], null);
            }

            Log::info('Consulta de última licencia (MTC WS3)', [
                'service' => 'MTC-UltimaLicencia',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
                'tipo_documento' => $tipoDocumento,
            ]);

            if ($curlResult['httpCode'] !== 200) {
                return $this->fallo("Error HTTP {$curlResult['httpCode']} en el servicio MTC", null);
            }

            $json = $this->decodificar($curlResult['response'], 'MTC-UltimaLicencia');
            if ($json === null) {
                return $this->fallo('Error al decodificar respuesta JSON de MTC WS3.', null);
            }

            $objeto = $this->extraerObjeto($json, [$this, 'esLicencia']);

            if ($objeto === []) {
                $mensaje = $this->buscar($json, ['dc'], 'No se encontró licencia de conducir para el administrado.');

                return $this->exito($mensaje, []);
            }

            return $this->exito('Consulta de última licencia exitosa', $this->mapearLicencia($objeto));
        } catch (\Exception $e) {
            return $this->excepcion('consultar última licencia', $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function consultarUltimasSanciones(string $tipoDocumento, string $numeroDocumento): array
    {
        $validacion = $this->validarParametros($tipoDocumento, $numeroDocumento);
        if ($validacion !== null) {
            return $validacion;
        }

        try {
            $curlResult = $this->httpClient->execute(
                $this->endpoint('UltimasSanciones', $tipoDocumento, $numeroDocumento),
                null,
                'GET',
                'MTC-UltimasSanciones'
            );

            if (! $curlResult['success']) {
                return $this->fallo($curlResult['error'], null);
            }

            Log::info('Consulta de últimas sanciones (MTC WS3)', [
                'service' => 'MTC-UltimasSanciones',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
                'tipo_documento' => $tipoDocumento,
            ]);

            if ($curlResult['httpCode'] !== 200) {
                return $this->fallo("Error HTTP {$curlResult['httpCode']} en el servicio MTC", null);
            }

            $json = $this->decodificar($curlResult['response'], 'MTC-UltimasSanciones');
            if ($json === null) {
                return $this->fallo('Error al decodificar respuesta JSON de MTC WS3.', null);
            }

            $mensaje = $this->buscar($json, ['dc'], '');
            $lista = $this->extraerLista($json, [$this, 'esPapeleta']);

            if ($mensaje !== '') {
                return $this->exito($mensaje, []);
            }

            if ($lista !== []) {
                return $this->exito('Consulta de últimas sanciones exitosa', array_map([$this, 'mapearSancion'], array_values($lista)));
            }

            return $this->fallo('Respuesta inesperada del servicio MTC-UltimasSanciones.', null);
        } catch (\Exception $e) {
            return $this->excepcion('consultar últimas sanciones', $e);
        }
    }

    // ========================================
    // VALIDACIÓN DE ENTRADA
    // ========================================

    private function validarParametros(string $tipoDocumento, string $numeroDocumento): ?array
    {
        if ($tipoDocumento === '' || $numeroDocumento === '') {
            return $this->fallo('Los valores de los parámetros no deben ser vacíos.', '100');
        }

        if (! in_array($tipoDocumento, self::TIPOS_DOCUMENTO, true)) {
            return $this->fallo('El tipo de documento enviado no es válido.', '101');
        }

        if (! preg_match('/^\d{1,15}$/', $numeroDocumento)) {
            return $this->fallo('El número de documento no es válido.', '102');
        }

        return null;
    }

    // ========================================
    // CONSTRUCCIÓN DE PETICIÓN
    // ========================================

    private function endpoint(string $operacion, string $tipoDocumento, string $numeroDocumento): string
    {
        return rtrim($this->urlMtc, '/')
            .'/'.$operacion
            .'?iTipoDocumento='.urlencode($tipoDocumento)
            .'&sNumDocumento='.urlencode($numeroDocumento)
            .'&out=json';
    }

    // ========================================
    // PROCESAMIENTO DE RESPUESTA
    // ========================================

    private function decodificar(string $response, string $servicio): ?array
    {
        $json = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Error al decodificar respuesta JSON del servicio MTC', [
                'service' => $servicio,
                'user_id' => auth()->id(),
                'error' => json_last_error_msg(),
            ]);

            return null;
        }

        return is_array($json) ? $json : null;
    }

    /**
     * Extrae la lista de registros de la respuesta, tolerando envolturas del
     * API Gateway (lista directa, clave contenedora u objeto único).
     */
    private function extraerLista(array $json, callable $esRegistro): array
    {
        if (array_is_list($json)) {
            return $json;
        }

        foreach ($json as $valor) {
            if (is_array($valor) && array_is_list($valor)) {
                return $valor;
            }
        }

        return $esRegistro($json) ? [$json] : [];
    }

    /**
     * Extrae el objeto principal (licencia) tolerando envolturas del gateway.
     */
    private function extraerObjeto(array $json, callable $esObjeto): array
    {
        if ($esObjeto($json)) {
            return $json;
        }

        foreach ($json as $valor) {
            if (is_array($valor) && $esObjeto($valor)) {
                return $valor;
            }
        }

        return [];
    }

    private function esPapeleta(array $item): bool
    {
        return $this->existe($item, ['NUM_INFRACCION', 'PAPELETA', 'FALTA']);
    }

    private function esLicencia(array $item): bool
    {
        return $this->existe($item, ['NUM_LICENCIA', 'NUM_DOCUMENTO', 'CATEGORIA']);
    }

    private function existe(array $item, array $claves): bool
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $item)) {
                return true;
            }
        }

        return false;
    }

    private function mapearPapeleta(array $item): array
    {
        return [
            'numInfraccion' => $this->floatVal($this->buscar($item, ['NUM_INFRACCION'])),
            'codEntidad' => $this->intVal($this->buscar($item, ['COD_ENTIDAD'])),
            'entidad' => $this->strVal($this->buscar($item, ['ENTIDAD'])),
            'papeleta' => $this->strVal($this->buscar($item, ['PAPELETA'])),
            'fechaFirme' => $this->strVal($this->buscar($item, ['DAT_FECHA_FIRME'])),
            'numeroResolucion' => $this->strVal($this->buscar($item, ['VAR_NRO_RESOLUCION'])),
            'falta' => $this->strVal($this->buscar($item, ['FALTA'])),
            'fecInfraccion' => $this->strVal($this->buscar($item, ['FEC_INFRACCION'])),
            'fecFirme' => $this->strVal($this->buscar($item, ['FEC_FIRME'])),
            'puntosFirmes' => $this->floatVal($this->buscar($item, ['PUNTOS_x0020_FIRMES', 'PUNTOS FIRMES'])),
            'pProceso' => $this->floatVal($this->buscar($item, ['P._x0020_PROCESO', 'P. PROCESO'])),
            'estado' => $this->strVal($this->buscar($item, ['ESTADO'])),
            'tipoPit' => $this->strVal($this->buscar($item, ['TIPOPIT'])),
        ];
    }

    private function mapearLicencia(array $item): array
    {
        return [
            'tipoDoc' => $this->strVal($this->buscar($item, ['TIPO_DOC'])),
            'numDocumento' => $this->strVal($this->buscar($item, ['NUM_DOCUMENTO'])),
            'numLicencia' => $this->strVal($this->buscar($item, ['NUM_LICENCIA'])),
            'categoria' => $this->strVal($this->buscar($item, ['CATEGORIA'])),
            'apellidoPaterno' => $this->strVal($this->buscar($item, ['APE_PATERNO'])),
            'apellidoMaterno' => $this->strVal($this->buscar($item, ['APE_MATERNO'])),
            'nombre' => $this->strVal($this->buscar($item, ['NOMBRE'])),
            'restriccion' => $this->strVal($this->buscar($item, ['RESTRICCION'])),
            'fecRev' => $this->strVal($this->buscar($item, ['FECREV'])),
            'fecExp' => $this->strVal($this->buscar($item, ['FECEXP'])),
            'estado' => $this->strVal($this->buscar($item, ['ESTADO'])),
        ];
    }

    private function mapearSancion(array $item): array
    {
        return [
            'numInfraccion' => $this->floatVal($this->buscar($item, ['NUM_INFRACCION'])),
            'papeleta' => $this->strVal($this->buscar($item, ['PAPELETA'])),
            'falta' => $this->strVal($this->buscar($item, ['FALTA'])),
            'fecInfraccion' => $this->strVal($this->buscar($item, ['FEC_INFRACCION'])),
            'estado' => $this->strVal($this->buscar($item, ['ESTADO'])),
            'descripcion' => $this->strVal($this->buscar($item, ['DESCRIPCION', 'dc'])),
        ];
    }

    // ========================================
    // HELPERS DE TIPADO
    // ========================================

    private function buscar(array $item, array $claves, $default = '')
    {
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $item) && $item[$clave] !== null && $item[$clave] !== '') {
                return $item[$clave];
            }
        }

        return $default;
    }

    private function strVal($valor): string
    {
        return trim((string) ($valor ?? ''));
    }

    private function intVal($valor): int
    {
        return (int) ($valor ?? 0);
    }

    private function floatVal($valor): float
    {
        return (float) ($valor ?? 0);
    }

    private function exito(string $mensaje, array $data): array
    {
        return [
            'success' => true,
            'message' => $mensaje,
            'errorCode' => null,
            'data' => $data,
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

    private function excepcion(string $accion, \Exception $exception): array
    {
        Log::error('Excepción al consultar servicio MTC WS3', [
            'service' => 'MTC',
            'user_id' => auth()->id(),
            'accion' => $accion,
            'exception' => $exception->getMessage(),
        ]);

        return $this->fallo("Error al $accion: ".$exception->getMessage(), null);
    }
}
