<?php

namespace Database\Factories;

use App\Models\TipoIncidente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoIncidente>
 */
class TipoIncidenteFactory extends Factory
{
    protected $model = TipoIncidente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Tipo '.fake()->unique()->numerify('###'),
        ];
    }
}
