<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use App\Livewire\ActualizarPassword;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'NuevaPass#2026')
            ->set('password_confirmation', 'NuevaPass#2026')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NuevaPass#2026', $usuario->refresh()->password_hash));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'wrong-password')
            ->set('password', 'NuevaPass#2026')
            ->set('password_confirmation', 'NuevaPass#2026')
            ->call('update')
            ->assertHasErrors(['currentPassword']);
    }

    public function test_weak_password_is_rejected_by_policy(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'password')
            ->set('password', 'short1')
            ->set('password_confirmation', 'short1')
            ->call('update')
            ->assertHasErrors(['password']);

        $this->assertTrue(Hash::check('password', $usuario->refresh()->password_hash));
    }
}
