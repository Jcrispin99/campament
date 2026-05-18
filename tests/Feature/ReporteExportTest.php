<?php

namespace Tests\Feature;

use App\Exports\ReportesExport;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReporteExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export(): void
    {
        $this->get(route('reportes.export'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_export(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        Reporte::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('reportes.export'))
            ->assertOk();

        Excel::assertDownloaded(
            'reportes-'.now()->format('Ymd-His').'.xlsx',
            fn (ReportesExport $export) => $export->query()->count() === 3,
        );
    }

    public function test_export_filters_by_date_range(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        Reporte::factory()->create(['fecha' => '2026-05-05']);
        Reporte::factory()->create(['fecha' => '2026-05-20']);
        Reporte::factory()->create(['fecha' => '2026-06-10']);

        $this->actingAs($user)
            ->get(route('reportes.export', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk();

        Excel::assertDownloaded(
            'reportes-'.now()->format('Ymd-His').'.xlsx',
            fn (ReportesExport $export) => $export->query()->count() === 2
                && $export->desde === '2026-05-01'
                && $export->hasta === '2026-05-31',
        );
    }

    public function test_export_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('reportes.dashboard'))
            ->get(route('reportes.export', [
                'desde' => '2026-05-31',
                'hasta' => '2026-05-01',
            ]))
            ->assertSessionHasErrors('hasta');
    }

    public function test_export_headings_are_in_spanish_and_in_correct_order(): void
    {
        $export = new ReportesExport;

        $this->assertSame(
            [
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
            ],
            $export->headings(),
        );
    }

    public function test_export_maps_reporte_to_human_readable_row(): void
    {
        $reporte = Reporte::factory()->create([
            'fecha' => '2026-05-18',
            'semana' => 21,
            'detalle_observacion' => 'Detalle X',
            'criticidad' => 'CRITICO',
            'se_corrigio' => true,
            'requiere_plan_accion' => false,
            'accion_inmediata' => 'Acción inmediata X',
            'recomendacion_salus' => 'Recomendación Salus X',
        ]);

        $reporte->load([
            'comedor',
            'servicio',
            'tipoIncidente',
            'clasificacion',
            'analisisCausa',
            'reportadoPor',
        ]);

        $row = (new ReportesExport)->map($reporte);

        $this->assertSame('Mayo 2026', $row[0]);
        $this->assertSame('18/05/2026', $row[1]);
        $this->assertSame(21, $row[2]);
        $this->assertSame('Detalle X', $row[5]);
        $this->assertSame('Crítico', $row[8]);
        $this->assertSame('Sí', $row[10]);
        $this->assertSame('Acción inmediata X', $row[11]);
        $this->assertSame('No', $row[12]);
        $this->assertSame('Recomendación Salus X', $row[13]);
    }
}
