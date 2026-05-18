<?php

namespace Database\Factories;

use App\Models\Evidencia;
use App\Models\Reporte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidencia>
 */
class EvidenciaFactory extends Factory
{
    protected $model = Evidencia::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporte_id' => Reporte::factory(),
            'imagen' => 'evidencias/'.fake()->uuid().'.jpg',
            'descripcion' => fake()->sentence(),
        ];
    }
}
