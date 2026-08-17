<?php

namespace App\Services\Pide;

use App\Services\Pide\Contracts\PideHttpClientInterface;
use App\Services\Pide\Contracts\SunatServiceInterface;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de consultas SUNAT.
 * Contiene toda la lógica de negocio extraída de ConsultasSunatController (SRP).
 */
class SunatService implements SunatServiceInterface
{
    /** @var PideHttpClientInterface */
    private $httpClient;

    /** @var string */
    private $urlSUNATRest;

    public function __construct(PideHttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->urlSUNATRest = config('pide.url_sunat');
    }

    /**
     * {@inheritdoc}
     */
    public function consultarRUC(string $ruc): array
    {
        try {
            $url = $this->urlSUNATRest . '/DatosPrincipales?numruc=' . urlencode($ruc) . '&out=json';

            $curlResult = $this->httpClient->execute($url, null, 'GET', 'SUNAT');

            if (!$curlResult['success']) {
                return [
                    'success' => false,
                    'message' => $curlResult['error'],
                    'data' => null
                ];
            }

            Log::info('Consulta RUC (SUNAT)', [
                'service' => 'SUNAT',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
            ]);

            if ($curlResult['httpCode'] == 200) {
                return $this->procesarRespuestaJSON($curlResult['response'], $ruc);
            } elseif ($curlResult['httpCode'] == 404) {
                return [
                    'success' => false,
                    'message' => 'No se encontró información para el RUC consultado',
                    'data' => null
                ];
            } elseif ($curlResult['httpCode'] == 500) {
                return [
                    'success' => false,
                    'message' => 'Error interno del servidor SUNAT',
                    'data' => null
                ];
            } else {
                return $this->serviceErrorResult('SUNAT', $curlResult['httpCode']);
            }
        } catch (\Exception $e) {
            return $this->exceptionResult('consultar RUC', $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buscarPorRazonSocial(string $razonSocial): array
    {
        try {
            $razonSocialParam = rawurlencode($razonSocial);
            $url = $this->urlSUNATRest . '/RazonSocial?RSocial=' . $razonSocialParam . '&out=json';

            $curlResult = $this->httpClient->execute($url, null, 'GET', 'SUNAT');

            Log::info('Búsqueda por razón social (SUNAT)', [
                'service' => 'SUNAT',
                'user_id' => auth()->id(),
                'http_code' => $curlResult['httpCode'],
            ]);

            if (!$curlResult['success']) {
                return [
                    'success' => false,
                    'message' => $curlResult['error'],
                    'data' => []
                ];
            }

            if ($curlResult['httpCode'] == 200) {
                return $this->procesarRespuestaBusquedaJSON($curlResult['response']);
            } elseif ($curlResult['httpCode'] == 404) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron resultados para la razón social consultada',
                    'data' => []
                ];
            } elseif ($curlResult['httpCode'] == 500) {
                return [
                    'success' => false,
                    'message' => 'Error interno del servidor SUNAT',
                    'data' => []
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Error HTTP {$curlResult['httpCode']} al buscar en SUNAT",
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            Log::error('Excepción al buscar por razón social (SUNAT)', [
                'service' => 'SUNAT',
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al buscar razón social: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    // ========================================
    // PROCESAMIENTO DE RESPUESTAS
    // ========================================

    private function procesarRespuestaJSON(string $jsonResponse, string $ruc): array
    {
        try {
            $respuesta = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Error al decodificar respuesta JSON de SUNAT: ' . json_last_error_msg(),
                    'data' => null
                ];
            }

            if (!isset($respuesta['list']['multiRef'])) {
                return [
                    'success' => false,
                    'message' => 'Formato de respuesta inválido de SUNAT',
                    'data' => null
                ];
            }

            $datos = $respuesta['list']['multiRef'];

            $rucObtenido = $this->extraerValorSunat('ddp_numruc', $datos);
            if (empty($rucObtenido)) {
                return [
                    'success' => false,
                    'message' => 'No se encontró información para el RUC consultado',
                    'data' => null
                ];
            }

            $resultado = [
                'success' => true,
                'message' => 'Consulta exitosa',
                'data' => $this->mapearDatosSunat($datos, $rucObtenido)
            ];

            $resultado['data']['direccion_completa'] = $this->construirDireccionDesdeArray($resultado['data']);

            $this->registrarConsulta('RUC');

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Excepción al procesar respuesta SUNAT', [
                'service' => 'SUNAT',
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar respuesta: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    private function procesarRespuestaBusquedaJSON(string $jsonResponse): array
    {
        try {
            $respuesta = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Error al decodificar respuesta JSON de SUNAT: ' . json_last_error_msg(),
                    'data' => []
                ];
            }

            if (!isset($respuesta['list']['multiRef'])) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron resultados para la razón social consultada',
                    'data' => []
                ];
            }

            $multiRef = $respuesta['list']['multiRef'];

            if (!is_array($multiRef)) {
                return [
                    'success' => false,
                    'message' => 'Formato de respuesta inválido',
                    'data' => []
                ];
            }

            // Normalizar a array múltiple
            if (!isset($multiRef[0])) {
                $multiRef = [$multiRef];
            }

            $resultados = [];

            foreach ($multiRef as $datos) {
                $ruc = $this->extraerValorSunat('ddp_numruc', $datos);

                if (!empty($ruc)) {
                    $resultado = $this->mapearDatosSunat($datos, $ruc);
                    $resultado['secuencia'] = (int)$this->extraerValorSunat('ddp_secuen', $datos);
                    $resultado['direccion_completa'] = $this->construirDireccionDesdeArray($resultado);

                    $resultados[] = $resultado;
                }
            }

            if (empty($resultados)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron resultados válidos para la razón social consultada',
                    'data' => []
                ];
            }

            return [
                'success' => true,
                'message' => 'Búsqueda exitosa',
                'data' => $resultados,
                'total' => count($resultados)
            ];
        } catch (\Exception $e) {
            Log::error('Excepción al procesar búsqueda SUNAT', [
                'service' => 'SUNAT',
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar respuesta: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    // ========================================
    // HELPERS
    // ========================================

    /**
     * Extrae un valor de la estructura de datos SUNAT, manejando @nil y $.
     */
    public function extraerValorSunat(string $campo, array $datos): string
    {
        if (!isset($datos[$campo])) {
            return '';
        }

        $valor = $datos[$campo];

        if (is_array($valor) && isset($valor['@nil']) && $valor['@nil'] === true) {
            return '';
        }

        if (is_array($valor) && isset($valor['$'])) {
            return trim($valor['$']);
        }

        if (is_string($valor)) {
            return trim($valor);
        }

        return '';
    }

    /**
     * Mapea datos crudos de SUNAT a estructura normalizada.
     */
    public function mapearDatosSunat(array $datos, string $ruc): array
    {
        return [
            // Datos principales
            'ruc' => $ruc,
            'razon_social' => $this->extraerValorSunat('ddp_nombre', $datos),

            // Ubicación
            'codigo_ubigeo' => $this->extraerValorSunat('ddp_ubigeo', $datos),
            'departamento' => $this->extraerValorSunat('desc_dep', $datos),
            'provincia' => $this->extraerValorSunat('desc_prov', $datos),
            'distrito' => $this->extraerValorSunat('desc_dist', $datos),
            'cod_dep' => $this->extraerValorSunat('cod_dep', $datos),
            'cod_prov' => $this->extraerValorSunat('cod_prov', $datos),
            'cod_dist' => $this->extraerValorSunat('cod_dist', $datos),

            // Dirección completa
            'tipo_via' => $this->extraerValorSunat('desc_tipvia', $datos),
            'codigo_tipo_via' => $this->extraerValorSunat('ddp_tipvia', $datos),
            'nombre_via' => $this->extraerValorSunat('ddp_nomvia', $datos),
            'numero' => $this->extraerValorSunat('ddp_numer1', $datos),
            'interior' => $this->extraerValorSunat('ddp_inter1', $datos),
            'tipo_zona' => $this->extraerValorSunat('desc_tipzon', $datos),
            'codigo_tipo_zona' => $this->extraerValorSunat('ddp_tipzon', $datos),
            'nombre_zona' => $this->extraerValorSunat('ddp_nomzon', $datos),
            'referencia' => $this->extraerValorSunat('ddp_refer1', $datos),

            // Estado y condición
            'estado_contribuyente' => $this->extraerValorSunat('desc_estado', $datos),
            'codigo_estado' => $this->extraerValorSunat('ddp_estado', $datos),
            'condicion_domicilio' => $this->extraerValorSunat('desc_flag22', $datos),
            'codigo_condicion' => $this->extraerValorSunat('ddp_flag22', $datos),

            // Tipo de contribuyente
            'tipo_contribuyente' => $this->extraerValorSunat('desc_tpoemp', $datos),
            'codigo_tipo_contribuyente' => $this->extraerValorSunat('ddp_tpoemp', $datos),
            'tipo_persona' => $this->extraerValorSunat('desc_identi', $datos),
            'codigo_tipo_persona' => $this->extraerValorSunat('ddp_identi', $datos),

            // Actividad económica
            'actividad_economica' => $this->extraerValorSunat('desc_ciiu', $datos),
            'codigo_ciiu' => $this->extraerValorSunat('ddp_ciiu', $datos),

            // Dependencia
            'dependencia' => $this->extraerValorSunat('desc_numreg', $datos),
            'codigo_dependencia' => $this->extraerValorSunat('ddp_numreg', $datos),

            // Fechas
            'fecha_actualizacion' => $this->extraerValorSunat('ddp_fecact', $datos),
            'fecha_alta' => $this->extraerValorSunat('ddp_fecalt', $datos),
            'fecha_baja' => $this->extraerValorSunat('ddp_fecbaj', $datos),

            // Otros datos
            'codigo_secuencia' => $this->extraerValorSunat('ddp_secuen', $datos),
            'libreta_tributaria' => $this->extraerValorSunat('ddp_lllttt', $datos),
            'tamaño' => $this->extraerValorSunat('desc_tamano', $datos),

            // Estados booleanos
            'es_activo' => $this->convertirBooleano($this->extraerValorSunat('esActivo', $datos)),
            'es_habido' => $this->convertirBooleano($this->extraerValorSunat('esHabido', $datos)),
            'estado_activo' => $this->convertirBooleano($this->extraerValorSunat('esActivo', $datos)) ? 'SÍ' : 'NO',
            'estado_habido' => $this->convertirBooleano($this->extraerValorSunat('esHabido', $datos)) ? 'SÍ' : 'NO'
        ];
    }

    public function construirDireccionDesdeArray(array $data): string
    {
        $partes = [];

        if (!empty($data['tipo_via']) && $data['tipo_via'] !== '-') {
            $partes[] = $data['tipo_via'];
        }
        if (!empty($data['nombre_via']) && $data['nombre_via'] !== '-') {
            $partes[] = $data['nombre_via'];
        }
        if (!empty($data['numero']) && $data['numero'] !== '-') {
            $partes[] = 'NRO. ' . $data['numero'];
        }
        if (!empty($data['interior']) && $data['interior'] !== '-') {
            $partes[] = 'INT. ' . $data['interior'];
        }
        if (!empty($data['nombre_zona']) && $data['nombre_zona'] !== '-') {
            $partes[] = $data['nombre_zona'];
        }
        if (!empty($data['referencia']) && $data['referencia'] !== '-') {
            $partes[] = '(' . $data['referencia'] . ')';
        }

        return implode(' ', $partes);
    }

    public function convertirBooleano($valor): bool
    {
        if (empty($valor)) {
            return false;
        }
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_numeric($valor)) {
            return (int)$valor === 1;
        }
        $valorStr = strtolower((string)$valor);
        return in_array($valorStr, ['true', '1', 'yes', 'si', 'sí']);
    }

    private function registrarConsulta(string $tipo): void
    {
        Log::info('Consulta SUNAT completada', [
            'service' => 'SUNAT',
            'tipo' => $tipo,
            'user_id' => auth()->id(),
        ]);
    }

    private function serviceErrorResult(string $servicio, int $httpCode): array
    {
        return [
            'success' => false,
            'message' => "Error HTTP $httpCode en el servicio $servicio",
            'data' => null
        ];
    }

    private function exceptionResult(string $accion, \Exception $exception): array
    {
        Log::error("Excepción SUNAT al $accion", [
            'service' => 'SUNAT',
            'user_id' => auth()->id(),
            'exception' => $exception->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => "Error al $accion: " . $exception->getMessage(),
            'data' => null
        ];
    }
}
