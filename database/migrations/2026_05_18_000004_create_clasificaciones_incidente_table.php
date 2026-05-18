<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificaciones_incidente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_incidente_id')
                ->constrained('tipos_incidente')
                ->cascadeOnDelete();
            $table->string('nombre', 200);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tipo_incidente_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clasificaciones_incidente');
    }
};
