<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PedidoController extends Controller
{
    public function index()
    {
        $query = Pedido::with('usuario');
        if (! auth()->user()->esAdministrador()) $query->where('user_id', auth()->id());
        $pedidos = $query->latest()->paginate(15);
        return view('pedidos.index', compact('pedidos'));
    }

    public function show(Pedido $pedido)
    {
        abort_unless(auth()->user()->esAdministrador() || (int) $pedido->user_id === (int) auth()->id(), 403);
        $pedido->load(['usuario', 'detalles']);
        return view('pedidos.show', compact('pedido'));
    }

    public function estado(Request $request, Pedido $pedido, StripeCheckoutService $stripe)
    {
        abort_unless(auth()->user()->esAdministrador(), 403);
        $datos = $request->validate(['estado' => ['required', Rule::in(['pendiente', 'enviado', 'entregado', 'cancelado'])]]);
        DB::transaction(function () use ($pedido, $datos, $stripe) {
            $pedido = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();
            $nuevo = $datos['estado'];
            $anterior = $pedido->estado;
            if (in_array($anterior, ['cancelado', 'entregado'], true)) {
                throw ValidationException::withMessages(['estado' => 'Un pedido cancelado o entregado no se puede modificar.']);
            }
            if ($nuevo === 'pendiente' && $anterior !== 'pendiente') {
                throw ValidationException::withMessages(['estado' => 'No se puede volver el pedido a pendiente.']);
            }
            if (in_array($nuevo, ['enviado', 'entregado'], true)
                && $pedido->metodo_pago === 'stripe_prueba' && $pedido->pago_estado !== 'pagado') {
                throw ValidationException::withMessages(['estado' => 'El pedido no se puede enviar ni entregar mientras Stripe no confirme el pago.']);
            }
            if ($nuevo === 'cancelado') {
                if ($pedido->metodo_pago === 'stripe_prueba') {
                    // Nunca reponer stock mientras haya una sesión de pago activa.
                    if ($pedido->pago_estado === 'pagado') {
                        throw ValidationException::withMessages(['estado' => 'Este pedido ya fue pagado. Gestiona la devolución del dinero por separado antes de cancelar.']);
                    }
                    if ($pedido->stripe_session_id) {
                        try {
                            $sesion = $stripe->consultarSesion($pedido->stripe_session_id);
                            if (($sesion['payment_status'] ?? null) === 'paid') {
                                throw ValidationException::withMessages(['estado' => 'Stripe confirma que este pedido fue pagado. Actualiza la pantalla.']);
                            }
                            if (($sesion['status'] ?? null) === 'open') $stripe->expirarSesion($pedido->stripe_session_id);
                            if (($sesion['status'] ?? null) === 'complete') {
                                throw ValidationException::withMessages(['estado' => 'El checkout ya se completó. Verifica el pago antes de cancelar.']);
                            }
                        } catch (ValidationException $e) {
                            throw $e;
                        } catch (\Throwable $e) {
                            throw ValidationException::withMessages(['estado' => 'No se pudo verificar o expirar la sesión de Stripe. No se modificó el inventario.']);
                        }
                    }
                }
                $detalles = $pedido->detalles()->orderBy('producto_variante_id')->get();
                $ids = $detalles->pluck('producto_variante_id');
                $info = ProductoVariante::whereIn('id', $ids)->get(['id', 'producto_id']);
                $productos = Producto::whereIn('id', $info->pluck('producto_id')->unique())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $variantes = ProductoVariante::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($detalles as $detalle) {
                    $variante = $variantes->get($detalle->producto_variante_id);
                    if (! $variante || ! $productos->get($variante->producto_id)) {
                        throw ValidationException::withMessages(['estado' => 'No se encontró una variante para devolver el inventario.']);
                    }
                    $variante->increment('stock', $detalle->cantidad);
                    $productos->get($variante->producto_id)->increment('stock', $detalle->cantidad);
                }
            }
            $pedido->update(['estado' => $nuevo]);
        });
        return redirect()->route('pedidos.show', $pedido)->with('success', 'Estado actualizado.');
    }
}
