<?php

namespace Database\Factories;

use App\Models\TipoProducto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoProducto>
 */
class TipoProductoFactory extends Factory
{
    protected $model = TipoProducto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Tipo '.fake()->unique()->numerify('###'),
            'activo' => true,
        ];
    }
}
