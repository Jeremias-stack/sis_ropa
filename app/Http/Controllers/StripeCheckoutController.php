<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StripeCheckoutController extends Controller
{
    private function autorizado(Pedido $pedido): void
    {
        abort_unless(auth()->user()->esAdministrador() || (int) $pedido->user_id === (int) auth()->id(), 403);
    }

    /** Reutiliza una sesión abierta para evitar generar enlaces nuevos innecesarios. */
    public function iniciar(Pedido $pedido, StripeCheckoutService $stripe)
    {
        $this->autorizado($pedido);
        if ($pedido->metodo_pago !== 'stripe_prueba' || $pedido->estado === 'cancelado') abort(403);
        if ($pedido->pago_estado === 'pagado') return redirect()->route('pedidos.show', $pedido);

        try {
            // Bloqueo único del pedido mientras se crea/reutiliza la sesión.
            $url = DB::transaction(function () use ($pedido, $stripe) {
                $pedido = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();
                if ($pedido->pago_estado === 'pagado') return null;
                if ($pedido->estado === 'cancelado') throw new RuntimeException('El pedido ya fue cancelado.');

                if ($pedido->stripe_session_id) {
                    $existente = $stripe->consultarSesion($pedido->stripe_session_id);
                    if ($stripe->sesionPagadaDePedido($existente, $pedido)) {
                        $pedido->update(['pago_estado' => 'pagado']);
                        return null;
                    }
                    if (($existente['status'] ?? null) === 'open' && ! empty($existente['url'])) {
                        return $existente['url'];
                    }
                    if (($existente['status'] ?? null) === 'complete') {
                        throw new RuntimeException('Revisa el estado del pago en Stripe antes de intentar nuevamente.');
                    }
                }

                $sesion = $stripe->crearSesion($pedido);
                $pedido->update(['stripe_session_id' => $sesion['id']]);
                return $sesion['url'];
            });
            return $url ? redirect()->away($url, 303) : redirect()->route('pedidos.show', $pedido)->with('success', 'Pago de prueba confirmado.');
        } catch (\Throwable $e) {
            Log::warning('Stripe no pudo abrir el checkout', ['pedido_id' => $pedido->id, 'motivo' => $e->getMessage()]);
            return back()->with('error', 'No se pudo iniciar el pago: '.$e->getMessage());
        }
    }

    public function retorno(Request $request, Pedido $pedido, StripeCheckoutService $stripe)
    {
        $this->autorizado($pedido);
        $request->validate(['session_id' => ['required', 'string', 'max:255']]);
        if ($pedido->metodo_pago !== 'stripe_prueba' || $pedido->estado === 'cancelado') abort(403);
        if ($request->input('session_id') !== $pedido->stripe_session_id) abort(403);

        try {
            $sesion = $stripe->consultarSesion($pedido->stripe_session_id);
            if (! $stripe->sesionPagadaDePedido($sesion, $pedido)) {
                return redirect()->route('pedidos.show', $pedido)
                    ->with('error', 'Stripe aún no confirma el pago. Puedes verificarlo de nuevo desde tu pedido.');
            }
            DB::transaction(function () use ($pedido, $stripe, $sesion) {
                $bloqueado = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();
                if ($bloqueado->estado !== 'cancelado' && $stripe->sesionPagadaDePedido($sesion, $bloqueado)) {
                    $bloqueado->update(['pago_estado' => 'pagado']);
                }
            });
            return redirect()->route('pedidos.show', $pedido)->with('success', 'Pago de prueba confirmado con Stripe.');
        } catch (\Throwable $e) {
            Log::warning('Fallo al verificar pago Stripe', ['pedido_id' => $pedido->id, 'motivo' => $e->getMessage()]);
            return redirect()->route('pedidos.show', $pedido)->with('error', 'No se pudo verificar el pago. Intenta de nuevo.');
        }
    }

    /** El webhook no usa sesión del navegador, únicamente firma HMAC de Stripe. */
    public function webhook(Request $request, StripeCheckoutService $stripe)
    {
        $payload = $request->getContent();
        if (! $stripe->verificarFirmaWebhook($payload, (string) $request->header('Stripe-Signature'))) {
            return response()->json(['error' => 'Firma no válida'], 400);
        }

        $evento = json_decode($payload, true);
        if (! is_array($evento)) return response()->json(['error' => 'Evento inválido'], 400);
        if (! in_array($evento['type'] ?? '', ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response()->json(['recibido' => true]);
        }
        $sesion = $evento['data']['object'] ?? null;
        if (! is_array($sesion)) return response()->json(['error' => 'Sesión faltante'], 400);
        $id = (int) ($sesion['metadata']['pedido_id'] ?? 0);
        if ($id <= 0) return response()->json(['recibido' => true]);

        DB::transaction(function () use ($id, $sesion, $stripe) {
            $pedido = Pedido::whereKey($id)->lockForUpdate()->first();
            if ($pedido && $pedido->estado !== 'cancelado'
                && $pedido->metodo_pago === 'stripe_prueba'
                && $stripe->sesionPagadaDePedido($sesion, $pedido)
                && $pedido->pago_estado !== 'pagado') {
                $pedido->update(['pago_estado' => 'pagado']);
            }
        });
        return response()->json(['recibido' => true]);
    }
}
