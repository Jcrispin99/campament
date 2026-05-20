<?php

namespace Database\Seeders;

use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            'CD NEWREST',
            'Z y P',
        ];

        foreach ($proveedores as $nombre) {
            Proveedor::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
