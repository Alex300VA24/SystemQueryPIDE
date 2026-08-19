<?php

namespace App\Livewire;

use App\Services\Pide\Contracts\CCoactivaServiceInterface;
use Livewire\Component;
use Throwable;

/**
 * Consulta de deudas en cobranza coactiva (SUNAT WS3).
 *
 * Uso estrictamente transaccional: una consulta por envío de formulario.
 * No implementa (ni debe implementarse) carga masiva/batch.
 */
class ConsultaCoactiva extends Component
{
    public string $tipoDocumento = '01';

    public string $numeroDocumento = '';

    public bool $searched = false;

    public bool $real = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $deudas = [];

    public function buscar(): void
    {
        $this->validate(
            [
                'tipoDocumento' => ['required', 'in:01,06'],
                'numeroDocumento' => ['required', 'digits:' . ($this->tipoDocumento === '01' ? 8 : 11)],
            ],
            [
                'tipoDocumento.required' => 'Seleccione el tipo de documento.',
                'tipoDocumento.in' => 'Tipo de documento no válido.',
                'numeroDocumento.required' => 'Ingrese el número de documento.',
                'numeroDocumento.digits' => 'Debe ingresar exactamente :digits dígitos numéricos.',
            ],
        );

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->deudas = [];
        $this->real = false;

        try {
            $resultado = app(CCoactivaServiceInterface::class)
                ->consultarDeudaCoactiva($this->tipoDocumento, $this->numeroDocumento);
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
        $this->deudas = $resultado['data'];
        $this->successMessage = empty($this->deudas)
            ? 'No se encontraron deudas en cobranza coactiva.'
            : 'Consulta realizada exitosamente.';
        $this->searched = true;
    }

    public function resetSearch(): void
    {
        $this->reset('numeroDocumento', 'searched', 'real', 'deudas', 'errorMessage', 'successMessage');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.consulta-coactiva');
    }
}
