@extends('layouts.app')

@section('title', 'Iniciar sesión | POS Ropa')

@section('content')

<div class="auth-page">

    <div class="auth-container">


        {{-- =====================================================
             PANEL IZQUIERDO
             ===================================================== --}}

        <section class="auth-showcase">

            <div class="auth-showcase-content">


                {{-- Logo --}}
                <div class="auth-brand">

                    <div class="auth-brand-icon">
                        <i class="bi bi-bag-heart-fill"></i>
                    </div>

                    <div>

                        <span class="auth-brand-name">
                            POS Ropa
                        </span>

                        <span class="auth-brand-caption">
                            Fashion Store
                        </span>

                    </div>

                </div>


                {{-- Información --}}
                <div class="auth-showcase-main">

                    <span class="auth-badge">
                        SISTEMA DE GESTIÓN
                    </span>

                    <h1>
                        Gestiona tu tienda
                        <span>de forma simple.</span>
                    </h1>

                    <p>
                        Administra productos, inventario, clientes,
                        ventas y reportes desde una plataforma
                        rápida, moderna y fácil de utilizar.
                    </p>


                    {{-- Características --}}
                    <div class="auth-features">


                        <div class="auth-feature">

                            <div class="auth-feature-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>

                            <div>

                                <strong>
                                    Control de inventario
                                </strong>

                                <small>
                                    Productos, existencias y alertas de stock
                                </small>

                            </div>

                        </div>


                        <div class="auth-feature">

                            <div class="auth-feature-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>

                            <div>

                                <strong>
                                    Punto de Venta
                                </strong>

                                <small>
                                    Registra ventas y genera facturas
                                </small>

                            </div>

                        </div>


                        <div class="auth-feature">

                            <div class="auth-feature-icon">
                                <i class="bi bi-bar-chart"></i>
                            </div>

                            <div>

                                <strong>
                                    Reportes
                                </strong>

                                <small>
                                    Consulta información de tus ventas
                                </small>

                            </div>

                        </div>


                    </div>

                </div>


                {{-- Footer --}}
                <div class="auth-showcase-footer">

                    <i class="bi bi-shield-check"></i>

                    <span>
                        Acceso protegido al sistema
                    </span>

                </div>

            </div>

        </section>



        {{-- =====================================================
             FORMULARIO
             ===================================================== --}}

        <section class="auth-form-section">

            <div class="auth-form-wrapper">


                {{-- Logo móvil --}}
                <div class="auth-mobile-brand">

                    <div class="auth-mobile-brand-icon">
                        <i class="bi bi-bag-heart-fill"></i>
                    </div>

                    <div>

                        <span class="auth-mobile-brand-name">
                            POS Ropa
                        </span>

                        <span class="auth-mobile-brand-caption">
                            Fashion Store
                        </span>

                    </div>

                </div>



                {{-- Encabezado --}}
                <div class="auth-form-header">

                    <span class="auth-form-eyebrow">
                        BIENVENIDO
                    </span>

                    <h2>
                        Iniciar sesión
                    </h2>

                    <p>
                        Ingresa tus credenciales para acceder
                        al sistema POS.
                    </p>

                </div>



                {{-- =================================================
                     MENSAJE DE ÉXITO
                     ================================================= --}}

                @if(session('success'))

                    <div class="auth-alert auth-alert-success">

                        <div class="auth-alert-icon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>

                        <div>

                            <strong>
                                Operación realizada
                            </strong>

                            <span>
                                {{ session('success') }}
                            </span>

                        </div>

                    </div>

                @endif



                {{-- =================================================
                     ERRORES
                     ================================================= --}}

                @if($errors->any())

                    <div class="auth-alert auth-alert-danger">

                        <div class="auth-alert-icon">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>

                        <div>

                            <strong>
                                No fue posible iniciar sesión
                            </strong>

                            <span>
                                Verifica el correo y la contraseña ingresados.
                            </span>

                        </div>

                    </div>

                @endif



                {{-- =================================================
                     FORMULARIO LOGIN
                     ================================================= --}}

                <form
                    method="POST"
                    action="{{ route('login.process') }}"
                    class="auth-form"
                    autocomplete="off"
                >

                    @csrf


                    {{-- Correo --}}
                    <div class="auth-field">

                        <label for="loginCorreo">

                            Correo electrónico

                        </label>


                        <div class="auth-input-wrapper">

                            <span class="auth-input-icon">

                                <i class="bi bi-envelope"></i>

                            </span>


                            <input
                                type="email"
                                id="loginCorreo"
                                name="correo"
                                value=""
                                placeholder="ejemplo@correo.com"
                                autocomplete="off"
                                required
                                autofocus
                                class="@error('correo') is-invalid @enderror"
                            >

                        </div>


                        @error('correo')

                            <span class="auth-field-error">

                                <i class="bi bi-exclamation-circle"></i>

                                {{ $message }}

                            </span>

                        @enderror

                    </div>



                    {{-- Contraseña --}}
                    <div class="auth-field">

                        <label for="loginPassword">

                            Contraseña

                        </label>


                        <div class="auth-input-wrapper">

                            <span class="auth-input-icon">

                                <i class="bi bi-lock"></i>

                            </span>


                            <input
                                type="password"
                                id="loginPassword"
                                name="password"
                                value=""
                                placeholder="Ingresa tu contraseña"
                                autocomplete="new-password"
                                required
                                class="@error('password') is-invalid @enderror"
                            >


                            <button
                                type="button"
                                class="auth-password-toggle"
                                id="loginPasswordToggle"
                                aria-label="Mostrar contraseña"
                            >

                                <i
                                    class="bi bi-eye"
                                    id="loginPasswordIcon"
                                ></i>

                            </button>

                        </div>


                        @error('password')

                            <span class="auth-field-error">

                                <i class="bi bi-exclamation-circle"></i>

                                {{ $message }}

                            </span>

                        @enderror

                    </div>



                    {{-- Botón --}}
                    <button
                        type="submit"
                        class="auth-submit"
                    >

                        <span>
                            Iniciar sesión
                        </span>

                        <i class="bi bi-arrow-right"></i>

                    </button>

                </form>



                {{-- Registro --}}
                <div class="auth-switch">

                    <span>
                        ¿No tienes una cuenta?
                    </span>

                    <a href="{{ route('register') }}">
                        Crear cuenta
                    </a>

                </div>



                {{-- Seguridad --}}
                <div class="auth-security">

                    <i class="bi bi-shield-lock-fill"></i>

                    <span>
                        Acceso protegido mediante autenticación segura
                    </span>

                </div>

            </div>

        </section>

    </div>

