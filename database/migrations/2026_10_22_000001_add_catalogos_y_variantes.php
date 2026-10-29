<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->timestamps();
        });
        Schema::create('tallas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30)->unique();
            $table->timestamps();
        });
        Schema::create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->timestamps();
        });
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
        });
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('talla_id')->nullable()->constrained('tallas')->restrictOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('colores')->restrictOnDelete();
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();
            $table->index(['producto_id', 'talla_id', 'color_id'], 'variantes_busqueda_idx');
        });
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->foreignId('producto_variante_id')->nullable()->constrained('producto_variantes')->restrictOnDelete();
            // Historial legible incluso si cambian los nombres de los catálogos.
            $table->string('talla_vendida', 30)->nullable();
            $table->string('color_vendido', 50)->nullable();
        });

        $now = now();
        foreach (['XS','S','M','L','XL','XXL','XXXL','28','30','32','34','36','38','40','42'] as $nombre) {
            DB::table('tallas')->insert(['nombre' => $nombre, 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach (['Negro','Blanco','Rojo','Azul','Gris','Verde','Beige'] as $nombre) {
            DB::table('colores')->insert(['nombre' => $nombre, 'created_at' => $now, 'updated_at' => $now]);
        }
        // Migración no destructiva: cada artículo anterior conserva su stock en una variante.
        DB::table('productos')->orderBy('id')->chunkById(100, function ($productos) use ($now) {
            foreach ($productos as $producto) {
                $tallaId = null;
                $colorId = null;
                if (filled($producto->talla)) {
                    $tallaId = DB::table('tallas')->where('nombre', $producto->talla)->value('id');
                    $tallaId ??= DB::table('tallas')->insertGetId(['nombre' => $producto->talla, 'created_at' => $now, 'updated_at' => $now]);
                }
                if (filled($producto->color)) {
                    $colorId = DB::table('colores')->where('nombre', $producto->color)->value('id');
                    $colorId ??= DB::table('colores')->insertGetId(['nombre' => $producto->color, 'created_at' => $now, 'updated_at' => $now]);
                }
                DB::table('producto_variantes')->insert([
                    'producto_id' => $producto->id, 'talla_id' => $tallaId, 'color_id' => $colorId,
                    'stock' => $producto->stock, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producto_variante_id');
            $table->dropColumn(['talla_vendida','color_vendido']);
        });
        Schema::dropIfExists('producto_variantes');
        Schema::table('productos', fn (Blueprint $table) => $table->dropConstrainedForeignId('marca_id'));
        Schema::dropIfExists('colores');
        Schema::dropIfExists('tallas');
        Schema::dropIfExists('marcas');
    }
};
