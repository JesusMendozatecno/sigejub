# descargar-paquetes.ps1
# Descarga los binarios "offline" que el instalador usa cuando el equipo destino
# no tiene internet (o cuando no hay PHP/Composer/BD instalados).
#
# Uso:
#   .\descargar-paquetes.ps1              -> php + composer + mariadb + postgres (todo)
#   .\descargar-paquetes.ps1 -SoloBasico  -> solo php + composer (rápido)
#
# Salida: paquetes/ junto a este script (no se sube a git)

param(
    [switch]$SoloBasico
)

$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$dir = Join-Path $PSScriptRoot "paquetes"
New-Item -ItemType Directory -Path $dir -Force | Out-Null

function Get-FileEx {
    param([string]$Url, [string]$Dest, [string]$Name)
    Write-Host "Descargando $Name ..." -ForegroundColor Cyan
    $tmp = "$Dest.part"
    Invoke-WebRequest -Uri $Url -OutFile $tmp -UseBasicParsing
    Move-Item -Force -Path $tmp -Destination $Dest
    $mb = [math]::Round((Get-Item $Dest).Length / 1MB, 1)
    Write-Host "  [OK] $mb MB -> $Dest" -ForegroundColor Green
}

function Get-LatestPhpZipName {
    $page = (Invoke-WebRequest -Uri "https://windows.php.net/downloads/releases/" -UseBasicParsing).Content
    $rx = [regex]'php-8\.\d+\.\d+-nts-Win32-vs17-x64\.zip'
    $names = @($rx.Matches($page) | ForEach-Object { $_.Value } | Sort-Object -Unique)
    if ($names.Count -eq 0) { throw "No se pudo detectar la ultima version de PHP en windows.php.net" }
    # Preferir la serie 8.4 (maxima compatibilidad con Laravel), si no la 8.3/8.5
    $cand = $names | Where-Object { $_ -like 'php-8.4.*' }
    if ($cand.Count -eq 0) { $cand = $names }
    return ($cand | Sort-Object -Descending)[0]
}

Write-Host "== Descargando paquetes offline SIGEJUB ==" -ForegroundColor Yellow

# 1) PHP (última 8.4.x NTS x64)
$phpName = Get-LatestPhpZipName
Get-FileEx -Url ("https://windows.php.net/downloads/releases/" + $phpName) `
           -Dest (Join-Path $dir $phpName) -Name "PHP ($phpName)"

# 2) Composer (phar portable)
Get-FileEx -Url "https://getcomposer.org/download/latest-stable/composer.phar" `
           -Dest (Join-Path $dir "composer.phar") -Name "Composer"

# 3) Runtime VC++ 2015-2022 x64 (lo necesita PHP portable)
Get-FileEx -Url "https://aka.ms/vs/17/release/vc_redist.x64.exe" `
           -Dest (Join-Path $dir "vc_redist.x64.exe") -Name "VC++ Runtime x64"

if (-not $SoloBasico) {
    # 4) MariaDB portable (Win x64 Zip)
    Get-FileEx -Url "https://archive.mariadb.org/mariadb-11.4.5/winx64-packages/mariadb-11.4.5-winx64.zip" `
               -Dest (Join-Path $dir "mariadb-11.4.5-winx64.zip") -Name "MariaDB 11.4.5 (Win64)"

    # 5) PostgreSQL portable (Win x64 Binaries)
    Get-FileEx -Url "https://get.enterprisedb.com/postgresql/postgresql-16.8-1-windows-x64-binaries.zip" `
               -Dest (Join-Path $dir "postgresql-16.8-1-windows-x64-binaries.zip") -Name "PostgreSQL 16.8 (Win64)"
} else {
    Write-Host "[AVISO] Omitidos MariaDB y PostgreSQL (usa -SoloBasico para solo PHP+Composer+VC++)." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Listo. Copia la carpeta 'paquetes' junto al instalador SIGEJUB-Installer.exe" -ForegroundColor Cyan
Write-Host "para que funcione sin internet en el equipo destino." -ForegroundColor Cyan