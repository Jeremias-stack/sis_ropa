<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\ReporteApiController;
use App\Http\Controllers\StripeCheckoutController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API REST del Sistema POS para Tienda de Ropa y Accesorios.
|
*/


/* =========================================================
   AUTENTICACIÓN
   ========================================================= */

Route::post(
    '/login',
    [ApiAuthController::class, 'login']
)->name('api.login');


/* =========================================================
   RUTAS PROTEGIDAS
   ========================================================= */

Route::middleware(['auth:sanctum', RoleMiddleware::class . ':administrador,cajero'])->group(function () {


    /* =====================================================
       PRODUCTOS
       ===================================================== */

    Route::get(
        '/productos',
        [ProductoApiController::class, 'index']
    )->name('api.productos.index');


    Route::get(
        '/productos/{producto}',
        [ProductoApiController::class, 'show']
    )->name('api.productos.show');


    Route::post(
        '/productos',
        [ProductoApiController::class, 'store']
    )->name('api.productos.store');


    Route::put(
        '/productos/{producto}',
        [ProductoApiController::class, 'update']
    )->name('api.productos.update');


    Route::delete(
        '/productos/{producto}',
        [ProductoApiController::class, 'destroy']
    )->name('api.productos.destroy');


    /* =====================================================
       CLIENTES
       ===================================================== */

    Route::get(
        '/clientes',
        [ClienteApiController::class, 'index']
    )->name('api.clientes.index');


    Route::get(
        '/clientes/{cliente}',
        [ClienteApiController::class, 'show']
    )->name('api.clientes.show');


    /* =====================================================
       REPORTES
       ===================================================== */

    Route::get(
        '/reportes/ventas',
        [ReporteApiController::class, 'ventas']
    )->name('api.reportes.ventas');


    /* =====================================================
       CERRAR SESIÓN API
       ===================================================== */

    Route::post(
        '/logout',
        [ApiAuthController::class, 'logout']
    )->name('api.logout');

});

// No utiliza sesión ni CSRF. Se verifica la firma enviada por Stripe.
Route::post('/pagos/stripe/webhook', [StripeCheckoutController::class, 'webhook'])->name('stripe.webhook');
