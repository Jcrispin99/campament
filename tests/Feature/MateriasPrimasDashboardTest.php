<?php

namespace Tests\Feature;

use App\Enums\ConformidadMp;
use App\Models\MateriaPrima;
use App\Models\Origen;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MateriasPrimasDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('materias-primas.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_returns_kpis_and_aggregates(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        // 3 conformes en su totalidad
        MateriaPrima::factory()->count(3)->create([
            'fecha' => '2026-05-15',
            'conformidad_mp' => ConformidadMp::Conforme->value,
            'conformidad_documentacion' => ConformidadMp::Conforme->value,
            'conformidad_vehiculo' => ConformidadMp::Conforme->value,
        ]);

        // 2 con NC en al menos una categoría
        MateriaPrima::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad_mp' => ConformidadMp::NoConforme->value,
            'conformidad_documentacion' => ConformidadMp::Conforme->value,
            'conformidad_vehiculo' => ConformidadMp::Conforme->value,
        ]);
        MateriaPrima::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad_mp' => ConformidadMp::Conforme->value,
            'conformidad_documentacion' => ConformidadMp::NoConforme->value,
            'conformidad_vehiculo' => ConformidadMp::NoConforme->value,
        ]);

        $this->actingAs($user)
            ->get(route('materias-primas.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('MateriasPrimas/Dashboard')
                ->where('kpis.total', 5)
                ->where('kpis.conformes', 3)
                ->where('kpis.conNc', 2)
                ->where('kpis.conformidadMp', 4)
                ->where('kpis.conformidadDocumentacion', 4)
                ->where('kpis.conformidadVehiculo', 4)
            );
    }

    public function test_dashboard_filters_by_date_range(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        MateriaPrima::factory()->create(['fecha' => '2026-04-15']);
        MateriaPrima::factory()->create(['fecha' => '2026-05-15']);
        MateriaPrima::factory()->create(['fecha' => '2026-06-15']);

        $this->actingAs($user)
            ->get(route('materias-primas.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('kpis.total', 1),
            );
    }

    public function test_dashboard_groups_by_tipo_producto_proveedor_y_origen(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        $tipoA = TipoProducto::factory()->create(['nombre' => 'Abarrotes']);
        $tipoB = TipoProducto::factory()->create(['nombre' => 'Congelados']);
        $provA = Proveedor::factory()->create(['nombre' => 'NEWREST']);
        $origenA = Origen::factory()->create(['nombre' => 'Lima']);

        MateriaPrima::factory()->count(3)->create([
            'fecha' => '2026-05-15',
            'tipo_producto_id' => $tipoA->id,
            'proveedor_id' => $provA->id,
            'origen_id' => $origenA->id,
        ]);
        MateriaPrima::factory()->create([
            'fecha' => '2026-05-15',
            'tipo_producto_id' => $tipoB->id,
            'proveedor_id' => $provA->id,
            'origen_id' => $origenA->id,
        ]);

        $this->actingAs($user)
            ->get(route('materias-primas.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('porTipoProducto', 2)
                ->where('porTipoProducto.0.nombre', 'Abarrotes')
                ->where('porTipoProducto.0.total', 3)
                ->where('porTipoProducto.1.nombre', 'Congelados')
                ->where('porTipoProducto.1.total', 1)
                ->has('porProveedor', 1)
                ->has('porOrigen', 1)
            );
    }

    public function test_dashboard_lists_ultimas_no_conformidades(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();

        MateriaPrima::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad_mp' => ConformidadMp::Conforme->value,
            'conformidad_documentacion' => ConformidadMp::Conforme->value,
            'conformidad_vehiculo' => ConformidadMp::Conforme->value,
        ]);
        MateriaPrima::factory()->create([
            'fecha' => '2026-05-15',
            'conformidad_mp' => ConformidadMp::NoConforme->value,
            'conformidad_documentacion' => ConformidadMp::Conforme->value,
            'conformidad_vehiculo' => ConformidadMp::NoConforme->value,
            'causa_nc_observacion' => 'Empaque dañado',
            'accion_realizada' => 'Rechazo',
        ]);

        $this->actingAs($user)
            ->get(route('materias-primas.dashboard', [
                'desde' => '2026-05-01',
                'hasta' => '2026-05-31',
            ]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('ultimasNc', 1)
                ->where('ultimasNc.0.causa', 'Empaque dañado')
                ->where('ultimasNc.0.accion', 'Rechazo')
                ->where('ultimasNc.0.ncEn', ['MP', 'Vehículo'])
            );
    }

    public function test_dashboard_rejects_invalid_date_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('materias-primas.index'))
            ->get(route('materias-primas.dashboard', [
                'desde' => '2026-05-31',
                'hasta' => '2026-05-01',
            ]))
            ->assertSessionHasErrors('hasta');
    }
}
