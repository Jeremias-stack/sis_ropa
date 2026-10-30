<?php
namespace Tests\Feature;

use App\Models\{Categoria, Color, Producto, Talla, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioVariantesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'nombre' => 'Administrador de prueba', 'correo' => 'admin-prueba@example.test',
            'password' => 'password_segura_123', 'rol' => 'administrador',
        ]);
    }

    public function test_productos_con_tallas_y_colores_se_guardan_una_sola_vez(): void
    {
        $categoria = Categoria::create(['nombre' => 'Playeras']);
        $m = Talla::where('nombre', 'M')->firstOrFail();
        $s = Talla::where('nombre', 'S')->firstOrFail();
        $negro = Color::where('nombre', 'Negro')->firstOrFail();
        $rojo = Color::where('nombre', 'Rojo')->firstOrFail();

        $this->actingAs($this->admin())->post(route('productos.store'), [
            'codigo' => 'PLY-001', 'nombre' => 'Playera Fly', 'precio' => '175',
            'categoria_id' => $categoria->id, 'activo' => 1,
            'variantes' => [
                ['talla_id' => $m->id, 'color_id' => $negro->id, 'stock' => 10],
                ['talla_id' => $s->id, 'color_id' => $rojo->id, 'stock' => 5],
            ],
        ])->assertRedirect(route('productos.index'));

        $producto = Producto::where('codigo', 'PLY-001')->firstOrFail();
        $this->assertSame(15, (int) $producto->stock);
        $this->assertSame(2, $producto->variantes()->count());

        $variante = $producto->variantes()->where('talla_id', $m->id)->firstOrFail();
        $this->actingAs($this->admin())->post(route('ventas.store'), [
            'metodo_pago' => 'efectivo', 'descuento' => 0,
            'items' => [[ 'producto_id' => $producto->id, 'variante_id' => $variante->id, 'cantidad' => 3 ]],
        ])->assertRedirect();

        $this->assertSame(7, (int)$variante->fresh()->stock);
        $this->assertSame(12, (int)$producto->fresh()->stock);
    }

    public function test_registro_publico_no_crea_cajeros(): void
    {
        $this->post(route('register.store'), [
            'nombre'=>'Persona de prueba', 'correo'=>'cliente-prueba@example.test',
            'password'=>'contrasena_123', 'password_confirmation'=>'contrasena_123',
        ])->assertRedirect(route('tienda.index'));
        $this->assertDatabaseHas('users', ['correo'=>'cliente-prueba@example.test', 'rol'=>'cliente']);
        $this->get(route('dashboard'))->assertForbidden();
    }
}
