<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Mostrar listado de clientes.
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Buscador por nombre, NIT, correo o teléfono.
        if ($request->filled('buscar')) {

            $termino = $request->buscar;

            $query->where(function ($q) use ($termino) {

                $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('nit', 'like', "%{$termino}%")
                    ->orWhere('correo', 'like', "%{$termino}%")
                    ->orWhere('telefono', 'like', "%{$termino}%");

            });
        }

        $clientes = $query
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }


    /**
     * Mostrar formulario para crear cliente.
     */
    public function create()
    {
        return view('clientes.create');
    }


    /**
     * Guardar nuevo cliente.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'nit' => [
                'nullable',
                'string',
                'max:20',
            ],

            'correo' => [
                'nullable',
                'email',
                'max:150',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20',
            ],

            'direccion' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);


        // Si no se proporciona NIT, se utiliza Consumidor Final.
        $datos['nit'] = trim($datos['nit'] ?? '') ?: 'CF';


        Cliente::create($datos);


        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Cliente registrado correctamente.'
            );
    }


    /**
     * Mostrar detalle de un cliente.
     */
    public function show(Cliente $cliente)
    {
        return view(
            'clientes.show',
            compact('cliente')
        );
    }


    /**
     * Mostrar formulario de edición.
     */
    public function edit(Cliente $cliente)
    {
        return view(
            'clientes.edit',
            compact('cliente')
        );
    }


    /**
     * Actualizar cliente.
     */
    public function update(
        Request $request,
        Cliente $cliente
    ) {

        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'nit' => [
                'nullable',
                'string',
                'max:20',
            ],

            'correo' => [
                'nullable',
                'email',
                'max:150',
            ],

            'telefono' => [
                'nullable',
                'string',
                'max:20',
            ],

            'direccion' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);


        $datos['nit'] = trim($datos['nit'] ?? '') ?: 'CF';


        $cliente->update($datos);


        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Cliente actualizado correctamente.'
            );
    }


    /**
     * Eliminar cliente.
     */
    public function destroy(Cliente $cliente)
    {
        // Si el cliente ya tiene ventas, no permitimos eliminarlo
        // para conservar el historial del POS.
        if ($cliente->ventas()->exists()) {

            return redirect()
                ->route('clientes.index')
                ->with(
                    'error',
                    'No se puede eliminar este cliente porque tiene ventas registradas.'
                );
        }


        $cliente->delete();


        return redirect()
            ->route('clientes.index')
            ->with(
                'success',
                'Cliente eliminado correctamente.'
            );
    }
}