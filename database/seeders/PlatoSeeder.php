<?php

namespace Database\Seeders;

use App\Models\Plato;
use Illuminate\Database\Seeder;

class PlatoSeeder extends Seeder
{
    public function run(): void
    {
        $platos = [
            'Fast food',
            'Fondo',
            'Sándwich',
            'Sopa',
        ];

        foreach ($platos as $nombre) {
            Plato::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
