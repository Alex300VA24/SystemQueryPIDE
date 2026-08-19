<?php

namespace App\Services\Pide\Contracts;

/**
 * Interface para el servicio de consulta de deudas en cobranza coactiva (SUNAT WS3).
 */
interface CCoactivaServiceInterface
{
    /**
     * Consulta deudas en cobranza coactiva por DNI o RUC.
     *
     * IMPORTANTE: uso transaccional uno-a-uno. Prohibido invocar en bucle o
     * procesos batch (ver manual técnico WS3 SUNAT / DS 067-2017-PCM,
     * DS 121-2017-PCM, DL 1246).
     *
     * @param string $tipoDocumento    '01' (DNI) o '06' (RUC)
     * @param string $numeroDocumento  DNI (8 dígitos) o RUC (11 dígitos)
     * @return array ['success' => bool, 'message' => string, 'errorCode' => string|null, 'data' => array]
     */
    public function consultarDeudaCoactiva(string $tipoDocumento, string $numeroDocumento): array;
}
