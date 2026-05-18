<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ComedorSeeder::class,
            ServicioSeeder::class,
            TipoIncidenteSeeder::class,
            ClasificacionIncidenteSeeder::class,
            AnalisisCausaSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ]);
    }
}
