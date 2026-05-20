<?php

namespace Database\Factories;

use App\Models\Origen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Origen>
 */
class OrigenFactory extends Factory
{
    protected $model = Origen::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Origen '.fake()->unique()->numerify('###'),
            'activo' => true,
        ];
    }
}
