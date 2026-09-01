@extends('layouts.app')

@section('title', 'Detalle del Cliente')

@section('page-title', 'Clientes')

@section('content')

<div class="cliente-form-page">

    <div class="cliente-form-header">

        <div>

            <span class="cliente-form-eyebrow">
                Clientes
            </span>

            <h1 class="cliente-form-title">
                Detalle del cliente
            </h1>

            <p class="cliente-form-subtitle">
                Información registrada en el sistema.
            </p>

        </div>


        <a
            href="{{ route('clientes.index') }}"
            class="cliente-back-button"
        >
            <i class="bi bi-arrow-left"></i>
            Volver
        </a>

    </div>


    <div class="cliente-detail-card">

        <div class="cliente-detail-hero">

            <div class="cliente-detail-avatar">

                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}

            </div>


            <div>

                <h2 class="cliente-detail-name">
                    {{ $cliente->nombre }}
                </h2>

                <div class="cliente-detail-nit">
                    NIT: {{ $cliente->nit ?: 'CF' }}
                </div>

            </div>

        </div>


        <div class="cliente-detail-grid">

            <div class="cliente-detail-item">

                <span class="cliente-detail-label">
                    Correo electrónico
                </span>

                <span class="cliente-detail-value">
                    {{ $cliente->correo ?: 'No registrado' }}
                </span>

            </div>


            <div class="cliente-detail-item">

                <span class="cliente-detail-label">
                    Teléfono
                </span>

                <span class="cliente-detail-value">
                    {{ $cliente->telefono ?: 'No registrado' }}
                </span>

            </div>


            <div class="cliente-detail-item">

                <span class="cliente-detail-label">
                    Dirección
                </span>

                <span class="cliente-detail-value">
                    {{ $cliente->direccion ?: 'No registrada' }}
                </span>

            </div>


            <div class="cliente-detail-item">

                <span class="cliente-detail-label">
                    Registrado
                </span>

                <span class="cliente-detail-value">
                    {{ $cliente->created_at->format('d/m/Y H:i') }}
                </span>

            </div>

        </div>


        <div class="cliente-detail-actions">

            <a
                href="{{ route('clientes.edit', $cliente) }}"
                class="cliente-form-save text-decoration-none"
            >
                <i class="bi bi-pencil"></i>

                Editar cliente
            </a>


            <a
                href="{{ route('clientes.index') }}"
                class="cliente-form-cancel"
            >
                Ver todos los clientes
            </a>

        </div>

    </div>

</div>

@endsection