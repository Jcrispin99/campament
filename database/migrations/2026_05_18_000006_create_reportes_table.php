<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedSmallInteger('semana');
            $table->text('detalle_observacion');
            $table->string('criticidad', 10);
            $table->boolean('se_corrigio')->default(false);
            $table->text('accion_inmediata');
            $table->boolean('requiere_plan_accion')->default(false);
            $table->text('recomendacion_salus');

            $table->foreignId('comedor_id')
                ->constrained('comedores')
                ->restrictOnDelete();
            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->restrictOnDelete();
            $table->foreignId('tipo_incidente_id')
                ->constrained('tipos_incidente')
                ->restrictOnDelete();
            $table->foreignId('clasificacion_id')
                ->constrained('clasificaciones_incidente')
                ->restrictOnDelete();
            $table->foreignId('analisis_causa_id')
                ->constrained('analisis_causas')
                ->restrictOnDelete();
            $table->foreignId('reportado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
