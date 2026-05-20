<?php

namespace Tests\Feature\Catalogos;

use App\Models\MateriaPrima;
use App\Models\Origen;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use App\Models\User;
use Database\Seeders\OrigenSeeder;
use Database\Seeders\ProveedorSeeder;
use Database\Seeders\TipoProductoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosMpTest extends TestCase
{
    use RefreshDatabase;

    public function test_tipo_producto_seeder_creates_four_initial_values(): void
    {
        $this->seed(TipoProductoSeeder::class);

        $this->assertSame(4, TipoProducto::count());
        foreach (['Abarrotes / Secos', 'Congelados', 'Frutas y verduras', 'Refrigerados'] as $nombre) {
            $this->assertDatabaseHas('tipos_producto', ['nombre' => $nombre]);
        }
    }

    public function test_proveedor_seeder_creates_two_initial_values(): void
    {
        $this->seed(ProveedorSeeder::class);

        $this->assertSame(2, Proveedor::count());
        foreach (['CD NEWREST', 'Z y P'] as $nombre) {
            $this->assertDatabaseHas('proveedores', ['nombre' => $nombre]);
        }
    }

    public function test_origen_seeder_creates_three_initial_values(): void
    {
        $this->seed(OrigenSeeder::class);

        $this->assertSame(3, Origen::count());
        foreach (['Arequipa', 'Cusco', 'Lima'] as $nombre) {
            $this->assertDatabaseHas('origenes', ['nombre' => $nombre]);
        }
    }

    public function test_can_crud_tipo_producto(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('catalogos.tipos-producto.store'), [
                'nombre' => 'Lácteos',
                'activo' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tipos_producto', ['nombre' => 'Lácteos']);
    }

    public function test_can_crud_proveedor(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create(['nombre' => 'Original']);

        $this->actingAs($user)
            ->put(route('catalogos.proveedores.update', $proveedor), [
                'nombre' => 'Modificado',
                'activo' => false,
            ])
            ->assertRedirect();

        $this->assertSame('Modificado', $proveedor->fresh()->nombre);
        $this->assertFalse($proveedor->fresh()->activo);
    }

    public function test_can_crud_origen(): void
    {
        $user = User::factory()->create();
        $origen = Origen::factory()->create();

        $this->actingAs($user)
            ->delete(route('catalogos.origenes.destroy', $origen))
            ->assertRedirect();

        $this->assertDatabaseMissing('origenes', ['id' => $origen->id]);
    }

    public function test_cannot_delete_tipo_producto_in_use(): void
    {
        $user = User::factory()->create();
        $tipo = TipoProducto::factory()->create();
        MateriaPrima::factory()->create(['tipo_producto_id' => $tipo->id]);

        $this->actingAs($user)
            ->from(route('catalogos.tipos-producto.index'))
            ->delete(route('catalogos.tipos-producto.destroy', $tipo))
            ->assertRedirect();

        $this->assertDatabaseHas('tipos_producto', ['id' => $tipo->id]);
    }

    public function test_cannot_delete_proveedor_in_use(): void
    {
        $user = User::factory()->create();
        $proveedor = Proveedor::factory()->create();
        MateriaPrima::factory()->create(['proveedor_id' => $proveedor->id]);

        $this->actingAs($user)
            ->from(route('catalogos.proveedores.index'))
            ->delete(route('catalogos.proveedores.destroy', $proveedor))
            ->assertRedirect();

        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id]);
    }
}
