<?php

namespace Database\Factories;

use App\Enums\EstatusGramaje;
use App\Models\Comedor;
use App\Models\Componente;
use App\Models\Gramaje;
use App\Models\Plato;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gramaje>
 */
class GramajeFactory extends Factory
{
    protected $model = Gramaje::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fecha = fake()->dateTimeBetween('-1 month', 'now');
        $gramajeEsperado = fake()->randomFloat(2, 50, 300);
        $pesoPromedio = fake()->randomFloat(2, $gramajeEsperado * 0.7, $gramajeEsperado * 1.3);
        $variacion = round(($pesoPromedio / $gramajeEsperado) * 100, 2);

        return [
            'fecha' => $fecha,
            'semana' => (int) $fecha->format('W'),
            'fecha_produccion' => $fecha,
            'comedor_id' => Comedor::factory(),
            'servicio_id' => Servicio::factory(),
            'plato_id' => Plato::factory(),
            'componente_id' => Componente::factory(),
            'tipo_corte_id' => null,
            'gramaje_esperado' => $gramajeEsperado,
            'cantidad_muestreada' => 0,
            'peso_promedio' => $pesoPromedio,
            'variacion_pct' => $variacion,
            'estatus' => EstatusGramaje::fromVariacion($variacion)->value,
            'reportado_por_id' => User::factory(),
        ];
    }
}
