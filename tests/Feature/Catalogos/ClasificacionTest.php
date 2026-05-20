<?php

namespace Tests\Feature\Catalogos;

use App\Models\ClasificacionIncidente;
use App\Models\Reporte;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClasificacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_clasificacion(): void
    {
        $user = User::factory()->create();
        $tipo = TipoIncidente::factory()->create();

        $this->actingAs($user)
            ->post(route('catalogos.clasificaciones.store'), [
                'tipo_incidente_id' => $tipo->id,
                'nombre' => 'Nueva clasificación',
                'activo' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clasificaciones_incidente', [
            'tipo_incidente_id' => $tipo->id,
            'nombre' => 'Nueva clasificación',
        ]);
    }

    public function test_unique_is_scoped_by_tipo_incidente(): void
    {
        $user = User::factory()->create();
        $tipoA = TipoIncidente::factory()->create();
        $tipoB = TipoIncidente::factory()->create();
        ClasificacionIncidente::factory()->create([
            'tipo_incidente_id' => $tipoA->id,
            'nombre' => 'Mismo nombre',
        ]);

        // Mismo nombre con OTRO tipo es válido
        $this->actingAs($user)
            ->post(route('catalogos.clasificaciones.store'), [
                'tipo_incidente_id' => $tipoB->id,
                'nombre' => 'Mismo nombre',
                'activo' => true,
            ])
            ->assertRedirect();

        // Mismo nombre con MISMO tipo falla
        $this->actingAs($user)
            ->from(route('catalogos.clasificaciones.index'))
            ->post(route('catalogos.clasificaciones.store'), [
                'tipo_incidente_id' => $tipoA->id,
                'nombre' => 'Mismo nombre',
                'activo' => true,
            ])
            ->assertSessionHasErrors('nombre');
    }

    public function test_can_update_clasificacion(): void
    {
        $user = User::factory()->create();
        $tipo = TipoIncidente::factory()->create();
        $clasificacion = ClasificacionIncidente::factory()->create([
            'tipo_incidente_id' => $tipo->id,
            'nombre' => 'Original',
        ]);

        $this->actingAs($user)
            ->put(route('catalogos.clasificaciones.update', $clasificacion), [
                'tipo_incidente_id' => $tipo->id,
                'nombre' => 'Modificado',
                'activo' => false,
            ])
            ->assertRedirect();

        $clasificacion->refresh();
        $this->assertSame('Modificado', $clasificacion->nombre);
        $this->assertFalse($clasificacion->activo);
    }

    public function test_cannot_delete_clasificacion_in_use(): void
    {
        $user = User::factory()->create();
        $tipo = TipoIncidente::factory()->create();
        $clasificacion = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $tipo->id]);
        Reporte::factory()->create([
            'tipo_incidente_id' => $tipo->id,
            'clasificacion_id' => $clasificacion->id,
        ]);

        $this->actingAs($user)
            ->from(route('catalogos.clasificaciones.index'))
            ->delete(route('catalogos.clasificaciones.destroy', $clasificacion))
            ->assertRedirect();

        $this->assertDatabaseHas('clasificaciones_incidente', ['id' => $clasificacion->id]);
    }
}
