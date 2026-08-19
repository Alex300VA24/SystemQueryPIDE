<?php

namespace App\Services\Pide;

use App\Services\Pide\Contracts\CAmbientalesServiceInterface;
use App\Services\Pide\Contracts\PideHttpClientInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de consulta de certificaciones ambientales (SENACE WS5).
 *
 * Endpoint: GET {url_cambientales}?TipoIga=&Expediente=&GrupoSector=&SubSector=&Actividad=&out=json
 *
 * POLÍTICA OBLIGATORIA: este servicio debe consumirse bajo demanda
 * transaccional (una consulta por invocación). Prohibido su uso para
 * procesos batch/masivos; infringirlo implica retiro de accesos por parte
 * del SENACE. Los datos obtenidos son confidenciales: no exponer
 * públicamente ni almacenar fuera del propósito autorizado
 * (DS 016-2020-PCM, DL 1246).
 */
class CAmbientalesService implements CAmbientalesServiceInterface
{
    /** Códigos válidos de Tipo de Instrumento de Gestión Ambiental. */
    private const TIPO_IGA_VALIDOS = ['01', '03', '04', '05', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];

    /** Códigos válidos de Grupo Sector. */
    private const GRUPO_SECTOR_VALIDOS = ['1', '2', '3', '4', '5'];

    /** Códigos válidos de SubSector. */
    private const SUB_SECTOR_VALIDOS = ['1', '3', '4', '5', '6', '7', '8'];

    /** Códigos válidos de Actividad. */
    private const ACTIVIDAD_VALIDOS = ['1', '3', '4', '6', '11', '12'];

    /** Parámetros obligatorios de negocio. */
    private const CAMPOS_OBLIGATORIOS = ['TipoIga', 'Expediente', 'GrupoSector', 'SubSector', 'Actividad'];

    /** Parámetros opcionales de filtrado admitidos. */
    private const CAMPOS_OPCIONALES = ['NroRuc', 'Titular', 'NroCatalogo', 'NomProyecto', 'IdDepa', 'IdProv', 'IdDist', 'Resolucion'];

    private $httpClient;
    private $urlCAmbientales;

    public function __construct(PideHttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->urlCAmbientales = config('pide.url_cambientales');
    }

