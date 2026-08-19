<?php

namespace Tests\Feature;

use App\Livewire\ConsultaCoactiva;
use App\Services\Pide\Contracts\CCoactivaServiceInterface;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultaCoactivaTest extends TestCase
{
    public function test_debt_fields_exist_before_search(): void
    {
        Livewire::test(ConsultaCoactiva::class)
            ->assertSeeHtml('id="coactiva-contribuyente"')
            ->assertSeeHtml('id="coactiva-monto"')
            ->assertDontSeeHtml('<table')
            ->assertSee('Sin deuda seleccionada');
    }

    public function test_user_can_navigate_between_debts_and_fields_change(): void
    {
        $service = \Mockery::mock(CCoactivaServiceInterface::class);
        $service->shouldReceive('consultarDeudaCoactiva')->once()->with('01', '74251836')->andReturn([
            'success' => true,
            'data' => [
                [
                    'nomRuc' => 'Contribuyente Uno',
                    'numRuc' => '20111111111',
                    'desEntidad' => 'Entidad Uno',
                    'perDoc' => '2024-01',
                    'mtoDeuda' => 150.5,
                    'fecTraCoa' => '2024-02-01',
                    'fecAct' => '2024-02-10',
                ],
                [
                    'nomRuc' => 'Contribuyente Dos',
                    'numRuc' => '20222222222',
                    'desEntidad' => 'Entidad Dos',
                    'perDoc' => '2024-02',
                    'mtoDeuda' => 275.25,
                    'fecTraCoa' => '2024-03-01',
                    'fecAct' => '2024-03-10',
                ],
            ],
        ]);
        $this->app->instance(CCoactivaServiceInterface::class, $service);

        Livewire::test(ConsultaCoactiva::class)
            ->set('numeroDocumento', '74251836')
            ->call('buscar')
            ->assertSet('deudaActual', 0)
            ->assertSee('Deuda 1 de 2')
            ->assertSee('Navegar entre deudas encontradas')
            ->assertDontSeeHtml('<table')
            ->assertSeeHtml('value="Contribuyente Uno"')
            ->call('deudaSiguiente')
            ->assertSet('deudaActual', 1)
            ->assertSee('Deuda 2 de 2')
            ->assertSeeHtml('value="Contribuyente Dos"')
            ->call('seleccionarDeuda', 0)
            ->assertSet('deudaActual', 0);
    }

    public function test_debt_navigation_stays_inside_available_range(): void
    {
        Livewire::test(ConsultaCoactiva::class)
            ->set('deudas', [['nomRuc' => 'Única deuda']])
            ->call('deudaAnterior')
            ->assertSet('deudaActual', 0)
            ->call('deudaSiguiente')
            ->assertSet('deudaActual', 0)
            ->call('seleccionarDeuda', 99)
            ->assertSet('deudaActual', 0);
    }
}
