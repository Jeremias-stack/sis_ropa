<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteApiController extends Controller
{
    /**
     * GET /api/clientes
     *
     * Listar clientes.
     */
    public function index(Request $request)
    {
        $query = Cliente::query();


        /*
         * Búsqueda opcional por:
         * nombre, NIT, correo o teléfono.
         */
        if ($request->filled('buscar')) {

            $buscar = $request->buscar;


            $query->where(function ($q) use ($buscar) {

                $q->where(
                    'nombre',
                    'like',
                    "%{$buscar}%"
                )
                ->orWhere(
                    'nit',
                    'like',
                    "%{$buscar}%"
                )
                ->orWhere(
                    'correo',
                    'like',
                    "%{$buscar}%"
                )
                ->orWhere(
                    'telefono',
                    'like',
                    "%{$buscar}%"
                );

            });

        }


        $clientes = $query
            ->orderBy('nombre')
            ->paginate(15);


        return response()->json([
            'ok' => true,

            'message' =>
                'Clientes obtenidos correctamente.',

            'data' =>
                $clientes->items(),

            'meta' => [
                'pagina_actual' =>
                    $clientes->currentPage(),

                'ultima_pagina' =>
                    $clientes->lastPage(),

                'por_pagina' =>
                    $clientes->perPage(),

                'total' =>
                    $clientes->total(),
            ],
        ], 200);
    }


    /**
     * GET /api/clientes/{cliente}
     *
     * Mostrar un cliente específico.
     */
    public function show(Cliente $cliente)
    {
        return response()->json([
            'ok' => true,

            'message' =>
                'Cliente obtenido correctamente.',

            'data' => [
                'id' =>
                    $cliente->id,

                'nombre' =>
                    $cliente->nombre,

                'nit' =>
                    $cliente->nit,

                'correo' =>
                    $cliente->correo,

                'telefono' =>
                    $cliente->telefono,

                'direccion' =>
                    $cliente->direccion,

                'created_at' =>
                    $cliente->created_at,

                'updated_at' =>
                    $cliente->updated_at,
            ],
        ], 200);
    }
}