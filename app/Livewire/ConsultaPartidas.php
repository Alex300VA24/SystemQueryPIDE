<?php

namespace App\Livewire;

use App\Http\Requests\ConsultaPartidasRequest;
use App\Services\Pide\Contracts\SunarpServiceInterface;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Throwable;

class ConsultaPartidas extends BaseConsultation
{
    public string $tab = 'natural';

    public ?string $activeModal = null;

    public string $naturalDni = '';

    public string $juridicaMode = 'ruc';

    public string $juridicaQuery = '';

    public array $people = [];

    public array $selectedPerson = [];

    public array $partidas = [];

    public array $selectedPartida = [];

    public array $detail = [];

    public int $partidasPage = 1;

    public int $partidasPerPage = 8;

    public ?string $statusMessage = null;

    public string $statusType = 'info';

    public function mount(): void
    {
        $this->dniUsuario = (string) (auth()->user()?->persona?->documento_numero ?? '');
        $this->pidePassword = (string) session('pide_password', '');
    }

    #[On('pide-credential-saved')]
    public function onPideCredentialSaved(string $pidePassword): void
    {
        $this->pidePassword = $pidePassword;

        if ($this->tab === 'natural') {
            $this->searchNatural();
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['natural', 'juridica', 'partida'], true) ? $tab : 'natural';
        $this->clearSearchState();
        $this->resetValidation();
    }

    public function openSearchModal(): void
    {
        if ($this->tab === 'partida') {
            return;
        }

        $this->activeModal = $this->tab;
        $this->people = [];
        $this->resetValidation(['naturalDni', 'juridicaQuery']);
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
    }

    public function searchNatural(): void
    {
        $this->validate(
            ConsultaPartidasRequest::naturalRules(),
            ConsultaPartidasRequest::validationMessages(),
            ConsultaPartidasRequest::validationAttributes(),
        );

        if (trim($this->pidePassword) === '') {
            $this->dispatch('open-pide-credential-modal');

            return;
        }

        $this->runPersonSearch(fn (SunarpServiceInterface $service) => $service->buscarPersonaNatural(
            $this->naturalDni,
            $this->dniUsuario,
            $this->pidePassword,
        ), 'RENIEC');
    }

    public function searchJuridica(): void
    {
        $this->validate(
            ConsultaPartidasRequest::juridicaRules($this->juridicaMode),
            ConsultaPartidasRequest::validationMessages(),
            ConsultaPartidasRequest::validationAttributes($this->juridicaMode),
        );

        $payload = ['tipoBusqueda' => $this->juridicaMode];
        $payload[$this->juridicaMode === 'ruc' ? 'ruc' : 'razonSocial'] = trim($this->juridicaQuery);

        $this->runPersonSearch(fn (SunarpServiceInterface $service) => $service->buscarPersonaJuridica($payload), 'SUNAT');
    }

    public function selectPerson(int $index): void
    {
        abort_unless(isset($this->people[$index]), 404);
        $this->selectedPerson = $this->people[$index];
        $this->activeModal = null;
        $this->setStatus('Persona seleccionada. Haz clic en “Consultar” para buscar sus partidas en SUNARP.', 'info');
    }

