# Ejecutar en PowerShell después de levantar los contenedores y crear sus tablas.
$base = 'http://127.0.0.1:8090'
$authBody = @{ correo = 'admin@demo.local'; password = 'Demo12345!' } | ConvertTo-Json
$session = Invoke-RestMethod -Method Post -Uri "$base/api/auth/login" -ContentType 'application/json' -Body $authBody
$headers = @{ Authorization = "Bearer $($session.token)" }
Write-Host "1) Login JWT correcto: $($session.usuario.rol)"
$antes = Invoke-RestMethod -Uri "$base/api/inventario/productos" -Headers $headers
$previo = ($antes.productos | Where-Object { $_.variante_id -eq 1 }).stock
Write-Host "2) Existencias de variante #1: $previo"
$venta = Invoke-RestMethod -Method Post -Uri "$base/api/ventas" -Headers $headers -ContentType 'application/json' -Body (@{variante_id=1;cantidad=1}|ConvertTo-Json)
Write-Host "3) Venta distribuida #$($venta.venta_id) por Q$($venta.total)"
$despues = Invoke-RestMethod -Uri "$base/api/inventario/productos" -Headers $headers
$actual = ($despues.productos | Where-Object { $_.variante_id -eq 1 }).stock
Write-Host "4) Existencias restantes: $actual"
if ([int]$actual -ne ([int]$previo - 1)) { throw 'ERROR: el inventario no se redujo en una unidad' }
$reporte = Invoke-RestMethod -Uri "$base/api/reportes/resumen" -Headers $headers
Write-Host "5) Reportes REST: total Q$($reporte.ventas.total), stock $($reporte.existencias)"
Write-Host 'PRUEBA BÁSICA SUPERADA. Esta demostración usa las BD de microservicios, NO el POS Laravel existente.'
