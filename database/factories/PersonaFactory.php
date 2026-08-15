<?php

namespace Database\Factories;

use App\Models\CatEstado;
use App\Models\TipoDocumento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'documento_tipo_id' => TipoDocumento::query()->firstOrCreate(
                ['codigo' => 'DNI'],
                ['nombre' => 'Documento Nacional de Identidad', 'abreviatura' => 'DNI', 'longitud_min' => 8, 'longitud_max' => 8, 'activo' => true],
            )->id,
            'documento_numero' => fake()->unique()->numerify('########'),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'nombres' => fake()->firstName(),
            'estado_id' => CatEstado::query()->firstOrCreate(
                ['codigo' => 'ACTIVO'],
                ['descripcion' => 'Activo', 'aplicable_a' => 'GENERAL'],
            )->id,
        ];
    }
}
