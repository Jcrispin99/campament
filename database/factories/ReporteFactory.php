<?php

namespace Database\Factories;

use App\Enums\Criticidad;
use App\Models\AnalisisCausa;
use App\Models\ClasificacionIncidente;
use App\Models\Comedor;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reporte>
 */
class ReporteFactory extends Factory
{
    protected $model = Reporte::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = TipoIncidente::factory()->create();
        $clasificacion = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $tipo->id]);

        $fecha = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'fecha' => $fecha,
            'semana' => (int) $fecha->format('W'),
            'detalle_observacion' => fake()->paragraph(),
            'criticidad' => fake()->randomElement(Criticidad::cases())->value,
            'se_corrigio' => fake()->boolean(),
            'accion_inmediata' => fake()->sentence(),
            'requiere_plan_accion' => fake()->boolean(),
            'recomendacion_salus' => fake()->sentence(),
            'comedor_id' => Comedor::factory(),
            'servicio_id' => Servicio::factory(),
            'tipo_incidente_id' => $tipo->id,
            'clasificacion_id' => $clasificacion->id,
            'analisis_causa_id' => AnalisisCausa::factory(),
            'reportado_por_id' => User::factory(),
        ];
    }
}
