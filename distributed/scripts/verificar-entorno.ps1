# Diagnóstico SIN modificaciones: no imprime secretos y no crea ventas.
$ErrorActionPreference = 'Stop'
$compose = 'docker-compose.distribuido.yml'
$envFile = '.env.distribuido'
if (!(Test-Path $compose) -or !(Test-Path $envFile)) {
    Write-Host 'Abre PowerShell en la raíz de sis_ropa; faltan compose o .env.distribuido.'
    exit 1
}
Write-Host '=== 1. Compose ==='
docker compose --env-file $envFile -f $compose config --quiet
if ($LASTEXITCODE -ne 0) { exit 1 }
Write-Host '=== 2. Contenedores ==='
docker compose --env-file $envFile -f $compose ps -a
if ($LASTEXITCODE -ne 0) { exit 1 }
Write-Host '=== 3. Laravel: migraciones pendientes ==='
docker compose --env-file $envFile -f $compose exec -T app php artisan migrate:status
Write-Host '=== 4. Gateway web ==='
try {
    $response = Invoke-WebRequest -Uri 'http://127.0.0.1:8090/tienda' -MaximumRedirection 5 -TimeoutSec 15
    Write-Host "HTTP $($response.StatusCode) - Tienda pública disponible"
} catch {
    Write-Warning "No se pudo consultar la tienda: $($_.Exception.Message)"
}
Write-Host '=== 5. Reportes de proyección (ejecución de script existente) ==='
if (Test-Path '.\distributed\scripts\probar-sincronizacion-pos.ps1') {
    & '.\distributed\scripts\probar-sincronizacion-pos.ps1'
}
Write-Host 'Diagnóstico terminado. Esto NO equivale a validar flujos completos ni hosting público.'
