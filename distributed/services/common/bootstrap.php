<?php

declare(strict_types=1);

function respond(int $code, array $data): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function payload(): array
{
    $body = file_get_contents('php://input');
    if (strlen($body) > 1048576) respond(413, ['error' => 'Solicitud demasiado grande']);
    $value = json_decode($body ?: '{}', true);
    if (!is_array($value) || json_last_error() !== JSON_ERROR_NONE) respond(400, ['error' => 'JSON inválido']);
    return $value;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'db-master';
        $name = getenv('DB_NAME') ?: '';
        $pdo = new PDO("mysql:host={$host};port=3306;dbname={$name};charset=utf8mb4", getenv('DB_USER'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

function bearer(): string
{
    if (!preg_match('/^Bearer\s+(.+)$/i', $_SERVER['HTTP_AUTHORIZATION'] ?? '', $m)) respond(401, ['error' => 'Falta token Bearer']);
    return trim($m[1]);
}

function b64url(string $binary): string
{
    return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
}

function unb64url(string $encoded): string|false
{
    return base64_decode(strtr($encoded, '-_', '+/'), true);
}

function makeToken(array $user): string
{
    $header = b64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $claims = b64url(json_encode([
        'sub' => (string)$user['id'], 'nombre' => $user['nombre'], 'rol' => $user['rol'],
        'iat' => time(), 'exp' => time() + 3600, 'iss' => 'sis-ropa-auth', 'aud' => 'sis-ropa-services',
    ]));
    $signature = b64url(hash_hmac('sha256', "$header.$claims", getenv('JWT_SECRET') ?: '', true));
    return "$header.$claims.$signature";
}

function verifyToken(string $token): array|false
{
    $parts = explode('.', $token);
    if (count($parts) !== 3 || !getenv('JWT_SECRET') || strlen(getenv('JWT_SECRET')) < 24) return false;
    [$head, $body, $sig] = $parts;
    $header = json_decode(unb64url($head) ?: '', true);
    if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') return false;
    $correct = b64url(hash_hmac('sha256', "$head.$body", getenv('JWT_SECRET'), true));
    if (!hash_equals($correct, $sig)) return false;
    $claims = json_decode(unb64url($body) ?: '', true);
    if (!is_array($claims) || ($claims['exp'] ?? 0) <= time() || ($claims['iss'] ?? '') !== 'sis-ropa-auth' || ($claims['aud'] ?? '') !== 'sis-ropa-services') return false;
    return $claims;
}

function callService(string $url, string $method = 'GET', ?array $data = null, ?string $token = null, array $extraHeaders = []): array
{
    $headers = ['Accept: application/json'];
    if ($data !== null) $headers[] = 'Content-Type: application/json';
    if ($token !== null) $headers[] = 'Authorization: Bearer ' . $token;
    $headers = array_merge($headers, $extraHeaders);
    $context = stream_context_create(['http' => [
        'method' => $method, 'header' => implode("\r\n", $headers), 'content' => $data === null ? '' : json_encode($data),
        'ignore_errors' => true, 'timeout' => 6,
    ]]);
    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) respond(503, ['error' => 'No se puede contactar el servicio dependiente', 'destino' => parse_url($url, PHP_URL_HOST)]);
    $line = $http_response_header[0] ?? 'HTTP/1.1 500';
    preg_match('/\s(\d{3})\s/', $line . ' ', $match);
    return ['status' => (int)($match[1] ?? 500), 'data' => json_decode($raw, true) ?: []];
}

function authenticated(array $roles = ['administrador','cajero']): array
{
    $token = bearer();
    $auth = callService((getenv('AUTH_URL') ?: 'http://auth:8083') . '/api/auth/verify', 'POST', [], $token);
    if ($auth['status'] !== 200) respond(401, ['error' => 'Token vencido o inválido']);
    $user = $auth['data']['usuario'] ?? null;
    if (!is_array($user) || !in_array($user['rol'] ?? '', $roles, true)) respond(403, ['error' => 'Rol sin permiso']);
    return $user;
}

function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) respond(405, ['error' => 'Método HTTP no permitido']);
}

function endpoint(): string
{
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
}

set_exception_handler(function (Throwable $e): void {
    error_log((string)$e);
    respond(500, ['error' => 'Error interno del servicio']);
});
