<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VentaController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;


/* =========================================================
   RUTA PRINCIPAL
   ========================================================= */

Route::get('/', function () {

    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');

});


/* =========================================================
   USUARIOS INVITADOS
   ========================================================= */

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [AuthController::class, 'showLogin']
    )->name('login');


    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->name('login.process');


    Route::get(
        '/registro',
        [AuthController::class, 'showRegister']
    )->name('register');


    Route::post(
        '/registro',
        [AuthController::class, 'register']
    )->name('register.store');

});


/* =========================================================
   RUTAS PROTEGIDAS
   ========================================================= */

Route::middleware('auth')->group(function () {


    /* =====================================================
       DASHBOARD
       ADMINISTRADOR + CAJERO
       ===================================================== */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');


    /* =====================================================
       PRODUCTOS
       CONSULTA: ADMINISTRADOR + CAJERO
       ===================================================== */

    Route::get(
        '/productos',
        [ProductoController::class, 'index']
    )->name('productos.index');


    /*
     * IMPORTANTE:
     * Las rutas administrativas se colocan antes
     * de /productos/{producto}.
     */
    Route::middleware(
        RoleMiddleware::class . ':administrador'
    )->group(function () {


        Route::get(
            '/productos/create',
            [ProductoController::class, 'create']
        )->name('productos.create');


        Route::post(
            '/productos',
            [ProductoController::class, 'store']
        )->name('productos.store');


        Route::get(
            '/productos/{producto}/edit',
            [ProductoController::class, 'edit']
        )->name('productos.edit');


        Route::put(
            '/productos/{producto}',
            [ProductoController::class, 'update']
        )->name('productos.update');


        Route::patch(
            '/productos/{producto}',
            [ProductoController::class, 'update']
        );


        Route::delete(
            '/productos/{producto}',
            [ProductoController::class, 'destroy']
        )->name('productos.destroy');

    });


    Route::get(
        '/productos/{producto}',
        [ProductoController::class, 'show']
    )->name('productos.show');


    /* =====================================================
       CLIENTES
       ADMINISTRADOR + CAJERO
       ===================================================== */

    Route::resource(
        'clientes',
        ClienteController::class
    );


    /* =====================================================
       PUNTO DE VENTA
       ADMINISTRADOR + CAJERO
       ===================================================== */

    Route::get(
        '/pos',
        [VentaController::class, 'create']
    )->name('ventas.create');


    Route::post(
        '/ventas',
        [VentaController::class, 'store']
    )->name('ventas.store');


    Route::get(
        '/ventas/{venta}',
        [VentaController::class, 'show']
    )->name('ventas.show');


    /* =====================================================
       REPORTES
       SOLO ADMINISTRADOR
       ===================================================== */

    Route::middleware(
        RoleMiddleware::class . ':administrador'
    )->group(function () {

        Route::get(
            '/reportes',
            [ReporteController::class, 'index']
        )->name('reportes.index');

    });


    /* =====================================================
       LOGOUT
       ===================================================== */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    )->name('logout');

});