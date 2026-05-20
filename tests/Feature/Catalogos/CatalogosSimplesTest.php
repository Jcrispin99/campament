<?php

namespace Tests\Feature\Catalogos;

use App\Models\AnalisisCausa;
use App\Models\Comedor;
use App\Models\Plato;
use App\Models\Reporte;
use App\Models\Servicio;
use App\Models\TipoCorte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosSimplesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_catalogos(): void
    {
        $this->get(route('catalogos.comedores.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_comedores(): void
    {
        $user = User::factory()->create();
        Comedor::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('catalogos.comedores.index'))
            ->assertOk();
    }

    public function test_can_create_comedor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('catalogos.comedores.store'), [
                'nombre' => 'Comedor Nuevo',
                'activo' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comedores', ['nombre' => 'Comedor Nuevo', 'activo' => true]);
    }

    public function test_cannot_create_duplicate_comedor(): void
    {
        $user = User::factory()->create();
        Comedor::factory()->create(['nombre' => 'Repetido']);

        $this->actingAs($user)
            ->from(route('catalogos.comedores.index'))
            ->post(route('catalogos.comedores.store'), [
                'nombre' => 'Repetido',
                'activo' => true,
            ])
            ->assertSessionHasErrors('nombre');
    }

    public function test_can_update_comedor_to_toggle_activo(): void
    {
        $user = User::factory()->create();
        $comedor = Comedor::factory()->create(['nombre' => 'Original', 'activo' => true]);

        $this->actingAs($user)
            ->put(route('catalogos.comedores.update', $comedor), [
                'nombre' => 'Original',
                'activo' => false,
            ])
            ->assertRedirect();

        $this->assertFalse($comedor->fresh()->activo);
    }

    public function test_can_delete_unused_comedor(): void
    {
        $user = User::factory()->create();
        $comedor = Comedor::factory()->create();

        $this->actingAs($user)
            ->delete(route('catalogos.comedores.destroy', $comedor))
            ->assertRedirect();

        $this->assertDatabaseMissing('comedores', ['id' => $comedor->id]);
    }

    public function test_cannot_delete_comedor_in_use(): void
    {
        $user = User::factory()->create();
        $comedor = Comedor::factory()->create();
        Reporte::factory()->create(['comedor_id' => $comedor->id]);

        $this->actingAs($user)
            ->from(route('catalogos.comedores.index'))
            ->delete(route('catalogos.comedores.destroy', $comedor))
            ->assertRedirect();

        $this->assertDatabaseHas('comedores', ['id' => $comedor->id]);
    }

    public function test_can_crud_servicio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('catalogos.servicios.store'), [
            'nombre' => 'Desayuno especial',
            'activo' => true,
        ])->assertRedirect();

        $servicio = Servicio::where('nombre', 'Desayuno especial')->sole();

        $this->actingAs($user)->put(route('catalogos.servicios.update', $servicio), [
            'nombre' => 'Desayuno renombrado',
            'activo' => false,
        ])->assertRedirect();

        $this->assertSame('Desayuno renombrado', $servicio->fresh()->nombre);
        $this->assertFalse($servicio->fresh()->activo);

        $this->actingAs($user)->delete(route('catalogos.servicios.destroy', $servicio))
            ->assertRedirect();

        $this->assertDatabaseMissing('servicios', ['id' => $servicio->id]);
    }

    public function test_can_crud_analisis_causa(): void
    {
        $user = User::factory()->create();
        $causa = AnalisisCausa::factory()->create();

        $this->actingAs($user)->put(route('catalogos.analisis-causas.update', $causa), [
            'nombre' => 'Causa renombrada',
            'activo' => true,
        ])->assertRedirect();

        $this->assertSame('Causa renombrada', $causa->fresh()->nombre);
    }

    public function test_can_crud_plato(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('catalogos.platos.store'), [
            'nombre' => 'Postre',
            'activo' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('platos', ['nombre' => 'Postre']);
    }

    public function test_can_crud_tipo_corte(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('catalogos.tipos-corte.store'), [
            'nombre' => 'Filete',
            'activo' => true,
        ])->assertRedirect();

        $tc = TipoCorte::where('nombre', 'Filete')->sole();

        $this->actingAs($user)->delete(route('catalogos.tipos-corte.destroy', $tc))
            ->assertRedirect();

        $this->assertDatabaseMissing('tipos_corte', ['id' => $tc->id]);
    }

    public function test_plato_factory_keeps_test_db_clean(): void
    {
        // Asegurar que las factories no requieren seeders del proyecto.
        Plato::factory()->create();
        $this->assertSame(1, Plato::count());
    }
}
