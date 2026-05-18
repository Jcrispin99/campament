<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')
                ->constrained('reportes')
                ->cascadeOnDelete();
            $table->string('imagen', 255);
            $table->string('descripcion', 200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_evidencias');
    }
};
