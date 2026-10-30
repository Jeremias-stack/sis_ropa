<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Talla;
use App\Models\Color;
use Illuminate\Support\Facades\DB;
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
        $query = Producto::with(['categoria', 'marca', 'variantes.talla', 'variantes.color']);


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
        $producto->load(['categoria', 'marca', 'variantes.talla', 'variantes.color']);


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


        // Compatibilidad con clientes antiguos de la API: se crea una variante única.
        $producto = DB::transaction(function () use ($datos) {
            $producto = Producto::create($datos);
            $producto->variantes()->create([
                'talla_id' => !empty($datos['talla']) ? Talla::firstOrCreate(['nombre' => $datos['talla']])->id : null,
                'color_id' => !empty($datos['color']) ? Color::firstOrCreate(['nombre' => $datos['color']])->id : null,
                'stock' => $datos['stock'],
            ]);
            return $producto;
        });


        $producto->load(['categoria', 'marca', 'variantes.talla', 'variantes.color']);


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


        // No permitir que un cliente REST cambie solo el total cuando hay múltiples
        // variantes: eso desajustaría las existencias reales de tallas y colores.
        if ($producto->variantes()->count() > 1 && (int) $datos['stock'] !== (int) $producto->stock) {
            return response()->json(['ok' => false, 'message' => 'Edita el inventario por talla/color desde el panel de productos.'], 422);
        }
        DB::transaction(function () use ($producto, $datos) {
            $producto = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
            $variantes = $producto->variantes()->lockForUpdate()->get();
            if ($variantes->count() === 1) {
                $variantes[0]->update([
                    'stock' => $datos['stock'],
                    'talla_id' => !empty($datos['talla']) ? Talla::firstOrCreate(['nombre' => $datos['talla']])->id : null,
                    'color_id' => !empty($datos['color']) ? Color::firstOrCreate(['nombre' => $datos['color']])->id : null,
                ]);
            }
            if ($variantes->count() > 1) {
                unset($datos['talla'], $datos['color']);
            }
            $producto->update($datos);
        });


        $producto->load(['categoria', 'marca', 'variantes.talla', 'variantes.color']);


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