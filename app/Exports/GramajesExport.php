<?php

namespace App\Exports;

use App\Models\Gramaje;
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

class GramajesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        public readonly ?string $desde = null,
        public readonly ?string $hasta = null,
    ) {}

    public function query(): Builder
    {
        return Gramaje::query()
            ->with([
                'comedor:id,nombre',
                'servicio:id,nombre',
                'plato:id,nombre',
                'componente:id,nombre,unidad',
                'tipoCorte:id,nombre',
                'reportadoPor:id,name',
            ])
            ->when($this->desde, fn (Builder $q) => $q->whereDate('fecha', '>=', $this->desde))
            ->when($this->hasta, fn (Builder $q) => $q->whereDate('fecha', '<=', $this->hasta))
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
            'Fecha de producción',
            'Comedor',
            'Servicio',
            'Plato',
            'Componente',
            'Tipo de corte',
            'Unidad',
            'Gramaje esperado',
            'Cantidad muestreada',
            'Peso promedio',
            'Variación %',
            'Estatus',
            'Reportado por',
        ];
    }

    /**
     * @param  Gramaje  $gramaje
     * @return list<string|int|float>
     */
    public function map($gramaje): array
    {
        return [
            ucfirst($gramaje->fecha->locale('es')->isoFormat('MMMM YYYY')),
            $gramaje->fecha->format('d/m/Y'),
            $gramaje->semana,
            $gramaje->fecha_produccion->format('d/m/Y'),
            $gramaje->comedor?->nombre ?? '',
            $gramaje->servicio?->nombre ?? '',
            $gramaje->plato?->nombre ?? '',
            $gramaje->componente?->nombre ?? '',
            $gramaje->tipoCorte?->nombre ?? '',
            $gramaje->componente?->unidad?->abreviado() ?? '',
            (float) $gramaje->gramaje_esperado,
            $gramaje->cantidad_muestreada,
            (float) $gramaje->peso_promedio,
            (float) $gramaje->variacion_pct,
            $gramaje->estatus->label(),
            $gramaje->reportadoPor?->name ?? '',
        ];
    }

    public function title(): string
    {
        return 'Gramajes';
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
