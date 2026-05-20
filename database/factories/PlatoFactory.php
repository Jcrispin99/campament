<?php

namespace Database\Factories;

use App\Models\Plato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plato>
 */
class PlatoFactory extends Factory
{
    protected $model = Plato::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Plato '.fake()->unique()->numerify('###'),
            'activo' => true,
        ];
    }
}
