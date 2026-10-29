<?php
namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $proveedores = Proveedor::query()->when($request->filled('buscar'), function ($q) use ($request) {
            $texto = $request->input('buscar');
            $q->where(fn ($x) => $x->where('nombre', 'like', "%{$texto}%")->orWhere('contacto', 'like', "%{$texto}%"));
        })->orderBy('nombre')->paginate(12)->withQueryString();
        return view('proveedores.index', compact('proveedores'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'contacto' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);
        Proveedor::create($datos);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado.');
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'contacto' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);
        $proveedor->update($datos);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado.');
    }
}
