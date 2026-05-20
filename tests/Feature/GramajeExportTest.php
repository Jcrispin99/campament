<?php

namespace Tests\Feature;

use App\Exports\GramajesExport;
use App\Models\Gramaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class GramajeExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export(): void
    {
        $this->get(route('gramajes.export'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_export(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        Gramaje::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('gramajes.export'))
            ->assertOk();

        Excel::assertDownloaded(
            'gramajes-'.now()->format('Ymd-His').'.xlsx',
            fn (GramajesExport $export) => $export->query()->count() === 3,
        );
    }

    public function test_export_filters_by_date_range(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        Gramaje::factory()->create(['fecha' => '2026-05-05']);
        Gramaje::factory()->create(['fecha' => '2026-05-20']);
        Gramaje::factory()->create(['fecha' => '2026-06-10']);

        $this->actingAs($user)
            ->get(route('gramajes.export', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk();

        Excel::assertDownloaded(
            'gramajes-'.now()->format('Ymd-His').'.xlsx',
            fn (GramajesExport $export) => $export->query()->count() === 2,
        );
    }

    public function test_export_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('gramajes.index'))
            ->get(route('gramajes.export', [
                'desde' => '2026-05-31',
                'hasta' => '2026-05-01',
            ]))
            ->assertSessionHasErrors('hasta');
    }

    public function test_export_headings_are_in_spanish_and_in_correct_order(): void
    {
        $export = new GramajesExport;

        $this->assertSame(
            [
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
            ],
            $export->headings(),
        );
    }
}
