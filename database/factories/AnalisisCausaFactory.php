<?php

namespace Database\Factories;

use App\Models\AnalisisCausa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalisisCausa>
 */
class AnalisisCausaFactory extends Factory
{
    protected $model = AnalisisCausa::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Causa '.fake()->unique()->numerify('####'),
            'activo' => true,
        ];
    }
}
