# SIGEJUB - Actualiza variables en .env (Windows)
# Uso:
#   powershell -NoProfile -ExecutionPolicy Bypass -File configurar-env.ps1 `
#     -EnvFile "C:\sigejub\.env" -AppPort 8000 -Connection pgsql `
#     -DbHost 127.0.0.1 -DbPort 5432 -Database sigejub -Username postgres -Password "clave"

param(
    [Parameter(Mandatory = $true)][string]$EnvFile,
    [string]$AppPort = '',
    [string]$Connection = '',
    [string]$DbHost = '',
    [string]$DbPort = '',
    [string]$Database = '',
    [string]$Username = '',
    [string]$Password = ''
)

if (!(Test-Path -LiteralPath $EnvFile)) {
    Write-Host "[ERROR] No existe el archivo: $EnvFile"
    exit 1
}

$map = [ordered]@{}
if ($AppPort -ne '') { $map['APP_PORT'] = $AppPort; $map['APP_URL'] = "http://localhost:$AppPort" }
if ($Connection -ne '') { $map['DB_CONNECTION'] = $Connection }
if ($DbHost -ne '') { $map['DB_HOST'] = $DbHost }
if ($DbPort -ne '') { $map['DB_PORT'] = $DbPort }
if ($Database -ne '') { $map['DB_DATABASE'] = $Database }
if ($Username -ne '') { $map['DB_USERNAME'] = $Username }
$map['DB_PASSWORD'] = $Password   # siempre: evita arrastrar la password de la variante

$lines = Get-Content -LiteralPath $EnvFile
$out = @()
$applied = @{}

foreach ($line in $lines) {
    if ($line -match '^\s*#') { $out += $line; continue }
    $key = ($line -split '=', 2)[0].Trim()

    # Las variables heredadas *_PGSQL se eliminan (el instalador escribe DB_* planos)
    if ($key -match '_PGSQL$') { continue }

    if ($map.Contains($key)) {
        if (-not $applied.ContainsKey($key)) {
            $out += "$key=$($map[$key])"
            $applied[$key] = $true
        }
        continue
    }
    $out += $line
}

foreach ($k in $map.Keys) {
    if (-not $applied.ContainsKey($k)) { $out += "$k=$($map[$k])" }
}

Set-Content -LiteralPath $EnvFile -Value $out -Encoding UTF8
Write-Host "[OK] .env actualizado: $EnvFile"
exit 0
