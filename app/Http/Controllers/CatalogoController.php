<?php
namespace App\Http\Controllers;

use App\Models\{Categoria, Marca, Talla, Color};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogoController extends Controller
{
    public function index()
    {
        return view('catalogos.index', [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'marcas' => Marca::orderBy('nombre')->get(),
            'tallas' => Talla::orderBy('nombre')->get(),
            'colores' => Color::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, string $tipo)
    {
        $modelos = ['categorias' => Categoria::class, 'marcas' => Marca::class, 'tallas' => Talla::class, 'colores' => Color::class];
        abort_unless(isset($modelos[$tipo]), 404);
        $modelo = $modelos[$tipo];
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:'.($tipo === 'tallas' ? 30 : ($tipo === 'colores' ? 50 : 100)), Rule::unique((new $modelo)->getTable(), 'nombre')],
        ]);
        $modelo::create(['nombre' => trim($datos['nombre'])]);
        return redirect()->route('catalogos.index')->with('success', 'Dato agregado correctamente. Ya puedes usarlo en tus productos.');
    }
}
