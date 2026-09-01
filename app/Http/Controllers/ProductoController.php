<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        $productos = $query
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:50', 'unique:productos,codigo'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'talla' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:40'],
            'genero' => [
                'nullable',
                Rule::in(['hombre', 'mujer', 'unisex', 'nino', 'nina'])
            ],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'activo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('imagen')) {
            $datos['imagen_url'] = $request
                ->file('imagen')
                ->store('productos', 'public');
        }

        $datos['activo'] = $request->boolean('activo');

        unset($datos['imagen']);

        Producto::create($datos);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load('categoria');

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')->ignore($producto->id),
            ],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'talla' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:40'],
            'genero' => [
                'nullable',
                Rule::in(['hombre', 'mujer', 'unisex', 'nino', 'nina'])
            ],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'activo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('imagen')) {

            if (
                $producto->imagen_url &&
                Storage::disk('public')->exists($producto->imagen_url)
            ) {
                Storage::disk('public')->delete($producto->imagen_url);
            }

            $datos['imagen_url'] = $request
                ->file('imagen')
                ->store('productos', 'public');
        }

        $datos['activo'] = $request->boolean('activo');

        unset($datos['imagen']);

        $producto->update($datos);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->detalleVentas()->exists()) {
            $producto->update([
                'activo' => false
            ]);

            return redirect()
                ->route('productos.index')
                ->with(
                    'success',
                    'El producto tiene ventas registradas, por lo que fue desactivado.'
                );
        }

        if (
            $producto->imagen_url &&
            Storage::disk('public')->exists($producto->imagen_url)
        ) {
            Storage::disk('public')->delete($producto->imagen_url);
        }

        $producto->delete();

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}