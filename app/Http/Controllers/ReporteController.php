<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    /**
     * Mostrar dashboard de reportes.
     */
    public function index(Request $request)
    {
        /* =====================================================
           RANGO DE FECHAS DEL REPORTE
           ===================================================== */

        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->fecha_inicio)->startOfDay()
            : now()->startOfMonth();

        $fechaFin = $request->filled('fecha_fin')
            ? Carbon::parse($request->fecha_fin)->endOfDay()
            : now()->endOfDay();


        /*
         * Evitar un rango invertido.
         */
        if ($fechaInicio->greaterThan($fechaFin)) {
            [$fechaInicio, $fechaFin] = [
                $fechaFin->copy()->startOfDay(),
                $fechaInicio->copy()->endOfDay(),
            ];
        }


        /* =====================================================
           RESUMEN GENERAL
           ===================================================== */

        $ventasHoy = Venta::where('estado', 'completada')
            ->whereDate('fecha', today())
            ->sum('total');


        $cantidadVentasHoy = Venta::where('estado', 'completada')
            ->whereDate('fecha', today())
            ->count();


        $ventasSemana = Venta::where('estado', 'completada')
            ->whereBetween('fecha', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->sum('total');


        $ventasMes = Venta::where('estado', 'completada')
            ->whereBetween('fecha', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->sum('total');


        /* =====================================================
           VENTAS DEL RANGO SELECCIONADO
           ===================================================== */

        $ventasRangoQuery = Venta::with([
                'cliente',
                'usuario',
            ])
            ->where('estado', 'completada')
            ->whereBetween('fecha', [
                $fechaInicio,
                $fechaFin,
            ]);


        $totalRango = (clone $ventasRangoQuery)
            ->sum('total');


        $cantidadVentasRango = (clone $ventasRangoQuery)
            ->count();


        $ivaRango = (clone $ventasRangoQuery)
            ->sum('iva');


        $descuentosRango = (clone $ventasRangoQuery)
            ->sum('descuento');


        $ventasRango = $ventasRangoQuery
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();


        /* =====================================================
           PRODUCTOS MÁS VENDIDOS
           ===================================================== */

        $productosMasVendidos = DetalleVenta::query()
            ->selectRaw(
                '
                producto_id,
                SUM(cantidad) as total_vendido,
                SUM(subtotal) as ingresos
                '
            )
            ->whereHas('venta', function ($query) use (
                $fechaInicio,
                $fechaFin
            ) {
                $query
                    ->where('estado', 'completada')
                    ->whereBetween('fecha', [
                        $fechaInicio,
                        $fechaFin,
                    ]);
            })
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();


        /* =====================================================
           GRÁFICA DIARIA - ÚLTIMOS 7 DÍAS
           ===================================================== */

        $ventasDiariasLabels = [];
        $ventasDiariasData = [];


        for ($i = 6; $i >= 0; $i--) {

            $fecha = now()
                ->copy()
                ->subDays($i);


            $ventasDiariasLabels[] =
                $fecha->format('d/m');


            $ventasDiariasData[] =
                (float) Venta::where(
                    'estado',
                    'completada'
                )
                    ->whereDate(
                        'fecha',
                        $fecha->toDateString()
                    )
                    ->sum('total');

        }


        /* =====================================================
           GRÁFICA SEMANAL - ÚLTIMAS 6 SEMANAS
           ===================================================== */

        $ventasSemanalesLabels = [];
        $ventasSemanalesData = [];


        for ($i = 5; $i >= 0; $i--) {

            $inicioSemana = now()
                ->copy()
                ->startOfWeek()
                ->subWeeks($i);

            $finSemana = $inicioSemana
                ->copy()
                ->endOfWeek();


            $ventasSemanalesLabels[] =
                $inicioSemana->format('d/m')
                . ' - '
                . $finSemana->format('d/m');


            $ventasSemanalesData[] =
                (float) Venta::where(
                    'estado',
                    'completada'
                )
                    ->whereBetween(
                        'fecha',
                        [
                            $inicioSemana,
                            $finSemana,
                        ]
                    )
                    ->sum('total');

        }


        /* =====================================================
           GRÁFICA MENSUAL - ÚLTIMOS 6 MESES
           ===================================================== */

        $ventasMensualesLabels = [];
        $ventasMensualesData = [];


        for ($i = 5; $i >= 0; $i--) {

            $mes = now()
                ->copy()
                ->startOfMonth()
                ->subMonths($i);


            $ventasMensualesLabels[] =
                ucfirst(
                    $mes->translatedFormat('M Y')
                );


            $ventasMensualesData[] =
                (float) Venta::where(
                    'estado',
                    'completada'
                )
                    ->whereYear(
                        'fecha',
                        $mes->year
                    )
                    ->whereMonth(
                        'fecha',
                        $mes->month
                    )
                    ->sum('total');

        }


        /* =====================================================
           ENVIAR DATOS A LA VISTA
           ===================================================== */

        return view(
            'reportes.index',
            compact(
                'fechaInicio',
                'fechaFin',

                'ventasHoy',
                'cantidadVentasHoy',
                'ventasSemana',
                'ventasMes',

                'totalRango',
                'cantidadVentasRango',
                'ivaRango',
                'descuentosRango',
                'ventasRango',

                'productosMasVendidos',

                'ventasDiariasLabels',
                'ventasDiariasData',

                'ventasSemanalesLabels',
                'ventasSemanalesData',

                'ventasMensualesLabels',
                'ventasMensualesData'
            )
        );
    }
}