<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gramajes', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedSmallInteger('semana');
            $table->date('fecha_produccion');

            $table->foreignId('comedor_id')
                ->constrained('comedores')
                ->restrictOnDelete();
            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->restrictOnDelete();
            $table->foreignId('plato_id')
                ->constrained('platos')
                ->restrictOnDelete();
            $table->foreignId('componente_id')
                ->constrained('componentes')
                ->restrictOnDelete();
            $table->foreignId('tipo_corte_id')
                ->nullable()
                ->constrained('tipos_corte')
                ->nullOnDelete();

            $table->decimal('gramaje_esperado', 8, 2);
            $table->unsignedSmallInteger('cantidad_muestreada')->default(0);
            $table->decimal('peso_promedio', 10, 2)->default(0);
            $table->decimal('variacion_pct', 8, 2)->default(0);
            $table->string('estatus', 20);

            $table->foreignId('reportado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gramajes');
    }
};