    public function searchSunarp(): void
    {
        $this->resetValidation();
        $this->clearResults();

        if ($this->tab === 'partida') {
            $this->validate(
                ConsultaPartidasRequest::partidaRules(),
                ConsultaPartidasRequest::validationMessages(),
                ConsultaPartidasRequest::validationAttributes(),
            );

            [$zona, $oficina] = array_pad(explode('|', $this->oficina, 2), 2, '');
            $this->partidas = [[
                'numero_partida' => $this->busqueda,
                'codigo_zona' => $zona,
                'codigo_oficina' => $oficina,
                'oficina' => $this->oficinaEtiqueta($this->oficina),
                'estado' => 'REGISTRADA',
                'numero_placa' => '',
            ]];
            $this->loadPartida(0);

            return;
        }

        if ($this->selectedPerson === []) {
            $this->addError('selectedPerson', 'Selecciona una persona antes de consultar.');
            $this->setStatus('Selecciona una persona antes de consultar.', 'warning');

            return;
        }

        try {
            $service = app(SunarpServiceInterface::class);
            $response = $this->tab === 'natural'
                ? $service->consultarTSIRSARPNatural(
                    (string) ($this->selectedPerson['apellido_paterno'] ?? ''),
                    (string) ($this->selectedPerson['apellido_materno'] ?? ''),
                    (string) ($this->selectedPerson['nombres'] ?? ''),
                )
                : $service->consultarTSIRSARPJuridica((string) ($this->selectedPerson['razon_social'] ?? ''));

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                $this->setStatus($response['message'] ?? 'No se encontraron registros en SUNARP.', 'warning');

                return;
            }

            $this->partidas = array_values($response['data']);
            $this->setStatus('Se encontraron '.count($this->partidas).' registro(s) en SUNARP.', 'success');
            $this->loadPartida(0);
        } catch (Throwable $e) {
            report($e);
            $this->setStatus('No se pudo conectar con SUNARP. Inténtalo nuevamente.', 'danger');
        }
    }

    public function selectPartida(int $index): void
    {
        abort_unless(isset($this->partidas[$index]), 404);
        $this->loadPartida($index);
    }

    public function setPartidasPage(int $page): void
    {
        $lastPage = max(1, (int) ceil(count($this->partidas) / $this->partidasPerPage));
        $this->partidasPage = min(max($page, 1), $lastPage);
    }

    public function resetSearch(): void
    {
        $this->clearSearchState();
        $this->naturalDni = '';
        $this->juridicaQuery = '';
        $this->oficina = '';
        $this->busqueda = '';
        $this->resetValidation();
    }

    public function render()
    {
        $offset = ($this->partidasPage - 1) * $this->partidasPerPage;

        return view('livewire.consulta-partidas', [
            'page' => $this->page(),
            'visiblePartidas' => array_slice($this->partidas, $offset, $this->partidasPerPage, true),
            'partidasLastPage' => max(1, (int) ceil(count($this->partidas) / $this->partidasPerPage)),
        ]);
    }

    protected function page(): array
    {
        return [
            'title' => 'Consulta de Partidas Registrales',
            'source' => 'SUNARP',
            'description' => 'Superintendencia Nacional de los Registros Públicos',
            'accent' => '#7c3aed',
            'oficinas' => $this->oficinasDisponibles(),
        ];
    }

    private function runPersonSearch(callable $callback, string $source): void
    {
        try {
            $response = $callback(app(SunarpServiceInterface::class));

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                $this->people = [];
                $message = $response['message'] ?? "No se encontraron datos en {$source}.";
                $this->setStatus($message, 'warning');

                if (($response['error_type'] ?? null) === 'credential_expired') {
                    session()->forget('pide_password');
                    $this->pidePassword = '';
                    $this->dispatch('open-pide-password-modal', dniUsuario: $this->dniUsuario, message: $message);
                }

                return;
            }

            $this->people = array_values($response['data']);
            $this->setStatus('Se encontraron '.count($this->people)." resultado(s) en {$source}.", 'success');
        } catch (Throwable $e) {
            report($e);
            $this->people = [];
            $this->setStatus("No se pudo conectar con {$source}. Inténtalo nuevamente.", 'danger');
        }
    }

    private function loadPartida(int $index): void
    {
        $partida = $this->partidas[$index];
        $this->selectedPartida = $partida;
        $this->detail = [];

        $numero = (string) ($partida['numero_partida'] ?? $partida['numeroPartida'] ?? $this->busqueda);
        $zona = (string) ($partida['codigo_zona'] ?? $partida['zona'] ?? '');
        $oficina = (string) ($partida['codigo_oficina'] ?? $partida['oficina'] ?? '');
        $placa = (string) ($partida['numero_placa'] ?? $partida['numeroPlaca'] ?? '');

        try {
            $response = app(SunarpServiceInterface::class)->cargarDetallePartida($numero, $zona, $oficina, $placa);

            if (! ($response['success'] ?? false)) {
                $this->setStatus($response['message'] ?? 'No se pudo cargar el detalle de la partida.', 'warning');

                return;
            }

            $this->detail = $response['data'] ?? [];
            $this->selectedPartida = array_merge($partida, $this->detail);
            $this->searched = true;
        } catch (Throwable $e) {
            report($e);
            $this->setStatus('No se pudo cargar el detalle registral. Inténtalo nuevamente.', 'danger');
        }
    }

    private function clearSearchState(): void
    {
        $this->people = [];
        $this->selectedPerson = [];
        $this->partidas = [];
        $this->selectedPartida = [];
        $this->detail = [];
        $this->partidasPage = 1;
        $this->searched = false;
        $this->statusMessage = null;
        $this->activeModal = null;
    }

    private function clearResults(): void
    {
        $this->partidas = [];
        $this->selectedPartida = [];
        $this->detail = [];
        $this->partidasPage = 1;
        $this->searched = false;
        $this->statusMessage = null;
    }

    private function setStatus(string $message, string $type): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
        $this->dispatch('pide-alert', message: $message, type: $type);
    }

    private function oficinasDisponibles(): array
    {
        return Cache::remember('sunarp_oficinas', now()->addDay(), function () {
            $response = app(SunarpServiceInterface::class)->consultarGOficina();

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                return [];
            }

            return collect($response['data'])
                ->map(fn (array $office) => [
                    'value' => ($office['codZona'] ?? '').'|'.($office['codOficina'] ?? ''),
                    'label' => $office['descripcion'] ?? 'Oficina registral',
                ])
                ->filter(fn (array $office) => $office['value'] !== '|')
                ->sortBy('label')
                ->values()
                ->all();
        });
    }

    private function oficinaEtiqueta(string $value): string
    {
        foreach ($this->oficinasDisponibles() as $office) {
            if ($office['value'] === $value) {
                return $office['label'];
            }
        }

        return $value;
    }
}
