# SIS_ROPA — Auditoría académica reproducible y SIN escrituras.
# Valida gateway, JWT, lectura REST y consistencia Master/Réplica.
# Usa las credenciales DEMO que ya se incluyen en distributed/db/01-init.sql.
# Ejecutar desde C:\Users\jobat\sis_ropa en Windows PowerShell.
$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8090'
$compose = 'docker-compose.distribuido.yml'
$envFile = '.env.distribuido'
$evidencias = New-Object 'System.Collections.Generic.List[string]'
$errores = New-Object 'System.Collections.Generic.List[string]'

function Registrar([string] $texto) {
    Write-Host $texto
    [void] $script:evidencias.Add($texto)
}
function Comprobar([bool] $condicion, [string] $nombre) {
    if ($condicion) { Registrar ('OK  - ' + $nombre) }
    else { Registrar ('FALLO - ' + $nombre); [void] $script:errores.Add($nombre) }
}
function ToNumero($valor) { return [int64] $valor }

if (!(Test-Path $compose) -or !(Test-Path $envFile)) {
    throw 'Ejecuta el script desde la raíz de sis_ropa. Faltan docker-compose.distribuido.yml o .env.distribuido.'
}
Registrar '=== SIS_ROPA - AUDITORIA ACADEMICA ==='
Registrar ('Fecha local: ' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))
Registrar 'Prueba de solo lectura: no crea productos, ventas ni pedidos.'

try {
    & docker compose --env-file $envFile -f $compose config --quiet
    Comprobar ($LASTEXITCODE -eq 0) 'Docker Compose: configuración válida'

    $necesarios = @('app', 'auth', 'inventory', 'sales', 'reports', 'gateway', 'db-master', 'db-replica')
    $ejecutando = @(& docker compose --env-file $envFile -f $compose ps --services --filter 'status=running')
    Comprobar ($LASTEXITCODE -eq 0) 'Docker: consulta de servicios'
    foreach ($nombre in $necesarios) {
        Comprobar ($ejecutando -contains $nombre) ('Contenedor activo: ' + $nombre)
    }

    $web = Invoke-WebRequest -UseBasicParsing -Uri "$base/tienda" -MaximumRedirection 5 -TimeoutSec 15
    Comprobar ($web.StatusCode -eq 200) 'Gateway Nginx: catálogo HTTP 200'

    # Un usuario NO autenticado no puede consultar inventario distribuido.
    $httpNoAutenticado = 0
    try {
        $respuesta = Invoke-WebRequest -UseBasicParsing -Uri "$base/api/inventario/pos/resumen" -TimeoutSec 10 -ErrorAction Stop
        $httpNoAutenticado = [int] $respuesta.StatusCode
    } catch {
        if ($_.Exception.Response -and $_.Exception.Response.StatusCode) {
            $httpNoAutenticado = [int] $_.Exception.Response.StatusCode
        }
    }
    Comprobar ($httpNoAutenticado -eq 401) 'Seguridad REST: sin JWT responde 401'

    $cuerpo = @{ correo='admin@demo.local'; password='Demo12345!' } | ConvertTo-Json -Compress
    $login = Invoke-RestMethod -Method Post -Uri "$base/api/auth/login" -ContentType 'application/json' -Body $cuerpo -TimeoutSec 15
    Comprobar (![string]::IsNullOrWhiteSpace([string] $login.token)) 'Auth: login JWT recibido'
    $headers = @{ Authorization = ('Bearer ' + $login.token) }
    $verificacion = Invoke-RestMethod -Method Post -Uri "$base/api/auth/verify" -Headers $headers -ContentType 'application/json' -Body '{}' -TimeoutSec 15
    Comprobar (($verificacion.valido -eq $true) -and ($verificacion.usuario.rol -eq 'administrador')) 'Auth: token JWT verificado por API'

    $inventario = Invoke-RestMethod -Uri "$base/api/inventario/pos/resumen" -Headers $headers -TimeoutSec 15
    $ventas = Invoke-RestMethod -Uri "$base/api/ventas/pos/resumen" -Headers $headers -TimeoutSec 15
    $reporte = Invoke-RestMethod -Uri "$base/api/reportes/pos/resumen" -Headers $headers -TimeoutSec 15
    Comprobar ($reporte.tipo -eq 'proyeccion_solo_lectura') 'Reportes: lectura por REST de inventario y ventas'

    $lineas = @(& docker compose --env-file $envFile -f $compose exec -T app php artisan distribuido:auditar-datos --json)
    $salida = ($lineas -join "`n").Trim()
    $datos = $salida | ConvertFrom-Json
    Comprobar ($LASTEXITCODE -eq 0 -and $datos.correcto -eq $true) 'MariaDB: master y réplica coinciden; catálogo mínimo de 20'
    if ($null -ne $datos.error) { throw $datos.error }

    Comprobar ((ToNumero $datos.master.productos) -eq (ToNumero $inventario.resumen.productos)) 'Productos Laravel = REST'
    Comprobar ((ToNumero $datos.master.existencias) -eq (ToNumero $inventario.resumen.existencias)) 'Stock Laravel = REST (sin descuento doble)'
    Comprobar ((ToNumero $datos.master.ventas_pos) -eq (ToNumero $ventas.resumen.ventas)) 'Ventas Laravel = REST'
    Comprobar ((ToNumero $datos.master.pedidos_web) -eq (ToNumero $ventas.pedidos.pedidos)) 'Pedidos web Laravel = REST'
    Comprobar ((ToNumero $reporte.inventario.existencias) -eq (ToNumero $inventario.resumen.existencias)) 'Reporte distribuido = inventario REST'

    Registrar ('Productos: ' + $datos.master.productos + ' | Unidades: ' + $datos.master.existencias)
    Registrar ('Ventas POS: ' + $datos.master.ventas_pos + ' | Pedidos web: ' + $datos.master.pedidos_web)
} catch {
    $mensaje = $_.Exception.Message
    Registrar ('ERROR DE AUDITORIA: ' + $mensaje)
    [void] $errores.Add($mensaje)
}

Registrar ('Resultado: ' + $(if ($errores.Count -eq 0) { 'PRUEBAS SUPERADAS' } else { 'HAY ' + $errores.Count + ' PRUEBA(S) POR CORREGIR' }))
Registrar 'Alcance: infraestructura local, réplica y proyecciones REST. No verifica hosting público ni escrituras de POS mediante microservicios.'
$carpeta = Join-Path (Get-Location) 'docs\evidencias'
New-Item -ItemType Directory -Path $carpeta -Force | Out-Null
$nombre = 'AUDITORIA_SIS_ROPA_' + (Get-Date -Format 'yyyyMMdd_HHmmss') + '.txt'
$ruta = Join-Path $carpeta $nombre
$evidencias | Out-File -FilePath $ruta -Encoding utf8
Write-Host ('Evidencia generada: ' + $ruta)
if ($errores.Count -gt 0) { exit 1 }
exit 0
