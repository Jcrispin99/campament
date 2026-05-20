<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias_primas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedSmallInteger('semana');

            $table->foreignId('tipo_producto_id')
                ->constrained('tipos_producto')
                ->restrictOnDelete();
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->restrictOnDelete();
            $table->foreignId('origen_id')
                ->constrained('origenes')
                ->restrictOnDelete();

            $table->string('conformidad_mp', 20);
            $table->string('conformidad_documentacion', 20);
            $table->string('conformidad_vehiculo', 20);

            $table->text('causa_nc_observacion');
            $table->text('productos_afectados');
            $table->text('accion_realizada');

            $table->foreignId('reportado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias_primas');
    }
};
