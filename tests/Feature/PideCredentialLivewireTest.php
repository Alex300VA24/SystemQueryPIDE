<?php

namespace Tests\Feature;

use App\Livewire\BaseConsultation;
use App\Livewire\ConsultaDni;
use App\Livewire\PideCredentialModal;
use App\Models\Usuario;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\PideCredentialStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PideCredentialLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_pide_credential_modal_stores_password_without_leaking_it_in_the_dispatched_event(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);

        Livewire::test(PideCredentialModal::class)
            ->set('password', 'super-secreta-pide')
            ->call('save')
            ->assertDispatched('pide-credential-saved');

        $this->assertSame('super-secreta-pide', app(PideCredentialStore::class)->get());
    }

    public function test_base_consultation_has_no_public_password_property(): void
    {
        $this->assertFalse(
            property_exists(BaseConsultation::class, 'pidePassword'),
            'BaseConsultation ya no debe declarar una propiedad pública pidePassword.',
        );
    }

    public function test_search_without_stored_credential_opens_the_credential_modal(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);

        Livewire::test(ConsultaDni::class)
            ->set('busqueda', '74251836')
            ->call('search')
            ->assertDispatched('open-pide-credential-modal')
            ->assertSet('searched', false);
    }

    public function test_search_with_stored_credential_proceeds_and_uses_the_store(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);

        $service = \Mockery::mock(ReniecServiceInterface::class);
        $service->shouldReceive('consultarDNI')
            ->once()
            ->with('74251836', \Mockery::any(), 'clave-guardada')
            ->andReturn(['success' => true, 'data' => ['dni' => '74251836']]);
        $this->app->instance(ReniecServiceInterface::class, $service);

        app(PideCredentialStore::class)->store('clave-guardada');

        Livewire::test(ConsultaDni::class)
            ->set('busqueda', '74251836')
            ->call('search')
            ->assertNotDispatched('open-pide-credential-modal')
            ->assertSet('searched', true);
    }
}
