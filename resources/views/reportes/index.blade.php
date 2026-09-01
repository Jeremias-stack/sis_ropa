@extends('layouts.app')

@section('title', 'Reportes')

@section('page-title', 'Reportes')

@section('content')

<div class="reportes-page">


    {{-- =====================================================
         ENCABEZADO
         ===================================================== --}}

    <div class="reportes-header">

        <div>

            <span class="reportes-eyebrow">
                Estadísticas
            </span>

            <h1 class="reportes-title">
                Reportes de ventas
            </h1>

            <p class="reportes-subtitle">
                Consulta el rendimiento y comportamiento de las ventas.
            </p>

        </div>


        <button
            type="button"
            class="reportes-print-button"
            onclick="window.print()"
        >

            <i class="bi bi-printer-fill"></i>

            Imprimir / PDF

        </button>

    </div>


    {{-- =====================================================
         FILTRO DE FECHAS
         ===================================================== --}}

    <div class="reportes-filter">

        <form
            method="GET"
            action="{{ route('reportes.index') }}"
            class="reportes-filter-form"
        >

            <div class="reportes-filter-group">

                <label
                    for="fecha_inicio"
                    class="reportes-label"
                >
                    Fecha inicial
                </label>

                <input
                    type="date"
                    name="fecha_inicio"
                    id="fecha_inicio"
                    class="reportes-control"
                    value="{{ $fechaInicio->toDateString() }}"
                >

            </div>


            <div class="reportes-filter-group">

                <label
                    for="fecha_fin"
                    class="reportes-label"
                >
                    Fecha final
                </label>

                <input
                    type="date"
                    name="fecha_fin"
                    id="fecha_fin"
                    class="reportes-control"
                    value="{{ $fechaFin->toDateString() }}"
                >

            </div>


            <button
                type="submit"
                class="reportes-filter-button"
            >

                <i class="bi bi-funnel-fill"></i>

                Aplicar filtro

            </button>

        </form>

    </div>


    {{-- =====================================================
         ESTADÍSTICAS
         ===================================================== --}}

    <div class="reportes-stats">


        <div class="reporte-stat">

            <div class="reporte-stat-icon">
                <i class="bi bi-calendar-day"></i>
            </div>

            <span class="reporte-stat-value">
                Q{{ number_format($ventasHoy, 2) }}
            </span>

            <span class="reporte-stat-label">
                Ventas de hoy · {{ $cantidadVentasHoy }} operaciones
            </span>

        </div>


        <div class="reporte-stat">

            <div class="reporte-stat-icon">
                <i class="bi bi-calendar-week"></i>
            </div>

            <span class="reporte-stat-value">
                Q{{ number_format($ventasSemana, 2) }}
            </span>

            <span class="reporte-stat-label">
                Ventas esta semana
            </span>

        </div>


        <div class="reporte-stat">

            <div class="reporte-stat-icon">
                <i class="bi bi-calendar3"></i>
            </div>

            <span class="reporte-stat-value">
                Q{{ number_format($ventasMes, 2) }}
            </span>

            <span class="reporte-stat-label">
                Ventas este mes
            </span>

        </div>


        <div class="reporte-stat">

            <div class="reporte-stat-icon">
                <i class="bi bi-cash-stack"></i>
            </div>

            <span class="reporte-stat-value">
                Q{{ number_format($totalRango, 2) }}
            </span>

            <span class="reporte-stat-label">
                Total del rango seleccionado
            </span>

        </div>

    </div>


    {{-- =====================================================
         GRÁFICA DIARIA + PRODUCTOS MÁS VENDIDOS
         ===================================================== --}}

    <div class="row g-4 mb-4">


        <div class="col-xl-7">

            <div class="reporte-panel">

                <div class="reporte-panel-header">

                    <h2 class="reporte-panel-title">
                        Ventas diarias
                    </h2>

                    <p class="reporte-panel-subtitle">
                        Comportamiento de los últimos 7 días
                    </p>

                </div>


                <div class="reporte-chart">

                    <canvas id="chartVentasDiarias"></canvas>

                </div>

            </div>

        </div>


        <div class="col-xl-5">

            <div class="reporte-panel">

                <div class="reporte-panel-header">

                    <h2 class="reporte-panel-title">
                        Productos más vendidos
                    </h2>

                    <p class="reporte-panel-subtitle">
                        Según el rango de fechas seleccionado
                    </p>

                </div>


                @forelse($productosMasVendidos as $index => $detalle)

                    <div class="reporte-product">

                        <span class="reporte-product-position">
                            {{ $index + 1 }}
                        </span>


                        <div class="reporte-product-info">

                            <span class="reporte-product-name">

                                {{ $detalle->producto->nombre ?? 'Producto' }}

                            </span>

                            <span class="reporte-product-meta">

                                {{ $detalle->total_vendido }}
                                unidades vendidas

                            </span>

                        </div>


                        <span class="reporte-product-income">

                            Q{{ number_format(
                                (float) $detalle->ingresos,
                                2
                            ) }}

                        </span>

                    </div>

                @empty

                    <div class="reporte-empty">

                        No hay productos vendidos dentro de este rango.

                    </div>

                @endforelse

            </div>

        </div>

    </div>


    {{-- =====================================================
         GRÁFICAS SEMANAL Y MENSUAL
         ===================================================== --}}

    <div class="row g-4 mb-4">


        <div class="col-lg-6">

            <div class="reporte-panel">

                <div class="reporte-panel-header">

                    <h2 class="reporte-panel-title">
                        Ventas semanales
                    </h2>

                    <p class="reporte-panel-subtitle">
                        Últimas 6 semanas
                    </p>

                </div>


                <div class="reporte-chart">

                    <canvas id="chartVentasSemanales"></canvas>

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="reporte-panel">

                <div class="reporte-panel-header">

                    <h2 class="reporte-panel-title">
                        Ventas mensuales
                    </h2>

                    <p class="reporte-panel-subtitle">
                        Últimos 6 meses
                    </p>

                </div>


                <div class="reporte-chart">

                    <canvas id="chartVentasMensuales"></canvas>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         VENTAS POR RANGO
         ===================================================== --}}

    <div class="reporte-panel">


        <div class="reporte-panel-header">

            <h2 class="reporte-panel-title">
                Ventas por rango de fechas
            </h2>

            <p class="reporte-panel-subtitle">

                Del {{ $fechaInicio->format('d/m/Y') }}
                al {{ $fechaFin->format('d/m/Y') }}

            </p>

        </div>


        <div class="reporte-range-summary">


            <div class="reporte-range-item">

                <span class="reporte-range-label">
                    Ventas
                </span>

                <span class="reporte-range-value">
                    {{ $cantidadVentasRango }}
                </span>

            </div>


            <div class="reporte-range-item">

                <span class="reporte-range-label">
                    Total
                </span>

                <span class="reporte-range-value">

                    Q{{ number_format(
                        $totalRango,
                        2
                    ) }}

                </span>

            </div>


            <div class="reporte-range-item">

                <span class="reporte-range-label">
                    IVA
                </span>

                <span class="reporte-range-value">

                    Q{{ number_format(
                        $ivaRango,
                        2
                    ) }}

                </span>

            </div>


            <div class="reporte-range-item">

                <span class="reporte-range-label">
                    Descuentos
                </span>

                <span class="reporte-range-value">

                    Q{{ number_format(
                        $descuentosRango,
                        2
                    ) }}

                </span>

            </div>

        </div>


        @if($ventasRango->count())


            <div class="reporte-table-wrapper">

                <table class="reporte-table">

                    <thead>

                        <tr>

                            <th>
                                Factura
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Cajero
                            </th>

                            <th>
                                Método
                            </th>

                            <th class="text-end">
                                Total
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($ventasRango as $venta)

                            <tr>

                                <td>

                                    <a
                                        href="{{ route('ventas.show', $venta) }}"
                                        class="reporte-factura text-decoration-none"
                                    >

                                        {{ $venta->numero_factura }}

                                    </a>

                                </td>


                                <td>

                                    {{ $venta->fecha->format('d/m/Y H:i') }}

                                </td>


                                <td>

                                    {{ $venta->cliente?->nombre ?? 'Consumidor Final' }}

                                </td>


                                <td>

                                    {{ $venta->usuario?->nombre ?? '—' }}

                                </td>


                                <td>

                                    <span class="reporte-method">

                                        {{ ucfirst($venta->metodo_pago) }}

                                    </span>

                                </td>


                                <td class="text-end reporte-total">

                                    Q{{ number_format(
                                        (float) $venta->total,
                                        2
                                    ) }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            @if($ventasRango->hasPages())

                <div class="p-3">

                    {{ $ventasRango->links('pagination::bootstrap-5') }}

                </div>

            @endif


        @else


            <div class="reporte-empty">

                <i class="bi bi-bar-chart d-block fs-3 mb-2"></i>

                No existen ventas dentro del rango seleccionado.

            </div>


        @endif

    </div>


    {{-- =====================================================
         DATOS PARA CHART.JS
         ===================================================== --}}

    <script
        type="application/json"
        id="ventasDiariasLabels"
    >@json($ventasDiariasLabels)</script>


    <script
        type="application/json"
        id="ventasDiariasData"
    >@json($ventasDiariasData)</script>


    <script
        type="application/json"
        id="ventasSemanalesLabels"
    >@json($ventasSemanalesLabels)</script>


    <script
        type="application/json"
        id="ventasSemanalesData"
    >@json($ventasSemanalesData)</script>


    <script
        type="application/json"
        id="ventasMensualesLabels"
    >@json($ventasMensualesLabels)</script>


    <script
        type="application/json"
        id="ventasMensualesData"
    >@json($ventasMensualesData)</script>

</div>

@endsection