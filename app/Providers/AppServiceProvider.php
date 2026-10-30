<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Venta;
use App\Observers\PedidoDistribuidoObserver;
use App\Observers\ProductoDistribuidoObserver;
use App\Observers\VarianteDistribuidaObserver;
use App\Observers\VentaDistribuidaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Producto::observe(ProductoDistribuidoObserver::class);
        ProductoVariante::observe(VarianteDistribuidaObserver::class);
        Venta::observe(VentaDistribuidaObserver::class);
        Pedido::observe(PedidoDistribuidoObserver::class);
    }
}
