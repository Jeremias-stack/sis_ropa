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


// Reportes por API REST propia: el servicio de Reportes consulta a Ventas
// mediante HTTP; jamás consulta directamente ventas_db ni pos_ropa.
if (in_array($path, ['/api/reportes/ventas', '/api/reportes/pos/ventas'], true) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated(['administrador']);
    $params=[];
    if (isset($_GET['desde'])) $params['desde']=(string)$_GET['desde'];
    if (isset($_GET['hasta'])) $params['hasta']=(string)$_GET['hasta'];
    $url=(getenv('SALES_URL') ?: 'http://sales:8082').'/api/ventas/pos/detalle';
    if ($params) $url.='?'.http_build_query($params);
    $resultado=callService($url, 'GET', null, bearer());
    if ($resultado['status'] !== 200) respond($resultado['status'], $resultado['data']);
    $ventas=$resultado['data']['ventas'] ?? [];
    $total=0.0; $diarias=[];
    foreach ($ventas as $venta) {
        $importe=(float)($venta['total']??0);
        $total+=$importe;
        $fecha=substr((string)($venta['fecha']??''),0,10);
        if (!isset($diarias[$fecha])) $diarias[$fecha]=['fecha'=>$fecha,'ventas'=>0,'total'=>0.0];
        $diarias[$fecha]['ventas']++;
        $diarias[$fecha]['total']+=$importe;
    }
    ksort($diarias);
    foreach ($diarias as &$dia) $dia['total']=round($dia['total'],2);
    unset($dia);
    respond(200,[
        'fuente'=>'proyeccion_laravel_por_rest','desde'=>$params['desde']??null,'hasta'=>$params['hasta']??null,
        'cantidad'=>count($ventas),'total'=>round($total,2),'ventas_por_dia'=>array_values($diarias),
        'truncado'=>(bool)($resultado['data']['truncado']??false),
        'nota'=>'Incluye ventas POS; los pedidos de tienda web se reportan aparte.',
    ]);
}

if ($path === '/api/reportes/top-productos' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated(['administrador']);
    $ventas=callService((getenv('SALES_URL') ?: 'http://sales:8082').'/api/ventas/pos/detalle', 'GET', null, bearer());
    $inventario=callService((getenv('INVENTORY_URL') ?: 'http://inventory:8081').'/api/inventario/pos/productos', 'GET', null, bearer());
    if ($ventas['status'] !== 200 || $inventario['status'] !== 200) respond(503,['error'=>'No se pudieron consultar Ventas e Inventario']);
    $productos=[]; $variantes=[];
    foreach ($inventario['data']['productos'] ?? [] as $row) {
        if (!empty($row['variante_id'])) $variantes[(int)$row['variante_id']]=(int)$row['producto_id'];
        $productos[(int)$row['producto_id']] = (string)$row['nombre'];
    }
    $cantidades=[];
    foreach ($ventas['data']['ventas'] ?? [] as $venta) {
        foreach ($venta['lineas'] ?? [] as $linea) {
            $id=$variantes[(int)($linea['variante_id']??0)] ?? null;
            if ($id===null) continue;
            $cantidades[$id]=($cantidades[$id]??0)+max(0,(int)($linea['cantidad']??0));
        }
    }
    arsort($cantidades, SORT_NUMERIC); $top=[];
    foreach (array_slice($cantidades,0,10,true) as $id=>$cantidad) {
        $top[]=['producto_id'=>(int)$id,'nombre'=>$productos[$id]??'Producto #'.$id,'unidades_vendidas'=>$cantidad];
    }
    respond(200, ['fuente'=>'proyecciones_por_rest','top_productos'=>$top,
        'truncado'=>(bool)($ventas['data']['truncado']??false)]);
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
