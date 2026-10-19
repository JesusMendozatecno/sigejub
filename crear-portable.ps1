# ============================================================
#  crear-portable.ps1 - Construye una copia portable de SIGEJUB
# ------------------------------------------------------------
#  Genera la carpeta SIGEJUB-Portable a partir de .\original
#  pensada para equipos que YA tienen PHP y MySQL/MariaDB
#  instalados (p.ej. XAMPP). Incluye vendor (no requiere Composer)
#  y excluye descargas del instalador, backups y logs.
#
#  Uso:
#     powershell -ExecutionPolicy Bypass -File .\crear-portable.ps1
#
#  Parametros opcionales:
#     -Destino  <ruta>   carpeta de salida (por defecto .\SIGEJUB-Portable)
#     -Puerto   8090     puerto web que usara la copia (APP_URL)
#     -Forzar            borra el destino si ya existe
# ============================================================
[CmdletBinding()]
param(
    [string]$Destino,
    [int]$Puerto = 8090,
    [switch]$Forzar
)

$ErrorActionPreference = 'Stop'
$utf8SinBom = New-Object System.Text.UTF8Encoding($false)

$raiz = $PSScriptRoot
$src  = Join-Path $raiz 'original'
if (-not (Test-Path -LiteralPath $src)) {
    throw "No se encontro la carpeta de origen: $src"
}
if (-not $Destino) { $Destino = Join-Path $raiz 'SIGEJUB-Portable' }

Write-Output '============================================'
Write-Output ' SIGEJUB - Generador de version portable'
Write-Output '============================================'
Write-Output "Origen : $src"
Write-Output "Destino: $Destino"
Write-Output ''

if (Test-Path -LiteralPath $Destino) {
    if ($Forzar) {
        Write-Output '[LIMPIEZA] Eliminando destino existente...'
        Remove-Item -LiteralPath $Destino -Recurse -Force
    } else {
        throw "El destino ya existe: $Destino  (usa -Forzar para reemplazarlo)"
    }
}

# ------------------------------------------------------------
# Copia del arbol (robocopy: codigos 0-7 = exito)
# ------------------------------------------------------------
$rcArgs = @(
    $src, $Destino, '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP', '/R:1', '/W:1',
    '/XD',
        (Join-Path $src 'compont'),
        (Join-Path $src 'installer-src'),
        (Join-Path $src 'node_modules'),
        (Join-Path $src 'storage\app\backups'),
        (Join-Path $src 'storage\logs'),
        (Join-Path $src 'storage\framework\cache'),
        (Join-Path $src 'storage\framework\sessions'),
        (Join-Path $src 'storage\framework\views'),
        (Join-Path $src 'public\storage'),
    '/XF',
        '.env', '*.log', '.phpunit.result.cache', 'SIGEJUB-Installer.exe',
        'build-installer.ps1', 'descargar-paquetes.ps1',
        'start.bat', 'start.sh', 'setup.bat', 'setup.sh',
        'instalar.sh', 'desinstalar.sh', 'sigejub-start.vbs'
)

Write-Output '[COPIA] Copiando el arbol (incluye vendor)...'
& robocopy @rcArgs | Out-Null
$rc = $LASTEXITCODE
if ($rc -ge 8) {
    throw "robocopy fallo con codigo $rc"
}

# ------------------------------------------------------------
# .env de la copia (mismo motor/DB que el proyecto, puerto propio)
# ------------------------------------------------------------
if (Test-Path -LiteralPath (Join-Path $src '.env')) {
    $envTexto = [System.IO.File]::ReadAllText((Join-Path $src '.env'))
} elseif (Test-Path -LiteralPath (Join-Path $src '.env.example')) {
    $envTexto = [System.IO.File]::ReadAllText((Join-Path $src '.env.example'))
} else {
    throw 'No hay .env ni .env.example en el origen.'
}
if ($envTexto -match '(?m)^APP_URL=') {
    $envTexto = [regex]::Replace($envTexto, '(?m)^APP_URL=.*$', "APP_URL=http://localhost:$Puerto")
}
if ($envTexto -match '(?m)^APP_PORT=') {
    $envTexto = [regex]::Replace($envTexto, '(?m)^APP_PORT=.*$', "APP_PORT=$Puerto")
}
[System.IO.File]::WriteAllText((Join-Path $Destino '.env'), $envTexto, $utf8SinBom)

# ------------------------------------------------------------
# Lanzadores: reutiliza start.bat / detener.bat de .\original
# e inyecta el enlace de storage dentro de iniciar.bat
# ------------------------------------------------------------
$storageBlock = @'
:: =============================================
:: ENLACE DE STORAGE (public\storage -> storage\app\public)
:: =============================================
if not exist "public\storage" (
    if exist "storage\app\public" (
        cmd /c mklink /J "public\storage" "storage\app\public" >nul 2>&1
        if not exist "public\storage" "%PHP_PATH%" artisan storage:link >nul 2>&1
    )
)

'@

$launcher = [System.IO.File]::ReadAllText((Join-Path $src 'start.bat'))
if ($launcher -notmatch [regex]::Escape('LEER PUERTO desde APP_URL en .env')) {
    throw 'No se encontro el marcador de puerto en start.bat'
}
$launcher = $launcher.Replace(':: LEER PUERTO desde APP_URL en .env', ($storageBlock + ':: LEER PUERTO desde APP_URL en .env'))
[System.IO.File]::WriteAllText((Join-Path $Destino 'iniciar.bat'), $launcher, $utf8SinBom)

$detener = [System.IO.File]::ReadAllText((Join-Path $src 'detener.bat'))
[System.IO.File]::WriteAllText((Join-Path $Destino 'detener.bat'), $detener, $utf8SinBom)

# ------------------------------------------------------------
# .gitignore propio de la portable
# ------------------------------------------------------------
$gitignore = @'
.env
*.log
/storage/logs/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/public/storage
/ini
'@
[System.IO.File]::WriteAllText((Join-Path $Destino '.gitignore'), $gitignore, $utf8SinBom)

# ------------------------------------------------------------
# Carpetas minimas que Laravel necesita en tiempo de ejecucion
# ------------------------------------------------------------
$minDirs = @(
    'storage\app\public', 'storage\framework\cache', 'storage\framework\sessions',
    'storage\framework\views', 'storage\logs', 'bootstrap\cache'
)
foreach ($d in $minDirs) {
    $full = Join-Path $Destino $d
    if (-not (Test-Path -LiteralPath $full)) {
        New-Item -ItemType Directory -Path $full -Force | Out-Null
    }
}

# ------------------------------------------------------------
# Resumen
# ------------------------------------------------------------
$bytes = (Get-ChildItem -LiteralPath $Destino -Recurse -Force -File |
    Measure-Object -Property Length -Sum).Sum
$mb = [math]::Round($bytes / 1MB, 1)

Write-Output ''
Write-Output '[OK] Version portable creada.'
Write-Output "Ruta  : $Destino"
Write-Output "Puerto: $Puerto"
Write-Output ("Tamano: {0} MB" -f $mb)
Write-Output ''
Write-Output 'Para usarla: ejecuta  iniciar.bat  dentro de la carpeta SIGEJUB-Portable.'
