<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'POS Ropa')
    </title>


    {{-- Google Fonts --}}
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- Bootstrap 5 --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    {{-- Bootstrap Icons --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    {{-- Archivos propios --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>


<body>


@auth

<div class="app-shell">


    {{-- =====================================================
         SIDEBAR
         ===================================================== --}}

    <aside
        class="app-sidebar"
        id="appSidebar"
    >


        {{-- Marca --}}
        <a
            href="{{ route('dashboard') }}"
            class="sidebar-brand"
        >

            <div class="sidebar-logo">
                <i class="bi bi-bag-heart-fill"></i>
            </div>


            <div class="sidebar-brand-text">

                <span class="sidebar-brand-title">
                    POS Ropa
                </span>

                <span class="sidebar-brand-subtitle">
                    Fashion Store
                </span>

            </div>

        </a>


        {{-- =================================================
             PRINCIPAL
             ================================================= --}}

        <div class="sidebar-section">

            <span class="sidebar-section-title">
                Principal
            </span>


            <nav class="sidebar-nav">


                {{-- Dashboard --}}
                <a
                    href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                >

                    <span class="sidebar-icon">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </span>

                    <span class="sidebar-link-text">
                        Dashboard
                    </span>

                </a>


                {{-- Productos --}}
                <a
                    href="{{ route('productos.index') }}"
                    class="sidebar-link {{ request()->routeIs('productos.*') ? 'active' : '' }}"
                >

                    <span class="sidebar-icon">
                        <i class="bi bi-box-seam-fill"></i>
                    </span>

                    <span class="sidebar-link-text">
                        Productos
                    </span>

                </a>

            </nav>

        </div>


        {{-- =================================================
             GESTIÓN
             ================================================= --}}

        <div class="sidebar-section">

            <span class="sidebar-section-title">
                Gestión
            </span>


            <nav class="sidebar-nav">


                {{-- Clientes --}}
                <a
                    href="{{ route('clientes.index') }}"
                    class="sidebar-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
                >

                    <span class="sidebar-icon">
                        <i class="bi bi-people-fill"></i>
                    </span>

                    <span class="sidebar-link-text">
                        Clientes
                    </span>

                </a>


                {{-- Punto de Venta --}}
                <a
                    href="{{ route('ventas.create') }}"
                    class="sidebar-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}"
                >

                    <span class="sidebar-icon">
                        <i class="bi bi-cart-check-fill"></i>
                    </span>

                    <span class="sidebar-link-text">
                        Punto de Venta
                    </span>

                </a>


                {{-- Reportes: solo Administrador --}}
                @if(auth()->user()->esAdministrador())

                    <a
                        href="{{ route('reportes.index') }}"
                        class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
                    >

                        <span class="sidebar-icon">
                            <i class="bi bi-bar-chart-fill"></i>
                        </span>

                        <span class="sidebar-link-text">
                            Reportes
                        </span>

                    </a>

                @endif

            </nav>

        </div>


        {{-- =================================================
             INFORMACIÓN DEL USUARIO
             ================================================= --}}

        <div class="sidebar-user">


            <div class="sidebar-user-info">


                <div class="sidebar-avatar">

                    {{ strtoupper(
                        substr(auth()->user()->nombre, 0, 1)
                    ) }}

                </div>


                <div class="sidebar-user-details">

                    <span class="sidebar-user-name">

                        {{ auth()->user()->nombre }}

                    </span>


                    <span class="sidebar-user-role">

                        @if(auth()->user()->esAdministrador())

                            <i class="bi bi-shield-check me-1"></i>
                            Administrador

                        @else

                            <i class="bi bi-person-badge me-1"></i>
                            Cajero

                        @endif

                    </span>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('logout') }}"
            >

                @csrf


                <button
                    type="submit"
                    class="sidebar-logout"
                >

                    <i class="bi bi-box-arrow-left"></i>

                    Cerrar sesión

                </button>

            </form>

        </div>

    </aside>


    {{-- =====================================================
         OVERLAY MÓVIL
         ===================================================== --}}

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>


    {{-- =====================================================
         CONTENIDO PRINCIPAL
         ===================================================== --}}

    <main class="app-main">


        <header class="app-topbar">


            <div class="d-flex align-items-center gap-3">


                <button
                    type="button"
                    class="mobile-menu-button"
                    id="mobileMenuButton"
                    aria-label="Abrir menú"
                    aria-expanded="false"
                >

                    <i class="bi bi-list"></i>

                </button>


                <h2 class="topbar-title">

                    @yield('page-title', 'Sistema POS')

                </h2>

            </div>


            <div class="topbar-user">

                @if(auth()->user()->esAdministrador())

                    <i class="bi bi-shield-check me-1"></i>

                @else

                    <i class="bi bi-person-badge me-1"></i>

                @endif


                {{ auth()->user()->nombre }}

            </div>

        </header>


        <div class="app-content">

            @yield('content')

        </div>

    </main>

</div>


@else


<main>

    @yield('content')

</main>


@endauth


{{-- Bootstrap JavaScript --}}
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>