<?php

namespace Tests\Feature;

use App\Enums\EstatusGramaje;
use App\Models\Comedor;
use App\Models\Componente;
use App\Models\Gramaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class GramajesDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('gramajes.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_returns_kpis_and_estatus_distribution(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        Gramaje::factory()->count(3)->create([
            'fecha' => '2026-05-15',
            'estatus' => EstatusGramaje::Conforme->value,
            'variacion_pct' => 105,
            'cantidad_muestreada' => 5,
        ]);
        Gramaje::factory()->count(2)->create([
            'fecha' => '2026-05-15',
            'estatus' => EstatusGramaje::Inconforme->value,
            'variacion_pct' => 80,
            'cantidad_muestreada' => 4,
        ]);

        $this->actingAs($user)
            ->get(route('gramajes.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Gramajes/Dashboard')
                ->where('kpis.total', 5)
                ->where('kpis.conformes', 3)
                ->where('kpis.inconformes', 2)
                ->where('kpis.totalMedidas', 23)
                ->where('estatusDistribucion.conformes', 3)
                ->where('estatusDistribucion.inconformes', 2)
            );
    }

    public function test_dashboard_filters_by_date_range(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        Gramaje::factory()->create(['fecha' => '2026-04-15']);
        Gramaje::factory()->create(['fecha' => '2026-05-15']);

        $this->actingAs($user)
            ->get(route('gramajes.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('kpis.total', 1),
            );
    }

    public function test_dashboard_groups_by_comedor_and_top_componentes(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $comedorA = Comedor::factory()->create(['nombre' => 'Bahía F1']);
        $comp = Componente::factory()->create(['nombre' => 'Pollo']);

        Gramaje::factory()->count(3)->create([
            'fecha' => '2026-05-15',
            'comedor_id' => $comedorA->id,
            'componente_id' => $comp->id,
            'estatus' => EstatusGramaje::Conforme->value,
        ]);
        Gramaje::factory()->create([
            'fecha' => '2026-05-15',
            'comedor_id' => $comedorA->id,
            'componente_id' => $comp->id,
            'estatus' => EstatusGramaje::Inconforme->value,
        ]);

        $this->actingAs($user)
            ->get(route('gramajes.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('porComedor.0.nombre', 'Bahía F1')
                ->where('porComedor.0.total', 4)
                ->where('porComponente.0.nombre', 'Pollo')
                ->where('porComponente.0.total', 4)
                ->where('porComponente.0.conformes', 3)
                ->where('porComponente.0.inconformes', 1)
            );
    }

    public function test_dashboard_lists_top_desviaciones_by_absolute_distance_from_100(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        // 100 (sin desviación) y 150 (50 de desv) y 60 (40 de desv)
        Gramaje::factory()->create(['fecha' => '2026-05-15', 'variacion_pct' => 100]);
        Gramaje::factory()->create(['fecha' => '2026-05-15', 'variacion_pct' => 150]);
        Gramaje::factory()->create(['fecha' => '2026-05-15', 'variacion_pct' => 60]);

        $this->actingAs($user)
            ->get(route('gramajes.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('topDesviaciones', 3)
                ->where('topDesviaciones.0.variacion', 150)
                ->where('topDesviaciones.1.variacion', 60)
                ->where('topDesviaciones.2.variacion', 100)
            );
    }

    public function test_dashboard_lists_ultimas_inconformes_only(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        Gramaje::factory()->create([
            'fecha' => '2026-05-15',
            'estatus' => EstatusGramaje::Conforme->value,
        ]);
        Gramaje::factory()->create([
            'fecha' => '2026-05-15',
            'estatus' => EstatusGramaje::Inconforme->value,
            'variacion_pct' => 75,
        ]);

        $this->actingAs($user)
            ->get(route('gramajes.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('ultimasInconformes', 1)
                ->where('ultimasInconformes.0.variacion', 75)
            );
    }

    public function test_dashboard_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('gramajes.index'))
            ->get(route('gramajes.dashboard', [
                'desde' => '2026-05-31',
                'hasta' => '2026-05-01',
            ]))
            ->assertSessionHasErrors('hasta');
    }
}
