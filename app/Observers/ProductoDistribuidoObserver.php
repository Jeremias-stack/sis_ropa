<?php

namespace App\Observers;

use App\Models\Producto;
use App\Services\SincronizacionDistribuida;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductoDistribuidoObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Producto $producto): void { app(SincronizacionDistribuida::class)->intentarProducto($producto->id); }
    public function updated(Producto $producto): void { app(SincronizacionDistribuida::class)->intentarProducto($producto->id); }
    public function deleted(Producto $producto): void { app(SincronizacionDistribuida::class)->intentarProducto($producto->id); }
}
