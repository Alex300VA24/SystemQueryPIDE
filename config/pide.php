<?php

return [
    'ruc_empresa' => env('PIDE_RUC_EMPRESA'),
    'url_reniec' => env('PIDE_URL_RENIEC'),
    'url_sunat' => env('PIDE_URL_SUNAT'),
    'url_sunarp' => env('PIDE_URL_SUNARP'),
    'url_ccoactiva' => env('PIDE_URL_CCOACTIVA'),
    'url_cambientales' => env('PIDE_URL_CAMBIENTALES'),
    'goficina' => env('PIDE_GOFICINA'),
    'sunarp_usuario' => env('PIDE_SUNARP_USUARIO'),
    'sunarp_pass' => env('PIDE_SUNARP_PASS'),

    // Minutos que se conserva en servidor la credencial PIDE ingresada por
    // el usuario antes de expirar (ver App\Services\Pide\PideCredentialStore).
    'credential_ttl_minutes' => env('PIDE_CREDENTIAL_TTL_MINUTES', 15),
];
