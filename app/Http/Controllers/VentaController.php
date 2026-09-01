<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    /**
     * Mostrar la interfaz principal del POS.
     */
    public function create()
    {
        $productos = Producto::with('categoria')
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->orderBy('nombre')
            ->get();

        $clientes = Cliente::orderBy('nombre')
            ->get();

        return view('ventas.pos', compact(
            'productos',
            'clientes'
        ));
    }


    /**
     * Registrar una nueva venta.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id' => [
                'nullable',
                'exists:clientes,id',
            ],

            'descuento' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'metodo_pago' => [
                'required',
                'in:efectivo,tarjeta,qr',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.producto_id' => [
                'required',
                'integer',
                'exists:productos,id',
            ],

            'items.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);


        /*
         * Agrupamos productos repetidos.
         *
         * Aunque desde JavaScript evitaremos duplicados,
         * hacemos esta validación también en el servidor.
         */
        $items = collect($datos['items'])
            ->groupBy('producto_id')
            ->map(function ($grupo, $productoId) {

                return [
                    'producto_id' => (int) $productoId,

                    'cantidad' => $grupo->sum(
                        fn ($item) => (int) $item['cantidad']
                    ),
                ];

            })
            ->values();


        $venta = DB::transaction(function () use (
            $datos,
            $items
        ) {

            $subtotal = 0;

            $productosVenta = [];


            /*
             * Comprobar nuevamente productos y existencias.
             *
             * Nunca confiamos en el precio que mande
             * JavaScript: el precio real se obtiene de MySQL.
             */
            foreach ($items as $item) {

                $producto = Producto::whereKey(
                    $item['producto_id']
                )
                    ->lockForUpdate()
                    ->first();


                if (!$producto) {

                    throw ValidationException::withMessages([
                        'items' => 'Uno de los productos ya no existe.',
                    ]);

                }


                if (!$producto->activo) {

                    throw ValidationException::withMessages([
                        'items' => "El producto {$producto->nombre} está inactivo.",
                    ]);

                }


                $cantidad = (int) $item['cantidad'];


                if ($cantidad > $producto->stock) {

                    throw ValidationException::withMessages([
                        'items' =>
                            "Stock insuficiente para {$producto->nombre}. "
                            . "Disponibles: {$producto->stock}.",
                    ]);

                }


                $precioUnitario = (float) $producto->precio;

                $subtotalProducto = round(
                    $precioUnitario * $cantidad,
                    2
                );


                $subtotal += $subtotalProducto;


                $productosVenta[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalProducto,
                ];

            }


            $subtotal = round($subtotal, 2);

            /*
             * IVA del 12%.
             */
            $iva = round(
                $subtotal * 0.12,
                2
            );


            /*
             * El descuento se manejará como cantidad monetaria.
             *
             * Ejemplo:
             * descuento = 25
             * significa Q25.00 de descuento.
             */
            $descuento = round(
                (float) ($datos['descuento'] ?? 0),
                2
            );


            $totalAntesDescuento = round(
                $subtotal + $iva,
                2
            );


            if ($descuento > $totalAntesDescuento) {

                throw ValidationException::withMessages([
                    'descuento' =>
                        'El descuento no puede ser mayor al total de la venta.',
                ]);

            }


            $total = round(
                $totalAntesDescuento - $descuento,
                2
            );


            /*
             * Generar número único de factura.
             */
            do {

                $numeroFactura =
                    'FAC-'
                    . now()->format('Ymd-His')
                    . '-'
                    . Str::upper(Str::random(4));

            } while (
                Venta::where(
                    'numero_factura',
                    $numeroFactura
                )->exists()
            );


            /*
             * Crear la venta principal.
             */
            $venta = Venta::create([
                'numero_factura' => $numeroFactura,

                'usuario_id' => auth()->id(),

                'cliente_id' =>
                    $datos['cliente_id'] ?? null,

                'fecha' => now(),

                'subtotal' => $subtotal,

                'iva' => $iva,

                'descuento' => $descuento,

                'total' => $total,

                'metodo_pago' =>
                    $datos['metodo_pago'],

                'estado' => 'completada',
            ]);


            /*
             * Crear los detalles y descontar stock.
             */
            foreach ($productosVenta as $item) {

                DetalleVenta::create([
                    'venta_id' => $venta->id,

                    'producto_id' =>
                        $item['producto']->id,

                    'cantidad' =>
                        $item['cantidad'],

                    'precio_unitario' =>
                        $item['precio_unitario'],

                    'subtotal' =>
                        $item['subtotal'],
                ]);


                $item['producto']->decrement(
                    'stock',
                    $item['cantidad']
                );

            }


            /*
             * Registrar el pago.
             */
            Pago::create([
                'venta_id' => $venta->id,

                'monto' => $total,

                'metodo' =>
                    $datos['metodo_pago'],

                'referencia_api' => null,

                'fecha' => now(),
            ]);


            /*
             * QR externo de la factura.
             *
             * Esta será una de nuestras APIs externas.
             */
            $contenidoQR =
                'Factura: '
                . $venta->numero_factura
                . ' | Total: Q'
                . number_format($total, 2, '.', '');


            $qrUrl =
                'https://api.qrserver.com/v1/create-qr-code/'
                . '?size=200x200'
                . '&data='
                . urlencode($contenidoQR);


            $venta->update([
                'qr_url' => $qrUrl,
            ]);


            return $venta;

        });


        return redirect()
            ->route(
                'ventas.show',
                $venta
            )
            ->with(
                'success',
                'Venta registrada correctamente.'
            );
    }


    /**
     * Mostrar factura / recibo.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'usuario',
            'cliente',
            'detalles.producto',
            'pagos',
        ]);


        return view(
            'ventas.show',
            compact('venta')
        );
    }
}