<?php

namespace Tests\Feature;

use App\Enums\ConformidadMp;
use App\Exports\MateriasPrimasExport;
use App\Models\MateriaPrima;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MateriaPrimaExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export(): void
    {
        $this->get(route('materias-primas.export'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_export(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        MateriaPrima::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('materias-primas.export'))
            ->assertOk();

        Excel::assertDownloaded(
            'materias-primas-'.now()->format('Ymd-His').'.xlsx',
            fn (MateriasPrimasExport $export) => $export->query()->count() === 3,
        );
    }

    public function test_export_filters_by_date_range(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        MateriaPrima::factory()->create(['fecha' => '2026-05-05']);
        MateriaPrima::factory()->create(['fecha' => '2026-05-20']);
        MateriaPrima::factory()->create(['fecha' => '2026-06-10']);

        $this->actingAs($user)
            ->get(route('materias-primas.export', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk();

        Excel::assertDownloaded(
            'materias-primas-'.now()->format('Ymd-His').'.xlsx',
            fn (MateriasPrimasExport $export) => $export->query()->count() === 2,
        );
    }

    public function test_export_headings_are_in_spanish_and_in_correct_order(): void
    {
        $export = new MateriasPrimasExport;

        $this->assertSame(
            [
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
            ],
            $export->headings(),
        );
    }

    public function test_export_maps_to_human_readable_conformidad_labels(): void
    {
        $mp = MateriaPrima::factory()->create([
            'fecha' => '2026-05-19',
            'semana' => 21,
            'conformidad_mp' => ConformidadMp::Conforme->value,
            'conformidad_documentacion' => ConformidadMp::NoConforme->value,
            'conformidad_vehiculo' => ConformidadMp::Conforme->value,
            'causa_nc_observacion' => 'Docs incompletos',
            'productos_afectados' => 'Lote A',
            'accion_realizada' => 'Solicitar reposición',
        ]);
        $mp->load(['tipoProducto', 'proveedor', 'origen', 'reportadoPor']);

        $row = (new MateriasPrimasExport)->map($mp);

        $this->assertSame('Mayo 2026', $row[0]);
        $this->assertSame('19/05/2026', $row[1]);
        $this->assertSame('Conforme', $row[6]);
        $this->assertSame('No Conforme', $row[7]);
        $this->assertSame('Conforme', $row[8]);
        $this->assertSame('Docs incompletos', $row[9]);
        $this->assertSame('Lote A', $row[10]);
        $this->assertSame('Solicitar reposición', $row[11]);
    }
}