    public function consultar(array $parametros): array
    {
        $validacion = $this->validarParametros($parametros);
        if ($validacion !== null) {
            return $validacion;
        }

        try {
            $query = $this->construirQuery($parametros);
            $url = $this->urlCAmbientales . '?' . $query;

            $curlResult = $this->httpClient->execute($url, null, 'GET', 'SENACE-CAmbientales');

            if (!$curlResult['success']) {
                return $this->fallo($curlResult['error'], null);
            }

            Log::info('Consulta certificaciones ambientales (SENACE WS5)', [
                'service' => 'SENACE-CAmbientales',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
                'expediente' => $parametros['Expediente'],
            ]);

            if ($curlResult['httpCode'] !== 200) {
                return $this->fallo("Error HTTP {$curlResult['httpCode']} en el servicio SENACE-CAmbientales", null);
            }

            return $this->procesarRespuesta($curlResult['response']);
        } catch (\Exception $e) {
            Log::error('Excepción al consultar certificaciones ambientales (SENACE WS5)', [
                'service' => 'SENACE-CAmbientales',
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return $this->fallo('Error al consultar certificación ambiental: ' . $e->getMessage(), null);
        }
    }

    private function validarParametros(array $parametros): ?array
    {
        foreach (self::CAMPOS_OBLIGATORIOS as $campo) {
            if (!isset($parametros[$campo]) || trim((string) $parametros[$campo]) === '') {
                return $this->fallo("El parámetro obligatorio '{$campo}' no debe estar vacío.", null);
            }
        }

        if (!in_array((string) $parametros['TipoIga'], self::TIPO_IGA_VALIDOS, true)) {
            return $this->fallo('El código de TipoIga no es válido.', null);
        }
        if (!in_array((string) $parametros['GrupoSector'], self::GRUPO_SECTOR_VALIDOS, true)) {
            return $this->fallo('El código de GrupoSector no es válido.', null);
        }
        if (!in_array((string) $parametros['SubSector'], self::SUB_SECTOR_VALIDOS, true)) {
            return $this->fallo('El código de SubSector no es válido.', null);
        }
        if (!in_array((string) $parametros['Actividad'], self::ACTIVIDAD_VALIDOS, true)) {
            return $this->fallo('El código de Actividad no es válido.', null);
        }
        if (isset($parametros['NroRuc']) && trim((string) $parametros['NroRuc']) !== '' && !preg_match('/^\d{11}$/', (string) $parametros['NroRuc'])) {
            return $this->fallo('El RUC del titular debe tener 11 dígitos.', null);
        }

        return null;
    }

    private function construirQuery(array $parametros): string
    {
        $partes = [];

        foreach (self::CAMPOS_OBLIGATORIOS as $campo) {
            $partes[] = $campo . '=' . urlencode((string) $parametros[$campo]);
        }

        foreach (self::CAMPOS_OPCIONALES as $campo) {
            if (isset($parametros[$campo]) && trim((string) $parametros[$campo]) !== '') {
                $partes[] = $campo . '=' . urlencode((string) $parametros[$campo]);
            }
        }

        $partes[] = 'out=json';

        return implode('&', $partes);
    }

    private function procesarRespuesta(string $jsonResponse): array
    {
        $respuesta = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fallo('Error al decodificar respuesta JSON de SENACE WS5: ' . json_last_error_msg(), null);
        }

        $success = filter_var($respuesta['Success'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $code = isset($respuesta['Code']) ? (string) $respuesta['Code'] : null;

        if ($code === '0' || !$success) {
            $mensaje = trim((string) ($respuesta['Message'] ?? $respuesta['message'] ?? ''));

            return $this->fallo($mensaje !== '' ? $mensaje : 'Problemas al procesar información', $code);
        }

        if ($code === '2') {
            return [
                'success' => true,
                'message' => 'No se ha encontrado ningún resultado para los filtros seleccionados.',
                'code' => $code,
                'data' => [],
            ];
        }

        $items = $respuesta['Certificacion'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        return [
            'success' => true,
            'message' => 'Consulta exitosa',
            'code' => $code,
            'data' => array_map([$this, 'mapearCertificacion'], array_values($items)),
        ];
    }

    private function mapearCertificacion(array $item): array
    {
        $ubigeo = is_array($item['ubigeo'] ?? null) ? $item['ubigeo'] : [];

        return [
            'actividad' => trim((string) ($item['actividad'] ?? '')),
            'catalogo' => trim((string) ($item['catalogo'] ?? '')),
            'consultora' => trim((string) ($item['consultora'] ?? '')),
            'ente' => trim((string) ($item['ente'] ?? '')),
            'estado' => trim((string) ($item['estado'] ?? '')),
            'expediente' => trim((string) ($item['expediente'] ?? '')),
            'fec_ingreso' => (string) ($item['fec_ingreso'] ?? ''),
            'fec_resol' => (string) ($item['fec_resol'] ?? ''),
            'id_certificacion' => (string) ($item['id_certificacion'] ?? ''),
            'nombre_proyecto' => trim((string) ($item['nombre_proyecto'] ?? '')),
            'nro_resol' => trim((string) ($item['nro_resol'] ?? '')),
            'ruc_consultora' => trim((string) ($item['ruc_consultora'] ?? '')),
            'ruc_titular' => trim((string) ($item['ruc_titular'] ?? '')),
            'sector' => trim((string) ($item['sector'] ?? '')),
            'subsector' => trim((string) ($item['subsector'] ?? '')),
            'tipo_iga' => trim((string) ($item['tipo_iga'] ?? '')),
            'titular' => trim((string) ($item['titular'] ?? '')),
            'ubigeo' => [
                'departamento' => trim((string) ($ubigeo['departamento'] ?? '')),
                'provincia' => trim((string) ($ubigeo['provincia'] ?? '')),
                'distrito' => trim((string) ($ubigeo['distrito'] ?? '')),
                'id_ubigeo' => (string) ($ubigeo['id_ubigeo'] ?? ''),
            ],
            'v_acceso' => is_array($item['v_acceso'] ?? null) ? $item['v_acceso'] : [],
            'v_lineaBase' => is_array($item['v_lineaBase'] ?? null) ? $item['v_lineaBase'] : [],
        ];
    }

    private function fallo(string $mensaje, ?string $code): array
    {
        return [
            'success' => false,
            'message' => $mensaje,
            'code' => $code,
            'data' => [],
        ];
    }
}
