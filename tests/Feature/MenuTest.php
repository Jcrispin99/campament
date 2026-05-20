<?php

namespace Tests\Feature;

use App\Models\Componente;
use App\Models\Menu;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_menus(): void
    {
        $this->get(route('menus.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_menu_and_dias_prevision_is_calculated(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['fecha_solicitud'] = '2026-05-10';
        $payload['fecha_cambio'] = '2026-05-13';

        $this->actingAs($user)
            ->post(route('menus.store'), $payload)
            ->assertRedirect();

        $menu = Menu::first();
        $this->assertSame(3, $menu->dias_prevision);
        $this->assertSame($user->id, $menu->reportado_por_id);
    }

    public function test_fecha_cambio_must_be_after_or_equal_fecha_solicitud(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['fecha_solicitud'] = '2026-05-13';
        $payload['fecha_cambio'] = '2026-05-10';

        $this->actingAs($user)
            ->from(route('menus.create'))
            ->post(route('menus.store'), $payload)
            ->assertSessionHasErrors('fecha_cambio');

        $this->assertDatabaseCount('menus', 0);
    }

    public function test_same_day_change_counts_zero_dias_prevision(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['fecha_solicitud'] = '2026-05-10';
        $payload['fecha_cambio'] = '2026-05-10';

        $this->actingAs($user)
            ->post(route('menus.store'), $payload)
            ->assertRedirect();

        $this->assertSame(0, Menu::first()->dias_prevision);
    }

    public function test_comentario_is_optional(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        unset($payload['comentario']);

        $this->actingAs($user)
            ->post(route('menus.store'), $payload)
            ->assertRedirect();

        $this->assertNull(Menu::first()->comentario);
    }

    public function test_update_recalculates_dias_prevision(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create([
            'fecha_solicitud' => '2026-05-10',
            'fecha_cambio' => '2026-05-12',
            'dias_prevision' => 2,
        ]);

        $payload = $this->basePayload();
        $payload['servicio_id'] = $menu->servicio_id;
        $payload['componente_id'] = $menu->componente_id;
        $payload['fecha_solicitud'] = '2026-05-10';
        $payload['fecha_cambio'] = '2026-05-17';

        $this->actingAs($user)
            ->put(route('menus.update', $menu), $payload)
            ->assertRedirect();

        $this->assertSame(7, $menu->fresh()->dias_prevision);
    }

    public function test_destroy_deletes_menu(): void
    {
        $user = User::factory()->create();
        $menu = Menu::factory()->create();

        $this->actingAs($user)
            ->delete(route('menus.destroy', $menu))
            ->assertRedirect(route('menus.index'));

        $this->assertDatabaseCount('menus', 0);
    }

    public function test_store_redirects_to_create_when_crear_otro_flag_is_set(): void
    {
        $user = User::factory()->create();

        $payload = $this->basePayload();
        $payload['crear_otro'] = '1';

        $this->actingAs($user)
            ->post(route('menus.store'), $payload)
            ->assertRedirect(route('menus.create'));

        $this->assertDatabaseCount('menus', 1);
    }

    public function test_index_eager_loads_relations(): void
    {
        $user = User::factory()->create();
        Menu::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('menus.index'))
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
            'fecha_solicitud' => '2026-05-10',
            'fecha_cambio' => '2026-05-15',
            'servicio_id' => Servicio::factory()->create()->id,
            'componente_id' => Componente::factory()->create()->id,
            'programado' => 'Pollo a la plancha',
            'propuesta' => 'Lomo saltado',
            'motivo' => 'Cambio por falta de insumo.',
            'comentario' => 'Comentario libre',
            'conformidad' => 'Conforme',
            'analisis' => 'Análisis realizado por el supervisor.',
        ];
    }
}
