# Auditoría REST de SOLO LECTURA (no crea ventas/pedidos, no descuenta stock).
# Ejecutar desde la raíz SIS_ROPA tras actualizar contenedores.
$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8090'
$evidencia = New-Object 'System.Collections.Generic.List[string]'
function Registrar([string]$mensaje) {
    Write-Host $mensaje
    [void]$script:evidencia.Add($mensaje)
}
Registrar '=== SIS_ROPA - PRUEBA DE ENDPOINTS REST ==='
Registrar ('Fecha: ' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))
Registrar 'Solo lectura: no se modifican bases de datos comerciales.'
try {
    $credenciales = @{ correo='admin@demo.local'; password='Demo12345!' } | ConvertTo-Json -Compress
    $sesion = Invoke-RestMethod -Method Post -Uri "$base/api/auth/login" -ContentType 'application/json' -Body $credenciales -TimeoutSec 15
    if ([string]::IsNullOrEmpty([string]$sesion.token)) { throw 'No se recibió JWT' }
    $cabeceras = @{ Authorization = ('Bearer ' + $sesion.token) }
    $resumen = Invoke-RestMethod -Uri "$base/api/inventario/pos/resumen" -Headers $cabeceras -TimeoutSec 15
    $ventas = Invoke-RestMethod -Uri "$base/api/reportes/pos/ventas" -Headers $cabeceras -TimeoutSec 15
    $top = Invoke-RestMethod -Uri "$base/api/reportes/top-productos" -Headers $cabeceras -TimeoutSec 15
    if ([int]$resumen.resumen.productos -lt 20) { throw 'No se encontraron 20 productos' }
    if ($ventas.fuente -ne 'proyeccion_laravel_por_rest') { throw 'El reporte de ventas no es REST de proyección' }
    if ($top.fuente -ne 'proyecciones_por_rest') { throw 'El top no procede de REST' }
    Registrar ('OK - Productos: ' + $resumen.resumen.productos)
    Registrar ('OK - GET /api/reportes/pos/ventas | ventas: ' + $ventas.cantidad + ', total Q' + $ventas.total)
    Registrar ('OK - GET /api/reportes/top-productos | filas: ' + @($top.top_productos).Count)
    if ($ventas.truncado -or $top.truncado) { Registrar 'ADVERTENCIA - Reportes parciales: se superó el máximo de 2000 ventas del endpoint.' }
    $detalle = Invoke-RestMethod -Uri "$base/api/ventas/pos/detalle" -Headers $cabeceras -TimeoutSec 15
    Registrar ('OK - GET /api/ventas/pos/detalle | ventas proyectadas: ' + @($detalle.ventas).Count)
    if (@($detalle.ventas | Where-Object { $null -ne $_ }).Count -gt 0) {
        $id = [int]$detalle.ventas[0].id
        $factura = Invoke-RestMethod -Uri "$base/api/ventas/$id/factura" -Headers $cabeceras -TimeoutSec 15
        if ($factura.venta_id -ne $id) { throw 'El ID de factura no coincide' }
        Registrar ('OK - GET /api/ventas/' + $id + '/factura | factura: ' + $factura.numero_factura)
    } else {
        Registrar 'INFO - No hay facturas POS proyectadas; endpoint sin venta para probar.'
    }
    Registrar 'RESULTADO: PRUEBAS REST SUPERADAS'
} catch {
    Registrar ('RESULTADO: ERROR - ' + $_.Exception.Message)
    $errorEncontrado = $true
}
$carpeta = Join-Path (Get-Location) 'docs\evidencias'
New-Item -ItemType Directory -Path $carpeta -Force | Out-Null
$ruta = Join-Path $carpeta ('ENDPOINTS_SIS_ROPA_' + (Get-Date -Format 'yyyyMMdd_HHmmss') + '.txt')
$evidencia | Out-File -FilePath $ruta -Encoding utf8
Write-Host ('Evidencia: ' + $ruta)
if ($errorEncontrado) { exit 1 }
