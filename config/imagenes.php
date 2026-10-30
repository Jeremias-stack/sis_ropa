<?php

return [
    // Sin estas tres claves se mantiene el almacenamiento local tradicional.
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
    'api_key' => env('CLOUDINARY_API_KEY', ''),
    'api_secret' => env('CLOUDINARY_API_SECRET', ''),
    'folder' => 'sis_ropa',
];
