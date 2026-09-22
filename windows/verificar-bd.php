<?php
// SIGEJUB - Verifica conexion a la BD y crea la base si no existe (uso interno del instalador).
// Uso:
//   set SIGEJUB_DB_PASS=clave
//   php verificar-bd.php <mysql|pgsql> <host> <puerto> <bd> <usuario>

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo se ejecuta por linea de comandos.\n");
    exit(2);
}

$args = $argv;
array_shift($args);
if (count($args) < 5) {
    fwrite(STDERR, "Uso: php verificar-bd.php <mysql|pgsql> <host> <puerto> <bd> <usuario>\n");
    fwrite(STDERR, "La contrasena se lee de la variable de entorno SIGEJUB_DB_PASS\n");
    exit(2);
}

[$engine, $host, $port, $db, $user] = $args;
$pass = (string) getenv('SIGEJUB_DB_PASS');
$engine = strtolower($engine);
if (!in_array($engine, ['mysql', 'pgsql'], true)) {
    fwrite(STDERR, "Motor no soportado: {$engine}\n");
    exit(2);
}

function quoteId($engine, $id)
{
    return $engine === 'mysql' ? '`' . str_replace('`', '``', $id) . '`' : '"' . str_replace('"', '""', $id) . '"';
}

try {
    // 1) Conexion al servidor (sin seleccionar BD)
    $pdo = new PDO($engine . ':host=' . $host . ';port=' . $port, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2) Crear la BD si no existe
    $qid = quoteId($engine, $db);
    if ($engine === 'mysql') {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$qid} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } else {
        $st = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
        $st->execute([$db]);
        if (!$st->fetchColumn()) {
            $pdo->exec("CREATE DATABASE {$qid}");
        }
    }

    // 3) Conexion a la BD ya creada
    $pdo = new PDO($engine . ':host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $pass);
    echo "OK: conexion a {$engine} host={$host} puerto={$port} bd={$db} usuario={$user}\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()) . "\n");
    exit(1);
}