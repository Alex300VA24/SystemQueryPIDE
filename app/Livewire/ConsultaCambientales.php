<?php

namespace App\Livewire;

use App\Services\Pide\Contracts\CAmbientalesServiceInterface;
use Livewire\Component;
use Throwable;

/**
 * Consulta de certificaciones ambientales (SENACE WS5).
 *
 * Uso estrictamente transaccional: una consulta por envío de formulario.
 * No implementa (ni debe implementarse) carga masiva/batch.
 */
class ConsultaCambientales extends Component
{
    public string $tipoIga = '';

    public string $expediente = '';

    public string $grupoSector = '';

    public string $subSector = '';

    public string $actividad = '';

    public string $nroRuc = '';

    public string $titular = '';

    public string $nroCatalogo = '';

    public string $nomProyecto = '';

    public string $idDepa = '';

    public string $idProv = '';

    public string $idDist = '';

    public string $resolucion = '';

    public bool $searched = false;

    public bool $real = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $certificaciones = [];

    public function buscar(): void
    {
        $this->validate(
            [
                'tipoIga' => ['required', 'in:01,03,04,05,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23'],
                'expediente' => ['required', 'string', 'max:50'],
                'grupoSector' => ['required', 'in:1,2,3,4,5'],
                'subSector' => ['required', 'in:1,3,4,5,6,7,8'],
                'actividad' => ['required', 'in:1,3,4,6,11,12'],
                'nroRuc' => ['nullable', 'digits:11'],
                'idDepa' => ['nullable', 'digits:2'],
                'idProv' => ['nullable', 'digits:2'],
                'idDist' => ['nullable', 'digits:2'],
            ],
            [
                'tipoIga.required' => 'Seleccione el tipo de instrumento de gestión ambiental.',
                'tipoIga.in' => 'Tipo de instrumento no válido.',
                'expediente.required' => 'Ingrese el número de expediente.',
                'grupoSector.required' => 'Seleccione el grupo sector.',
                'subSector.required' => 'Seleccione el subsector.',
                'actividad.required' => 'Seleccione la actividad.',
                'nroRuc.digits' => 'El RUC debe tener 11 dígitos.',
                'idDepa.digits' => 'El código de departamento debe tener 2 dígitos.',
                'idProv.digits' => 'El código de provincia debe tener 2 dígitos.',
                'idDist.digits' => 'El código de distrito debe tener 2 dígitos.',
            ],
        );

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->certificaciones = [];
        $this->real = false;

        try {
            $resultado = app(CAmbientalesServiceInterface::class)->consultar([
                'TipoIga' => $this->tipoIga,
                'Expediente' => $this->expediente,
                'GrupoSector' => $this->grupoSector,
                'SubSector' => $this->subSector,
                'Actividad' => $this->actividad,
                'NroRuc' => $this->nroRuc,
                'Titular' => $this->titular,
                'NroCatalogo' => $this->nroCatalogo,
                'NomProyecto' => $this->nomProyecto,
                'IdDepa' => $this->idDepa,
                'IdProv' => $this->idProv,
                'IdDist' => $this->idDist,
                'Resolucion' => $this->resolucion,
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo conectar con el servicio PIDE. Inténtalo nuevamente.';
            $this->searched = true;

            return;
        }

        if (!$resultado['success']) {
            $this->errorMessage = $resultado['message'] ?? 'Servicio PIDE no disponible en este momento.';
            $this->searched = true;

            return;
        }

        $this->real = true;
        $this->certificaciones = $resultado['data'];
        $this->successMessage = $resultado['message'] ?: (empty($this->certificaciones)
            ? 'No se ha encontrado ningún resultado para los filtros seleccionados.'
            : 'Consulta realizada exitosamente.');
        $this->searched = true;
    }

    public function resetSearch(): void
    {
        $this->reset(
            'expediente', 'nroRuc', 'titular', 'nroCatalogo', 'nomProyecto',
            'idDepa', 'idProv', 'idDist', 'resolucion',
            'searched', 'real', 'certificaciones', 'errorMessage', 'successMessage',
        );
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.consulta-cambientales');
    }
}
