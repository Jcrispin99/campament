<?php

namespace Tests\Feature;

use App\Enums\UnidadGramaje;
use App\Models\Componente;
use App\Models\Plato;
use App\Models\TipoCorte;
use Database\Seeders\ComponenteSeeder;
use Database\Seeders\PlatoSeeder;
use Database\Seeders\TipoCorteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosGramajeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_plato_seeder_loads_four_plates(): void
    {
        $this->seed(PlatoSeeder::class);

        $this->assertSame(4, Plato::count());

        foreach (['Fast food', 'Fondo', 'Sándwich', 'Sopa'] as $nombre) {
            $this->assertDatabaseHas('platos', ['nombre' => $nombre, 'activo' => true]);
        }
    }

    public function test_componente_seeder_loads_fourteen_with_correct_unidades(): void
    {
        $this->seed(ComponenteSeeder::class);

        $this->assertSame(14, Componente::count());

        $crepes = Componente::where('nombre', 'Crepes Dulce')->firstOrFail();
        $this->assertSame(UnidadGramaje::Unidades, $crepes->unidad);
        $this->assertSame('2.00', (string) $crepes->gramaje_sugerido);

        $hamburguesas = Componente::where('nombre', 'Hamburguesas Cárnico')->firstOrFail();
        $this->assertSame(UnidadGramaje::Gramos, $hamburguesas->unidad);
        $this->assertSame('150.00', (string) $hamburguesas->gramaje_sugerido);

        $sandwich = Componente::where('nombre', 'Sándwiches mixto Embutido y queso')->firstOrFail();
        $this->assertNotNull($sandwich->observacion);
    }

    public function test_tipo_corte_seeder_loads_six_initial_values(): void
    {
        $this->seed(TipoCorteSeeder::class);

        $this->assertSame(6, TipoCorte::count());
        foreach (['Filete', 'Cubos', 'Tiras', 'Picado', 'Molido', 'Entero'] as $nombre) {
            $this->assertDatabaseHas('tipos_corte', ['nombre' => $nombre, 'activo' => true]);
        }
    }
}
