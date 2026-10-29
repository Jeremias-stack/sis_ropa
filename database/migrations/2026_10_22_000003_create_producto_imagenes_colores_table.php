<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_imagenes_colores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('color_id')->constrained('colores')->restrictOnDelete();
            $table->string('ruta');
            $table->timestamps();
            $table->unique(['producto_id', 'color_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_imagenes_colores');
    }
};
