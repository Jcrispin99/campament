<?php

namespace Database\Seeders;

use App\Enums\ConformidadMp;
use App\Models\MateriaPrima;
use App\Models\Origen;
use App\Models\Proveedor;
use App\Models\TipoProducto;
use App\Models\User;
use Illuminate\Database\Seeder;

class MateriaPrimaSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();
        $tipos = TipoProducto::pluck('id');
        $proveedores = Proveedor::pluck('id');
        $origenes = Origen::pluck('id');

        if ($tipos->isEmpty() || $proveedores->isEmpty() || $origenes->isEmpty()) {
            $this->command->warn('Catálogos vacíos. Ejecuta TipoProductoSeeder, ProveedorSeeder y OrigenSeeder primero.');

            return;
        }

        $observacionesNc = [
            'Empaque dañado en algunas unidades.',
            'Temperatura fuera de rango al recibir.',
            'Documentación incompleta del lote.',
            'Vehículo sin certificado vigente.',
            'Producto con olor atípico.',
        ];

        $accionesNc = [
            'Rechazo total del lote y devolución al proveedor.',
            'Aceptación parcial con cuarentena de unidades afectadas.',
            'Solicitud de documentación complementaria.',
            'Reposición programada para el día siguiente.',
        ];

        for ($i = 0; $i < 20; $i++) {
            $confMp = fake()->boolean(80) ? ConformidadMp::Conforme : ConformidadMp::NoConforme;
            $confDoc = fake()->boolean(85) ? ConformidadMp::Conforme : ConformidadMp::NoConforme;
            $confVeh = fake()->boolean(85) ? ConformidadMp::Conforme : ConformidadMp::NoConforme;

            $todoConforme = $confMp === ConformidadMp::Conforme
                && $confDoc === ConformidadMp::Conforme
                && $confVeh === ConformidadMp::Conforme;

            MateriaPrima::factory()->create([
                'tipo_producto_id' => $tipos->random(),
                'proveedor_id' => $proveedores->random(),
                'origen_id' => $origenes->random(),
                'conformidad_mp' => $confMp->value,
                'conformidad_documentacion' => $confDoc->value,
                'conformidad_vehiculo' => $confVeh->value,
                'causa_nc_observacion' => $todoConforme
                    ? 'Sin novedad.'
                    : fake()->randomElement($observacionesNc),
                'productos_afectados' => $todoConforme
                    ? 'Ninguno.'
                    : fake()->randomElement(['Lote completo', '3 cajas', '5 unidades', 'Solo el bloque trasero']),
                'accion_realizada' => $todoConforme
                    ? 'Recepción normal.'
                    : fake()->randomElement($accionesNc),
                'reportado_por_id' => $user->id,
            ]);
        }

        $this->command->info('20 recepciones de MP de muestra creadas.');
    }
}
