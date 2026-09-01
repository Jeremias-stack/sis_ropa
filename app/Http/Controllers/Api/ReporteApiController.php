<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReporteApiController extends Controller
{
    /**
     * GET /api/reportes/ventas
     *
     * Obtener resumen de ventas por rango de fechas.
     * Solo Administrador.
     */
    public function ventas(Request $request)
    {
        /*
         * Seguridad por rol.
         */
        if (!$request->user()->esAdministrador()) {
            return response()->json([
                'ok' => false,
                'message' => 'No tienes permisos para consultar reportes.',
            ], 403);
        }


        /*
         * Validar fechas opcionales.
         */
        $validator = Validator::make(
            $request->all(),
            [
                'fecha_inicio' => [
                    'nullable',
                    'date',
                ],

                'fecha_fin' => [
                    'nullable',
                    'date',
                ],
            ]
        );


        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Las fechas enviadas no son válidas.',
                'errors' => $validator->errors(),
            ], 422);
        }


        /*
         * Si no se mandan fechas,
         * usar el mes actual.
         */
        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : now()->startOfMonth();


        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : now()->endOfDay();


        /*
         * Validar que el rango tenga sentido.
         */
        if ($fechaInicio->greaterThan($fechaFin)) {
            return response()->json([
                'ok' => false,
                'message' => 'La fecha inicial no puede ser mayor que la fecha final.',
            ], 422);
        }


        /*
         * Consulta principal.
         */
        $query = Venta::with([
            'cliente',
            'usuario',
        ])
            ->whereBetween(
                'fecha',
                [
                    $fechaInicio,
                    $fechaFin,
                ]
            );


        /*
         * Resumen.
         */
        $cantidadVentas = (clone $query)->count();

        $totalIngresos = (float) (clone $query)
            ->sum('total');

        $totalIva = (float) (clone $query)
            ->sum('iva');

        $totalDescuentos = (float) (clone $query)
            ->sum('descuento');


        /*
         * Últimas ventas del rango.
         */
        $ventas = $query
            ->orderByDesc('fecha')
            ->limit(50)
            ->get();


        return response()->json([
            'ok' => true,

            'message' => 'Reporte de ventas obtenido correctamente.',

            'rango' => [
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_fin' => $fechaFin->toDateString(),
            ],

            'resumen' => [
                'cantidad_ventas' => $cantidadVentas,
                'total_ingresos' => round($totalIngresos, 2),
                'total_iva' => round($totalIva, 2),
                'total_descuentos' => round($totalDescuentos, 2),
            ],

            'data' => $ventas,
        ], 200);
    }
}