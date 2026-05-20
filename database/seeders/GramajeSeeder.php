<?php

namespace Database\Seeders;

use App\Enums\EstatusGramaje;
use App\Models\Comedor;
use App\Models\Componente;
use App\Models\Gramaje;
use App\Models\Plato;
use App\Models\Servicio;
use App\Models\TipoCorte;
use App\Models\User;
use Illuminate\Database\Seeder;

class GramajeSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();
        $comedores = Comedor::pluck('id');
        $servicios = Servicio::pluck('id');
        $platos = Plato::pluck('id');
        $componentes = Componente::all();
        $tiposCorte = TipoCorte::pluck('id');

        if ($comedores->isEmpty() || $servicios->isEmpty() || $platos->isEmpty() || $componentes->isEmpty()) {
            $this->command->warn('Catálogos vacíos. Ejecuta primero los seeders de Comedor, Servicio, Plato y Componente.');

            return;
        }

        for ($i = 0; $i < 25; $i++) {
            $componente = $componentes->random();
            $esperado = (float) $componente->gramaje_sugerido;
            $cantidadMedidas = fake()->numberBetween(3, 10);

            // Genera medidas alrededor del esperado con variación de ±20%
            $medidas = collect(range(1, $cantidadMedidas))->map(
                fn () => round(fake()->randomFloat(2, $esperado * 0.8, $esperado * 1.2), 2),
            );

            $promedio = round($medidas->avg(), 2);
            $variacion = $esperado > 0 ? round(($promedio / $esperado) * 100, 2) : 0.0;

            $gramaje = Gramaje::factory()->create([
                'comedor_id' => $comedores->random(),
                'servicio_id' => $servicios->random(),
                'plato_id' => $platos->random(),
                'componente_id' => $componente->id,
                'tipo_corte_id' => $tiposCorte->isNotEmpty() && fake()->boolean(40)
                    ? $tiposCorte->random()
                    : null,
                'gramaje_esperado' => $esperado,
                'cantidad_muestreada' => $cantidadMedidas,
                'peso_promedio' => $promedio,
                'variacion_pct' => $variacion,
                'estatus' => EstatusGramaje::fromVariacion($variacion)->value,
                'reportado_por_id' => $user->id,
            ]);

            foreach ($medidas->values() as $idx => $peso) {
                $gramaje->medidas()->create([
                    'peso' => $peso,
                    'orden' => $idx,
                ]);
            }
        }

        $this->command->info('25 gramajes de muestra creados.');
    }
}
