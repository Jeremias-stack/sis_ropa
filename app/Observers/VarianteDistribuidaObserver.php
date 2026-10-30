<?php

namespace App\Observers;

use App\Models\ProductoVariante;
use App\Services\SincronizacionDistribuida;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class VarianteDistribuidaObserver implements ShouldHandleEventsAfterCommit
{
    public function created(ProductoVariante $variante): void { app(SincronizacionDistribuida::class)->intentarProducto($variante->producto_id); }
    public function updated(ProductoVariante $variante): void { app(SincronizacionDistribuida::class)->intentarProducto($variante->producto_id); }
    public function deleted(ProductoVariante $variante): void { app(SincronizacionDistribuida::class)->intentarProducto($variante->producto_id); }
}
