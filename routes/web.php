<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CatalogoController;
use App\Models\Producto;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\NoCacheMiddleware;


/* =========================================================
   RUTA PRINCIPAL
   ========================================================= */

Route::get('/', function () {

    return auth()->check() && auth()->user()->rol !== 'cliente'
        ? redirect()->route('dashboard')
        : redirect()->route('tienda.index');

});



// Tienda pública y carrito: funcionan también sin iniciar sesión.
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/tienda', [TiendaController::class, 'index'])->name('tienda.index');
Route::get('/carrito', [TiendaController::class, 'carrito'])->name('tienda.carrito');
Route::post('/carrito', [TiendaController::class, 'agregar'])->name('tienda.agregar');
Route::put('/carrito', [TiendaController::class, 'actualizar'])->name('tienda.actualizar');

// Confirmación de pedido: requiere cuenta; nunca se cobra una tarjeta aquí.
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [TiendaController::class, 'checkout'])->name('tienda.checkout');
    Route::post('/checkout', [TiendaController::class, 'confirmar'])->name('tienda.confirmar');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::post('/pedidos/{pedido}/pagar-stripe', [StripeCheckoutController::class, 'iniciar'])->name('stripe.iniciar');
    Route::get('/pedidos/{pedido}/pago-confirmado', [StripeCheckoutController::class, 'retorno'])->name('stripe.retorno');
});

// Gestión de compras y estados: solamente administrador.
Route::middleware(['auth', RoleMiddleware::class . ':administrador', NoCacheMiddleware::class])->group(function () {
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])->name('proveedores.update');
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/nueva', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::get('/compras/{compra}', [CompraController::class, 'show'])->name('compras.show');
    Route::post('/compras/{compra}/recibir', [CompraController::class, 'recibir'])->name('compras.recibir');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'estado'])->name('pedidos.estado');
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

Route::middleware([
    'auth',
    RoleMiddleware::class . ':administrador,cajero',
    NoCacheMiddleware::class,
])->group(function () {


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


    // Categorías, marcas, tallas y colores: admin y cajero pueden agregar valores.
    Route::middleware(RoleMiddleware::class . ':administrador,cajero')->group(function () {
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::post('/catalogos/{tipo}', [CatalogoController::class, 'store'])->name('catalogos.store');
    });

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



});

// Solo administrador: cuentas del sistema (no confundir con clientes de facturación).
Route::middleware(['auth', RoleMiddleware::class . ':administrador', NoCacheMiddleware::class])
    ->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{usuario}/editar', [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
    });
