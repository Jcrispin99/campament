<?php

namespace Database\Factories;

use App\Enums\ConformidadMp;
use App\Models\MateriaPrima;
use App\Models\Origen;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MateriaPrima>
 */
class MateriaPrimaFactory extends Factory
{
    protected $model = MateriaPrima::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fecha = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'fecha' => $fecha,
            'semana' => (int) $fecha->format('W'),
            'tipo_producto_id' => TipoProducto::factory(),
            'proveedor_id' => Proveedor::factory(),
            'origen_id' => Origen::factory(),
            'conformidad_mp' => fake()->randomElement(ConformidadMp::cases())->value,
            'conformidad_documentacion' => fake()->randomElement(ConformidadMp::cases())->value,
            'conformidad_vehiculo' => fake()->randomElement(ConformidadMp::cases())->value,
            'causa_nc_observacion' => fake()->paragraph(),
            'productos_afectados' => fake()->sentence(),
            'accion_realizada' => fake()->paragraph(),
            'reportado_por_id' => User::factory(),
        ];
    }
}
