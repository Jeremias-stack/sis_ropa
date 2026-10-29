<?php

namespace App\Observers;

use App\Models\Venta;
use App\Services\SincronizacionDistribuida;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class VentaDistribuidaObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Venta $venta): void { app(SincronizacionDistribuida::class)->intentarVenta($venta->id); }
}
