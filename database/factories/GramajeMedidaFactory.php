<?php

namespace Database\Factories;

use App\Models\Gramaje;
use App\Models\GramajeMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GramajeMedida>
 */
class GramajeMedidaFactory extends Factory
{
    protected $model = GramajeMedida::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gramaje_id' => Gramaje::factory(),
            'peso' => fake()->randomFloat(2, 80, 300),
            'orden' => 0,
        ];
    }
}