</div>



{{-- =========================================================
     JAVASCRIPT LOGIN
     ========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const correo =
        document.getElementById('loginCorreo');

    const password =
        document.getElementById('loginPassword');

    const toggle =
        document.getElementById('loginPasswordToggle');

    const icon =
        document.getElementById('loginPasswordIcon');


    /*
     * Limpiar los campos al entrar al Login.
     */
    if (correo) {
        correo.value = '';
    }

    if (password) {
        password.value = '';
    }


    /*
     * Algunos navegadores intentan restaurar
     * credenciales después de cargar la página.
     */
    setTimeout(function () {

        if (correo) {
            correo.value = '';
        }

        if (password) {
            password.value = '';
        }

    }, 150);


    /*
     * Mostrar / ocultar contraseña.
     */
    if (
        password &&
        toggle &&
        icon
    ) {

        toggle.addEventListener(
            'click',
            function () {

                const visible =
                    password.type === 'text';


                password.type =
                    visible
                        ? 'password'
                        : 'text';


                icon.className =
                    visible
                        ? 'bi bi-eye'
                        : 'bi bi-eye-slash';


                toggle.setAttribute(
                    'aria-label',
                    visible
                        ? 'Mostrar contraseña'
                        : 'Ocultar contraseña'
                );

            }
        );

    }

});

</script>

@endsection