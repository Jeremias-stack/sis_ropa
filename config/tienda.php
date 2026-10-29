<?php

return [
    // Valores predeterminados para comprobantes internos de prueba.
    'nombre' => env('TIENDA_NOMBRE', 'SIS_ROPA'),
    'descripcion' => env('TIENDA_DESCRIPCION', 'Ropa y accesorios'),
    'direccion' => env('TIENDA_DIRECCION', 'Guatemala'),
    'telefono' => env('TIENDA_TELEFONO', ''),
    'nit' => env('TIENDA_NIT', ''),
    // Imagen pública dentro de public/. Personalizable sin instalar paquetes.
    'logo' => env('TIENDA_LOGO', 'imagenes/logo-sisropa.svg'),
];
