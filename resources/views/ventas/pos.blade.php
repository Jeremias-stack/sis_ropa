@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('page-title', 'Punto de Venta')

@section('content')

<div class="pos-page">

    {{-- =====================================================
         ENCABEZADO
         ===================================================== --}}

    <div class="pos-header">

        <div>

            <span class="pos-eyebrow">
                Ventas
            </span>

            <h1 class="pos-title">
                Punto de Venta
            </h1>

            <p class="pos-subtitle">
                Selecciona productos, prepara el carrito y registra la venta.
            </p>

        </div>


        <div class="pos-status">

            <span class="pos-status-dot"></span>

            Caja disponible

        </div>

    </div>


    {{-- =====================================================
         ERRORES
         ===================================================== --}}

    @if($errors->any())

        <div class="pos-error">

            <i class="bi bi-exclamation-circle-fill"></i>

            <div>

                @foreach($errors->all() as $error)

                    <div>
                        {{ $error }}
                    </div>

                @endforeach

            </div>

        </div>

    @endif


    {{-- =====================================================
         FORMULARIO DE VENTA
         ===================================================== --}}

    <form
        method="POST"
        action="{{ route('ventas.store') }}"
        id="posForm"
    >

        @csrf


        <div class="pos-layout">


            {{-- =================================================
                 PRODUCTOS
                 ================================================= --}}

            <section class="pos-panel">

                <div class="pos-panel-header">

                    <h2 class="pos-panel-title">
                        Productos
                    </h2>

                    <p class="pos-panel-subtitle">
                        Haz clic sobre un producto para agregarlo.
                    </p>

                </div>


                {{-- Buscador --}}
                <div class="pos-search-area">

                    <div class="pos-search-wrapper">

                        <i class="bi bi-search pos-search-icon"></i>

                        <input
                            type="text"
                            id="posProductSearch"
                            class="pos-search"
                            placeholder="Buscar por nombre o código..."
                            autocomplete="off"
                        >

                    </div>

                </div>


                {{-- Productos --}}
                <div
                    class="pos-products"
                    id="posProducts"
                >

                    @foreach($productos as $producto)

                        <article
                            class="pos-product-card"
                            data-product-id="{{ $producto->id }}"
                            data-product-name="{{ $producto->nombre }}"
                            data-product-code="{{ $producto->codigo }}"
                            data-product-price="{{ $producto->precio }}"
                            data-product-stock="{{ $producto->stock }}"
                            role="button"
                            tabindex="0"
                        >

                            <div class="pos-product-image-wrapper">

                                @if($producto->imagen_url)

                                    <img
                                        src="{{ asset('storage/' . $producto->imagen_url) }}"
                                        alt="{{ $producto->nombre }}"
                                        class="pos-product-image"
                                    >

                                @else

                                    <div class="pos-product-no-image">

                                        <i class="bi bi-bag"></i>

                                    </div>

                                @endif

                            </div>


                            <div class="pos-product-content">

                                <span class="pos-product-category">

                                    {{ $producto->categoria->nombre ?? 'Sin categoría' }}

                                </span>


                                <h3 class="pos-product-name">

                                    {{ $producto->nombre }}

                                </h3>


                                <span class="pos-product-code">

                                    {{ $producto->codigo }}

                                </span>


                                <div class="pos-product-bottom">

                                    <span class="pos-product-price">

                                        Q{{ number_format($producto->precio, 2) }}

                                    </span>


                                    <span
                                        class="pos-product-stock {{ $producto->stock < 5 ? 'low' : '' }}"
                                    >

                                        {{ $producto->stock }} disp.

                                    </span>

                                </div>

                            </div>

                        </article>

                    @endforeach


                    <div
                        class="pos-no-results"
                        id="posNoResults"
                    >

                        <i class="bi bi-search"></i>

                        No se encontraron productos.

                    </div>

                </div>

            </section>


            {{-- =================================================
                 CARRITO
                 ================================================= --}}

            <aside class="pos-panel pos-cart-panel">

                <div class="pos-panel-header">

                    <h2 class="pos-panel-title">

                        <i class="bi bi-cart3 me-1"></i>

                        Carrito

                    </h2>

                    <p class="pos-panel-subtitle">

                        <span id="posCartCount">
                            0
                        </span>

                        productos agregados

                    </p>

                </div>


                {{-- Productos del carrito --}}
                <div
                    class="pos-cart-items"
                    id="posCartItems"
                >

                    <div
                        class="pos-cart-empty"
                        id="posCartEmpty"
                    >

                        <div class="pos-cart-empty-icon">

                            <i class="bi bi-cart"></i>

                        </div>

                        <strong>
                            Tu carrito está vacío
                        </strong>

                        <span>
                            Selecciona productos para comenzar.
                        </span>

                    </div>

                </div>


                {{-- Inputs ocultos generados con JS --}}
                <div id="posHiddenInputs"></div>


                {{-- =================================================
                     DATOS DE VENTA
                     ================================================= --}}

                <div class="pos-sale-data">

                    <div class="mb-3">

                        <label
                            for="cliente_id"
                            class="form-label pos-label"
                        >
                            Cliente
                        </label>

                        <select
                            name="cliente_id"
                            id="cliente_id"
                            class="form-select pos-control"
                        >

                            <option value="">
                                Consumidor Final
                            </option>

                            @foreach($clientes as $cliente)

                                <option
                                    value="{{ $cliente->id }}"
                                    @selected(old('cliente_id') == $cliente->id)
                                >

                                    {{ $cliente->nombre }}

                                    — {{ $cliente->nit }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="row g-2">

                        <div class="col-6">

                            <label
                                for="descuento"
                                class="form-label pos-label"
                            >
                                Descuento
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    Q
                                </span>

                                <input
                                    type="number"
                                    name="descuento"
                                    id="descuento"
                                    class="form-control pos-control"
                                    value="{{ old('descuento', 0) }}"
                                    min="0"
                                    step="0.01"
                                >

                            </div>

                        </div>


                        <div class="col-6">

                            <label
                                for="metodo_pago"
                                class="form-label pos-label"
                            >
                                Método de pago
                            </label>

                            <select
                                name="metodo_pago"
                                id="metodo_pago"
                                class="form-select pos-control"
                                required
                            >

                                <option value="efectivo">
                                    Efectivo
                                </option>

                                <option value="tarjeta">
                                    Tarjeta
                                </option>

                                <option value="qr">
                                    QR
                                </option>

                            </select>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     TOTALES
                     ================================================= --}}

                <div class="pos-totals">

                    <div class="pos-total-row">

                        <span>
                            Subtotal
                        </span>

                        <strong id="posSubtotal">
                            Q0.00
                        </strong>

                    </div>


                    <div class="pos-total-row">

                        <span>
                            IVA (12%)
                        </span>

                        <strong id="posIva">
                            Q0.00
                        </strong>

                    </div>


                    <div class="pos-total-row discount">

                        <span>
                            Descuento
                        </span>

                        <strong id="posDiscount">
                            - Q0.00
                        </strong>

                    </div>


                    <div class="pos-total-row final">

                        <span>
                            Total
                        </span>

                        <strong id="posTotal">
                            Q0.00
                        </strong>

                    </div>

                </div>


                {{-- =================================================
                     COBRAR
                     ================================================= --}}

                <div class="pos-checkout-area">

                    <button
                        type="submit"
                        class="pos-checkout-button"
                        id="posCheckoutButton"
                        disabled
                    >

                        <i class="bi bi-cash-coin"></i>

                        Cobrar venta

                    </button>

                </div>

            </aside>

        </div>

    </form>

</div>

@endsection