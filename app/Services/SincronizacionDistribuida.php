<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Proyección de SOLO LECTURA del inventario real Laravel en los microservicios.
 * El POS, las compras y los pedidos continúan modificando exclusivamente la BD de Laravel.
 * Nunca se vuelve a descontar stock al sincronizar.
 */
class SincronizacionDistribuida
{
    public function habilitada(): bool
    {
        return (bool) config('distribuido.enabled') && strlen((string) config('distribuido.internal_token')) >= 24;
    }

    private function solicitar(string $servicio, string $ruta, array $datos): void
    {
        $url = rtrim((string) config('distribuido.'.$servicio.'_url'), '/').$ruta;
        $respuesta = Http::timeout(5)->connectTimeout(2)
            ->withHeaders(['X-Internal-Token' => (string) config('distribuido.internal_token')])
            ->acceptJson()->post($url, $datos);
        if (!$respuesta->successful()) {
            throw new RuntimeException('Sincronización HTTP '.$respuesta->status().' con '.$servicio.': '.mb_substr($respuesta->body(), 0, 200));
        }
    }

    public function producto(int $id): void
    {
        if (!$this->habilitada()) return;
        $producto = Producto::with(['variantes.talla', 'variantes.color'])->find($id);
        // Si el producto fue eliminado, borrar únicamente su proyección distribuida.
        if (!$producto) {
            $this->solicitar('inventory', '/api/inventario/pos/sincronizar', [
                'id' => $id, 'eliminado' => true,
            ]);
            return;
        }
        $this->solicitar('inventory', '/api/inventario/pos/sincronizar', [
            'id' => (int) $producto->id,
            'nombre' => $producto->nombre,
            'codigo' => $producto->codigo,
            'precio' => (string) $producto->precio,
            'activo' => (bool) $producto->activo,
            'variantes' => $producto->variantes->map(fn ($v) => [
                'id' => (int) $v->id,
                'talla' => $v->talla?->nombre,
                'color' => $v->color?->nombre,
                'stock' => (int) $v->stock,
            ])->values()->all(),
        ]);
    }

    public function venta(int $id): void
    {
        if (!$this->habilitada()) return;
        $venta = Venta::with('detalles')->find($id);
        if (!$venta) return;
        $this->solicitar('sales', '/api/ventas/pos/sincronizar', [
            'id' => (int) $venta->id,
            'numero_factura' => $venta->numero_factura,
            'usuario_id' => (int) $venta->usuario_id,
            'total' => (string) $venta->total,
            'fecha' => $venta->fecha ? (string) $venta->fecha : now()->toDateTimeString(),
            'lineas' => $venta->detalles->map(fn ($d) => [
                'variante_id' => (int) $d->producto_variante_id,
                'cantidad' => (int) $d->cantidad,
                'subtotal' => (string) $d->subtotal,
            ])->values()->all(),
        ]);
    }

    public function pedido(int $id): void
    {
        if (!$this->habilitada()) return;
        $pedido = Pedido::with('detalles')->find($id);
        if (!$pedido) return;
        $this->solicitar('sales', '/api/ventas/pos/sincronizar-pedido', [
            'id' => (int) $pedido->id,
            'usuario_id' => (int) $pedido->user_id,
            'total' => (string) $pedido->total,
            'estado' => $pedido->estado,
            'pago_estado' => $pedido->pago_estado,
            'metodo_pago' => $pedido->metodo_pago,
            'fecha' => $pedido->created_at?->format('Y-m-d H:i:s') ?? now()->toDateTimeString(),
            'lineas' => $pedido->detalles->map(fn ($d) => [
                'variante_id' => (int) $d->producto_variante_id,
                'cantidad' => (int) $d->cantidad,
                'subtotal' => (string) $d->subtotal,
            ])->values()->all(),
        ]);
    }

    /** Si Docker está apagado, NO se anula la venta real; se puede repetir la sincronización. */
    public function intentarProducto(int $id): void
    {
        try { $this->producto($id); }
        catch (\Throwable $e) { Log::warning('Pendiente sincronización de producto '.$id.': '.$e->getMessage()); }
    }

    public function intentarPedido(int $id): void
    {
        try { $this->pedido($id); }
        catch (\Throwable $e) { Log::warning('Pendiente sincronización de pedido '.$id.': '.$e->getMessage()); }
    }

    public function intentarVenta(int $id): void
    {
        try { $this->venta($id); }
        catch (\Throwable $e) { Log::warning('Pendiente sincronización de venta '.$id.': '.$e->getMessage()); }
    }
}
