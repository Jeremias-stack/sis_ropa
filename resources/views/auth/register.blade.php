@extends('layouts.app')

@section('title', 'Crear cuenta | POS Ropa')

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
                        NUEVO USUARIO
                    </span>

                    <h1>
                        Crea tu cuenta
                        <span>y comienza a trabajar.</span>
                    </h1>

                    <p>
                        Regístrate para acceder a las herramientas
                        necesarias para gestionar clientes, consultar
                        productos y realizar ventas.
                    </p>


                    {{-- Características --}}
                    <div class="auth-features">


                        <div class="auth-feature">

                            <div class="auth-feature-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>

                            <div>

                                <strong>
                                    Cuenta de Cajero
                                </strong>

                                <small>
                                    Acceso únicamente a funciones autorizadas
                                </small>

                            </div>

                        </div>


                        <div class="auth-feature">

                            <div class="auth-feature-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>

                            <div>

                                <strong>
                                    Registra ventas
                                </strong>

                                <small>
                                    Utiliza el punto de venta del sistema
                                </small>

                            </div>

                        </div>


                        <div class="auth-feature">

                            <div class="auth-feature-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>

                            <div>

                                <strong>
                                    Acceso seguro
                                </strong>

                                <small>
                                    Tu contraseña se almacena protegida
                                </small>

                            </div>

                        </div>


                    </div>

                </div>


                {{-- Footer --}}
                <div class="auth-showcase-footer">

                    <i class="bi bi-lock-fill"></i>

                    <span>
                        Sistema con control de acceso por roles
                    </span>

                </div>

            </div>

        </section>



        {{-- =====================================================
             PANEL DERECHO
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
                        REGISTRO
                    </span>

                    <h2>
                        Crear una cuenta
                    </h2>

                    <p>
                        Completa la información para registrarte
                        como usuario del sistema.
                    </p>

                </div>



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
                                Revisa la información
                            </strong>

                            <span>
                                Algunos campos contienen errores.
                            </span>

                        </div>

                    </div>

                @endif



                {{-- =================================================
                     FORMULARIO REGISTRO
                     ================================================= --}}

                <form
                    method="POST"
                    action="{{ route('register.store') }}"
                    class="auth-form auth-register-form"
                    autocomplete="off"
                >

                    @csrf



                    {{-- Nombre --}}
                    <div class="auth-field">

                        <label for="registerNombre">

                            Nombre completo

                        </label>


                        <div class="auth-input-wrapper">

                            <span class="auth-input-icon">

                                <i class="bi bi-person"></i>

                            </span>


                            <input
                                type="text"
                                id="registerNombre"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                placeholder="Ej. Juan Pérez"
                                autocomplete="name"
                                required
                                autofocus
                                class="@error('nombre') is-invalid @enderror"
                            >

                        </div>


                        @error('nombre')

                            <span class="auth-field-error">

                                <i class="bi bi-exclamation-circle"></i>

                                {{ $message }}

                            </span>

                        @enderror

                    </div>



                    {{-- Correo --}}
                    <div class="auth-field">

                        <label for="registerCorreo">

                            Correo electrónico

                        </label>


                        <div class="auth-input-wrapper">

                            <span class="auth-input-icon">

                                <i class="bi bi-envelope"></i>

                            </span>


                            <input
                                type="email"
                                id="registerCorreo"
                                name="correo"
                                value="{{ old('correo') }}"
                                placeholder="ejemplo@correo.com"
                                autocomplete="email"
                                required
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

                        <label for="registerPassword">

                            Contraseña

                        </label>


                        <div class="auth-input-wrapper">

                            <span class="auth-input-icon">

                                <i class="bi bi-lock"></i>

                            </span>


                            <input
                                type="password"
                                id="registerPassword"
                                name="password"
                                placeholder="Crea una contraseña"
                                autocomplete="new-password"
                                required
                                class="@error('password') is-invalid @enderror"
                            >


                            <button
                                type="button"
                                class="auth-password-toggle"
                                id="registerPasswordToggle"
                                aria-label="Mostrar contraseña"
                            >

                                <i
                                    class="bi bi-eye"
                                    id="registerPasswordIcon"
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



                    {{-- Confirmación --}}
                    <div class="auth-field">

                        <label for="registerPasswordConfirmation">

                            Confirmar contraseña

                        </label>


                        <div class="auth-input-wrapper">

                            <span class="auth-input-icon">

                                <i class="bi bi-shield-lock"></i>

                            </span>


                            <input
                                type="password"
                                id="registerPasswordConfirmation"
                                name="password_confirmation"
                                placeholder="Repite tu contraseña"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="auth-password-toggle"
                                id="registerConfirmationToggle"
                                aria-label="Mostrar contraseña"
                            >

                                <i
                                    class="bi bi-eye"
                                    id="registerConfirmationIcon"
                                ></i>

                            </button>

                        </div>

                    </div>



                    {{-- Rol --}}
                    <div class="auth-role-box">

                        <div class="auth-role-icon">

                            <i class="bi bi-person-badge"></i>

                        </div>


                        <div>

                            <strong>
                                Perfil de Cajero
                            </strong>

                            <span>
                                Las cuentas creadas desde este formulario
                                reciben automáticamente permisos de Cajero.
                            </span>

                        </div>

                    </div>



                    {{-- Botón --}}
                    <button
                        type="submit"
                        class="auth-submit"
                    >

                        <span>
                            Crear cuenta
                        </span>

                        <i class="bi bi-person-plus"></i>

                    </button>

                </form>



                {{-- Volver --}}
                <div class="auth-switch">

                    <span>
                        ¿Ya tienes una cuenta?
                    </span>

                    <a href="{{ route('login') }}">
                        Iniciar sesión
                    </a>

                </div>



                {{-- Seguridad --}}
                <div class="auth-security">

                    <i class="bi bi-shield-lock-fill"></i>

                    <span>
                        Tus credenciales se almacenan de forma protegida
                    </span>

                </div>

            </div>

        </section>

    </div>

</div>



{{-- =========================================================
     JAVASCRIPT REGISTRO
     ========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {


    function configurarToggle(
        inputId,
        buttonId,
        iconId
    ) {

        const input =
            document.getElementById(inputId);

        const button =
            document.getElementById(buttonId);

        const icon =
            document.getElementById(iconId);


        if (
            !input ||
            !button ||
            !icon
        ) {

            return;

        }


        button.addEventListener(
            'click',
            function () {

                const visible =
                    input.type === 'text';


                input.type =
                    visible
                        ? 'password'
                        : 'text';


                icon.className =
                    visible
                        ? 'bi bi-eye'
                        : 'bi bi-eye-slash';


                button.setAttribute(
                    'aria-label',
                    visible
                        ? 'Mostrar contraseña'
                        : 'Ocultar contraseña'
                );

            }
        );

    }


    configurarToggle(
        'registerPassword',
        'registerPasswordToggle',
        'registerPasswordIcon'
    );


    configurarToggle(
        'registerPasswordConfirmation',
        'registerConfirmationToggle',
        'registerConfirmationIcon'
    );

});

</script>

@endsection