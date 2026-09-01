@extends('layouts.app')

@section('title', 'Nuevo Producto')

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
                            Nuevo producto
                        </h1>

                        <p class="producto-form-subtitle">
                            Registra una nueva prenda o accesorio y completa la información necesaria para mantener el inventario actualizado.
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


            {{-- Formulario --}}
            <form
                action="{{ route('productos.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >

                @csrf

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
                            Guardar producto
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection