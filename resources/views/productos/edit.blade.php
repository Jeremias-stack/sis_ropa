@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')

<div class="container py-5 producto-form-page">

    <div class="row justify-content-center">

        <div class="col-12 col-xl-10">

            {{-- Encabezado --}}
            <div class="producto-form-header">

                <span class="producto-form-eyebrow">
                    Inventario
                </span>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">

                    <div>

                        <h1 class="producto-form-title">
                            Editar producto
                        </h1>

                        <p class="producto-form-subtitle">
                            Actualiza la información de
                            <strong>{{ $producto->nombre }}</strong>
                            y mantén el inventario al día.
                        </p>

                    </div>

                    <a
                        href="{{ route('productos.index') }}"
                        class="btn-producto-cancel"
                    >
                        ← Volver al inventario
                    </a>

                </div>

            </div>


            {{-- Imagen actual --}}
            @if($producto->imagen_url)

                <div class="producto-form-card mb-4">

                    <div class="producto-form-section">

                        <div class="producto-form-section-title">
                            Imagen actual
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">

                            <img
                                src="{{ asset('storage/' . $producto->imagen_url) }}"
                                alt="{{ $producto->nombre }}"
                                class="producto-current-image"
                            >

                            <div>

                                <h5 class="fw-bold mb-1">
                                    {{ $producto->nombre }}
                                </h5>

                                <p class="text-muted mb-0">
                                    Puedes seleccionar una nueva imagen en el formulario para reemplazarla.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- Formulario --}}
            <form
                action="{{ route('productos.update', $producto) }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf
                @method('PUT')

                <div class="producto-form-card">

                    @include('productos._form')

                    <div class="producto-form-actions">

                        <a
                            href="{{ route('productos.index') }}"
                            class="btn-producto-cancel"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn-producto-save"
                        >
                            Guardar cambios
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection