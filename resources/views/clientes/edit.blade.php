@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('page-title', 'Clientes')

@section('content')

<div class="cliente-form-page">

    <div class="cliente-form-header">

        <div>

            <span class="cliente-form-eyebrow">
                Clientes
            </span>

            <h1 class="cliente-form-title">
                Editar cliente
            </h1>

            <p class="cliente-form-subtitle">
                Actualiza la información de {{ $cliente->nombre }}.
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


    <div class="cliente-form-card">

        <form
            method="POST"
            action="{{ route('clientes.update', $cliente) }}"
        >

            @csrf
            @method('PUT')

            @include('clientes._form')


            <div class="cliente-form-actions">

                <a
                    href="{{ route('clientes.index') }}"
                    class="cliente-form-cancel"
                >
                    Cancelar
                </a>


                <button
                    type="submit"
                    class="cliente-form-save"
                >
                    <i class="bi bi-check-lg"></i>

                    Guardar cambios
                </button>

            </div>

        </form>

    </div>

</div>

@endsection