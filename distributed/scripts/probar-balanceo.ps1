param(
    [switch]$ProbarFallo
)

$ErrorActionPreference = 'Stop'
$raiz = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$base = Join-Path $raiz 'docker-compose.distribuido.yml'
$extra = Join-Path $raiz 'docker-compose.balanceo.yml'
$envFile = Join-Path $raiz '.env.distribuido'
$salida = Join-Path $raiz 'docs\evidencias'
$endpoint = 'http://127.0.0.1:8090/api/inventario/pos/resumen'

if (-not (Test-Path $envFile)) { throw 'Falta .env.distribuido en la raíz del proyecto.' }
if (-not (Test-Path $extra)) { throw 'Falta docker-compose.balanceo.yml en la raíz del proyecto.' }

Push-Location $raiz
try {
    $compose = @('compose', '--env-file', '.env.distribuido', '-f', 'docker-compose.distribuido.yml', '-f', 'docker-compose.balanceo.yml')

    function Consultar-Inventario {
        param([int]$Intentos = 1)
        for ($n = 1; $n -le $Intentos; $n++) {
            $codigo = 0
            $nodo = ''
            try {
                $r = Invoke-WebRequest -Uri $endpoint -UseBasicParsing -TimeoutSec 15 -ErrorAction Stop
                $codigo = [int]$r.StatusCode
                $nodo = [string]$r.Headers['X-SISROPA-Upstream']
            } catch {
                if ($null -eq $_.Exception.Response) {
                    Write-Warning "Intento $n de ${Intentos}: solicitud agotada o no hubo respuesta de Nginx."
                    if ($n -eq $Intentos) { throw }
                    Start-Sleep -Seconds 1
                    continue
                }
                $r = $_.Exception.Response
                $codigo = [int]$r.StatusCode
                $nodo = [string]$r.Headers['X-SISROPA-Upstream']
            }
            # HTTP 401 es esperado SIN token: prueba que el backend protege la API.
            if ($codigo -eq 401 -and -not [string]::IsNullOrWhiteSpace($nodo)) {
                return $nodo
            }
            if ($n -eq $Intentos) { throw "Respuesta inesperada: HTTP $codigo, nodo '$nodo'." }
            Start-Sleep -Seconds 1
        }
    }

    Write-Host '=== SIS_ROPA: BALANCEO NGINX ==='
    & docker @compose config --quiet
    if ($LASTEXITCODE -ne 0) { throw 'Docker Compose no pasó la validación.' }

    $nodos = @()
    for ($i = 1; $i -le 8; $i++) {
        $nodo = Consultar-Inventario -Intentos 2
        $nodos += $nodo
        Write-Host "Consulta $i -> HTTP 401 (JWT requerido) -> $nodo"
    }
    $distintos = @($nodos | Select-Object -Unique)
    if ($distintos.Count -lt 2) {
        throw 'No se comprobaron dos nodos diferentes. Revisa inventory2 y la configuración de Nginx.'
    }
    Write-Host "OK: Nginx distribuyó consultas entre $($distintos.Count) nodos."

    $pruebaFalloOK = $false
    if ($ProbarFallo) {
        Write-Host '=== TOLERANCIA A FALLOS (se detendrá inventory temporalmente) ==='
        $detenido = $false
        try {
            & docker @compose stop inventory
            if ($LASTEXITCODE -ne 0) { throw 'No se pudo detener inventory.' }
            $detenido = $true
            Start-Sleep -Seconds 3
            $contenedorRespaldo = (& docker @compose ps -q inventory2 | Select-Object -First 1)
            if ([string]::IsNullOrWhiteSpace($contenedorRespaldo)) {
                throw 'No se pudo identificar el contenedor inventory2.'
            }
            $ipRespaldo = ((& docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $contenedorRespaldo) | Select-Object -First 1).Trim()
            if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($ipRespaldo)) {
                throw 'No se pudo obtener la IP del nodo de respaldo.'
            }
            $nodoDisponible = Consultar-Inventario -Intentos 4
            # X-SISROPA-Upstream puede enumerar un intento fallido y luego el nodo que respondió.
            $ultimoNodo = ($nodoDisponible -split ',')[-1].Trim()
            if (-not $ultimoNodo.StartsWith("${ipRespaldo}:")) {
                throw "Falló la verificación de respaldo: último upstream '$ultimoNodo', inventory2='$ipRespaldo'."
            }
            Write-Host "OK: inventory2 respondió con inventory detenido: $ultimoNodo"
            $pruebaFalloOK = $true
        } finally {
            if ($detenido) {
                Write-Host 'Restaurando inventory...'
                & docker @compose start inventory
                if ($LASTEXITCODE -ne 0) {
                    Write-Warning 'No se logró reiniciar inventory. Ejecútalo manualmente con docker compose ... start inventory.'
                }
            }
        }
    }

    New-Item -ItemType Directory -Path $salida -Force | Out-Null
    $archivo = Join-Path $salida ("BALANCEO_SIS_ROPA_{0}.txt" -f (Get-Date -Format 'yyyyMMdd_HHmmss'))
    @(
        'SIS_ROPA - EVIDENCIA DE BALANCEO DE CARGA LOCAL',
        "Fecha: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')",
        "Ruta: $endpoint",
        'Resultado: 8 consultas respondidas HTTP 401 (autenticación JWT exigida por backend).',
        "Nodos distintos observados: $($distintos.Count)",
        "Nodos: $($distintos -join ', ')",
        "Prueba de fallo solicitada: $([bool]$ProbarFallo)",
        "Prueba de fallo superada: $pruebaFalloOK",
        'ALCANCE: balanceo/failover de lecturas REST en Docker local.',
        'NO PRUEBA: despliegue público, réplicas DB de lectura, ni escritura del POS mediante microservicios.'
    ) | Out-File -FilePath $archivo -Encoding UTF8
    if ($ProbarFallo) {
        Write-Host "PRUEBA DE BALANCEO Y TOLERANCIA A FALLOS SUPERADA. Evidencia: $archivo"
    } else {
        Write-Host "PRUEBA DE BALANCEO SUPERADA. Evidencia: $archivo"
    }
} finally {
    Pop-Location
}
