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
        $productos = Producto::with(['categoria','marca','variantes.talla','variantes.color'])
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
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago' => ['required', 'in:efectivo,tarjeta,qr'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.variante_id' => ['required', 'integer', 'exists:producto_variantes,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        // Agrupar por variante (nunca por producto): talla y color tienen stock propio.
        $items = collect($datos['items'])->groupBy(fn ($i) => $i['producto_id'].'|'.$i['variante_id'])
            ->map(fn ($grupo) => [
                'producto_id' => (int) $grupo->first()['producto_id'],
                'variante_id' => (int) $grupo->first()['variante_id'],
                'cantidad' => $grupo->sum(fn ($i) => (int) $i['cantidad']),
            ])->values()->sortBy(fn ($i) => sprintf('%012d-%012d', $i['producto_id'], $i['variante_id']));

        $venta = DB::transaction(function () use ($datos, $items) {
            $subtotal = 0;
            $lineas = [];

            foreach ($items as $item) {
                $producto = Producto::whereKey($item['producto_id'])->lockForUpdate()->firstOrFail();
                $variante = $producto->variantes()->whereKey($item['variante_id'])->lockForUpdate()->first();
                if (!$producto->activo || !$variante) {
                    throw ValidationException::withMessages(['items' => 'El producto o la variante seleccionada ya no está disponible.']);
                }
                if ($item['cantidad'] > $variante->stock || $item['cantidad'] > $producto->stock) {
                    throw ValidationException::withMessages([
                        'items' => "Existencias insuficientes de {$producto->nombre}. Disponibles: {$variante->stock} para esta talla y color.",
                    ]);
                }
                $variante->load(['talla', 'color']);
                $precio = (float) $producto->precio; // El servidor define el precio, nunca el navegador.
                $importe = round($precio * $item['cantidad'], 2);
                $subtotal += $importe;
                $lineas[] = compact('producto', 'variante', 'precio', 'importe') + ['cantidad' => $item['cantidad']];
            }

            $subtotal = round($subtotal, 2);
            $iva = round($subtotal * 0.12, 2); // Comportamiento previo del proyecto.
            $descuento = round((float)($datos['descuento'] ?? 0), 2);
            $total = round($subtotal + $iva - $descuento, 2);
            if ($total < 0) {
                throw ValidationException::withMessages(['descuento' => 'El descuento supera el total de la venta.']);
            }

            do {
                $numeroFactura = 'FAC-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));
            } while (Venta::where('numero_factura', $numeroFactura)->exists());

            $venta = Venta::create([
                'numero_factura' => $numeroFactura,
                'usuario_id' => auth()->id(),
                'cliente_id' => $datos['cliente_id'] ?? null,
                'fecha' => now(),
                'subtotal' => $subtotal,
                'iva' => $iva,
                'descuento' => $descuento,
                'total' => $total,
                'metodo_pago' => $datos['metodo_pago'],
                'estado' => 'completada',
            ]);

            foreach ($lineas as $linea) {
                $producto = $linea['producto'];
                $variante = $linea['variante'];
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'producto_variante_id' => $variante->id,
                    'talla_vendida' => $variante->talla?->nombre,
                    'color_vendido' => $variante->color?->nombre,
                    'cantidad' => $linea['cantidad'],
                    'precio_unitario' => $linea['precio'],
                    'subtotal' => $linea['importe'],
                ]);
                $variante->decrement('stock', $linea['cantidad']);
                $producto->decrement('stock', $linea['cantidad']);
            }

            Pago::create([
                'venta_id' => $venta->id,
                'monto' => $total,
                'metodo' => $datos['metodo_pago'],
                'referencia_api' => null,
                'fecha' => now(),
            ]);

            $contenidoQR = 'Comprobante: '.$venta->numero_factura.' | Total: Q'.number_format($total, 2, '.', '');
            $venta->update(['qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($contenidoQR)]);
            return $venta;
        });

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta registrada correctamente.');
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