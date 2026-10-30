<?php
require __DIR__ . '/../common/bootstrap.php';

// Proyección del inventario REAL de Laravel. Es de solo lectura para las APIs
// públicas: los descuentos de POS, compras y checkout suceden SOLO en Laravel.
function posInventoryTables(): void
{
    db()->exec("CREATE TABLE IF NOT EXISTS productos_pos (
        id BIGINT UNSIGNED PRIMARY KEY, codigo VARCHAR(50) NULL,
        nombre VARCHAR(150) NOT NULL, precio DECIMAL(12,2) NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1, stock_total INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    db()->exec("CREATE TABLE IF NOT EXISTS variantes_pos (
        id BIGINT UNSIGNED PRIMARY KEY, producto_id BIGINT UNSIGNED NOT NULL,
        talla VARCHAR(30) NULL, color VARCHAR(50) NULL, stock INT UNSIGNED NOT NULL,
        KEY variantes_pos_producto_idx (producto_id),
        CONSTRAINT variantes_pos_producto_fk FOREIGN KEY (producto_id)
          REFERENCES productos_pos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function posInternalAuth(): void
{
    $secreto = getenv('INTERNAL_TOKEN') ?: '';
    if (strlen($secreto) < 24 || !hash_equals($secreto, $_SERVER['HTTP_X_INTERNAL_TOKEN'] ?? '')) {
        respond(403, ['error'=>'No autorizado para sincronizar datos']);
    }
}

$posPath = endpoint();
if ($posPath === '/api/inventario/pos/sincronizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    posInternalAuth();
    $input = payload();
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if ($id === false || $id === null) respond(422, ['error'=>'ID de producto inválido']);
    posInventoryTables();
    $pdo = db(); $pdo->beginTransaction();
    try {
        if (($input['eliminado'] ?? false) === true) {
            $stmt = $pdo->prepare('DELETE FROM productos_pos WHERE id=?'); $stmt->execute([$id]);
        } else {
            $nombre = trim((string)($input['nombre'] ?? ''));
            $precio = $input['precio'] ?? null;
            $variantes = $input['variantes'] ?? null;
            if ($nombre === '' || strlen($nombre)>150 || !is_numeric($precio) || $precio < 0
                || !is_array($variantes) || count($variantes)>150) {
                respond(422, ['error'=>'Datos del producto inválidos']);
            }
            $total=0; $ids=[];
            foreach ($variantes as $v) {
                $vid = filter_var($v['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
                if (!$vid || isset($ids[$vid]) || !isset($v['stock']) || !is_int($v['stock']) || $v['stock']<0) {
                    respond(422, ['error'=>'Variante incorrecta']);
                }
                $ids[$vid]=true; $total+=$v['stock'];
            }
            $stmt=$pdo->prepare('INSERT INTO productos_pos(id,codigo,nombre,precio,activo,stock_total)
                VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE codigo=VALUES(codigo),nombre=VALUES(nombre),
                precio=VALUES(precio),activo=VALUES(activo),stock_total=VALUES(stock_total)');
            $stmt->execute([$id,$input['codigo']??null,$nombre,$precio,empty($input['activo'])?0:1,$total]);
            $pdo->prepare('DELETE FROM variantes_pos WHERE producto_id=?')->execute([$id]);
            $stmt=$pdo->prepare('INSERT INTO variantes_pos(id,producto_id,talla,color,stock) VALUES(?,?,?,?,?)');
            foreach ($variantes as $v) {
                $stmt->execute([$v['id'],$id,$v['talla']??null,$v['color']??null,$v['stock']]);
            }
        }
        $pdo->commit();
        respond(200, ['sincronizado'=>true,'producto_id'=>$id]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
if ($posPath === '/api/inventario/pos/productos' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated(); posInventoryTables();
    $rows = db()->query('SELECT p.id AS producto_id,p.codigo,p.nombre,p.precio,p.activo,
        p.stock_total, v.id AS variante_id,v.talla,v.color,v.stock
        FROM productos_pos p LEFT JOIN variantes_pos v ON v.producto_id=p.id ORDER BY p.id,v.id')->fetchAll();
    respond(200, ['fuente'=>'proyeccion_laravel','productos'=>$rows]);
}
if ($posPath === '/api/inventario/pos/resumen' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated(); posInventoryTables();
    $summary=db()->query('SELECT COUNT(*) AS productos,COALESCE(SUM(stock_total),0) AS existencias FROM productos_pos')->fetch();
    respond(200, ['fuente'=>'proyeccion_laravel','resumen'=>$summary]);
}


$path = endpoint();
if ($path === '/health') respond(200, ['servicio' => 'inventario', 'estado' => 'ok']);
if ($path === '/api/inventario/productos' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated();
    $products = db()->query('SELECT p.id, p.nombre, p.precio, v.id AS variante_id, v.talla, v.color, v.stock FROM productos p JOIN variantes v ON v.producto_id=p.id WHERE p.activo=1 ORDER BY p.id, v.id')->fetchAll();
    respond(200, ['productos' => $products]);
}
if ($path === '/api/inventario/alertas' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    authenticated();
    $rows = db()->query('SELECT p.nombre, v.id AS variante_id, v.talla, v.color, v.stock FROM variantes v JOIN productos p ON p.id=v.producto_id WHERE p.activo=1 AND v.stock<5 ORDER BY v.stock')->fetchAll();
    respond(200, ['alertas' => $rows]);
}
if ($path === '/api/inventario/productos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    authenticated(['administrador']);
    $input = payload();
    $nombre = trim((string)($input['nombre'] ?? ''));
    $precio = $input['precio'] ?? null;
    $talla = trim((string)($input['talla'] ?? ''));
    $color = trim((string)($input['color'] ?? ''));
    $stock = $input['stock'] ?? null;
    if (!$nombre || !is_numeric($precio) || $precio <= 0 || !$talla || !$color || !is_int($stock) || $stock < 0) {
        respond(422, ['error' => 'nombre, precio positivo, talla, color y stock entero no negativo requeridos']);
    }
    $pdo=db(); $pdo->beginTransaction();
    try {
        $stmt=$pdo->prepare('INSERT INTO productos(nombre, precio, activo) VALUES(?,?,1)');
        $stmt->execute([$nombre,$precio]);
        $id=(int)$pdo->lastInsertId();
        $stmt=$pdo->prepare('INSERT INTO variantes(producto_id,talla,color,stock) VALUES(?,?,?,?)');
        $stmt->execute([$id,$talla,$color,$stock]);
        $variante=(int)$pdo->lastInsertId();
        $pdo->commit();
        respond(201,['producto_id'=>$id,'variante_id'=>$variante]);
    } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
}
if (preg_match('#^/api/inventario/productos/(\d+)$#', $path, $matches)) {
    authenticated(['administrador']);
    $id=(int)$matches[1];
    if ($_SERVER['REQUEST_METHOD']==='PUT') {
        $input=payload(); $nombre=trim((string)($input['nombre']??'')); $precio=$input['precio']??null;
        if (!$nombre || !is_numeric($precio) || $precio<=0) respond(422,['error'=>'nombre y precio válido requeridos']);
        $stmt=db()->prepare('UPDATE productos SET nombre=?, precio=? WHERE id=? AND activo=1');
        $stmt->execute([$nombre,$precio,$id]);
        if (!$stmt->rowCount()) respond(404,['error'=>'No existe producto activo o no hubo cambios']);
        respond(200,['producto_id'=>$id,'actualizado'=>true]);
    }
    if ($_SERVER['REQUEST_METHOD']==='DELETE') {
        $stmt=db()->prepare('UPDATE productos SET activo=0 WHERE id=? AND activo=1');
        $stmt->execute([$id]);
        if (!$stmt->rowCount()) respond(404,['error'=>'Producto no encontrado']);
        respond(200,['producto_id'=>$id,'desactivado'=>true]);
    }
}
if (preg_match('#^/api/inventario/variantes/(\d+)$#', $path, $m)) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        authenticated();
        $stmt = db()->prepare('SELECT p.id AS producto_id, p.nombre, p.precio, v.id AS variante_id, v.talla, v.color, v.stock FROM variantes v JOIN productos p ON p.id=v.producto_id WHERE v.id=? AND p.activo=1');
        $stmt->execute([(int)$m[1]]);
        $row = $stmt->fetch();
        if (!$row) respond(404, ['error' => 'Variante no encontrada']);
        respond(200, ['variante' => $row]);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        authenticated();
        $internal = getenv('INTERNAL_TOKEN');
        if (!$internal || !hash_equals($internal, $_SERVER['HTTP_X_INTERNAL_TOKEN'] ?? '')) respond(403, ['error' => 'Operación interna no autorizada']);
        $input = payload();
        $delta = $input['delta'] ?? null;
        if (!is_int($delta) || $delta === 0 || abs($delta) > 10000) respond(422, ['error' => 'delta entero entre -10000 y 10000 (distinto de cero)']);
        $pdo = db();
        $stmt = $pdo->prepare('UPDATE variantes SET stock=stock+?, updated_at=NOW() WHERE id=? AND stock+?>=0');
        $stmt->execute([$delta, (int)$m[1], $delta]);
        if ($stmt->rowCount() !== 1) respond(409, ['error' => 'Variante inexistente o existencias insuficientes']);
        $stmt = $pdo->prepare('SELECT id, stock FROM variantes WHERE id=?');
        $stmt->execute([(int)$m[1]]);
        respond(200, ['variante' => $stmt->fetch()]);
    }
}
respond(404, ['error' => 'Ruta no encontrada']);
