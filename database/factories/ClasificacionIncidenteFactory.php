<?php

namespace Database\Factories;

use App\Models\ClasificacionIncidente;
use App\Models\TipoIncidente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClasificacionIncidente>
 */
class ClasificacionIncidenteFactory extends Factory
{
    protected $model = ClasificacionIncidente::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_incidente_id' => TipoIncidente::factory(),
            'nombre' => 'Clasificación '.fake()->unique()->numerify('####'),
            'activo' => true,
        ];
    }
}
