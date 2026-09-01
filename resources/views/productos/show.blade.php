@extends('layouts.app')

@section('title', 'Detalle del Producto')

@section('page-title', 'Productos')

@section('content')

<div class="container py-5 productos-page">


    {{-- =====================================================
         ENCABEZADO
         ===================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 productos-header">

        <div>

            <span class="productos-eyebrow">
                Inventario
            </span>

            <h1 class="productos-title">
                Detalle del producto
            </h1>

            <p class="productos-subtitle mb-0">
                Consulta la información registrada del producto.
            </p>

        </div>


        <div class="d-flex flex-wrap gap-2">


            {{-- Volver: disponible para todos --}}
            <a
                href="{{ route('productos.index') }}"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Volver

            </a>


            {{-- Editar: SOLO ADMINISTRADOR --}}
            @if(auth()->user()->esAdministrador())

                <a
                    href="{{ route('productos.edit', $producto) }}"
                    class="btn btn-dark"
                >

                    <i class="bi bi-pencil me-1"></i>

                    Editar producto

                </a>

            @endif

        </div>

    </div>


    {{-- =====================================================
         DETALLE
         ===================================================== --}}

    <div class="productos-card">

        <div class="row g-0">


            {{-- =================================================
                 IMAGEN
                 ================================================= --}}

            <div class="col-lg-5">

                <div class="p-4 h-100 d-flex align-items-center justify-content-center bg-light">

                    @if($producto->imagen_url)

                        <img
                            src="{{ asset('storage/' . $producto->imagen_url) }}"
                            alt="{{ $producto->nombre }}"
                            class="img-fluid rounded-4"
                            style="
                                max-height: 430px;
                                width: 100%;
                                object-fit: contain;
                            "
                        >

                    @else

                        <div
                            class="d-flex flex-column align-items-center justify-content-center text-muted"
                            style="min-height: 350px;"
                        >

                            <div
                                class="producto-sin-imagen mb-3"
                                style="
                                    width: 90px;
                                    height: 90px;
                                    font-size: 2.5rem;
                                "
                            >
                                👕
                            </div>

                            <span>
                                Sin imagen registrada
                            </span>

                        </div>

                    @endif

                </div>

            </div>


            {{-- =================================================
                 INFORMACIÓN
                 ================================================= --}}

            <div class="col-lg-7">

                <div class="p-4 p-lg-5">


                    {{-- Código --}}
                    <div class="mb-2">

                        <span class="producto-codigo">

                            {{ $producto->codigo }}

                        </span>

                    </div>


                    {{-- Nombre --}}
                    <h2 class="fw-bold mb-2">

                        {{ $producto->nombre }}

                    </h2>


                    {{-- Categoría --}}
                    <p class="text-muted mb-4">

                        <i class="bi bi-tag me-1"></i>

                        {{ $producto->categoria->nombre ?? 'Sin categoría' }}

                    </p>


                    {{-- Precio --}}
                    <div class="mb-4">

                        <span class="text-muted small d-block mb-1">
                            Precio
                        </span>

                        <span
                            class="fw-bold"
                            style="font-size: 1.8rem;"
                        >

                            Q{{ number_format($producto->precio, 2) }}

                        </span>

                    </div>


                    {{-- Estado y stock --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">


                        @if($producto->activo)

                            <span class="badge text-bg-success producto-badge">

                                <i class="bi bi-check-circle me-1"></i>

                                Activo

                            </span>

                        @else

                            <span class="badge text-bg-secondary producto-badge">

                                Inactivo

                            </span>

                        @endif


                        @if($producto->stock_bajo)

                            <span class="badge text-bg-danger producto-badge">

                                <i class="bi bi-exclamation-triangle me-1"></i>

                                Stock bajo:
                                {{ $producto->stock }} unidades

                            </span>


                        @elseif($producto->stock <= 10)

                            <span class="badge text-bg-warning producto-badge">

                                {{ $producto->stock }} unidades

                            </span>


                        @else

                            <span class="badge text-bg-success producto-badge">

                                {{ $producto->stock }} unidades disponibles

                            </span>

                        @endif

                    </div>


                    {{-- =================================================
                         DATOS
                         ================================================= --}}

                    <div class="row g-3 mb-4">


                        <div class="col-sm-6">

                            <div class="p-3 bg-light rounded-3 h-100">

                                <span class="text-muted small d-block mb-1">
                                    Talla
                                </span>

                                <strong>

                                    {{ $producto->talla ?: 'No aplica' }}

                                </strong>

                            </div>

                        </div>


                        <div class="col-sm-6">

                            <div class="p-3 bg-light rounded-3 h-100">

                                <span class="text-muted small d-block mb-1">
                                    Color
                                </span>

                                <strong>

                                    {{ $producto->color ?: 'No especificado' }}

                                </strong>

                            </div>

                        </div>


                        <div class="col-sm-6">

                            <div class="p-3 bg-light rounded-3 h-100">

                                <span class="text-muted small d-block mb-1">
                                    Género
                                </span>

                                <strong>

                                    {{ $producto->genero
                                        ? ucfirst($producto->genero)
                                        : 'No especificado' }}

                                </strong>

                            </div>

                        </div>


                        <div class="col-sm-6">

                            <div class="p-3 bg-light rounded-3 h-100">

                                <span class="text-muted small d-block mb-1">
                                    Categoría
                                </span>

                                <strong>

                                    {{ $producto->categoria->nombre ?? 'Sin categoría' }}

                                </strong>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         DESCRIPCIÓN
                         ================================================= --}}

                    <div>

                        <h3
                            class="fw-bold mb-2"
                            style="font-size: 0.9rem;"
                        >
                            Descripción
                        </h3>

                        <p class="text-muted mb-0">

                            {{ $producto->descripcion
                                ?: 'Este producto no tiene una descripción registrada.' }}

                        </p>

                    </div>


                    {{-- =================================================
                         INFORMACIÓN SEGÚN ROL
                         ================================================= --}}

                    @if(auth()->user()->esCajero())

                        <div
                            class="alert alert-light border mt-4 mb-0"
                            role="alert"
                        >

                            <i class="bi bi-eye me-2"></i>

                            Estás consultando este producto en modo de solo lectura.

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection