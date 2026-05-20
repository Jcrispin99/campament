<?php

namespace Database\Factories;

use App\Enums\UnidadGramaje;
use App\Models\Componente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Componente>
 */
class ComponenteFactory extends Factory
{
    protected $model = Componente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Componente '.fake()->unique()->numerify('####'),
            'gramaje_sugerido' => fake()->randomFloat(2, 50, 300),
            'unidad' => UnidadGramaje::Gramos->value,
            'observacion' => null,
            'activo' => true,
        ];
    }
}
