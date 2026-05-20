<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gramaje_medidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gramaje_id')
                ->constrained('gramajes')
                ->cascadeOnDelete();
            $table->decimal('peso', 10, 2);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['gramaje_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gramaje_medidas');
    }
};
