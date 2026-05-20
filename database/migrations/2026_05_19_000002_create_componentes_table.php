<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('componentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200)->unique();
            $table->decimal('gramaje_sugerido', 8, 2);
            $table->string('unidad', 20)->default('GRAMOS');
            $table->string('observacion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('componentes');
    }
};
