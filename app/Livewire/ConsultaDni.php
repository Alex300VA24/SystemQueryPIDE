<?php

namespace App\Livewire;

use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\PideCredentialStore;

class ConsultaDni extends BaseConsultation
{
    protected function page(): array
    {
        return [
            'title' => 'Consulta DNI', 'source' => 'RENIEC', 'description' => 'Registro Nacional de Identificación y Estado Civil', 'field' => 'Número de DNI', 'placeholder' => 'Ingrese 8 dígitos', 'hint' => 'Ingresa los 8 dígitos del documento.', 'rules' => 'required|digits:8', 'accent' => '#059669', 'icon' => 'search',
            'needsCredentials' => true,
            'hasPhoto' => true,
            'resultTitle' => 'Información Personal',
            'fullWidthFields' => ['Dirección', 'Restricción'],
            'result' => ['DNI' => '74251836', 'Nombres' => 'María Elena', 'Apellido paterno' => 'Quispe', 'Apellido materno' => 'Ramos', 'Estado civil' => 'Soltera', 'Ubigeo' => '150101 · Lima', 'Dirección' => 'Av. Demostración 123, Lima', 'Restricción' => 'Ninguna'],
        ];
    }

    protected function attemptReal(): ?array
    {
        $passwordPide = app(PideCredentialStore::class)->get() ?? '';
        $resultado = app(ReniecServiceInterface::class)->consultarDNI($this->busqueda, $this->dniUsuario, $passwordPide);

        if (! $resultado['success'] || empty($resultado['data'])) {
            $this->errorMessage = $resultado['message'] ?? 'RENIEC no devolvió resultados.';
            $this->pideCredentialExpired = ($resultado['error_type'] ?? null) === 'credential_expired';

            return null;
        }

        $data = $resultado['data'];
        $this->photo = empty($data['foto'])
            ? null
            : (str_starts_with($data['foto'], 'data:image') ? $data['foto'] : 'data:image/jpeg;base64,'.$data['foto']);

        return [
            'DNI' => $data['dni'] ?? $this->busqueda,
            'Nombres' => $data['nombres'] ?? '',
            'Apellido paterno' => $data['apellido_paterno'] ?? '',
            'Apellido materno' => $data['apellido_materno'] ?? '',
            'Estado civil' => $data['estado_civil'] ?? '',
            'Dirección' => $data['direccion'] ?? '',
            'Restricción' => $data['restriccion'] ?? 'Ninguna',
            'Ubigeo' => $data['ubigeo'] ?? '',
        ];
    }
}
