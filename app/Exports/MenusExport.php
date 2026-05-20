<?php

namespace App\Exports;

use App\Models\Menu;
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

class MenusExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        public readonly ?string $desde = null,
        public readonly ?string $hasta = null,
    ) {}

    public function query(): Builder
    {
        return Menu::query()
            ->with([
                'servicio:id,nombre',
                'componente:id,nombre',
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
            'Fecha de solicitud',
            'Fecha de cambio',
            'Días de previsión',
            'Servicio',
            'Componente',
            'Programado',
            'Propuesta',
            'Motivo',
            'Comentario',
            'Conformidad',
            'Análisis',
            'Reportado por',
        ];
    }

    /**
     * @param  Menu  $menu
     * @return list<string|int>
     */
    public function map($menu): array
    {
        return [
            ucfirst($menu->fecha->locale('es')->isoFormat('MMMM YYYY')),
            $menu->fecha->format('d/m/Y'),
            $menu->semana,
            $menu->fecha_solicitud->format('d/m/Y'),
            $menu->fecha_cambio->format('d/m/Y'),
            $menu->dias_prevision,
            $menu->servicio?->nombre ?? '',
            $menu->componente?->nombre ?? '',
            $menu->programado,
            $menu->propuesta,
            $menu->motivo,
            $menu->comentario ?? '',
            $menu->conformidad,
            $menu->analisis,
            $menu->reportadoPor?->name ?? '',
        ];
    }

    public function title(): string
    {
        return 'Cambios de menú';
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
