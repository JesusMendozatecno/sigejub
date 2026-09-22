<?php
// Comando de Artisan para generar el changelog desde git log.
// Clasifica automáticamente los commits por tipo (fix, feature, docs, etc.) y sección del sistema.

namespace App\Console\Commands;

use App\Models\Changelog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateChangelog extends Command
{
    protected $signature = 'changelog:generate {--silent : No mostrar output}';
    protected $description = 'Genera changelog desde git log';

    public function handle()
    {
        $basePath = base_path();
        $lastEntry = Changelog::latest('created_at')->first();
        $since = $lastEntry ? $lastEntry->created_at->subDay()->format('Y-m-d') : '2026-01-01';

        // Un solo proceso git para todos los commits (%b = cuerpo, %x00 separa registros).
        // Evita ejecutar git por commit, lo que provocaba timeouts en /documentacion.
        $cmd = 'git -C ' . escapeshellarg($basePath)
            . ' log --since="' . $since . '"'
            . ' --format="%H|||%an|||%ae|||%s|||%ad|||%b%x00"'
            . ' --date=format:"%Y-%m-%d %H:%M:%S"';
        $output = shell_exec($cmd);

        if (!$output) {
            if (!$this->option('silent')) $this->warn('No hay commits nuevos o no se pudo leer git log.');
            return 0;
        }

        $records = array_filter(explode("\x00", (string) $output), fn ($r) => trim((string) $r) !== '');
        $count = 0;

        foreach ($records as $record) {
            $parts = explode('|||', trim($record));
            if (count($parts) < 4) continue;

            $hash = trim($parts[0]);
            $author = trim($parts[1]);
            $email = trim($parts[2]);
            $message = trim($parts[3]);
            $date = isset($parts[4]) ? trim($parts[4]) : now()->toDateTimeString();
            $body = isset($parts[5]) ? trim($parts[5]) : '';

            if (Changelog::where('hash_commit', $hash)->exists()) continue;

            $section = $this->detectSection($message);

            Changelog::create([
                'nombre_autor' => $author,
                'correo_autor' => $email,
                'hash_commit' => $hash,
                'mensaje_commit' => $message,
                'descripcion' => $body,
                'tipo' => $this->detectType($message),
                'seccion' => $section,
                'created_at' => Carbon::parse($date),
            ]);
            $count++;
        }

        if (!$this->option('silent')) {
            $this->info("Se registraron {$count} cambios nuevos en el changelog.");
        }

        return 0;
    }

    private function detectType(string $message): string
    {
        $msg = strtolower($message);
        if (str_starts_with($msg, 'fix') || str_contains($msg, 'bug') || str_contains($msg, 'corrige') || str_contains($msg, 'arregla')) return 'fix';
        if (str_starts_with($msg, 'feat') || str_starts_with($msg, 'add') || str_starts_with($msg, 'nuev') || str_starts_with($msg, 'agrega') || str_starts_with($msg, 'crea')) return 'feature';
        if (str_starts_with($msg, 'refactor') || str_starts_with($msg, 'mejora') || str_starts_with($msg, 'optimiza')) return 'improvement';
        if (str_starts_with($msg, 'doc') || str_contains($msg, 'documentac') || str_contains($msg, 'docs')) return 'docs';
        if (str_starts_with($msg, 'security') || str_contains($msg, 'seguridad') || str_contains($msg, 'xss') || str_contains($msg, 'csrf')) return 'security';
        if (str_starts_with($msg, 'style') || str_starts_with($msg, 'css') || str_contains($msg, 'responsive') || str_contains($msg, 'diseño')) return 'style';
        return 'change';
    }

    private function detectSection(string $message): ?string
    {
        $msg = strtolower($message);
        $sections = [
            'login' => ['login', 'auth', 'autenticacion', 'sesion'],
            'trabajadores' => ['trabajador'],
            'solicitudes' => ['solicitud'],
            'expedientes' => ['expediente'],
            'prestaciones' => ['prestacion'],
            'perfil' => ['perfil', 'profile', 'usuario'],
            'dashboard' => ['dashboard', 'inicio', 'estadistica'],
            'seguridad' => ['seguridad', 'xss', 'csrf', 'validacion', 'validation'],
            'caja-negra' => ['caja negra', 'audit', 'historial'],
        ];

        foreach ($sections as $section => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($msg, $kw)) return $section;
            }
        }
        return null;
    }
}
