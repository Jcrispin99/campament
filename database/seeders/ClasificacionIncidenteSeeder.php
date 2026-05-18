<?php

namespace Database\Seeders;

use App\Models\ClasificacionIncidente;
use App\Models\TipoIncidente;
use Illuminate\Database\Seeder;

class ClasificacionIncidenteSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Inocuidad' => [
                'Incumplimiento de BPM',
                'Inadecuado almacenamiento',
                'Inadecuada rotulación',
                'Falta de materiales',
                'Materiales en mal estado',
                'Deficiente limpieza',
                'Presentación e higiene de manipulador',
                'Preparación de servicios anteriores',
                'Deficiente higiene de vajilla / cubertería',
                'Vajilla rota / quebrada',
                'Falta de carné sanitario',
                'Deficiencias en muestra de referencia',
                'Deficiencias en registros y controles',
                'Inadecuado manejo / información de alérgenos',
                'Incumplimiento de documentos de insumos',
                'Inadecuado control de temperaturas del alimento',
                'Insumos vencidos / sin información',
                'Alimentos deteriorados',
                'Incidente alimentario',
            ],
            'Servicio' => [
                'Falta de opciones al inicio del servicio',
                'Demora en reposición',
                'Equipos apagados durante el servicio',
                'Alimento / preparación no conforme',
                'Falta de supervisión de servicio',
                'Falta de AGL en comedor',
                'Incumplimiento de gramajes en servicio',
                'Bajo gramaje promedio de cárnicos',
                'Falta de menajería en línea de servicio',
                'Disposición inadecuada de menajería',
                'Falta de personal de atención',
                'Uso de uniforme deteriorado',
                'Cambio de menú con menos de 72 horas',
            ],
            'Seguridad' => [
                'Falta de información de PQ',
                'Inadecuado uso de PQ',
                'Inadecuada disposición de residuos',
                'Falta de orden',
                'Personal sin EPP / uso inadecuado',
                'Mal uso de equipos',
                'Condición insegura',
                'Acto inseguro',
                'EPP en mal estado',
            ],
        ];

        foreach ($data as $tipoNombre => $clasificaciones) {
            $tipo = TipoIncidente::where('nombre', $tipoNombre)->firstOrFail();
            foreach ($clasificaciones as $nombre) {
                ClasificacionIncidente::firstOrCreate(
                    ['tipo_incidente_id' => $tipo->id, 'nombre' => $nombre],
                    ['activo' => true]
                );
            }
        }
    }
}
