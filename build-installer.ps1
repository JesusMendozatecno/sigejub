# build-installer.ps1
# Compila el instalador grafico SIGEJUB a .exe usando el compilador C# de .NET Framework
# Uso: .\build-installer.ps1
# Salida: SIGEJUB-Installer.exe

$ErrorActionPreference = 'Stop'
$projectDir = Split-Path -Parent $MyInvocation.MyCommand.Path

# â”€â”€â”€ Buscar csc.exe â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$csc = Get-ChildItem -Path "$env:SystemRoot\Microsoft.NET\Framework\v*\csc.exe" |
    Sort-Object { [version]$_.Directory.Name.Substring(1) } -Descending |
    Select-Object -First 1 -ExpandProperty FullName

if (-not $csc) {
    Write-Host "[ERROR] No se encontro csc.exe (.NET Framework)." -Foreground Red
    Write-Host "Instala .NET Framework desde https://dotnet.microsoft.com/download" -Foreground Yellow
    exit 1
}

$icoPath = Join-Path $projectDir "public\img\imagen_2026-05-19_065531142.ico"
if (-not (Test-Path $icoPath)) {
    Write-Host "[ADVERTENCIA] Icono no encontrado en $icoPath" -Foreground Yellow
    $icoPath = ""
}

Write-Host "[OK] Compilador: $csc" -Foreground Green

# ─── Escribir codigo C# a archivo temporal ──────
$csFile = Join-Path $env:TEMP "sigejub-installer.cs"
$sourceCs = Join-Path $projectDir "installer-src\Installer.cs"
if (-not (Test-Path $sourceCs)) {
    Write-Host "[ERROR] No se encontro installer-src\Installer.cs" -Foreground Red
    exit 1
}
Copy-Item -Path $sourceCs -Destination $csFile -Force

# â”€â”€â”€ Compilar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$outExe = Join-Path $projectDir "SIGEJUB-Installer.exe"

# Usar strings individuales (Start-Process maneja el quoting)
$argList = @()
$argList += "/noconfig"
$argList += "/target:winexe"
$argList += '/out:"' + $outExe + '"'
$argList += "/reference:System.dll"
$argList += "/reference:System.Core.dll"
$argList += "/reference:System.Windows.Forms.dll"
$argList += "/reference:System.Drawing.dll"
$argList += "/reference:System.Net.dll"
$argList += "/reference:Microsoft.CSharp.dll"

if ($icoPath -and (Test-Path $icoPath)) {
    $argList += '/win32icon:"' + $icoPath + '"'
}

$argList += $csFile

Write-Host "Compilando..." -Foreground Yellow
$p = Start-Process -FilePath $csc -ArgumentList $argList -NoNewWindow -Wait -PassThru

# Si el .exe ya existe y no se pudo sobrescribir, reintentar
if ($p.ExitCode -ne 0) {
    Start-Sleep -Seconds 1
    try { Remove-Item $outExe -Force -ErrorAction Stop } catch { }
    $p = Start-Process -FilePath $csc -ArgumentList $argList -NoNewWindow -Wait -PassThru
}

if ($p.ExitCode -eq 0) {
    $size = [math]::Round((Get-Item $outExe).Length / 1KB)
    Write-Host "[OK] Instalador creado: $outExe ($size KB)" -Foreground Green
    Write-Host "" -Foreground Green
    Write-Host "Ejecuta:" -Foreground Cyan
    Write-Host "  .\SIGEJUB-Installer.exe" -Foreground White
    Write-Host "" -Foreground Cyan
    Write-Host "Puedes copiar el .exe a otros PCs (no requiere PHP ni nada extra)." -Foreground Cyan
} else {
    Write-Host "[ERROR] Compilacion fallida (exit code: $($p.ExitCode))" -Foreground Red
    Write-Host "Revisa $csFile para errores de sintaxis C#." -Foreground Yellow
}
