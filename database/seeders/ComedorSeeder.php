<?php

namespace Database\Seeders;

use App\Models\Comedor;
use Illuminate\Database\Seeder;

class ComedorSeeder extends Seeder
{
    public function run(): void
    {
        $comedores = [
            'Bahía Fórmula 1',
            'Bahía Tatu Tacu',
            'Campamento 4',
            'Mijuna Wasi',
            'Planta Antapaccay',
            'Planta Tintaya',
            'Tambo Machay',
            'Truck Shop',
        ];

        foreach ($comedores as $nombre) {
            Comedor::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
