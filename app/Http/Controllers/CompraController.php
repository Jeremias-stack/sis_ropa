<?php
namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraController extends Controller
{
    public function index()
    {
        $compras = Compra::with('proveedor')->latest()->paginate(15);
        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        $proveedores = Proveedor::orderBy('nombre')->get();
        $productos = Producto::with(['variantes.talla','variantes.color'])->orderBy('nombre')->get();
        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'fecha' => ['required', 'date'],
            'referencia' => ['nullable', 'string', 'max:80'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.variante_id' => ['required', 'integer', 'exists:producto_variantes,id', 'distinct'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.costo_unitario' => ['required', 'numeric', 'min:0', 'max:999999'],
        ]);

        $compra = DB::transaction(function () use ($datos) {
            $lineas = collect($datos['items']);
            $total = $lineas->sum(fn ($x) => round($x['cantidad'] * $x['costo_unitario'], 2));
            $compra = Compra::create([
                'proveedor_id' => $datos['proveedor_id'], 'usuario_id' => auth()->id(),
                'fecha' => $datos['fecha'], 'referencia' => $datos['referencia'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'total' => round($total, 2), 'estado' => 'pendiente',
            ]);
            foreach ($lineas as $item) {
                $compra->detalles()->create([
                    'producto_variante_id' => $item['variante_id'], 'cantidad' => $item['cantidad'],
                    'cantidad_recibida' => 0, 'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => round($item['cantidad'] * $item['costo_unitario'], 2),
                ]);
            }
            return $compra;
        });
        return redirect()->route('compras.show', $compra)->with('success', 'Compra registrada. El inventario aumenta al recibir la mercancía.');
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor','usuario','detalles.variante.producto','detalles.variante.talla','detalles.variante.color']);
        return view('compras.show', compact('compra'));
    }

    public function recibir(Request $request, Compra $compra)
    {
        $datos = $request->validate([
            'recibir' => ['required', 'array', 'min:1'],
            'recibir.*' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        DB::transaction(function () use ($compra, $datos) {
            $compra = Compra::whereKey($compra->id)->lockForUpdate()->firstOrFail();
            if ($compra->estado === 'recibida') {
                throw ValidationException::withMessages(['recibir' => 'Esta compra ya está recibida.']);
            }
            $detalles = $compra->detalles()->orderBy('producto_variante_id')->lockForUpdate()->get();
            foreach ($datos['recibir'] as $detalleId => $cantidad) {
                if (!$detalles->contains('id', (int) $detalleId)) {
                    throw ValidationException::withMessages(['recibir' => 'Hay una línea de compra no válida.']);
                }
            }
            foreach ($detalles as $detalle) {
                $cantidad = (int) ($datos['recibir'][$detalle->id] ?? 0);
                $pendiente = $detalle->cantidad - $detalle->cantidad_recibida;
                if ($cantidad > $pendiente) {
                    throw ValidationException::withMessages(['recibir' => 'No puedes recibir más unidades de las pendientes.']);
                }
                if ($cantidad === 0) continue;
                // Mantener el mismo orden de bloqueo que las ventas y pedidos: producto -> variante.
                $productoId = ProductoVariante::whereKey($detalle->producto_variante_id)->value('producto_id');
                $producto = Producto::whereKey($productoId)->lockForUpdate()->firstOrFail();
                $variante = ProductoVariante::whereKey($detalle->producto_variante_id)->lockForUpdate()->firstOrFail();
                $variante->increment('stock', $cantidad);
                $producto->increment('stock', $cantidad);
                $detalle->increment('cantidad_recibida', $cantidad);
            }
            $compra->estado = $compra->detalles()->whereColumn('cantidad_recibida', '<', 'cantidad')->exists()
                ? 'parcial' : 'recibida';
            $compra->save();
        });
        return redirect()->route('compras.show', $compra)->with('success', 'Recepción registrada. Existencias actualizadas por talla y color.');
    }
}
