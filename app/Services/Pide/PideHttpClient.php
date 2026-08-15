<?php

namespace App\Services\Pide;

use App\Services\Pide\Contracts\PideHttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Cliente HTTP para la plataforma PIDE, respaldado por Guzzle.
 */
class PideHttpClient implements PideHttpClientInterface
{
    private Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'verify' => false,
            'connect_timeout' => 30,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $url, ?array $data = null, string $method = 'POST', string $servicio = 'PIDE', int $timeout = 45): array
    {
        $options = [
            'timeout' => $timeout,
            'headers' => ['Content-Type' => 'application/json; charset=UTF-8'],
        ];

        if ($method === 'POST' && $data !== null) {
            $options['body'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        try {
            $response = $this->client->request($method, $url, $options);

            return [
                'success' => true,
                'httpCode' => $response->getStatusCode(),
                'response' => (string) $response->getBody(),
                'error' => null,
            ];
        } catch (GuzzleException $e) {
            $httpCode = method_exists($e, 'getResponse') && $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $body = method_exists($e, 'getResponse') && $e->getResponse() ? (string) $e->getResponse()->getBody() : null;

            if ($httpCode > 0) {
                return [
                    'success' => true,
                    'httpCode' => $httpCode,
                    'response' => $body,
                    'error' => null,
                ];
            }

            error_log("Guzzle Error $servicio: " . $e->getMessage());

            return [
                'success' => false,
                'httpCode' => 0,
                'response' => null,
                'error' => "Error de conexión con $servicio: " . $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decodeJsonResponse(string $response, string $servicio = 'PIDE'): array
    {
        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error JSON decode $servicio: " . json_last_error_msg());

            return [
                'success' => false,
                'data' => null,
                'message' => "Error al decodificar respuesta JSON de $servicio: " . json_last_error_msg(),
            ];
        }

        return [
            'success' => true,
            'data' => $decoded,
            'message' => 'OK',
        ];
    }
}
