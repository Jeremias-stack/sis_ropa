<?php

return [
    // Desactivado fuera de Docker: Railway/XAMPP siguen funcionando sin servicios externos.
    'enabled' => filter_var(env('DISTRIBUTED_SYNC_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'inventory_url' => env('DISTRIBUTED_INVENTORY_URL', 'http://inventory:8081'),
    'sales_url' => env('DISTRIBUTED_SALES_URL', 'http://sales:8082'),
    'internal_token' => env('DISTRIBUTED_INTERNAL_TOKEN', ''),
];
