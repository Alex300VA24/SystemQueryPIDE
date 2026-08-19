<?php

namespace App\Services\Pide\Contracts;

/**
 * Interface para el servicio de consulta de certificaciones ambientales (SENACE WS5).
 */
interface CAmbientalesServiceInterface
{
    /**
     * Consulta certificaciones ambientales por expediente/sector/actividad
     * y filtros opcionales.
     *
     * IMPORTANTE: uso transaccional uno-a-uno. Prohibido invocar en bucle o
     * procesos batch (ver manual técnico WS5 SENACE / DS 016-2020-PCM,
     * DL 1246).
     *
     * @param array $parametros Parámetros de negocio y filtros (ver CAmbientalesService::consultar)
     * @return array ['success' => bool, 'message' => string, 'code' => string|null, 'data' => array]
     */
    public function consultar(array $parametros): array;
}
