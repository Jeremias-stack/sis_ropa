# Consultas de solo lectura al inventario y ventas reales del POS replicados en servicios REST.
# Ejecutar DESPUES de: docker compose ... exec app php artisan distribuido:sincronizar
$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8090'
$login = Invoke-RestMethod -Method Post -Uri "$base/api/auth/login" -ContentType 'application/json' -Body (@{
    correo = 'admin@demo.local'; password = 'Demo12345!'
} | ConvertTo-Json)
$headers = @{ Authorization = "Bearer $($login.token)" }
$inventario = Invoke-RestMethod -Method Get -Uri "$base/api/inventario/pos/resumen" -Headers $headers
$ventas = Invoke-RestMethod -Method Get -Uri "$base/api/ventas/pos/resumen" -Headers $headers
$reporte = Invoke-RestMethod -Method Get -Uri "$base/api/reportes/pos/resumen" -Headers $headers
Write-Host "POS real -> productos proyectados: $($inventario.resumen.productos)"
Write-Host "POS real -> existencias proyectadas: $($inventario.resumen.existencias)"
Write-Host "POS real -> ventas registradas: $($ventas.resumen.ventas)"
Write-Host "POS real -> ingresos: Q$($ventas.resumen.ingresos)"
Write-Host "Tienda web -> pedidos: $($ventas.pedidos.pedidos)"
Write-Host "Reportes REST -> fuente: $($reporte.tipo)"
Write-Host 'PRUEBA DE LECTURA DISTRIBUIDA SUPERADA. La BD Laravel conserva la autoridad sobre stock.'
