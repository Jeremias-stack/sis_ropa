<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Checkout alojado en Stripe, sin paquete adicional ni manejo de números de tarjeta. */
class StripeCheckoutService
{
    public function disponible(): bool
    {
        return str_starts_with((string) config('stripe_checkout.secret_key'), 'sk_test_');
    }

    private function request()
    {
        if (! $this->disponible()) {
            throw new RuntimeException('Configura STRIPE_SECRET_KEY con una clave de prueba sk_test_ en .env.');
        }

        return Http::withToken(config('stripe_checkout.secret_key'))
            ->acceptJson()->asForm()->timeout(20);
    }

    private function respuesta(Response $respuesta): array
    {
        if ($respuesta->failed()) {
            $descripcion = $respuesta->json('error.message') ?: 'Respuesta no válida de Stripe.';
            throw new RuntimeException('Stripe: '.substr((string) $descripcion, 0, 240));
        }

        return $respuesta->json() ?: [];
    }

    public function crearSesion(Pedido $pedido): array
    {
        $pedido->loadMissing('detalles');
        $lineas = [];
        foreach ($pedido->detalles as $detalle) {
            $nombre = (function_exists('mb_substr') ? mb_substr($detalle->nombre_producto.' - '.($detalle->talla ?: 'Única').' / '.($detalle->color ?: 'Sin color'), 0, 120) : substr($detalle->nombre_producto.' - '.($detalle->talla ?: 'Única').' / '.($detalle->color ?: 'Sin color'), 0, 120));
            $lineas[] = [
                'price_data' => [
                    'currency' => config('stripe_checkout.currency'),
                    'unit_amount' => (int) round((float) $detalle->precio_unitario * 100),
                    'product_data' => ['name' => $nombre],
                ],
                'quantity' => (int) $detalle->cantidad,
            ];
        }
        if (! $lineas) throw new RuntimeException('El pedido no tiene artículos.');

        $url = route('stripe.retorno', ['pedido' => $pedido->id], true);
        $respuesta = $this->respuesta($this->request()->post('https://api.stripe.com/v1/checkout/sessions', [
            'mode' => 'payment',
            'success_url' => $url.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('pedidos.show', ['pedido' => $pedido->id], true),
            'client_reference_id' => (string) $pedido->id,
            'metadata' => ['pedido_id' => (string) $pedido->id],
            'line_items' => $lineas,
        ]));
        if (! isset($respuesta['id'], $respuesta['url']) || ! str_starts_with($respuesta['id'], 'cs_test_')) {
            throw new RuntimeException('Stripe no devolvió una sesión de prueba válida.');
        }
        return $respuesta;
    }

    public function consultarSesion(string $id): array
    {
        if (! preg_match('/^cs_test_[A-Za-z0-9_]+$/', $id)) {
            throw new RuntimeException('Identificador Stripe no válido.');
        }
        return $this->respuesta($this->request()->get('https://api.stripe.com/v1/checkout/sessions/'.$id));
    }

    public function expirarSesion(string $id): void
    {
        $this->respuesta($this->request()->post('https://api.stripe.com/v1/checkout/sessions/'.$id.'/expire', []));
    }

    public function sesionPagadaDePedido(array $sesion, Pedido $pedido): bool
    {
        return ($sesion['id'] ?? null) === $pedido->stripe_session_id
            && ($sesion['metadata']['pedido_id'] ?? null) === (string) $pedido->id
            && ($sesion['client_reference_id'] ?? null) === (string) $pedido->id
            && ($sesion['payment_status'] ?? null) === 'paid'
            && strtolower((string) ($sesion['currency'] ?? '')) === config('stripe_checkout.currency')
            && (int) ($sesion['amount_total'] ?? -1) === (int) round((float) $pedido->total * 100);
    }

    public function verificarFirmaWebhook(string $payload, string $cabecera): bool
    {
        $secreto = (string) config('stripe_checkout.webhook_secret');
        if (! str_starts_with($secreto, 'whsec_')) return false;
        $partes = [];
        foreach (explode(',', $cabecera) as $segmento) {
            $par = explode('=', trim($segmento), 2);
            if (count($par) === 2) $partes[$par[0]][] = $par[1];
        }
        $timestamp = $partes['t'][0] ?? null;
        if (! $timestamp || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300) return false;
        $firma = hash_hmac('sha256', $timestamp.'.'.$payload, $secreto);
        foreach ($partes['v1'] ?? [] as $candidate) {
            if (hash_equals($firma, $candidate)) return true;
        }
        return false;
    }
}
