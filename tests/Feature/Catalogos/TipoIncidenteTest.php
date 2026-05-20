<?php

namespace Tests\Feature\Catalogos;

use App\Models\ClasificacionIncidente;
use App\Models\Reporte;
use App\Models\TipoIncidente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TipoIncidenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_tipo_incidente(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('catalogos.tipos-incidente.store'), ['nombre' => 'Calidad'])
            ->assertRedirect();

        $this->assertDatabaseHas('tipos_incidente', ['nombre' => 'Calidad']);
    }

    public function test_index_shows_clasificaciones_count(): void
    {
        $user = User::factory()->create();
        $tipo = TipoIncidente::factory()->create();
        ClasificacionIncidente::factory()->count(3)->create(['tipo_incidente_id' => $tipo->id]);

        $this->actingAs($user)
            ->get(route('catalogos.tipos-incidente.index'))
            ->assertOk();
    }

    public function test_can_update_tipo(): void
    {
        $user = User::factory()->create();
        $tipo = TipoIncidente::factory()->create(['nombre' => 'Original']);

        $this->actingAs($user)
            ->put(route('catalogos.tipos-incidente.update', $tipo), ['nombre' => 'Modificado'])
            ->assertRedirect();

        $this->assertSame('Modificado', $tipo->fresh()->nombre);
    }

    public function test_cannot_delete_tipo_with_clasificaciones_used_in_reportes(): void
    {
        $user = User::factory()->create();
        $tipo = TipoIncidente::factory()->create();
        $clasificacion = ClasificacionIncidente::factory()->create(['tipo_incidente_id' => $tipo->id]);
        Reporte::factory()->create([
            'tipo_incidente_id' => $tipo->id,
            'clasificacion_id' => $clasificacion->id,
        ]);

        $this->actingAs($user)
            ->from(route('catalogos.tipos-incidente.index'))
            ->delete(route('catalogos.tipos-incidente.destroy', $tipo))
            ->assertRedirect();

        $this->assertDatabaseHas('tipos_incidente', ['id' => $tipo->id]);
    }
}
