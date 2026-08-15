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
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $usuario->refresh()->password_hash));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario);

        Livewire::test(ActualizarPassword::class)
            ->set('currentPassword', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('update')
            ->assertHasErrors(['currentPassword']);
    }
}
