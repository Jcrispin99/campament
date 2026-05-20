<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->unsignedSmallInteger('semana');
            $table->date('fecha_solicitud');
            $table->date('fecha_cambio');

            $table->foreignId('servicio_id')
                ->constrained('servicios')
                ->restrictOnDelete();
            $table->foreignId('componente_id')
                ->constrained('componentes')
                ->restrictOnDelete();

            $table->string('programado', 200);
            $table->string('propuesta', 200);
            $table->text('motivo');
            $table->text('comentario')->nullable();
            $table->unsignedSmallInteger('dias_prevision')->default(0);
            $table->string('conformidad', 50);
            $table->text('analisis');

            $table->foreignId('reportado_por_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
