@extends('layouts.app')

@section('title', 'Clientes')

@section('page-title', 'Clientes')

@section('content')

<div class="clientes-page">

    {{-- =====================================================
         ENCABEZADO
         ===================================================== --}}

    <div class="clientes-header">

        <div>

            <span class="clientes-eyebrow">
                Gestión
            </span>

            <h1 class="clientes-title">
                Clientes
            </h1>

            <p class="clientes-subtitle">
                Administra los clientes registrados en el sistema.
            </p>

        </div>


        <a
            href="{{ route('clientes.create') }}"
            class="clientes-new-button"
        >

            <i class="bi bi-person-plus-fill"></i>

            Nuevo cliente

        </a>

    </div>


    {{-- =====================================================
         MENSAJES
         ===================================================== --}}

    @if(session('success'))

        <div class="clientes-alert success">

            <i class="bi bi-check-circle-fill"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif


    @if(session('error'))

        <div class="clientes-alert error">

            <i class="bi bi-exclamation-circle-fill"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif


    {{-- =====================================================
         BUSCADOR
         ===================================================== --}}

    <div class="clientes-search-card">

        <form
            method="GET"
            action="{{ route('clientes.index') }}"
            class="clientes-search-form"
        >

            <div class="clientes-search-wrapper">

                <i class="bi bi-search clientes-search-icon"></i>

                <input
                    type="text"
                    name="buscar"
                    class="clientes-search-input"
                    placeholder="Buscar por nombre, NIT, correo o teléfono..."
                    value="{{ request('buscar') }}"
                    autocomplete="off"
                >

            </div>


            <button
                type="submit"
                class="clientes-search-button"
            >

                <i class="bi bi-search"></i>

                Buscar

            </button>


            @if(request('buscar'))

                <a
                    href="{{ route('clientes.index') }}"
                    class="clientes-clear-button"
                >

                    <i class="bi bi-x-lg"></i>

                    Limpiar

                </a>

            @endif

        </form>

    </div>


    {{-- =====================================================
         SI EXISTEN CLIENTES
         ===================================================== --}}

    @if($clientes->count())


        {{-- =================================================
             TABLA ESCRITORIO
             ================================================= --}}

        <div class="clientes-table-card d-none d-md-block">

            <div class="table-responsive">

                <table class="clientes-table">

                    <thead>

                        <tr>

                            <th>
                                Cliente
                            </th>

                            <th>
                                Contacto
                            </th>

                            <th>
                                Dirección
                            </th>

                            <th class="text-end">
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($clientes as $cliente)

                            <tr>

                                {{-- Cliente --}}
                                <td>

                                    <div class="cliente-main">

                                        <div class="cliente-avatar">

                                            {{ strtoupper(
                                                substr($cliente->nombre, 0, 1)
                                            ) }}

                                        </div>


                                        <div>

                                            <span class="cliente-name">
                                                {{ $cliente->nombre }}
                                            </span>

                                            <span class="cliente-nit">
                                                NIT: {{ $cliente->nit ?: 'CF' }}
                                            </span>

                                        </div>

                                    </div>

                                </td>


                                {{-- Contacto --}}
                                <td>

                                    <div class="cliente-contact">

                                        <span>

                                            <i class="bi bi-envelope"></i>

                                            {{ $cliente->correo ?: 'Sin correo' }}

                                        </span>

                                        <span>

                                            <i class="bi bi-telephone"></i>

                                            {{ $cliente->telefono ?: 'Sin teléfono' }}

                                        </span>

                                    </div>

                                </td>


                                {{-- Dirección --}}
                                <td>

                                    {{ $cliente->direccion ?: 'Sin dirección registrada' }}

                                </td>


                                {{-- Acciones --}}
                                <td>

                                    <div class="cliente-actions">

                                        <a
                                            href="{{ route('clientes.show', $cliente) }}"
                                            class="cliente-action-button"
                                            title="Ver cliente"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <a
                                            href="{{ route('clientes.edit', $cliente) }}"
                                            class="cliente-action-button"
                                            title="Editar cliente"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <form
                                            method="POST"
                                            action="{{ route('clientes.destroy', $cliente) }}"
                                            onsubmit="return confirm('¿Deseas eliminar este cliente?');"
                                        >

                                            @csrf
                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="cliente-action-button delete"
                                                title="Eliminar cliente"
                                            >

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>


        {{-- =================================================
             TARJETAS MÓVILES
             ================================================= --}}

        <div class="clientes-mobile-list d-md-none">

            @foreach($clientes as $cliente)

                <div class="cliente-mobile-card">


                    <div class="cliente-mobile-top">

                        <div class="cliente-avatar">

                            {{ strtoupper(
                                substr($cliente->nombre, 0, 1)
                            ) }}

                        </div>


                        <div class="cliente-mobile-info">

                            <span class="cliente-mobile-name">
                                {{ $cliente->nombre }}
                            </span>

                            <span class="cliente-mobile-nit">
                                NIT: {{ $cliente->nit ?: 'CF' }}
                            </span>

                        </div>

                    </div>


                    <div class="cliente-mobile-details">

                        <div class="cliente-mobile-detail">

                            <span class="cliente-mobile-detail-label">
                                Teléfono
                            </span>

                            <span class="cliente-mobile-detail-value">

                                {{ $cliente->telefono ?: 'No registrado' }}

                            </span>

                        </div>


                        <div class="cliente-mobile-detail">

                            <span class="cliente-mobile-detail-label">
                                Correo
                            </span>

                            <span class="cliente-mobile-detail-value">

                                {{ $cliente->correo ?: 'No registrado' }}

                            </span>

                        </div>


                        <div class="cliente-mobile-detail">

                            <span class="cliente-mobile-detail-label">
                                Dirección
                            </span>

                            <span class="cliente-mobile-detail-value">

                                {{ $cliente->direccion ?: 'No registrada' }}

                            </span>

                        </div>

                    </div>


                    <div class="cliente-mobile-actions">

                        <a
                            href="{{ route('clientes.show', $cliente) }}"
                            class="cliente-mobile-action"
                        >

                            <i class="bi bi-eye"></i>

                            Ver

                        </a>


                        <a
                            href="{{ route('clientes.edit', $cliente) }}"
                            class="cliente-mobile-action"
                        >

                            <i class="bi bi-pencil"></i>

                            Editar

                        </a>


                        <form
                            method="POST"
                            action="{{ route('clientes.destroy', $cliente) }}"
                            onsubmit="return confirm('¿Deseas eliminar este cliente?');"
                        >

                            @csrf
                            @method('DELETE')


                            <button
                                type="submit"
                                class="cliente-mobile-action delete w-100"
                            >

                                <i class="bi bi-trash"></i>

                                Eliminar

                            </button>

                        </form>

                    </div>

                </div>

            @endforeach

        </div>


        {{-- =================================================
             PAGINACIÓN
             ================================================= --}}

        @if($clientes->hasPages())

            <div class="clientes-pagination">

                {{ $clientes->links('pagination::bootstrap-5') }}

            </div>

        @endif


    {{-- =====================================================
         SIN CLIENTES
         ===================================================== --}}

    @else

        <div class="clientes-table-card">

            <div class="clientes-empty">

                <div class="clientes-empty-icon">
                    <i class="bi bi-people"></i>
                </div>


                @if(request('buscar'))

                    <h3>
                        No encontramos clientes
                    </h3>

                    <p>
                        No existen resultados para
                        “{{ request('buscar') }}”.
                    </p>

                @else

                    <h3>
                        Aún no hay clientes
                    </h3>

                    <p>
                        Registra el primer cliente para comenzar.
                    </p>

                @endif

            </div>

        </div>

    @endif

</div>

@endsection