<?php

namespace Database\Seeders;

use App\Models\TipoIncidente;
use Illuminate\Database\Seeder;

class TipoIncidenteSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Inocuidad', 'Servicio', 'Seguridad'] as $nombre) {
            TipoIncidente::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
