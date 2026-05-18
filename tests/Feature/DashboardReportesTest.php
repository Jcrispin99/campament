<?php

namespace Tests\Feature;

use App\Enums\Criticidad;
use App\Models\AnalisisCausa;
use App\Models\ClasificacionIncidente;
use App\Models\Reporte;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access(): void
    {
        $this->get(route('reportes.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_default_window_is_last_30_days(): void
    {
        $user = User::factory()->create();

        Reporte::factory()->create(['fecha' => now()->subDays(5)]);
        Reporte::factory()->create(['fecha' => now()->subDays(45)]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('reportes.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reportes/Dashboard')
                ->where('kpis.total', 1));
    }

    public function test_filters_by_date_range(): void
    {
        $user = User::factory()->create();

        Reporte::factory()->create(['fecha' => '2026-05-10']);
        Reporte::factory()->create(['fecha' => '2026-05-20']);
        Reporte::factory()->create(['fecha' => '2026-06-05']);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('reportes.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('kpis.total', 2));
    }

    public function test_kpis_count_criticos_corregidos_and_plan_accion(): void
    {
        $user = User::factory()->create();

        Reporte::factory()->create([
            'fecha' => now()->subDays(2),
            'criticidad' => Criticidad::Critico,
            'se_corrigio' => true,
            'requiere_plan_accion' => true,
        ]);
        Reporte::factory()->create([
            'fecha' => now()->subDays(3),
            'criticidad' => Criticidad::Leve,
            'se_corrigio' => false,
            'requiere_plan_accion' => false,
        ]);
        Reporte::factory()->create([
            'fecha' => now()->subDays(4),
            'criticidad' => Criticidad::Moderado,
            'se_corrigio' => true,
            'requiere_plan_accion' => false,
        ]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('reportes.dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('kpis.total', 3)
                ->where('kpis.criticos', 1)
                ->where('kpis.corregidos', 2)
                ->where('kpis.planAccion', 1));
    }

    public function test_aggregations_group_by_tipo_incidente(): void
    {
        $user = User::factory()->create();

        $inocuidad = TipoIncidente::factory()->create(['nombre' => 'Inocuidad']);
        $servicio = TipoIncidente::factory()->create(['nombre' => 'Servicio']);

        $clasifInocuidad = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $inocuidad->id]);
        $clasifServicio = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $servicio->id]);

        Reporte::factory()->count(3)->create([
            'fecha' => now()->subDays(2),
            'tipo_incidente_id' => $inocuidad->id,
            'clasificacion_id' => $clasifInocuidad->id,
        ]);
        Reporte::factory()->create([
            'fecha' => now()->subDays(2),
            'tipo_incidente_id' => $servicio->id,
            'clasificacion_id' => $clasifServicio->id,
        ]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('reportes.dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('porTipo', 2)
                ->where('porTipo.0.nombre', 'Inocuidad')
                ->where('porTipo.0.total', 3)
                ->where('porTipo.1.nombre', 'Servicio')
                ->where('porTipo.1.total', 1));
    }

    public function test_por_causa_returns_grouped_counts(): void
    {
        $user = User::factory()->create();

        $causaA = AnalisisCausa::factory()->create(['nombre' => 'Causa A']);
        $causaB = AnalisisCausa::factory()->create(['nombre' => 'Causa B']);

        Reporte::factory()->count(2)->create(['fecha' => now()->subDays(2), 'analisis_causa_id' => $causaA->id]);
        Reporte::factory()->create(['fecha' => now()->subDays(2), 'analisis_causa_id' => $causaB->id]);

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('reportes.dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('porCausa', 2)
                ->where('porCausa.0.total', 2));
    }

    public function test_top_clasificaciones_limits_to_ten(): void
    {
        $user = User::factory()->create();

        $tipo = TipoIncidente::factory()->create();
        for ($i = 0; $i < 12; $i++) {
            $clasif = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $tipo->id]);
            Reporte::factory()->create([
                'fecha' => now()->subDays(2),
                'tipo_incidente_id' => $tipo->id,
                'clasificacion_id' => $clasif->id,
            ]);
        }

        $this->actingAs($user)
            ->withoutVite()
            ->get(route('reportes.dashboard'))
            ->assertInertia(fn ($page) => $page->has('porClasificacion', 10));
    }
}
