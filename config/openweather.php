<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenWeather API
    |--------------------------------------------------------------------------
    |
    | Configuración utilizada para consultar el clima actual
    | desde OpenWeatherMap.
    |
    */

    'key' => env(
        'OPENWEATHER_API_KEY'
    ),


    /*
     * Coordenadas aproximadas de
     * Santa Cruz del Quiché, Guatemala.
     */

    'lat' => env(
        'OPENWEATHER_LAT',
        '15.0306'
    ),

    'lon' => env(
        'OPENWEATHER_LON',
        '-91.1487'
    ),


    /*
     * Nombre que mostraremos
     * dentro del sistema.
     */

    'location' => env(
        'OPENWEATHER_LOCATION',
        'Santa Cruz del Quiché'
    ),


    /*
     * Celsius.
     */

    'units' => 'metric',


    /*
     * Respuesta en español.
     */

    'lang' => 'es',

];