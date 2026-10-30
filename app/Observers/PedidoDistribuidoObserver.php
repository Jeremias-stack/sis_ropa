<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Services\SincronizacionDistribuida;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PedidoDistribuidoObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Pedido $pedido): void { app(SincronizacionDistribuida::class)->intentarPedido($pedido->id); }
    public function updated(Pedido $pedido): void { app(SincronizacionDistribuida::class)->intentarPedido($pedido->id); }
}
