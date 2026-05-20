<?php

namespace Tests\Feature\Catalogos;

use App\Enums\UnidadGramaje;
use App\Models\Componente;
use App\Models\Gramaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_componente(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('catalogos.componentes.store'), [
                'nombre' => 'Pollo a la plancha',
                'gramaje_sugerido' => 150,
                'unidad' => UnidadGramaje::Gramos->value,
                'observacion' => 'Sin sal',
                'activo' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('componentes', [
            'nombre' => 'Pollo a la plancha',
            'unidad' => UnidadGramaje::Gramos->value,
        ]);
    }

    public function test_unidad_must_be_valid_enum(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('catalogos.componentes.index'))
            ->post(route('catalogos.componentes.store'), [
                'nombre' => 'X',
                'gramaje_sugerido' => 100,
                'unidad' => 'INVALIDO',
                'activo' => true,
            ])
            ->assertSessionHasErrors('unidad');
    }

    public function test_gramaje_sugerido_must_be_positive(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('catalogos.componentes.index'))
            ->post(route('catalogos.componentes.store'), [
                'nombre' => 'X',
                'gramaje_sugerido' => -10,
                'unidad' => UnidadGramaje::Gramos->value,
                'activo' => true,
            ])
            ->assertSessionHasErrors('gramaje_sugerido');
    }

    public function test_can_update_componente(): void
    {
        $user = User::factory()->create();
        $componente = Componente::factory()->create(['nombre' => 'Original']);

        $this->actingAs($user)
            ->put(route('catalogos.componentes.update', $componente), [
                'nombre' => 'Modificado',
                'gramaje_sugerido' => 200,
                'unidad' => UnidadGramaje::Unidades->value,
                'observacion' => null,
                'activo' => false,
            ])
            ->assertRedirect();

        $componente->refresh();
        $this->assertSame('Modificado', $componente->nombre);
        $this->assertSame(UnidadGramaje::Unidades, $componente->unidad);
        $this->assertFalse($componente->activo);
    }

    public function test_cannot_delete_componente_in_use(): void
    {
        $user = User::factory()->create();
        $componente = Componente::factory()->create();
        Gramaje::factory()->create(['componente_id' => $componente->id]);

        $this->actingAs($user)
            ->from(route('catalogos.componentes.index'))
            ->delete(route('catalogos.componentes.destroy', $componente))
            ->assertRedirect();

        $this->assertDatabaseHas('componentes', ['id' => $componente->id]);
    }
}
