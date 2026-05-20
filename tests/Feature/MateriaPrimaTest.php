<?php

namespace Tests\Feature;

use App\Enums\ConformidadMp;
use App\Models\MateriaPrima;
use App\Models\Origen;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MateriaPrimaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_materias_primas(): void
    {
        $this->get(route('materias-primas.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_materia_prima(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('materias-primas.store'), $this->basePayload())
            ->assertRedirect();

        $this->assertDatabaseCount('materias_primas', 1);
        $this->assertSame($user->id, MateriaPrima::first()->reportado_por_id);
    }

    public function test_conformidades_must_be_valid_enum(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['conformidad_mp'] = 'TAL_VEZ';

        $this->actingAs($user)
            ->from(route('materias-primas.create'))
            ->post(route('materias-primas.store'), $payload)
            ->assertSessionHasErrors('conformidad_mp');
    }

    public function test_can_have_mixed_conformidades(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['conformidad_mp'] = ConformidadMp::Conforme->value;
        $payload['conformidad_documentacion'] = ConformidadMp::NoConforme->value;
        $payload['conformidad_vehiculo'] = ConformidadMp::Conforme->value;

        $this->actingAs($user)
            ->post(route('materias-primas.store'), $payload)
            ->assertRedirect();

        $mp = MateriaPrima::first();
        $this->assertSame(ConformidadMp::Conforme, $mp->conformidad_mp);
        $this->assertSame(ConformidadMp::NoConforme, $mp->conformidad_documentacion);
        $this->assertSame(ConformidadMp::Conforme, $mp->conformidad_vehiculo);
    }

    public function test_can_update_materia_prima(): void
    {
        $user = User::factory()->create();
        $mp = MateriaPrima::factory()->create();

        $payload = $this->basePayload();
        $payload['tipo_producto_id'] = $mp->tipo_producto_id;
        $payload['proveedor_id'] = $mp->proveedor_id;
        $payload['origen_id'] = $mp->origen_id;
        $payload['accion_realizada'] = 'Acción nueva tras revisión.';

        $this->actingAs($user)
            ->put(route('materias-primas.update', $mp), $payload)
            ->assertRedirect();

        $this->assertSame('Acción nueva tras revisión.', $mp->fresh()->accion_realizada);
    }

    public function test_destroy_deletes_materia_prima(): void
    {
        $user = User::factory()->create();
        $mp = MateriaPrima::factory()->create();

        $this->actingAs($user)
            ->delete(route('materias-primas.destroy', $mp))
            ->assertRedirect(route('materias-primas.index'));

        $this->assertDatabaseCount('materias_primas', 0);
    }

    public function test_store_redirects_to_create_when_crear_otro(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['crear_otro'] = '1';

        $this->actingAs($user)
            ->post(route('materias-primas.store'), $payload)
            ->assertRedirect(route('materias-primas.create'));
    }

    public function test_index_eager_loads_relations(): void
    {
        $user = User::factory()->create();
        MateriaPrima::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('materias-primas.index'))
            ->assertOk();
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(): array
    {
        return [
            'fecha' => '2026-05-19',
            'semana' => 21,
            'tipo_producto_id' => TipoProducto::factory()->create()->id,
            'proveedor_id' => Proveedor::factory()->create()->id,
            'origen_id' => Origen::factory()->create()->id,
            'conformidad_mp' => ConformidadMp::Conforme->value,
            'conformidad_documentacion' => ConformidadMp::Conforme->value,
            'conformidad_vehiculo' => ConformidadMp::Conforme->value,
            'causa_nc_observacion' => 'Sin novedad.',
            'productos_afectados' => 'Ninguno.',
            'accion_realizada' => 'Recepción normal.',
        ];
    }
}
