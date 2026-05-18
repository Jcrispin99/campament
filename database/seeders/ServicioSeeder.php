<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            'Desayuno',
            'Almuerzo',
            'Cena',
            'Monoporción',
            'Bolsas frías',
            'EMA Desayuno',
            'Almacenes',
            'SSHH-Vestuarios',
            'Producción',
            'Áreas extras',
            'Cumplimiento de menú y servicios',
            'Bandeja de frutas',
            'Pedido / Evento',
            'Gramaje cárnicos',
            'Vajilla-Pañol',
            'Armado de transportados',
        ];

        foreach ($servicios as $nombre) {
            Servicio::firstOrCreate(['nombre' => $nombre], ['activo' => true]);
        }
    }
}
