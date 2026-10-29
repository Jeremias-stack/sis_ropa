<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Talla;
use App\Models\Color;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            ['codigo' => 'CAM-001', 'nombre' => 'Camisa Oxford Manga Larga', 'categoria' => 'Camisas', 'precio' => 149.99, 'stock' => 20, 'talla' => 'M', 'color' => 'Celeste', 'genero' => 'hombre'],
            ['codigo' => 'CAM-002', 'nombre' => 'Blusa Casual Estampada', 'categoria' => 'Camisas', 'precio' => 129.50, 'stock' => 15, 'talla' => 'S', 'color' => 'Blanco', 'genero' => 'mujer'],
            ['codigo' => 'PAN-001', 'nombre' => 'Jean Slim Fit', 'categoria' => 'Pantalones', 'precio' => 219.00, 'stock' => 12, 'talla' => '32', 'color' => 'Azul', 'genero' => 'hombre'],
            ['codigo' => 'PAN-002', 'nombre' => 'Pantalón de Vestir', 'categoria' => 'Pantalones', 'precio' => 249.00, 'stock' => 4, 'talla' => '34', 'color' => 'Negro', 'genero' => 'hombre'],
            ['codigo' => 'VES-001', 'nombre' => 'Vestido Casual Floral', 'categoria' => 'Vestidos', 'precio' => 279.99, 'stock' => 8, 'talla' => 'M', 'color' => 'Rosado', 'genero' => 'mujer'],
            ['codigo' => 'CHA-001', 'nombre' => 'Chumpa Impermeable', 'categoria' => 'Chaquetas', 'precio' => 349.00, 'stock' => 3, 'talla' => 'L', 'color' => 'Negro', 'genero' => 'unisex'],
            ['codigo' => 'CAL-001', 'nombre' => 'Tenis Urbano', 'categoria' => 'Calzado', 'precio' => 399.00, 'stock' => 10, 'talla' => '9', 'color' => 'Blanco', 'genero' => 'unisex'],
            ['codigo' => 'ACC-001', 'nombre' => 'Cinturón de Cuero', 'categoria' => 'Accesorios', 'precio' => 89.00, 'stock' => 25, 'talla' => 'Único', 'color' => 'Café', 'genero' => 'unisex'],
            ['codigo' => 'ACC-002', 'nombre' => 'Gorra Bordada', 'categoria' => 'Accesorios', 'precio' => 65.00, 'stock' => 2, 'talla' => 'Único', 'color' => 'Gris', 'genero' => 'unisex'],
            // Productos extra para alcanzar el mínimo académico de 20 sin duplicar existentes.
            ['codigo' => 'CAM-003', 'nombre' => 'Playera Básica de Algodón', 'categoria' => 'Camisas', 'precio' => 79.00, 'stock' => 18, 'talla' => 'M', 'color' => 'Negro', 'genero' => 'unisex'],
            ['codigo' => 'CAM-004', 'nombre' => 'Camisa de Lino Casual', 'categoria' => 'Camisas', 'precio' => 179.00, 'stock' => 11, 'talla' => 'L', 'color' => 'Beige', 'genero' => 'hombre'],
            ['codigo' => 'CAM-005', 'nombre' => 'Blusa de Manga Corta', 'categoria' => 'Camisas', 'precio' => 109.00, 'stock' => 9, 'talla' => 'S', 'color' => 'Azul', 'genero' => 'mujer'],
            ['codigo' => 'PAN-003', 'nombre' => 'Pantalón Cargo Urbano', 'categoria' => 'Pantalones', 'precio' => 235.00, 'stock' => 14, 'talla' => '32', 'color' => 'Verde', 'genero' => 'hombre'],
            ['codigo' => 'PAN-004', 'nombre' => 'Jeans Corte Recto', 'categoria' => 'Pantalones', 'precio' => 199.00, 'stock' => 8, 'talla' => '30', 'color' => 'Azul', 'genero' => 'mujer'],
            ['codigo' => 'VES-002', 'nombre' => 'Vestido Midi Liso', 'categoria' => 'Vestidos', 'precio' => 289.00, 'stock' => 7, 'talla' => 'M', 'color' => 'Negro', 'genero' => 'mujer'],
            ['codigo' => 'VES-003', 'nombre' => 'Vestido de Verano', 'categoria' => 'Vestidos', 'precio' => 225.00, 'stock' => 10, 'talla' => 'S', 'color' => 'Blanco', 'genero' => 'mujer'],
            ['codigo' => 'CHA-002', 'nombre' => 'Chaqueta de Mezclilla', 'categoria' => 'Chaquetas', 'precio' => 315.00, 'stock' => 6, 'talla' => 'L', 'color' => 'Azul', 'genero' => 'unisex'],
            ['codigo' => 'CHA-003', 'nombre' => 'Sudadera con Capucha', 'categoria' => 'Chaquetas', 'precio' => 195.00, 'stock' => 13, 'talla' => 'M', 'color' => 'Gris', 'genero' => 'unisex'],
            ['codigo' => 'CAL-002', 'nombre' => 'Sandalias Casual', 'categoria' => 'Calzado', 'precio' => 169.00, 'stock' => 12, 'talla' => '8', 'color' => 'Negro', 'genero' => 'unisex'],
            ['codigo' => 'CAL-003', 'nombre' => 'Zapato Formal de Cuero', 'categoria' => 'Calzado', 'precio' => 499.00, 'stock' => 5, 'talla' => '9', 'color' => 'Café', 'genero' => 'hombre'],
            ['codigo' => 'ACC-003', 'nombre' => 'Bolso de Mano Clásico', 'categoria' => 'Accesorios', 'precio' => 149.00, 'stock' => 16, 'talla' => 'Único', 'color' => 'Negro', 'genero' => 'mujer'],

        ];

        foreach ($productos as $producto) {
            $categoria = Categoria::where('nombre', $producto['categoria'])->first();

            // Solo insertar demostraciones nuevas; no sobrescribir existencias de producción.
            $nuevo = Producto::firstOrCreate(
                ['codigo' => $producto['codigo']],
                [
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['nombre'].' - '.$producto['categoria'],
                    'precio' => $producto['precio'],
                    'stock' => $producto['stock'],
                    'categoria_id' => $categoria->id,
                    'talla' => $producto['talla'],
                    'color' => $producto['color'],
                    'genero' => $producto['genero'],
                    'imagen_url' => null,
                    'activo' => true,
                ]
            );
            // Compatibilidad del seeder antiguo con inventario por variantes.
            if (!$nuevo->variantes()->exists()) {
                $nuevo->variantes()->create([
                    'talla_id' => Talla::firstOrCreate(['nombre' => $producto['talla']])->id,
                    'color_id' => Color::firstOrCreate(['nombre' => $producto['color']])->id,
                    'stock' => $nuevo->stock,
                ]);
            }
        }
    }
}
