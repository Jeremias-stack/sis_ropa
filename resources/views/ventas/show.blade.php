@extends('layouts.app')

@section('title', 'Factura ' . $venta->numero_factura)

@section('page-title', 'Factura')

@section('content')

<div class="factura-page">


    {{-- =====================================================
         ENCABEZADO EXTERIOR
         ===================================================== --}}

    <div class="factura-page-header">

        <div>

            <span class="factura-eyebrow">
                Venta completada
            </span>

            <h1 class="factura-page-title">
                Factura / Recibo
            </h1>

            <p class="factura-page-subtitle">
                Detalle de la venta registrada en el sistema.
            </p>

        </div>


        <div class="factura-header-actions">

            <a
                href="{{ route('ventas.create') }}"
                class="factura-action"
            >

                <i class="bi bi-plus-lg"></i>

                Nueva venta

            </a>


            <button
                type="button"
                class="factura-action primary"
                onclick="window.print()"
            >

                <i class="bi bi-printer-fill"></i>

                Imprimir / PDF

            </button>

        </div>

    </div>


    {{-- =====================================================
         MENSAJE
         ===================================================== --}}

    @if(session('success'))

        <div class="factura-success">

            <i class="bi bi-check-circle-fill"></i>

            {{ session('success') }}

        </div>

    @endif


    {{-- =====================================================
         DOCUMENTO
         ===================================================== --}}

    <article class="factura-document">


        {{-- =================================================
             EMPRESA
             ================================================= --}}

        <header class="factura-company">


            <div class="factura-company-brand">

                <div class="factura-company-logo">

                    <i class="bi bi-bag-heart-fill"></i>

                </div>


                <div>

                    <h2 class="factura-company-name">
                        POS Ropa
                    </h2>

                    <span class="factura-company-type">
                        Tienda de Ropa y Accesorios
                    </span>

                </div>

            </div>


            <div class="factura-number-area">

                <span class="factura-label-small">
                    No. de factura
                </span>

                <span class="factura-number">
                    {{ $venta->numero_factura }}
                </span>

            </div>

        </header>


        {{-- =================================================
             DATOS GENERALES
             ================================================= --}}

        <section class="factura-info-grid">


            {{-- Fecha --}}
            <div class="factura-info-box">

                <span class="factura-label-small">
                    Fecha y hora
                </span>

                <span class="factura-info-value">

                    {{ $venta->fecha->format('d/m/Y') }}

                </span>

                <span class="factura-info-secondary">

                    {{ $venta->fecha->format('H:i:s') }}

                </span>

            </div>


            {{-- Cliente --}}
            <div class="factura-info-box">

                <span class="factura-label-small">
                    Cliente
                </span>

                <span class="factura-info-value">

                    {{ $venta->cliente?->nombre ?? 'Consumidor Final' }}

                </span>

                <span class="factura-info-secondary">

                    NIT:
                    {{ $venta->cliente?->nit ?? 'CF' }}

                </span>

            </div>


            {{-- Cajero --}}
            <div class="factura-info-box">

                <span class="factura-label-small">
                    Atendido por
                </span>

                <span class="factura-info-value">

                    {{ $venta->usuario->nombre ?? 'Usuario del sistema' }}

                </span>

                <span class="factura-info-secondary">

                    {{ ucfirst($venta->usuario->rol ?? 'cajero') }}

                </span>

            </div>

        </section>


        {{-- =================================================
             PRODUCTOS
             ================================================= --}}

        <section class="factura-products">

            <h3 class="factura-section-title">
                Detalle de productos
            </h3>


            <table class="factura-table">

                <thead>

                    <tr>

                        <th>
                            Producto
                        </th>

                        <th class="factura-text-right">
                            Cant.
                        </th>

                        <th class="factura-text-right">
                            Precio
                        </th>

                        <th class="factura-text-right">
                            Subtotal
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($venta->detalles as $detalle)

                        <tr>

                            <td>

                                <span class="factura-product-name">

                                    {{ $detalle->producto->nombre ?? 'Producto' }}

                                </span>


                                <span class="factura-product-code">

                                    {{ $detalle->producto->codigo ?? '—' }}

                                </span>

                            </td>


                            <td class="factura-text-right">

                                {{ $detalle->cantidad }}

                            </td>


                            <td class="factura-text-right">

                                Q{{ number_format(
                                    (float) $detalle->precio_unitario,
                                    2
                                ) }}

                            </td>


                            <td class="factura-text-right">

                                <strong>

                                    Q{{ number_format(
                                        (float) $detalle->subtotal,
                                        2
                                    ) }}

                                </strong>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </section>


        {{-- =================================================
             PAGO Y TOTALES
             ================================================= --}}

        <section class="factura-bottom">


            {{-- Método de pago --}}
            <div>

                <div class="factura-payment-box">

                    <span class="factura-label-small">
                        Método de pago
                    </span>


                    <div class="factura-payment-method">

                        @if($venta->metodo_pago === 'efectivo')

                            <i class="bi bi-cash-stack"></i>

                        @elseif($venta->metodo_pago === 'tarjeta')

                            <i class="bi bi-credit-card-fill"></i>

                        @else

                            <i class="bi bi-qr-code"></i>

                        @endif


                        {{ ucfirst($venta->metodo_pago) }}

                    </div>


                    <p class="factura-payment-text">

                        Estado de la venta:
                        <strong>
                            {{ ucfirst($venta->estado) }}
                        </strong>.

                    </p>

                </div>

            </div>


            {{-- Totales --}}
            <div class="factura-totals">


                <div class="factura-total-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>

                        Q{{ number_format(
                            (float) $venta->subtotal,
                            2
                        ) }}

                    </strong>

                </div>


                <div class="factura-total-row">

                    <span>
                        IVA (12%)
                    </span>

                    <strong>

                        Q{{ number_format(
                            (float) $venta->iva,
                            2
                        ) }}

                    </strong>

                </div>


                @if((float) $venta->descuento > 0)

                    <div class="factura-total-row discount">

                        <span>
                            Descuento
                        </span>

                        <strong>

                            - Q{{ number_format(
                                (float) $venta->descuento,
                                2
                            ) }}

                        </strong>

                    </div>

                @endif


                <div class="factura-total-row final">

                    <span>
                        TOTAL
                    </span>

                    <strong>

                        Q{{ number_format(
                            (float) $venta->total,
                            2
                        ) }}

                    </strong>

                </div>

            </div>

        </section>


        {{-- =================================================
             QR
             ================================================= --}}

        @if($venta->qr_url)

            <section class="factura-qr-section">


                <div class="factura-qr-info">

                    <h3 class="factura-qr-title">
                        Código QR de la factura
                    </h3>

                    <p class="factura-qr-description">

                        Este código QR identifica la factura
                        {{ $venta->numero_factura }} y fue generado
                        mediante una API externa.

                    </p>

                </div>


                <img
                    src="{{ $venta->qr_url }}"
                    alt="Código QR de la factura {{ $venta->numero_factura }}"
                    class="factura-qr-image"
                >

            </section>

        @endif


        {{-- =================================================
             PIE
             ================================================= --}}

        <footer class="factura-footer">

            Gracias por su compra · POS Ropa ·
            Documento generado automáticamente por el sistema.

        </footer>

    </article>

</div>

@endsection