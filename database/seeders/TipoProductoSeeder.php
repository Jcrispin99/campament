<?php

namespace Database\Seeders;

use App\Models\TipoProducto;
use Illuminate\Database\Seeder;

class TipoProductoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Abarrotes / Secos',
            'Congelados',
            'Frutas y verduras',
            'Refrigerados',
        ];

        foreach ($tipos as $nombre) {
            TipoProducto::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
