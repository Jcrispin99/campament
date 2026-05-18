<?php

namespace Tests\Feature;

use App\Models\AnalisisCausa;
use App\Models\ClasificacionIncidente;
use App\Models\Comedor;
use App\Models\Servicio;
use App\Models\TipoIncidente;
use Database\Seeders\AnalisisCausaSeeder;
use Database\Seeders\ClasificacionIncidenteSeeder;
use Database\Seeders\ComedorSeeder;
use Database\Seeders\ServicioSeeder;
use Database\Seeders\TipoIncidenteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogosSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_populate_expected_counts(): void
    {
        $this->seed([
            ComedorSeeder::class,
            ServicioSeeder::class,
            TipoIncidenteSeeder::class,
            ClasificacionIncidenteSeeder::class,
            AnalisisCausaSeeder::class,
        ]);

        $this->assertSame(8, Comedor::count());
        $this->assertSame(16, Servicio::count());
        $this->assertSame(3, TipoIncidente::count());
        $this->assertSame(41, ClasificacionIncidente::count());
        $this->assertSame(4, AnalisisCausa::count());
    }

    public function test_clasificaciones_are_grouped_by_tipo_incidente(): void
    {
        $this->seed([TipoIncidenteSeeder::class, ClasificacionIncidenteSeeder::class]);

        $inocuidad = TipoIncidente::where('nombre', 'Inocuidad')->firstOrFail();
        $servicio = TipoIncidente::where('nombre', 'Servicio')->firstOrFail();
        $seguridad = TipoIncidente::where('nombre', 'Seguridad')->firstOrFail();

        $this->assertSame(19, $inocuidad->clasificaciones()->count());
        $this->assertSame(13, $servicio->clasificaciones()->count());
        $this->assertSame(9, $seguridad->clasificaciones()->count());
    }

    public function test_seeders_are_idempotent(): void
    {
        $this->seed([ComedorSeeder::class, ComedorSeeder::class]);

        $this->assertSame(8, Comedor::count());
    }
}
