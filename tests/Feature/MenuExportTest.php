<?php

namespace Tests\Feature;

use App\Exports\MenusExport;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MenuExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_export(): void
    {
        $this->get(route('menus.export'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_export(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        Menu::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('menus.export'))
            ->assertOk();

        Excel::assertDownloaded(
            'menus-'.now()->format('Ymd-His').'.xlsx',
            fn (MenusExport $export) => $export->query()->count() === 3,
        );
    }

    public function test_export_filters_by_date_range(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        Menu::factory()->create(['fecha' => '2026-05-05']);
        Menu::factory()->create(['fecha' => '2026-05-20']);
        Menu::factory()->create(['fecha' => '2026-06-10']);

        $this->actingAs($user)
            ->get(route('menus.export', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk();

        Excel::assertDownloaded(
            'menus-'.now()->format('Ymd-His').'.xlsx',
            fn (MenusExport $export) => $export->query()->count() === 2,
        );
    }

    public function test_export_headings_are_in_spanish_and_in_correct_order(): void
    {
        $export = new MenusExport;

        $this->assertSame(
            [
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
            ],
            $export->headings(),
        );
    }

    public function test_export_maps_menu_to_human_readable_row(): void
    {
        $menu = Menu::factory()->create([
            'fecha' => '2026-05-19',
            'semana' => 21,
            'fecha_solicitud' => '2026-05-10',
            'fecha_cambio' => '2026-05-13',
            'dias_prevision' => 3,
            'conformidad' => 'Conforme',
            'programado' => 'Pollo a la plancha',
            'propuesta' => 'Lomo saltado',
            'motivo' => 'Cambio por insumo',
            'comentario' => 'Sin más',
            'analisis' => 'Análisis OK',
        ]);
        $menu->load(['servicio', 'componente', 'reportadoPor']);

        $row = (new MenusExport)->map($menu);

        $this->assertSame('Mayo 2026', $row[0]);
        $this->assertSame('19/05/2026', $row[1]);
        $this->assertSame(21, $row[2]);
        $this->assertSame('10/05/2026', $row[3]);
        $this->assertSame('13/05/2026', $row[4]);
        $this->assertSame(3, $row[5]);
        $this->assertSame('Pollo a la plancha', $row[8]);
        $this->assertSame('Lomo saltado', $row[9]);
        $this->assertSame('Conforme', $row[12]);
    }
}
