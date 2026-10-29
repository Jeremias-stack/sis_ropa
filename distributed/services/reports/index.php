<?php
require __DIR__ . '/../common/bootstrap.php';

$path = endpoint();
if ($path === '/health') respond(200, ['servicio' => 'reportes', 'estado' => 'ok']);
// Reporte distribuido basado en PROYECCIONES del POS real, no en datos demo.
if ($path === '/api/reportes/pos/resumen' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated(['administrador']);
    $token = bearer();
    $ventas = callService((getenv('SALES_URL') ?: 'http://sales:8082') . '/api/ventas/pos/resumen', 'GET', null, $token);
    $inventario = callService((getenv('INVENTORY_URL') ?: 'http://inventory:8081') . '/api/inventario/pos/resumen', 'GET', null, $token);
    if ($ventas['status'] !== 200 || $inventario['status'] !== 200) {
        respond(503, ['error'=>'No se pudieron leer las proyecciones del POS']);
    }
    respond(200, [
        'tipo'=>'proyeccion_solo_lectura',
        'ventas'=>$ventas['data']['resumen'],
        'pedidos'=>$ventas['data']['pedidos'] ?? null,
        'inventario'=>$inventario['data']['resumen'],
    ]);
}
if ($path === '/api/reportes/resumen' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = authenticated(['administrador']);
    $token = bearer();
    // El servicio NO accede directamente a las bases de datos de otros servicios.
    $sales = callService((getenv('SALES_URL') ?: 'http://sales:8082') . '/api/ventas', 'GET', null, $token);
    $inventory = callService((getenv('INVENTORY_URL') ?: 'http://inventory:8081') . '/api/inventario/productos', 'GET', null, $token);
    if ($sales['status'] !== 200 || $inventory['status'] !== 200) respond(503, ['error' => 'No se pudieron consultar los microservicios']);
    $products = $inventory['data']['productos'] ?? [];
    $totalStock = array_sum(array_map(fn($p) => (int)$p['stock'], $products));
    $low = array_values(array_filter($products, fn($p) => (int)$p['stock'] < 5));
    $stmt = db()->prepare('INSERT INTO consultas (usuario_id, created_at) VALUES (?,NOW())');
    $stmt->execute([$user['id']]);
    respond(200, ['ventas' => $sales['data']['resumen'], 'existencias' => $totalStock, 'variantes_stock_bajo' => $low]);
}
respond(404, ['error' => 'Ruta no encontrada']);
