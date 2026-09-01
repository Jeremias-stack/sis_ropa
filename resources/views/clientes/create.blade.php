@extends('layouts.app')

@section('title', 'Nuevo Cliente')

@section('page-title', 'Clientes')

@section('content')

<div class="cliente-form-page">

    <div class="cliente-form-header">

        <div>

            <span class="cliente-form-eyebrow">
                Clientes
            </span>

            <h1 class="cliente-form-title">
                Nuevo cliente
            </h1>

            <p class="cliente-form-subtitle">
                Registra la información del cliente.
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
            action="{{ route('clientes.store') }}"
        >

            @csrf

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

                    Guardar cliente
                </button>

            </div>

        </form>

    </div>

</div>

@endsection