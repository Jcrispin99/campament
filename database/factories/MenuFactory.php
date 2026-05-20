<?php

namespace Database\Factories;

use App\Models\Componente;
use App\Models\Menu;
use App\Models\Servicio;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fecha = CarbonImmutable::instance(fake()->dateTimeBetween('-1 month', 'now'));
        $solicitud = $fecha->subDays(fake()->numberBetween(1, 7));
        $cambio = $solicitud->addDays(fake()->numberBetween(1, 5));
        $dias = (int) $solicitud->diffInDays($cambio);

        return [
            'fecha' => $fecha,
            'semana' => (int) $fecha->format('W'),
            'fecha_solicitud' => $solicitud,
            'fecha_cambio' => $cambio,
            'servicio_id' => Servicio::factory(),
            'componente_id' => Componente::factory(),
            'programado' => fake()->sentence(3),
            'propuesta' => fake()->sentence(3),
            'motivo' => fake()->paragraph(),
            'comentario' => fake()->boolean() ? fake()->sentence() : null,
            'dias_prevision' => $dias,
            'conformidad' => fake()->randomElement(['Conforme', 'Inconforme', 'Pendiente']),
            'analisis' => fake()->paragraph(),
            'reportado_por_id' => User::factory(),
        ];
    }
}
