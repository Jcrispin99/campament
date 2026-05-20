<?php

namespace Database\Seeders;

use App\Models\AnalisisCausa;
use App\Models\ClasificacionIncidente;
use App\Models\Comedor;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReporteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();
        $comedores = Comedor::pluck('id');
        $servicios = Servicio::pluck('id');
        $causas = AnalisisCausa::pluck('id');
        $tipos = TipoIncidente::with('clasificaciones')->get();

        if ($comedores->isEmpty() || $servicios->isEmpty() || $causas->isEmpty() || $tipos->isEmpty()) {
            $this->command->warn('Catálogos vacíos. Ejecuta primero ComedorSeeder, ServicioSeeder, TipoIncidenteSeeder, ClasificacionIncidenteSeeder y AnalisisCausaSeeder.');

            return;
        }

        for ($i = 0; $i < 25; $i++) {
            $tipo = $tipos->random();
            /** @var ClasificacionIncidente $clasificacion */
            $clasificacion = $tipo->clasificaciones->random();

            Reporte::factory()->create([
                'comedor_id' => $comedores->random(),
                'servicio_id' => $servicios->random(),
                'tipo_incidente_id' => $tipo->id,
                'clasificacion_id' => $clasificacion->id,
                'analisis_causa_id' => $causas->random(),
                'reportado_por_id' => $user->id,
            ]);
        }

        $this->command->info('25 reportes de muestra creados.');
    }
}
