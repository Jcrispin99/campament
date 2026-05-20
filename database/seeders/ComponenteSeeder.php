<?php

namespace Database\Seeders;

use App\Enums\UnidadGramaje;
use App\Models\Componente;
use Illuminate\Database\Seeder;

class ComponenteSeeder extends Seeder
{
    public function run(): void
    {
        $componentes = [
            ['nombre' => 'Hamburguesas Cárnico', 'gramaje_sugerido' => 150, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Enchiladas Cárnico', 'gramaje_sugerido' => 120, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Sándwiches mixto Embutido y queso', 'gramaje_sugerido' => 50, 'unidad' => UnidadGramaje::Gramos, 'observacion' => 'Queso y jamón (50 g cada uno)'],
            ['nombre' => 'Sándwiches Lomo', 'gramaje_sugerido' => 130, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Sándwiches Pollo a la plancha Cárnico', 'gramaje_sugerido' => 130, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Sándwiches Pollo deshilachado Cárnico', 'gramaje_sugerido' => 130, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Crepes Dulce', 'gramaje_sugerido' => 2, 'unidad' => UnidadGramaje::Unidades, 'observacion' => null],
            ['nombre' => 'Tacos Cárnico', 'gramaje_sugerido' => 120, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Enrollados salados Cárnico', 'gramaje_sugerido' => 120, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Ensalada de frutas con yogurt y cereales', 'gramaje_sugerido' => 250, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Cárnicos en picadillo', 'gramaje_sugerido' => 150, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Cárnicos (fondos o sopas) — Con Hueso', 'gramaje_sugerido' => 250, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Cárnicos (fondos o sopas) — Sin Hueso', 'gramaje_sugerido' => 200, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
            ['nombre' => 'Pan', 'gramaje_sugerido' => 80, 'unidad' => UnidadGramaje::Gramos, 'observacion' => null],
        ];

        foreach ($componentes as $componente) {
            Componente::firstOrCreate(
                ['nombre' => $componente['nombre']],
                [
                    'gramaje_sugerido' => $componente['gramaje_sugerido'],
                    'unidad' => $componente['unidad']->value,
                    'observacion' => $componente['observacion'],
                    'activo' => true,
                ]
            );
        }
    }
}
