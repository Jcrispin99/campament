<?php

namespace Database\Seeders;

use App\Models\TipoCorte;
use Illuminate\Database\Seeder;

class TipoCorteSeeder extends Seeder
{
    public function run(): void
    {
        $cortes = [
            'Filete',
            'Cubos',
            'Tiras',
            'Picado',
            'Molido',
            'Entero',
        ];

        foreach ($cortes as $nombre) {
            TipoCorte::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
