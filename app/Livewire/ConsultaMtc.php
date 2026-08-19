<?php

namespace App\Livewire;

use App\Services\Pide\Contracts\MtcServiceInterface;
use Livewire\Component;
use Throwable;

class ConsultaMtc extends Component
{
    public string $tipoDocumento = '1';

    public string $numeroDocumento = '';

    public string $operacion = 'papeletas';

    public bool $searched = false;

    public bool $sinRegistros = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $result = [];

    public function consultar(): void
    {
        $this->validate([
            'tipoDocumento' => ['required', 'in:1,2'],
            'numeroDocumento' => ['required', 'regex:/^\d{1,15}$/'],
        ], [
            'tipoDocumento.required' => 'Selecciona el tipo de documento.',
            'tipoDocumento.in' => 'El tipo de documento no es válido.',
            'numeroDocumento.required' => 'Ingresa el número de documento.',
            'numeroDocumento.regex' => 'El número de documento solo debe contener dígitos.',
        ]);

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->result = [];
        $this->sinRegistros = false;

        try {
            $servicio = app(MtcServiceInterface::class);

            $resultado = match ($this->operacion) {
                'licencia' => $servicio->consultarUltimaLicencia($this->tipoDocumento, $this->numeroDocumento),
                'sanciones' => $servicio->consultarUltimasSanciones($this->tipoDocumento, $this->numeroDocumento),
                default => $servicio->consultarPapeletas($this->tipoDocumento, $this->numeroDocumento),
            };
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo conectar con el servicio PIDE. Inténtalo nuevamente.';

            return;
        }

        if (! $resultado['success']) {
            $this->errorMessage = $resultado['message'] ?? 'Error al consultar el servicio MTC.';

            return;
        }

        $this->successMessage = $resultado['message'] ?? 'Consulta realizada exitosamente.';
        $this->result = $resultado['data'] ?? [];
        $this->sinRegistros = $this->result === [];
        $this->searched = true;
    }

    public function resetSearch(): void
    {
        $this->reset('numeroDocumento', 'searched', 'result', 'sinRegistros', 'errorMessage', 'successMessage');
        $this->resetValidation();
    }

    public function operaciones(): array
    {
        return [
            'papeletas' => ['label' => 'Papeletas', 'icon' => 'document'],
            'licencia' => ['label' => 'Última Licencia', 'icon' => 'id'],
            'sanciones' => ['label' => 'Sanciones', 'icon' => 'warning'],
        ];
    }

    public function columnas(): array
    {
        return match ($this->operacion) {
            'licencia' => [
                'tipoDoc' => 'Tipo documento',
                'numDocumento' => 'N° documento',
                'numLicencia' => 'N° licencia',
                'categoria' => 'Categoría',
                'apellidoPaterno' => 'Apellido paterno',
                'apellidoMaterno' => 'Apellido materno',
                'nombre' => 'Nombres',
                'restriccion' => 'Restricción',
                'fecRev' => 'Revalidación',
                'fecExp' => 'Expedición',
                'estado' => 'Estado',
            ],
            'sanciones' => [
                'papeleta' => 'Papeleta',
                'falta' => 'Falta',
                'fecInfraccion' => 'Fecha infracción',
                'estado' => 'Estado',
                'descripcion' => 'Descripción',
            ],
            default => [
                'papeleta' => 'Papeleta',
                'falta' => 'Falta',
                'entidad' => 'Entidad',
                'fecInfraccion' => 'Fecha infracción',
                'estado' => 'Estado',
                'puntosFirmes' => 'Puntos firmes',
                'pProceso' => 'Puntos en proceso',
                'tipoPit' => 'Tipo',
            ],
        };
    }

    public function render()
    {
        return view('livewire.consulta-mtc');
    }
}
