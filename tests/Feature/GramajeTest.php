<?php

namespace Tests\Feature;

use App\Enums\EstatusGramaje;
use App\Models\Comedor;
use App\Models\Componente;
use App\Models\Gramaje;
use App\Models\Plato;
use App\Models\Servicio;
use App\Models\TipoCorte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GramajeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_gramajes(): void
    {
        $this->get(route('gramajes.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_gramaje_and_derivados_are_calculated(): void
    {
        $user = User::factory()->create();
        $comedor = Comedor::factory()->create();
        $servicio = Servicio::factory()->create();
        $plato = Plato::factory()->create();
        $componente = Componente::factory()->create(['gramaje_sugerido' => 150]);

        $payload = [
            'fecha' => '2026-05-19',
            'semana' => 21,
            'fecha_produccion' => '2026-05-18',
            'comedor_id' => $comedor->id,
            'servicio_id' => $servicio->id,
            'plato_id' => $plato->id,
            'componente_id' => $componente->id,
            'tipo_corte_id' => null,
            'gramaje_esperado' => 150,
            'medidas' => [148, 152, 150, 149, 151],
        ];

        $this->actingAs($user)
            ->post(route('gramajes.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('gramajes', 1);

        $gramaje = Gramaje::first();
        $this->assertSame(5, $gramaje->cantidad_muestreada);
        $this->assertSame('150.00', (string) $gramaje->peso_promedio);
        $this->assertSame('100.00', (string) $gramaje->variacion_pct);
        $this->assertSame(EstatusGramaje::Conforme, $gramaje->estatus);
        $this->assertSame($user->id, $gramaje->reportado_por_id);
        $this->assertSame(5, $gramaje->medidas()->count());
    }

    public function test_estatus_is_inconforme_when_average_below_expected(): void
    {
        $user = User::factory()->create();
        $componente = Componente::factory()->create(['gramaje_sugerido' => 200]);

        $payload = [
            'fecha' => '2026-05-19',
            'semana' => 21,
            'fecha_produccion' => '2026-05-19',
            'comedor_id' => Comedor::factory()->create()->id,
            'servicio_id' => Servicio::factory()->create()->id,
            'plato_id' => Plato::factory()->create()->id,
            'componente_id' => $componente->id,
            'tipo_corte_id' => null,
            'gramaje_esperado' => 200,
            'medidas' => [180, 190, 170],
        ];

        $this->actingAs($user)
            ->post(route('gramajes.store'), $payload)
            ->assertRedirect();

        $gramaje = Gramaje::first();
        $this->assertSame(3, $gramaje->cantidad_muestreada);
        $this->assertSame('180.00', (string) $gramaje->peso_promedio);
        $this->assertSame('90.00', (string) $gramaje->variacion_pct);
        $this->assertSame(EstatusGramaje::Inconforme, $gramaje->estatus);
    }

    public function test_estatus_is_conforme_when_average_above_expected(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['gramaje_esperado'] = 100;
        $payload['medidas'] = [110, 120, 130];

        $this->actingAs($user)
            ->post(route('gramajes.store'), $payload)
            ->assertRedirect();

        $gramaje = Gramaje::first();
        $this->assertSame('120.00', (string) $gramaje->peso_promedio);
        $this->assertSame('120.00', (string) $gramaje->variacion_pct);
        $this->assertSame(EstatusGramaje::Conforme, $gramaje->estatus);
    }

    public function test_medidas_required(): void
    {
        $user = User::factory()->create();
        $payload = $this->basePayload();
        $payload['medidas'] = [];

        $this->actingAs($user)
            ->from(route('gramajes.create'))
            ->post(route('gramajes.store'), $payload)
            ->assertSessionHasErrors('medidas');

        $this->assertDatabaseCount('gramajes', 0);
    }

    public function test_medidas_must_be_positive_numbers(): void
    {
        $user = User::factory()->create();
        $payload = $this->basePayload();
        $payload['medidas'] = [100, -5, 50];

        $this->actingAs($user)
            ->from(route('gramajes.create'))
            ->post(route('gramajes.store'), $payload)
            ->assertSessionHasErrors('medidas.1');
    }

    public function test_update_replaces_medidas_and_recalculates(): void
    {
        $user = User::factory()->create();
        $gramaje = Gramaje::factory()->create([
            'gramaje_esperado' => 100,
            'peso_promedio' => 100,
            'variacion_pct' => 100,
            'cantidad_muestreada' => 0,
            'estatus' => EstatusGramaje::Conforme->value,
        ]);
        $gramaje->medidas()->create(['peso' => 100, 'orden' => 0]);

        $payload = $this->basePayload();
        $payload['comedor_id'] = $gramaje->comedor_id;
        $payload['servicio_id'] = $gramaje->servicio_id;
        $payload['plato_id'] = $gramaje->plato_id;
        $payload['componente_id'] = $gramaje->componente_id;
        $payload['gramaje_esperado'] = 100;
        $payload['medidas'] = [80, 90];

        $this->actingAs($user)
            ->put(route('gramajes.update', $gramaje), $payload)
            ->assertRedirect();

        $gramaje->refresh();
        $this->assertSame(2, $gramaje->cantidad_muestreada);
        $this->assertSame('85.00', (string) $gramaje->peso_promedio);
        $this->assertSame('85.00', (string) $gramaje->variacion_pct);
        $this->assertSame(EstatusGramaje::Inconforme, $gramaje->estatus);
        $this->assertSame(2, $gramaje->medidas()->count());
    }

    public function test_destroy_deletes_gramaje_and_cascades_medidas(): void
    {
        $user = User::factory()->create();
        $gramaje = Gramaje::factory()->create();
        $gramaje->medidas()->create(['peso' => 100, 'orden' => 0]);
        $gramaje->medidas()->create(['peso' => 110, 'orden' => 1]);

        $this->actingAs($user)
            ->delete(route('gramajes.destroy', $gramaje))
            ->assertRedirect();

        $this->assertDatabaseCount('gramajes', 0);
        $this->assertDatabaseCount('gramaje_medidas', 0);
    }

    public function test_store_redirects_to_create_when_crear_otro_flag_is_set(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['crear_otro'] = '1';

        $this->actingAs($user)
            ->post(route('gramajes.store'), $payload)
            ->assertRedirect(route('gramajes.create'));

        $this->assertDatabaseCount('gramajes', 1);
    }

    public function test_store_redirects_to_show_when_crear_otro_flag_is_absent(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('gramajes.store'), $this->basePayload())
            ->assertRedirect(route('gramajes.show', Gramaje::first()));
    }

    public function test_index_eager_loads_relations(): void
    {
        $user = User::factory()->create();
        Gramaje::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('gramajes.index'))
            ->assertOk();
    }

    public function test_tipo_corte_is_optional_and_nullable(): void
    {
        $user = User::factory()->create();
        $tipoCorte = TipoCorte::factory()->create();

        $payload = $this->basePayload();
        $payload['tipo_corte_id'] = $tipoCorte->id;
        $payload['medidas'] = [100];

        $this->actingAs($user)
            ->post(route('gramajes.store'), $payload)
            ->assertRedirect();

        $this->assertSame($tipoCorte->id, Gramaje::first()->tipo_corte_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(): array
    {
        return [
            'fecha' => '2026-05-19',
            'semana' => 21,
            'fecha_produccion' => '2026-05-19',
            'comedor_id' => Comedor::factory()->create()->id,
            'servicio_id' => Servicio::factory()->create()->id,
            'plato_id' => Plato::factory()->create()->id,
            'componente_id' => Componente::factory()->create()->id,
            'tipo_corte_id' => null,
            'gramaje_esperado' => 100,
            'medidas' => [100],
        ];
    }
}
