<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductoApiController extends Controller
{
    /**
     * GET /api/productos
     *
     * Listar productos.
     */
    public function index(Request $request)
    {
        $query = Producto::with('categoria');


        /*
         * Búsqueda opcional.
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
                    'codigo',
                    'like',
                    "%{$buscar}%"
                )
                ->orWhere(
                    'color',
                    'like',
                    "%{$buscar}%"
                )
                ->orWhere(
                    'talla',
                    'like',
                    "%{$buscar}%"
                );

            });
        }


        $productos = $query
            ->orderBy('nombre')
            ->paginate(15);


        return response()->json([
            'ok' => true,

            'message' =>
                'Productos obtenidos correctamente.',

            'data' =>
                $productos->items(),

            'meta' => [
                'pagina_actual' =>
                    $productos->currentPage(),

                'ultima_pagina' =>
                    $productos->lastPage(),

                'por_pagina' =>
                    $productos->perPage(),

                'total' =>
                    $productos->total(),
            ],
        ], 200);
    }


    /**
     * GET /api/productos/{producto}
     *
     * Mostrar un producto.
     */
    public function show(Producto $producto)
    {
        $producto->load('categoria');


        return response()->json([
            'ok' => true,

            'message' =>
                'Producto obtenido correctamente.',

            'data' =>
                $producto,
        ], 200);
    }


    /**
     * POST /api/productos
     *
     * Crear producto.
     * Solo Administrador.
     */
    public function store(Request $request)
    {
        /*
         * Seguridad por rol.
         */
        if (!$request->user()->esAdministrador()) {

            return response()->json([
                'ok' => false,

                'message' =>
                    'No tienes permisos para crear productos.',
            ], 403);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'codigo' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:productos,codigo',
                ],

                'nombre' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'descripcion' => [
                    'nullable',
                    'string',
                ],

                'precio' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'stock' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'categoria_id' => [
                    'required',
                    'integer',
                    'exists:categorias,id',
                ],

                'talla' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'color' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'genero' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'activo' => [
                    'nullable',
                    'boolean',
                ],
            ]
        );


        if ($validator->fails()) {

            return response()->json([
                'ok' => false,

                'message' =>
                    'Los datos enviados no son válidos.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $datos = $validator->validated();


        /*
         * Si no se envía "activo",
         * se crea activo por defecto.
         */
        $datos['activo'] =
            array_key_exists('activo', $datos)
                ? (bool) $datos['activo']
                : true;


        $producto = Producto::create($datos);


        $producto->load('categoria');


        return response()->json([
            'ok' => true,

            'message' =>
                'Producto creado correctamente.',

            'data' =>
                $producto,
        ], 201);
    }


    /**
     * PUT /api/productos/{producto}
     * PATCH /api/productos/{producto}
     *
     * Actualizar producto.
     * Solo Administrador.
     */
    public function update(
        Request $request,
        Producto $producto
    ) {

        /*
         * Seguridad por rol.
         */
        if (!$request->user()->esAdministrador()) {

            return response()->json([
                'ok' => false,

                'message' =>
                    'No tienes permisos para editar productos.',
            ], 403);
        }


        $validator = Validator::make(
            $request->all(),
            [
                'codigo' => [
                    'required',
                    'string',
                    'max:50',

                    Rule::unique(
                        'productos',
                        'codigo'
                    )->ignore($producto->id),
                ],

                'nombre' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'descripcion' => [
                    'nullable',
                    'string',
                ],

                'precio' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'stock' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'categoria_id' => [
                    'required',
                    'integer',
                    'exists:categorias,id',
                ],

                'talla' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'color' => [
                    'nullable',
                    'string',
                    'max:50',
                ],

                'genero' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'activo' => [
                    'nullable',
                    'boolean',
                ],
            ]
        );


        if ($validator->fails()) {

            return response()->json([
                'ok' => false,

                'message' =>
                    'Los datos enviados no son válidos.',

                'errors' =>
                    $validator->errors(),
            ], 422);
        }


        $datos = $validator->validated();


        /*
         * Si no se manda activo,
         * conservar el valor actual.
         */
        if (!array_key_exists('activo', $datos)) {

            $datos['activo'] =
                $producto->activo;
        }


        $producto->update($datos);


        $producto->load('categoria');


        return response()->json([
            'ok' => true,

            'message' =>
                'Producto actualizado correctamente.',

            'data' =>
                $producto,
        ], 200);
    }


    /**
     * DELETE /api/productos/{producto}
     *
     * Eliminar producto.
     * Solo Administrador.
     */
    public function destroy(
        Request $request,
        Producto $producto
    ) {

        /*
         * Seguridad por rol.
         */
        if (!$request->user()->esAdministrador()) {

            return response()->json([
                'ok' => false,

                'message' =>
                    'No tienes permisos para eliminar productos.',
            ], 403);
        }


        try {

            $producto->delete();


            return response()->json([
                'ok' => true,

                'message' =>
                    'Producto eliminado correctamente.',
            ], 200);

        } catch (QueryException $e) {

            /*
             * Si el producto ya forma parte de una venta,
             * la base de datos puede impedir eliminarlo.
             * En ese caso lo desactivamos para conservar
             * el historial.
             */

            $producto->update([
                'activo' => false,
            ]);


            return response()->json([
                'ok' => true,

                'message' =>
                    'El producto posee registros asociados, por lo que fue desactivado en lugar de eliminarse.',
            ], 200);
        }
    }
}