<?php
require __DIR__ . '/../common/bootstrap.php';

$path = endpoint();
if ($path === '/health') respond(200, ['servicio' => 'auth', 'estado' => 'ok']);
if ($path === '/api/auth/login') {
    requireMethod('POST');
    $input = payload();
    if (!is_string($input['correo'] ?? null) || !is_string($input['password'] ?? null)) respond(422, ['error' => 'Se requieren correo y password']);
    $stmt = db()->prepare('SELECT id, nombre, correo, rol, password_hash FROM usuarios WHERE correo = ? LIMIT 1');
    $stmt->execute([$input['correo']]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($input['password'], $user['password_hash'])) respond(401, ['error' => 'Credenciales incorrectas']);
    unset($user['password_hash']);
    respond(200, ['token' => makeToken($user), 'token_type' => 'Bearer', 'expira_en' => 3600, 'usuario' => $user]);
}
if ($path === '/api/auth/register') {
    requireMethod('POST');
    $input = payload();
    $nombre = trim((string)($input['nombre'] ?? ''));
    $correo = trim((string)($input['correo'] ?? ''));
    $password = $input['password'] ?? '';
    if (strlen($nombre) < 2 || !filter_var($correo, FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) < 8) {
        respond(422, ['error' => 'Nombre, correo válido y contraseña de al menos 8 caracteres']);
    }
    // Nunca se acepta el rol recibido por el cliente al registrarse.
    try {
        $stmt = db()->prepare('INSERT INTO usuarios(nombre, correo, password_hash, rol) VALUES(?,?,?,?)');
        $stmt->execute([$nombre, $correo, password_hash($password, PASSWORD_BCRYPT), 'cliente']);
        respond(201, ['usuario_id' => (int)db()->lastInsertId(), 'rol' => 'cliente']);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') respond(409, ['error' => 'Correo ya registrado']);
        throw $e;
    }
}
if ($path === '/api/auth/verify') {
    requireMethod('POST');
    $claims = verifyToken(bearer());
    if (!$claims) respond(401, ['error' => 'Token inválido']);
    respond(200, ['valido' => true, 'usuario' => ['id' => (int)$claims['sub'], 'nombre' => $claims['nombre'], 'rol' => $claims['rol']]]);
}
if ($path === '/api/auth/users') {
    requireMethod('GET');
    $user = verifyToken(bearer());
    if (!$user || ($user['rol'] ?? null) !== 'administrador') respond(403, ['error' => 'Solo administradores']);
    respond(200, ['usuarios' => db()->query('SELECT id, nombre, correo, rol FROM usuarios ORDER BY id')->fetchAll()]);
}
respond(404, ['error' => 'Ruta no encontrada']);
