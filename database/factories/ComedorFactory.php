<?php

namespace Database\Factories;

use App\Models\Comedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comedor>
 */
class ComedorFactory extends Factory
{
    protected $model = Comedor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Comedor '.fake()->unique()->numerify('###'),
            'activo' => true,
        ];
    }
}
