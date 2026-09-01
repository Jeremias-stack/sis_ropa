@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')

<div class="dashboard-page">


    {{-- =====================================================
         BIENVENIDA
         ===================================================== --}}

    <div class="dashboard-welcome">

        <span class="dashboard-eyebrow">
            Resumen general
        </span>

        <h1 class="dashboard-title">

            ¡Hola, {{ auth()->user()->nombre }}!

        </h1>

        <p class="dashboard-subtitle">

            @if(auth()->user()->esAdministrador())

                Aquí tienes un resumen general del inventario
                y actividad de la tienda.

            @else

                Aquí puedes consultar el inventario
                y acceder rápidamente al punto de venta.

            @endif

        </p>

    </div>


    {{-- =====================================================
         CLIMA - OPENWEATHER
         ===================================================== --}}

    @if($clima)

        <section class="weather-card">


            <div class="weather-main">

                <span class="weather-eyebrow">

                    <i class="bi bi-cloud-sun-fill"></i>

                    Clima actual

                </span>


                <h2 class="weather-location">

                    {{ $clima['ubicacion'] }}

                </h2>


                <p class="weather-description">

                    {{ $clima['descripcion'] }}

                </p>


                <div class="weather-temperature-area">

                    @if($clima['icono'])

                        <img
                            src="https://openweathermap.org/img/wn/{{ $clima['icono'] }}@2x.png"
                            alt="{{ $clima['descripcion'] }}"
                            class="weather-icon"
                        >

                    @endif


                    <span class="weather-temperature">

                        {{ $clima['temperatura'] }}°

                    </span>

                </div>


                <span class="weather-api-label">

                    <i class="bi bi-cloud-arrow-down"></i>

                    Datos proporcionados por OpenWeather

                </span>

            </div>


            <div class="weather-details">


                <div class="weather-detail">

                    <i class="bi bi-thermometer-half weather-detail-icon"></i>

                    <span class="weather-detail-label">
                        Sensación
                    </span>

                    <span class="weather-detail-value">

                        {{ $clima['sensacion'] }} °C

                    </span>

                </div>


                <div class="weather-detail">

                    <i class="bi bi-droplet-fill weather-detail-icon"></i>

                    <span class="weather-detail-label">
                        Humedad
                    </span>

                    <span class="weather-detail-value">

                        {{ $clima['humedad'] }}%

                    </span>

                </div>


                <div class="weather-detail">

                    <i class="bi bi-wind weather-detail-icon"></i>

                    <span class="weather-detail-label">
                        Viento
                    </span>

                    <span class="weather-detail-value">

                        {{ $clima['viento'] }} km/h

                    </span>

                </div>

            </div>

        </section>


    @elseif($climaError)

        <div class="weather-error">

            <div class="weather-error-icon">

                <i class="bi bi-cloud-slash-fill"></i>

            </div>


            <div>

                <strong>
                    Información del clima no disponible
                </strong>

                <div>
                    {{ $climaError }}
                </div>

            </div>

        </div>

    @endif


    {{-- =====================================================
         ALERTA STOCK BAJO
         ===================================================== --}}

    @if($stockBajo > 0)

        <div class="dashboard-stock-alert">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>

                Tienes

                <strong>

                    {{ $stockBajo }}

                    {{ $stockBajo === 1
                        ? 'producto'
                        : 'productos' }}

                </strong>

                con menos de 5 unidades disponibles.

            </div>

        </div>

    @endif


    {{-- =====================================================
         ESTADÍSTICAS
         ===================================================== --}}

    <div class="row g-3 mb-4">


        {{-- Productos registrados --}}
        <div class="col-6 col-xl">

            <div class="dashboard-stat-card">

                <div class="dashboard-stat-top">

                    <div class="dashboard-stat-icon">

                        <i class="bi bi-box-seam-fill"></i>

                    </div>

                </div>


                <h3 class="dashboard-stat-value">

                    {{ $totalProductos }}

                </h3>


                <span class="dashboard-stat-label">

                    Productos registrados

                </span>

            </div>

        </div>


        {{-- Productos activos --}}
        <div class="col-6 col-xl">

            <div class="dashboard-stat-card">

                <div class="dashboard-stat-top">

                    <div class="dashboard-stat-icon success">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>

                </div>


                <h3 class="dashboard-stat-value">

                    {{ $productosActivos }}

                </h3>


                <span class="dashboard-stat-label">

                    Productos activos

                </span>

            </div>

        </div>


        {{-- Stock bajo --}}
        <div class="col-6 col-xl">

            <div class="dashboard-stat-card">

                <div class="dashboard-stat-top">

                    <div class="dashboard-stat-icon danger">

                        <i class="bi bi-exclamation-triangle-fill"></i>

                    </div>

                </div>


                <h3 class="dashboard-stat-value">

                    {{ $stockBajo }}

                </h3>


                <span class="dashboard-stat-label">

                    Con stock bajo

                </span>

            </div>

        </div>


        {{-- Categorías --}}
        <div class="col-6 col-xl">

            <div class="dashboard-stat-card">

                <div class="dashboard-stat-top">

                    <div class="dashboard-stat-icon info">

                        <i class="bi bi-tags-fill"></i>

                    </div>

                </div>


                <h3 class="dashboard-stat-value">

                    {{ $totalCategorias }}

                </h3>


                <span class="dashboard-stat-label">

                    Categorías

                </span>

            </div>

        </div>


        {{-- Valor del inventario --}}
        <div class="col-12 col-xl">

            <div class="dashboard-stat-card">

                <div class="dashboard-stat-top">

                    <div class="dashboard-stat-icon warning">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                </div>


                <h3 class="dashboard-stat-value">

                    Q{{ number_format(
                        $valorInventario,
                        2
                    ) }}

                </h3>


                <span class="dashboard-stat-label">

                    Valor del inventario

                </span>

            </div>

        </div>

    </div>


    {{-- =====================================================
         PRODUCTOS RECIENTES + ACCESOS
         ===================================================== --}}

    <div class="row g-4">


        {{-- Productos recientes --}}
        <div class="col-lg-7">

            <div class="dashboard-panel">


                <div class="dashboard-panel-header">

                    <div>

                        <h2 class="dashboard-panel-title">

                            Productos recientes

                        </h2>


                        <p class="dashboard-panel-subtitle">

                            Últimos productos agregados
                            al inventario

                        </p>

                    </div>


                    <a
                        href="{{ route('productos.index') }}"
                        class="btn btn-sm btn-outline-dark"
                    >

                        Ver todos

                    </a>

                </div>


                @forelse($ultimosProductos as $producto)

                    <a
                        href="{{ route('productos.show', $producto) }}"
                        class="dashboard-product text-decoration-none"
                    >


                        @if($producto->imagen_url)

                            <img
                                src="{{ asset(
                                    'storage/' .
                                    $producto->imagen_url
                                ) }}"
                                alt="{{ $producto->nombre }}"
                                class="dashboard-product-image"
                            >

                        @else

                            <div class="dashboard-product-image">

                                👕

                            </div>

                        @endif


                        <div class="dashboard-product-info">

                            <span class="dashboard-product-name">

                                {{ $producto->nombre }}

                            </span>


                            <span class="dashboard-product-meta">

                                {{ $producto->codigo }}

                                @if($producto->categoria)

                                    ·
                                    {{ $producto->categoria->nombre }}

                                @endif

                            </span>

                        </div>


                        <span class="dashboard-product-price">

                            Q{{ number_format(
                                $producto->precio,
                                2
                            ) }}

                        </span>

                    </a>


                @empty

                    <div class="text-center py-5 text-muted">

                        <i class="bi bi-box-seam fs-1 d-block mb-2"></i>

                        Aún no existen productos.

                    </div>

                @endforelse

            </div>

        </div>


        {{-- =================================================
             ACCESOS RÁPIDOS
             ================================================= --}}

        <div class="col-lg-5">

            <div class="dashboard-panel">


                <div class="dashboard-panel-header">

                    <div>

                        <h2 class="dashboard-panel-title">

                            Accesos rápidos

                        </h2>


                        <p class="dashboard-panel-subtitle">

                            Funciones disponibles para tu usuario

                        </p>

                    </div>

                </div>


                <div class="dashboard-actions">


                    {{-- =====================================
                         ADMINISTRADOR
                         ===================================== --}}

                    @if(auth()->user()->esAdministrador())


                        {{-- Nuevo producto --}}
                        <a
                            href="{{ route('productos.create') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-plus-lg"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Nuevo producto

                                </span>

                                <span class="dashboard-action-description">

                                    Agregar al inventario

                                </span>

                            </div>

                        </a>


                        {{-- Inventario --}}
                        <a
                            href="{{ route('productos.index') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-box-seam"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Inventario

                                </span>

                                <span class="dashboard-action-description">

                                    Administrar productos

                                </span>

                            </div>

                        </a>


                        {{-- Nueva venta --}}
                        <a
                            href="{{ route('ventas.create') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-cart-plus"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Nueva venta

                                </span>

                                <span class="dashboard-action-description">

                                    Abrir punto de venta

                                </span>

                            </div>

                        </a>


                        {{-- Reportes --}}
                        <a
                            href="{{ route('reportes.index') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-bar-chart"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Reportes

                                </span>

                                <span class="dashboard-action-description">

                                    Consultar estadísticas

                                </span>

                            </div>

                        </a>


                    {{-- =====================================
                         CAJERO
                         ===================================== --}}

                    @else


                        {{-- Nueva venta --}}
                        <a
                            href="{{ route('ventas.create') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-cart-plus"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Nueva venta

                                </span>

                                <span class="dashboard-action-description">

                                    Abrir punto de venta

                                </span>

                            </div>

                        </a>


                        {{-- Productos --}}
                        <a
                            href="{{ route('productos.index') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-box-seam"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Productos

                                </span>

                                <span class="dashboard-action-description">

                                    Consultar inventario

                                </span>

                            </div>

                        </a>


                        {{-- Clientes --}}
                        <a
                            href="{{ route('clientes.index') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-people"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Clientes

                                </span>

                                <span class="dashboard-action-description">

                                    Gestionar clientes

                                </span>

                            </div>

                        </a>


                        {{-- Otra venta --}}
                        <a
                            href="{{ route('ventas.create') }}"
                            class="dashboard-action"
                        >

                            <span class="dashboard-action-icon">

                                <i class="bi bi-cash-coin"></i>

                            </span>


                            <div>

                                <span class="dashboard-action-title">

                                    Caja

                                </span>

                                <span class="dashboard-action-description">

                                    Registrar una venta

                                </span>

                            </div>

                        </a>


                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection