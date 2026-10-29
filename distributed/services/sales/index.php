<?php
require __DIR__ . '/../common/bootstrap.php';

// Registro idempotente de ventas REALES de Laravel. No modifica inventario.
function posVentasTables(): void
{
    db()->exec("CREATE TABLE IF NOT EXISTS ventas_pos (
        id BIGINT UNSIGNED PRIMARY KEY, numero_factura VARCHAR(100) NULL,
        usuario_id BIGINT UNSIGNED NOT NULL, total DECIMAL(12,2) NOT NULL,
        fecha DATETIME NOT NULL, lineas_json LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    db()->exec("CREATE TABLE IF NOT EXISTS pedidos_pos (
        id BIGINT UNSIGNED PRIMARY KEY, usuario_id BIGINT UNSIGNED NOT NULL,
        total DECIMAL(12,2) NOT NULL, estado VARCHAR(30) NOT NULL,
        pago_estado VARCHAR(30) NULL, metodo_pago VARCHAR(40) NOT NULL,
        fecha DATETIME NOT NULL, lineas_json LONGTEXT NOT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

$posVentasPath = endpoint();
if ($posVentasPath === '/api/ventas/pos/sincronizar' && $_SERVER['REQUEST_METHOD']==='POST') {
    $internal = getenv('INTERNAL_TOKEN') ?: '';
    if (strlen($internal)<24 || !hash_equals($internal, $_SERVER['HTTP_X_INTERNAL_TOKEN'] ?? '')) {
        respond(403, ['error'=>'Sincronización no autorizada']);
    }
    $input=payload();
    $id=filter_var($input['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $usuarioId=filter_var($input['usuario_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $total=$input['total']??null;
    $lineas=$input['lineas']??null;
    $fecha=(string)($input['fecha']??'');
    if (!$id || !$usuarioId || !is_numeric($total) || $total<0 || !is_array($lineas)
        || count($lineas)>200 || !preg_match('/^\d{4}-\d\d-\d\d[ T]\d\d:\d\d:\d\d/', $fecha)) {
        respond(422,['error'=>'Datos de venta inválidos']);
    }
    posVentasTables();
    $stmt=db()->prepare('INSERT INTO ventas_pos(id,numero_factura,usuario_id,total,fecha,lineas_json)
        VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE numero_factura=VALUES(numero_factura),
        usuario_id=VALUES(usuario_id),total=VALUES(total),fecha=VALUES(fecha),lineas_json=VALUES(lineas_json)');
    $stmt->execute([$id,$input['numero_factura']??null,$usuarioId,$total,substr($fecha,0,19),json_encode($lineas)]);
    respond(200,['sincronizado'=>true,'venta_id'=>$id]);
}
if ($posVentasPath === '/api/ventas/pos/sincronizar-pedido' && $_SERVER['REQUEST_METHOD']==='POST') {
    $internal = getenv('INTERNAL_TOKEN') ?: '';
    if (strlen($internal)<24 || !hash_equals($internal, $_SERVER['HTTP_X_INTERNAL_TOKEN'] ?? '')) {
        respond(403, ['error'=>'Sincronización no autorizada']);
    }
    $input=payload();
    $id=filter_var($input['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $usuarioId=filter_var($input['usuario_id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    $fecha=(string)($input['fecha']??'');
    if (!$id || !$usuarioId || !is_numeric($input['total']??null) || ($input['total']??0)<0
       || !is_array($input['lineas']??null) || count($input['lineas'])>200
       || !preg_match('/^\d{4}-\d\d-\d\d[ T]\d\d:\d\d:\d\d/', $fecha)) {
        respond(422, ['error'=>'Pedido no válido']);
    }
    posVentasTables();
    $stmt=db()->prepare('INSERT INTO pedidos_pos(id,usuario_id,total,estado,pago_estado,metodo_pago,fecha,lineas_json)
        VALUES(?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE usuario_id=VALUES(usuario_id),total=VALUES(total),
        estado=VALUES(estado),pago_estado=VALUES(pago_estado),metodo_pago=VALUES(metodo_pago),
        fecha=VALUES(fecha),lineas_json=VALUES(lineas_json)');
    $stmt->execute([$id,$usuarioId,$input['total'],$input['estado']??'pendiente',
        $input['pago_estado']??null,$input['metodo_pago']??'contra_entrega',
        substr($fecha,0,19),json_encode($input['lineas'])]);
    respond(200,['sincronizado'=>true,'pedido_id'=>$id]);
}
if ($posVentasPath === '/api/ventas/pos/resumen' && $_SERVER['REQUEST_METHOD']==='GET') {
    authenticated(); posVentasTables();
    $totales=db()->query('SELECT COUNT(*) AS ventas,COALESCE(SUM(total),0) AS ingresos FROM ventas_pos')->fetch();
    $pedidos=db()->query("SELECT COUNT(*) AS pedidos, COALESCE(SUM(CASE WHEN estado <> 'cancelado' THEN total ELSE 0 END),0) AS total_no_cancelado FROM pedidos_pos")->fetch();
    respond(200,['fuente'=>'proyeccion_laravel','resumen'=>$totales,'pedidos'=>$pedidos]);
}


$path = endpoint();
if ($path === '/health') respond(200, ['servicio' => 'ventas', 'estado' => 'ok']);
if ($path === '/api/ventas' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated();
    $rows = db()->query('SELECT id, usuario_id, variante_id, cantidad, subtotal, iva_incluido, created_at FROM ventas ORDER BY id DESC LIMIT 100')->fetchAll();
    $summary = db()->query('SELECT COUNT(*) AS cantidad_ventas, COALESCE(SUM(subtotal),0) AS total FROM ventas')->fetch();
    respond(200, ['ventas' => $rows, 'resumen' => $summary]);
}
if ($path === '/api/ventas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = authenticated();
    $input = payload();
    // Demostración mínima del flujo REST: una variante por venta. El POS Laravel conserva su propio carrito múltiple.
    $id = $input['variante_id'] ?? null;
    $qty = $input['cantidad'] ?? null;
    if (!is_int($id) || $id < 1 || !is_int($qty) || $qty < 1 || $qty > 100) respond(422, ['error' => 'variante_id y cantidad deben ser enteros válidos']);
    $token = bearer();
    $inventory = getenv('INVENTORY_URL') ?: 'http://inventory:8081';
    $item = callService($inventory . '/api/inventario/variantes/' . $id, 'GET', null, $token);
    if ($item['status'] !== 200) respond($item['status'], $item['data']);
    $price = (float)($item['data']['variante']['precio'] ?? 0);
    if ($price <= 0) respond(409, ['error' => 'Precio inválido']);
    $stock = callService($inventory . '/api/inventario/variantes/' . $id, 'PATCH', ['delta' => -$qty], $token, ['X-Internal-Token: ' . (getenv('INTERNAL_TOKEN') ?: '')]);
    if ($stock['status'] !== 200) respond($stock['status'], $stock['data']);
    try {
        $stmt = db()->prepare('INSERT INTO ventas (usuario_id, variante_id, cantidad, subtotal, iva_incluido, created_at) VALUES (?,?,?,?,?,NOW())');
        $total = round($price * $qty, 2);
        $iva = round($total * 12 / 112, 2); // IVA incluido en el precio de ejemplo
        $stmt->execute([$user['id'], $id, $qty, $total, $iva]);
        respond(201, ['venta_id' => (int)db()->lastInsertId(), 'total' => $total, 'iva_incluido' => $iva, 'stock_restante' => $stock['data']['variante']['stock']]);
    } catch (Throwable $e) {
        error_log('Venta no guardada, intentando reversar movimiento: ' . $e);
        callService($inventory . '/api/inventario/variantes/' . $id, 'PATCH', ['delta' => $qty], $token, ['X-Internal-Token: ' . (getenv('INTERNAL_TOKEN') ?: '')]);
        respond(500, ['error' => 'No se pudo finalizar la venta; revisar el estado del inventario']);
    }
}
respond(404, ['error' => 'Ruta no encontrada']);
