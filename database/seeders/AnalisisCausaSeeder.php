<?php

namespace Database\Seeders;

use App\Models\AnalisisCausa;
use Illuminate\Database\Seeder;

class AnalisisCausaSeeder extends Seeder
{
    public function run(): void
    {
        $causas = [
            'Falta de capacitación / concientización del personal',
            'Falta de personal',
            'Supervisión ineficiente',
            'Falta de materiales',
        ];

        foreach ($causas as $nombre) {
            AnalisisCausa::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
