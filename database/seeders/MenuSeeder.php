<?php

namespace Database\Seeders;

use App\Models\Componente;
use App\Models\Menu;
use App\Models\Servicio;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();
        $servicios = Servicio::pluck('id');
        $componentes = Componente::pluck('id');

        if ($servicios->isEmpty() || $componentes->isEmpty()) {
            $this->command->warn('Catálogos vacíos. Ejecuta ServicioSeeder y ComponenteSeeder primero.');

            return;
        }

        $motivosEjemplo = [
            'Falta de insumo en almacén.',
            'Solicitud del cliente por preferencia.',
            'Producto no apto detectado en recepción.',
            'Ajuste por alta demanda del servicio anterior.',
            'Cambio por estacionalidad.',
        ];

        for ($i = 0; $i < 15; $i++) {
            $fecha = CarbonImmutable::now()->subDays(fake()->numberBetween(0, 20));
            $solicitud = $fecha->subDays(fake()->numberBetween(1, 7));
            $cambio = $solicitud->addDays(fake()->numberBetween(0, 5));
            $dias = (int) $solicitud->diffInDays($cambio);

            Menu::factory()->create([
                'fecha' => $fecha,
                'semana' => (int) $fecha->format('W'),
                'fecha_solicitud' => $solicitud,
                'fecha_cambio' => $cambio,
                'servicio_id' => $servicios->random(),
                'componente_id' => $componentes->random(),
                'programado' => fake()->randomElement(['Pollo a la plancha', 'Lomo saltado', 'Sopa de pollo', 'Arroz con pollo', 'Estofado']),
                'propuesta' => fake()->randomElement(['Filete de pescado', 'Pollo broaster', 'Crema de zapallo', 'Arroz chaufa', 'Guiso de res']),
                'motivo' => fake()->randomElement($motivosEjemplo),
                'comentario' => fake()->boolean(60) ? fake()->sentence() : null,
                'dias_prevision' => $dias,
                'conformidad' => $dias >= 3 ? 'Conforme' : 'Inconforme',
                'analisis' => fake()->paragraph(),
                'reportado_por_id' => $user->id,
            ]);
        }

        $this->command->info('15 cambios de menú de muestra creados.');
    }
}
