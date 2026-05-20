<?php

namespace Database\Factories;

use App\Models\TipoCorte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoCorte>
 */
class TipoCorteFactory extends Factory
{
    protected $model = TipoCorte::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Corte '.fake()->unique()->numerify('###'),
            'activo' => true,
        ];
    }
}
