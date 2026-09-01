@extends('layouts.app')

@section('title', 'Inventario de Productos')

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
                Productos
            </h1>

            <p class="productos-subtitle mb-0">
                Administra las prendas y accesorios disponibles en la tienda.
            </p>

        </div>


        {{-- Solo Administrador puede crear productos --}}
        @if(auth()->user()->esAdministrador())

            <a
                href="{{ route('productos.create') }}"
                class="btn btn-producto-primary"
            >
                <i class="bi bi-plus-lg me-1"></i>
                Nuevo producto
            </a>

        @endif

    </div>


    {{-- =====================================================
         MENSAJES
         ===================================================== --}}

    @if(session('success'))

        <div
            class="alert alert-success alert-dismissible fade show shadow-sm border-0"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if(session('error'))

        <div
            class="alert alert-danger alert-dismissible fade show shadow-sm border-0"
            role="alert"
        >

            <i class="bi bi-exclamation-circle-fill me-2"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- =====================================================
         BUSCADOR
         ===================================================== --}}

    <div class="productos-card productos-search-card mb-4">

        <form
            method="GET"
            action="{{ route('productos.index') }}"
        >

            <div class="row g-3 align-items-end">


                <div class="col-lg-9">

                    <label
                        for="buscar"
                        class="productos-search-label"
                    >
                        Buscar producto
                    </label>


                    <div class="productos-search-wrapper">

                        <span class="productos-search-icon">
                            🔎
                        </span>

                        <input
                            type="text"
                            name="buscar"
                            id="buscar"
                            class="productos-search"
                            placeholder="Busca por nombre o código..."
                            value="{{ request('buscar') }}"
                            autocomplete="off"
                        >

                    </div>

                </div>


                <div class="col-lg-3">

                    <div class="d-flex gap-2 productos-search-actions">

                        <button
                            type="submit"
                            class="btn-productos-search"
                        >
                            Buscar
                        </button>


                        @if(request('buscar'))

                            <a
                                href="{{ route('productos.index') }}"
                                class="btn-productos-clear"
                            >
                                Limpiar
                            </a>

                        @endif

                    </div>

                </div>

            </div>

        </form>

    </div>


    {{-- =====================================================
         VISTA ESCRITORIO
         ===================================================== --}}

    <div class="productos-card d-none d-md-block">

        <div class="table-responsive">

            <table class="table productos-table align-middle">

                <thead>

                    <tr>

                        <th class="ps-4">
                            Producto
                        </th>

                        <th>
                            Código
                        </th>

                        <th>
                            Categoría
                        </th>

                        <th>
                            Precio
                        </th>

                        <th>
                            Stock
                        </th>

                        <th>
                            Estado
                        </th>

                        <th class="text-end pe-4">
                            Acciones
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($productos as $producto)

                        <tr>


                            {{-- Producto --}}
                            <td class="ps-4">

                                <div class="d-flex align-items-center gap-3">


                                    @if($producto->imagen_url)

                                        <img
                                            src="{{ asset('storage/' . $producto->imagen_url) }}"
                                            alt="{{ $producto->nombre }}"
                                            class="producto-miniatura"
                                        >

                                    @else

                                        <div class="producto-sin-imagen">
                                            👕
                                        </div>

                                    @endif


                                    <div>

                                        <div class="producto-nombre">

                                            {{ $producto->nombre }}

                                        </div>


                                        @if($producto->talla || $producto->color)

                                            <div class="producto-detalle">

                                                @if($producto->talla)

                                                    Talla {{ $producto->talla }}

                                                @endif


                                                @if($producto->talla && $producto->color)

                                                    •

                                                @endif


                                                @if($producto->color)

                                                    {{ $producto->color }}

                                                @endif

                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- Código --}}
                            <td>

                                <span class="producto-codigo">

                                    {{ $producto->codigo }}

                                </span>

                            </td>


                            {{-- Categoría --}}
                            <td>

                                {{ $producto->categoria->nombre ?? 'Sin categoría' }}

                            </td>


                            {{-- Precio --}}
                            <td>

                                <span class="producto-precio">

                                    Q{{ number_format($producto->precio, 2) }}

                                </span>

                            </td>


                            {{-- Stock --}}
                            <td>

                                @if($producto->stock_bajo)

                                    <span class="badge text-bg-danger producto-badge">

                                        {{ $producto->stock }} unidades

                                    </span>


                                @elseif($producto->stock <= 10)

                                    <span class="badge text-bg-warning producto-badge">

                                        {{ $producto->stock }} unidades

                                    </span>


                                @else

                                    <span class="badge text-bg-success producto-badge">

                                        {{ $producto->stock }} unidades

                                    </span>

                                @endif

                            </td>


                            {{-- Estado --}}
                            <td>

                                @if($producto->activo)

                                    <span class="badge text-bg-success producto-badge">
                                        Activo
                                    </span>

                                @else

                                    <span class="badge text-bg-secondary producto-badge">
                                        Inactivo
                                    </span>

                                @endif

                            </td>


                            {{-- Acciones --}}
                            <td class="pe-4">

                                <div class="producto-actions">


                                    {{-- Todos pueden consultar --}}
                                    <a
                                        href="{{ route('productos.show', $producto) }}"
                                        class="btn btn-sm btn-outline-secondary producto-action-btn"
                                    >
                                        <i class="bi bi-eye me-1"></i>
                                        Ver
                                    </a>


                                    {{-- Solo Administrador --}}
                                    @if(auth()->user()->esAdministrador())

                                        <a
                                            href="{{ route('productos.edit', $producto) }}"
                                            class="btn btn-sm btn-outline-dark producto-action-btn"
                                        >
                                            <i class="bi bi-pencil me-1"></i>
                                            Editar
                                        </a>


                                        <form
                                            action="{{ route('productos.destroy', $producto) }}"
                                            method="POST"
                                            onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');"
                                        >

                                            @csrf
                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger producto-action-btn"
                                            >
                                                <i class="bi bi-trash me-1"></i>
                                                Eliminar
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center productos-empty"
                            >

                                <div class="productos-empty-icon mb-3">
                                    👕
                                </div>

                                <h5 class="fw-bold">
                                    No hay productos
                                </h5>

                                <p class="text-muted mb-3">

                                    @if(request('buscar'))

                                        No encontramos productos que coincidan con
                                        "{{ request('buscar') }}".

                                    @else

                                        Todavía no hay productos registrados.

                                    @endif

                                </p>


                                @if(auth()->user()->esAdministrador())

                                    <a
                                        href="{{ route('productos.create') }}"
                                        class="btn btn-producto-primary"
                                    >
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Crear producto
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- =====================================================
         VISTA CELULAR
         ===================================================== --}}

    <div class="productos-card d-md-none">

        @forelse($productos as $producto)

            <div class="producto-mobile-card">


                {{-- Encabezado --}}
                <div class="producto-mobile-header">


                    @if($producto->imagen_url)

                        <img
                            src="{{ asset('storage/' . $producto->imagen_url) }}"
                            alt="{{ $producto->nombre }}"
                            class="producto-miniatura"
                        >

                    @else

                        <div class="producto-sin-imagen">
                            👕
                        </div>

                    @endif


                    <div class="producto-mobile-info">

                        <h3 class="producto-mobile-name">

                            {{ $producto->nombre }}

                        </h3>


                        <div class="producto-mobile-code">

                            {{ $producto->codigo }}

                        </div>


                        <div class="mt-2">

                            @if($producto->activo)

                                <span class="badge text-bg-success producto-badge">
                                    Activo
                                </span>

                            @else

                                <span class="badge text-bg-secondary producto-badge">
                                    Inactivo
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Información --}}
                <div class="producto-mobile-grid">


                    <div class="producto-mobile-field">

                        <span class="producto-mobile-label">
                            Categoría
                        </span>

                        <span class="producto-mobile-value">

                            {{ $producto->categoria->nombre ?? 'Sin categoría' }}

                        </span>

                    </div>


                    <div class="producto-mobile-field">

                        <span class="producto-mobile-label">
                            Precio
                        </span>

                        <span class="producto-mobile-value">

                            Q{{ number_format($producto->precio, 2) }}

                        </span>

                    </div>


                    <div class="producto-mobile-field">

                        <span class="producto-mobile-label">
                            Stock
                        </span>


                        <span class="producto-mobile-value">

                            @if($producto->stock_bajo)

                                <span class="text-danger">

                                    {{ $producto->stock }} unidades

                                </span>


                            @elseif($producto->stock <= 10)

                                <span class="text-warning">

                                    {{ $producto->stock }} unidades

                                </span>


                            @else

                                <span class="text-success">

                                    {{ $producto->stock }} unidades

                                </span>

                            @endif

                        </span>

                    </div>


                    <div class="producto-mobile-field">

                        <span class="producto-mobile-label">
                            Talla
                        </span>

                        <span class="producto-mobile-value">

                            {{ $producto->talla ?: 'No aplica' }}

                        </span>

                    </div>


                    @if($producto->color)

                        <div class="producto-mobile-field">

                            <span class="producto-mobile-label">
                                Color
                            </span>

                            <span class="producto-mobile-value">

                                {{ $producto->color }}

                            </span>

                        </div>

                    @endif


                    @if($producto->genero)

                        <div class="producto-mobile-field">

                            <span class="producto-mobile-label">
                                Género
                            </span>

                            <span class="producto-mobile-value">

                                {{ ucfirst($producto->genero) }}

                            </span>

                        </div>

                    @endif

                </div>


                {{-- Acciones --}}
                <div class="producto-mobile-actions">


                    {{-- Disponible para Administrador y Cajero --}}
                    <a
                        href="{{ route('productos.show', $producto) }}"
                        class="btn btn-outline-secondary producto-action-btn"
                    >
                        <i class="bi bi-eye me-1"></i>
                        Ver
                    </a>


                    {{-- Solo Administrador --}}
                    @if(auth()->user()->esAdministrador())

                        <a
                            href="{{ route('productos.edit', $producto) }}"
                            class="btn btn-outline-dark producto-action-btn"
                        >
                            <i class="bi bi-pencil me-1"></i>
                            Editar
                        </a>


                        <form
                            action="{{ route('productos.destroy', $producto) }}"
                            method="POST"
                            onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');"
                        >

                            @csrf
                            @method('DELETE')


                            <button
                                type="submit"
                                class="btn btn-outline-danger producto-action-btn w-100"
                            >
                                <i class="bi bi-trash me-1"></i>
                                Eliminar
                            </button>

                        </form>

                    @endif

                </div>

            </div>


        @empty

            <div class="text-center productos-empty">

                <div class="productos-empty-icon mb-3">
                    👕
                </div>


                <h5 class="fw-bold">
                    No hay productos
                </h5>


                <p class="text-muted mb-3">

                    @if(request('buscar'))

                        No encontramos productos que coincidan con
                        "{{ request('buscar') }}".

                    @else

                        Todavía no hay productos registrados.

                    @endif

                </p>


                @if(auth()->user()->esAdministrador())

                    <a
                        href="{{ route('productos.create') }}"
                        class="btn btn-producto-primary"
                    >
                        <i class="bi bi-plus-lg me-1"></i>
                        Crear producto
                    </a>

                @endif

            </div>

        @endforelse

    </div>


    {{-- =====================================================
         PAGINACIÓN
         ===================================================== --}}

    @if($productos->hasPages())

        <div class="mt-4">

            {{ $productos->links('pagination::bootstrap-5') }}

        </div>

    @endif

</div>

@endsection