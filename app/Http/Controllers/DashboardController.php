<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        /* =====================================================
           INVENTARIO
           ===================================================== */

        $totalProductos = Producto::count();


        $productosActivos = Producto::where(
            'activo',
            true
        )->count();


        $stockBajo = Producto::where(
            'stock',
            '<',
            5
        )
            ->where(
                'activo',
                true
            )
            ->count();


        $totalCategorias = Categoria::count();


        $valorInventario = Producto::where(
            'activo',
            true
        )
            ->selectRaw(
                'SUM(precio * stock) as total'
            )
            ->value('total') ?? 0;


        $ultimosProductos = Producto::with(
            'categoria'
        )
            ->latest()
            ->take(5)
            ->get();


        /* =====================================================
           OPENWEATHER API
           ===================================================== */

        $clima = null;

        $climaError = null;


        $apiKey =
            config('openweather.key');

        $latitud =
            config('openweather.lat');

        $longitud =
            config('openweather.lon');


        /*
         * Si todavía no existe una API Key,
         * el sistema sigue funcionando.
         */
        if (!$apiKey) {

            $climaError =
                'La API de clima todavía no está configurada.';

        } else {

            try {

                /*
                 * Guardamos el clima durante 10 minutos
                 * para no hacer una petición a la API
                 * cada vez que alguien recarga el dashboard.
                 */
                $cacheKey =
                    'openweather.current.'
                    . $latitud
                    . '.'
                    . $longitud;


                $clima = Cache::remember(
                    $cacheKey,
                    now()->addMinutes(10),
                    function () use (
                        $apiKey,
                        $latitud,
                        $longitud
                    ) {

                        $response = Http::acceptJson()
                            ->timeout(8)
                            ->get(
                                'https://api.openweathermap.org/data/2.5/weather',
                                [
                                    'lat' =>
                                        $latitud,

                                    'lon' =>
                                        $longitud,

                                    'appid' =>
                                        $apiKey,

                                    'units' =>
                                        config(
                                            'openweather.units',
                                            'metric'
                                        ),

                                    'lang' =>
                                        config(
                                            'openweather.lang',
                                            'es'
                                        ),
                                ]
                            );


                        /*
                         * Si OpenWeather responde con error,
                         * devolvemos una marca para manejarla
                         * fuera del callback.
                         */
                        if (!$response->successful()) {

                            return [
                                'error' => true,
                            ];

                        }


                        $datos =
                            $response->json();


                        $velocidadViento =
                            (float) data_get(
                                $datos,
                                'wind.speed',
                                0
                            );


                        return [

                            'error' => false,


                            'ubicacion' =>
                                config(
                                    'openweather.location',
                                    'Santa Cruz del Quiché'
                                ),


                            'temperatura' =>
                                round(
                                    (float) data_get(
                                        $datos,
                                        'main.temp',
                                        0
                                    )
                                ),


                            'sensacion' =>
                                round(
                                    (float) data_get(
                                        $datos,
                                        'main.feels_like',
                                        0
                                    )
                                ),


                            'humedad' =>
                                (int) data_get(
                                    $datos,
                                    'main.humidity',
                                    0
                                ),


                            'descripcion' =>
                                ucfirst(
                                    (string) data_get(
                                        $datos,
                                        'weather.0.description',
                                        'Sin información'
                                    )
                                ),


                            'icono' =>
                                data_get(
                                    $datos,
                                    'weather.0.icon'
                                ),


                            /*
                             * OpenWeather devuelve el viento
                             * en metros por segundo usando metric.
                             *
                             * Lo convertimos a km/h.
                             */
                            'viento' =>
                                round(
                                    $velocidadViento
                                    * 3.6,
                                    1
                                ),

                        ];

                    }
                );


                if (
                    !$clima ||
                    ($clima['error'] ?? false)
                ) {

                    $clima = null;

                    $climaError =
                        'No fue posible consultar el clima en este momento.';

                }

            } catch (\Throwable $error) {

                $clima = null;

                $climaError =
                    'No fue posible conectar con OpenWeather.';

            }

        }


        /* =====================================================
           VISTA
           ===================================================== */

        return view(
            'dashboard',
            compact(
                'totalProductos',
                'productosActivos',
                'stockBajo',
                'totalCategorias',
                'valorInventario',
                'ultimosProductos',
                'clima',
                'climaError'
            )
        );
    }
}