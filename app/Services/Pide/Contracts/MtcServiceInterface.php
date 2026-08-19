<?php

namespace App\Services\Pide\Contracts;

/**
 * Interface para el servicio de consultas del récord de conductor del MTC (WS3 PIDE).
 */
interface MtcServiceInterface
{
    /**
     * Consulta las papeletas de tránsito del conductor.
     *
     * IMPORTANTE: uso transaccional uno-a-uno bajo demanda. Prohibido invocar
     * en bucle o procesos batch/masivos; infringirlo implica revocación
     * inmediata de accesos por parte del MTC. Los datos son confidenciales:
     * no exponer ni almacenar fuera del propósito autorizado
     * (DS 083-2017-PCM, DL 1246).
     *
     * @param  string  $tipoDocumento  '1' o '2' según estándares MTC
     * @param  string  $numeroDocumento  Número de documento del conductor
     * @return array ['success' => bool, 'message' => string, 'errorCode' => string|null, 'data' => array]
     */
    public function consultarPapeletas(string $tipoDocumento, string $numeroDocumento): array;

    /**
     * Consulta los datos de la última licencia de conducir emitida.
     *
     * Mismas políticas de uso transaccional y confidencialidad que consultarPapeletas().
     *
     * @param  string  $tipoDocumento  '1' o '2' según estándares MTC
     * @param  string  $numeroDocumento  Número de documento del conductor
     * @return array ['success' => bool, 'message' => string, 'errorCode' => string|null, 'data' => array]
     */
    public function consultarUltimaLicencia(string $tipoDocumento, string $numeroDocumento): array;

    /**
     * Consulta las últimas sanciones vigentes del conductor.
     *
     * Mismas políticas de uso transaccional y confidencialidad que consultarPapeletas().
     *
     * @param  string  $tipoDocumento  '1' o '2' según estándares MTC
     * @param  string  $numeroDocumento  Número de documento del conductor
     * @return array ['success' => bool, 'message' => string, 'errorCode' => string|null, 'data' => array]
     */
    public function consultarUltimasSanciones(string $tipoDocumento, string $numeroDocumento): array;
}
