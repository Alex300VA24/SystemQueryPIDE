<?php

namespace Database\Factories;

use App\Models\CatEstado;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'persona_id' => Persona::factory(),
            'username' => fake()->unique()->userName(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'email' => fake()->unique()->safeEmail(),
            'requiere_cambio_password' => false,
            'intentos_fallidos' => 0,
            'estado_id' => CatEstado::query()->firstOrCreate(
                ['codigo' => 'ACTIVO'],
                ['descripcion' => 'Activo', 'aplicable_a' => 'GENERAL'],
            )->id,
            'cui' => (string) random_int(0, 9),
            'remember_token' => Str::random(10),
        ];
    }
}
