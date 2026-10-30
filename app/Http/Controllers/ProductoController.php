<?php

namespace App\Http\Controllers;

use App\Models\{Categoria, Producto, ProductoVariante, Marca, Talla, Color};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ImagenProductoStorage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca', 'variantes.talla', 'variantes.color']);
        if ($request->filled('buscar')) $query->buscar($request->buscar);
        $productos = $query->orderBy('nombre')->paginate(10)->withQueryString();
        return view('productos.index', compact('productos'));
    }

    private function catalogos(): array
    {
        return [
            'categorias' => Categoria::orderBy('nombre')->get(),
            'marcas' => Marca::orderBy('nombre')->get(),
            'tallas' => Talla::orderBy('nombre')->get(),
            'colores' => Color::orderBy('nombre')->get(),
        ];
    }

    public function create() { return view('productos.create', $this->catalogos()); }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:50', Rule::unique('productos', 'codigo')->ignore($producto?->id)],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'marca_id' => ['nullable', 'exists:marcas,id'],
            'genero' => ['nullable', Rule::in(['hombre','mujer','unisex','nino','nina'])],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'imagenes_colores' => ['nullable', 'array'],
            'imagenes_colores.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'activo' => ['nullable', 'boolean'],
            'variantes' => ['required', 'array', 'min:1', 'max:150'],
            'variantes.*.id' => ['nullable', 'integer'],
            'variantes.*.talla_id' => ['nullable', 'exists:tallas,id'],
            'variantes.*.color_id' => ['nullable', 'exists:colores,id'],
            'variantes.*.stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $vistos = [];
        $identificadores = [];
        foreach ($datos['variantes'] as $variante) {
            $clave = ($variante['talla_id'] ?? 'sin-talla').'|'.($variante['color_id'] ?? 'sin-color');
            if (isset($vistos[$clave])) throw ValidationException::withMessages(['variantes' => 'No puedes repetir la misma combinación de talla y color.']);
            $vistos[$clave] = true;
            if (!empty($variante['id'])) {
                if (!$producto || !ProductoVariante::where('producto_id', $producto->id)->whereKey($variante['id'])->exists() || isset($identificadores[$variante['id']])) {
                    throw ValidationException::withMessages(['variantes' => 'Se recibió una variante no válida.']);
                }
                $identificadores[$variante['id']] = true;
                $anterior = $producto->variantes()->find($variante['id']);
                if ((string)($anterior->talla_id ?? '') !== (string)($variante['talla_id'] ?? '') || (string)($anterior->color_id ?? '') !== (string)($variante['color_id'] ?? '')) {
                    throw ValidationException::withMessages(['variantes' => 'Para cambiar una talla o color, agrega una nueva combinación en vez de modificar una existente.']);
                }
            }
        }
        if ($producto && $producto->variantes()->count() !== count($identificadores)) {
            throw ValidationException::withMessages(['variantes' => 'Mantén las variantes existentes. Puedes colocar su cantidad en cero.']);
        }
        return $datos;
    }

    private function guardar(Request $request, ?Producto $producto = null)
    {
        $datos = $this->validar($request, $producto);
        $variantes = $datos['variantes'];
        $fotosPorColor = $request->file('imagenes_colores', []);
        $coloresDisponibles = collect($variantes)->pluck('color_id')->filter()
            ->map(fn ($id) => (int) $id)->unique()->all();
        foreach ($fotosPorColor as $colorId => $foto) {
            if ($foto && (!ctype_digit((string) $colorId) || !in_array((int) $colorId, $coloresDisponibles, true))) {
                throw ValidationException::withMessages([
                    'imagenes_colores' => 'Selecciona el color en las variantes antes de cargar su fotografía.',
                ]);
            }
        }
        unset($datos['variantes'], $datos['imagen'], $datos['imagenes_colores']);
        $datos['activo'] = $request->boolean('activo');
        $datos['stock'] = collect($variantes)->sum('stock');
        // Los campos anteriores siguen existiendo para compatibilidad, pero las variantes mandan.
        $datos['talla'] = null;
        $datos['color'] = null;
        $almacen = app(ImagenProductoStorage::class);
        $anteriorImagen = $producto?->imagen_url;
        $fotosNuevas = [];
        $imagenNueva = null;
        $guardado = null;
        try {
            // Preparar las fotografías antes de modificar la base. Si falla Cloudinary, no se
            // cambia el producto ni el stock; si falla SQL, se limpian las cargas nuevas.
            if ($request->hasFile('imagen')) {
                $imagenNueva = $almacen->subir($request->file('imagen'), 'productos');
                $datos['imagen_url'] = $imagenNueva;
            }
            foreach ($fotosPorColor as $colorId => $foto) {
                if ($foto) $fotosNuevas[(int) $colorId] = $almacen->subir($foto, 'productos/colores');
            }

            $fotosAntiguas = [];
            $guardado = DB::transaction(function () use ($datos, $variantes, $producto, $fotosNuevas, &$fotosAntiguas) {
                if ($producto) {
                    $producto = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
                    $producto->update($datos);
                } else {
                    $producto = Producto::create($datos);
                }
                foreach ($variantes as $variante) {
                    $campos = [
                        'talla_id' => $variante['talla_id'] ?? null,
                        'color_id' => $variante['color_id'] ?? null,
                        'stock' => $variante['stock'],
                    ];
                    if (!empty($variante['id'])) {
                        $producto->variantes()->whereKey($variante['id'])->update($campos);
                    } else {
                        $producto->variantes()->create($campos);
                    }
                }
                foreach ($fotosNuevas as $colorId => $rutaNueva) {
                    $anterior = $producto->imagenesColores()->where('color_id', $colorId)->first();
                    if ($anterior && $anterior->ruta !== $rutaNueva) $fotosAntiguas[] = $anterior->ruta;
                    $producto->imagenesColores()->updateOrCreate(
                        ['color_id' => $colorId], ['ruta' => $rutaNueva]
                    );
                }
                return $producto;
            });
        } catch (\Throwable $error) {
            if ($imagenNueva) $almacen->eliminar($imagenNueva);
            foreach ($fotosNuevas as $ruta) $almacen->eliminar($ruta);
            throw $error;
        }

        // La eliminación anterior ocurre SOLO después de guardar exitosamente.
        if ($anteriorImagen && $imagenNueva && $anteriorImagen !== $imagenNueva) {
            $almacen->eliminar($anteriorImagen);
        }
        foreach ($fotosAntiguas as $ruta) $almacen->eliminar($ruta);
        return $guardado;
    }

    public function store(Request $request)
    {
        $this->guardar($request);
        return redirect()->route('productos.index')->with('success', 'Producto y variantes registrados correctamente.');
    }

    public function edit(Producto $producto)
    {
        $producto->load(['variantes', 'imagenesColores.color']);
        return view('productos.edit', ['producto' => $producto] + $this->catalogos());
    }

    public function update(Request $request, Producto $producto)
    {
        $this->guardar($request, $producto);
        return redirect()->route('productos.index')->with('success', 'Producto y existencias actualizados.');
    }

    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'marca', 'variantes.talla', 'variantes.color', 'imagenesColores.color']);
        return view('productos.show', compact('producto'));
    }

    public function destroy(Producto $producto)
    {
        if ($producto->detalleVentas()->exists()) {
            $producto->update(['activo' => false]);
            return redirect()->route('productos.index')->with('success', 'Producto con ventas: se desactivó para conservar el historial.');
        }
        $fotos = $producto->imagenesColores()->pluck('ruta')->all();
        DB::transaction(function () use ($producto) { $producto->delete(); });
        if ($producto->imagen_url) app(ImagenProductoStorage::class)->eliminar($producto->imagen_url);
        foreach ($fotos as $foto) app(ImagenProductoStorage::class)->eliminar($foto);
        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }
}
