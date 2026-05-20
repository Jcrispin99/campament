<?php

namespace Database\Seeders;

use App\Models\Origen;
use Illuminate\Database\Seeder;

class OrigenSeeder extends Seeder
{
    public function run(): void
    {
        $origenes = [
            'Arequipa',
            'Cusco',
            'Lima',
        ];

        foreach ($origenes as $nombre) {
            Origen::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
