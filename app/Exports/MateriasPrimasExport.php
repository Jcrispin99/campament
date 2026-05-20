<?php

namespace App\Exports;

use App\Models\MateriaPrima;
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

class MateriasPrimasExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        public readonly ?string $desde = null,
        public readonly ?string $hasta = null,
    ) {}

    public function query(): Builder
    {
        return MateriaPrima::query()
            ->with([
                'tipoProducto:id,nombre',
                'proveedor:id,nombre',
                'origen:id,nombre',
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
            'Tipo de producto',
            'Proveedor',
            'Origen',
            'Conformidad MP',
            'Conformidad documentación',
            'Conformidad vehículo',
            'Causa NC / Observación',
            'Productos afectados',
            'Acción realizada',
            'Reportado por',
        ];
    }

    /**
     * @param  MateriaPrima  $mp
     * @return list<string|int>
     */
    public function map($mp): array
    {
        return [
            ucfirst($mp->fecha->locale('es')->isoFormat('MMMM YYYY')),
            $mp->fecha->format('d/m/Y'),
            $mp->semana,
            $mp->tipoProducto?->nombre ?? '',
            $mp->proveedor?->nombre ?? '',
            $mp->origen?->nombre ?? '',
            $mp->conformidad_mp->label(),
            $mp->conformidad_documentacion->label(),
            $mp->conformidad_vehiculo->label(),
            $mp->causa_nc_observacion,
            $mp->productos_afectados,
            $mp->accion_realizada,
            $mp->reportadoPor?->name ?? '',
        ];
    }

    public function title(): string
    {
        return 'Materia prima';
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
