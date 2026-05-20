<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MenusDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('menus.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_returns_kpis_with_conformidad_text_classification(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        Menu::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad' => 'Conforme',
            'dias_prevision' => 5,
        ]);
        Menu::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad' => 'Conforme parcial',
            'dias_prevision' => 3,
        ]);
        Menu::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad' => 'Inconforme',
            'dias_prevision' => 1,
        ]);
        Menu::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad' => 'Pendiente',
            'dias_prevision' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('menus.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Menus/Dashboard')
                ->where('kpis.total', 4)
                ->where('kpis.conformesAprox', 2)
                ->where('kpis.inconformesAprox', 1)
                ->where('kpis.sinPrevision', 2)
                ->where('kpis.previsionPromedio', 2.3)
            );
    }

    public function test_distribucion_prevision_buckets(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        // Crítico (<3): 0, 2
        Menu::factory()->create(['fecha' => '2026-05-15', 'dias_prevision' => 0]);
        Menu::factory()->create(['fecha' => '2026-05-15', 'dias_prevision' => 2]);

        // Aceptable (3-4): 3, 4
        Menu::factory()->create(['fecha' => '2026-05-15', 'dias_prevision' => 3]);
        Menu::factory()->create(['fecha' => '2026-05-15', 'dias_prevision' => 4]);

        // Bueno (5+): 5, 10
        Menu::factory()->create(['fecha' => '2026-05-15', 'dias_prevision' => 5]);
        Menu::factory()->create(['fecha' => '2026-05-15', 'dias_prevision' => 10]);

        $this->actingAs($user)
            ->get(route('menus.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('distribucionPrevision.0.rango', 'Menos de 3 días')
                ->where('distribucionPrevision.0.total', 2)
                ->where('distribucionPrevision.1.rango', '3-4 días')
                ->where('distribucionPrevision.1.total', 2)
                ->where('distribucionPrevision.2.rango', '5 o más días')
                ->where('distribucionPrevision.2.total', 2)
            );
    }

    public function test_groups_by_servicio_and_componente(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $servicio = Servicio::factory()->create(['nombre' => 'Desayuno']);

        Menu::factory()->count(3)->create([
            'fecha' => '2026-05-15',
            'servicio_id' => $servicio->id,
        ]);

        $this->actingAs($user)
            ->get(route('menus.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('porServicio.0.nombre', 'Desayuno')
                ->where('porServicio.0.total', 3)
            );
    }

    public function test_lists_cambios_urgentes(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        Menu::factory()->create([
            'fecha' => '2026-05-15',
            'dias_prevision' => 5,
        ]);
        Menu::factory()->create([
            'fecha' => '2026-05-15',
            'dias_prevision' => 1,
            'motivo' => 'Urgencia inmediata',
        ]);

        $this->actingAs($user)
            ->get(route('menus.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('cambiosUrgentes', 1)
                ->where('cambiosUrgentes.0.motivo', 'Urgencia inmediata')
                ->where('cambiosUrgentes.0.diasPrevision', 1)
            );
    }

    public function test_dashboard_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('menus.index'))
            ->get(route('menus.dashboard', [
                'desde' => '2026-05-31',
                'hasta' => '2026-05-01',
            ]))
            ->assertSessionHasErrors('hasta');
    }
}
