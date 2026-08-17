<?php

namespace Tests\Feature;

use App\Livewire\ConsultaDni;
use App\Models\Usuario;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\PideCredentialStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DniPdfIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function mockReniecSuccess(): void
    {
        $service = \Mockery::mock(ReniecServiceInterface::class);
        $service->shouldReceive('consultarDNI')->andReturn([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'dni' => '74251836',
                'nombres' => 'María Elena',
                'apellido_paterno' => 'Quispe',
                'apellido_materno' => 'Ramos',
                'estado_civil' => 'Soltera',
                'direccion' => 'Av. Real 123',
                'restriccion' => 'Ninguna',
                'ubigeo' => '150101',
                'foto' => null,
            ],
        ]);
        $this->app->instance(ReniecServiceInterface::class, $service);
    }

    public function test_successful_search_produces_a_pdf_token_usable_by_its_owner(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);
        $this->mockReniecSuccess();
        app(PideCredentialStore::class)->store('secreto');

        $component = Livewire::test(ConsultaDni::class)
            ->set('busqueda', '74251836')
            ->call('search')
            ->assertSet('real', true);

        $token = $component->get('pdfToken');
        $this->assertNotEmpty($token);

        $response = $this->post(route('consulta.dni.pdf'), ['token' => $token]);

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('dni-74251836', $response->headers->get('content-disposition'));
    }

    public function test_client_supplied_personal_data_is_ignored_by_the_pdf_endpoint(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);
        $this->mockReniecSuccess();
        app(PideCredentialStore::class)->store('secreto');

        $component = Livewire::test(ConsultaDni::class)
            ->set('busqueda', '74251836')
            ->call('search');

        $token = $component->get('pdfToken');

        // El endpoint solo declara 'token' como regla de validación: cualquier
        // otro campo enviado por el cliente (nombres, dirección, etc.) es
        // ignorado por completo, nunca llega a construir el PDF.
        $response = $this->post(route('consulta.dni.pdf'), [
            'token' => $token,
            'nombres' => 'Nombre Falsificado',
            'direccion' => 'Dirección Falsificada',
            'dni' => '00000000',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('dni-74251836', $response->headers->get('content-disposition'));
    }

    public function test_a_user_cannot_generate_the_pdf_of_another_users_consultation(): void
    {
        $owner = Usuario::factory()->create();
        $intruder = Usuario::factory()->create();

        $this->actingAs($owner);
        $this->mockReniecSuccess();
        app(PideCredentialStore::class)->store('secreto');

        $component = Livewire::test(ConsultaDni::class)
            ->set('busqueda', '74251836')
            ->call('search');

        $token = $component->get('pdfToken');

        $this->actingAs($intruder);
        $response = $this->post(route('consulta.dni.pdf'), ['token' => $token]);

        $response->assertForbidden();
    }

    public function test_an_expired_or_unknown_token_cannot_be_used(): void
    {
        $usuario = Usuario::factory()->create();
        $this->actingAs($usuario);

        $response = $this->post(route('consulta.dni.pdf'), ['token' => (string) Str::uuid()]);

        $response->assertNotFound();
    }

    public function test_pdf_route_requires_authentication(): void
    {
        $response = $this->post(route('consulta.dni.pdf'), ['token' => (string) Str::uuid()]);

        $response->assertRedirect(route('login'));
    }
}
