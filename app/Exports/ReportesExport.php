<?php

namespace App\Exports;

use App\Models\Reporte;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function query(): Builder
    {
        return Reporte::query()
            ->with([
                'comedor:id,nombre',
                'servicio:id,nombre',
                'tipoIncidente:id,nombre',
                'clasificacion:id,nombre',
                'analisisCausa:id,nombre',
                'reportadoPor:id,name',
            ])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Mes',
            'Fecha',
            'Semana',
            'Comedor',
            'Servicio',
            'Detalle de observación',
            'Tipo de incidencia',
            'Clasificación',
            'Criticidad de hallazgo',
            'Análisis de causa',
            'Se corrigió',
            'Acción inmediata',
            'Requiere plan de acción',
            'Recomendación Salus',
            'Reportado por',
        ];
    }

    /**
     * @param  Reporte  $reporte
     * @return list<string|int>
     */
    public function map($reporte): array
    {
        return [
            ucfirst($reporte->fecha->locale('es')->isoFormat('MMMM YYYY')),
            $reporte->fecha->format('d/m/Y'),
            $reporte->semana,
            $reporte->comedor?->nombre ?? '',
            $reporte->servicio?->nombre ?? '',
            $reporte->detalle_observacion,
            $reporte->tipoIncidente?->nombre ?? '',
            $reporte->clasificacion?->nombre ?? '',
            $reporte->criticidad->label(),
            $reporte->analisisCausa?->nombre ?? '',
            $reporte->se_corrigio ? 'Sí' : 'No',
            $reporte->accion_inmediata,
            $reporte->requiere_plan_accion ? 'Sí' : 'No',
            $reporte->recomendacion_salus,
            $reporte->reportadoPor?->name ?? '',
        ];
    }

    public function title(): string
    {
        return 'Reportes';
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $lastColumn = $sheet->getHighestColumn();
        $sheet->setAutoFilter("A1:{$lastColumn}1");

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F2937'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true,
                ],
            ],
        ];
    }
}
