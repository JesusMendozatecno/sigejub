<?php

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

        $cmd = 'git -C ' . escapeshellarg($basePath) . ' log --since="' . $since . '" --format="%H|||%an|||%ae|||%s|||%ad" --date=format:"%Y-%m-%d %H:%M:%S"';
        $output = shell_exec($cmd);

        if (!$output) {
            if (!$this->option('silent')) $this->warn('No hay commits nuevos o no se pudo leer git log.');
            return 0;
        }

        $lines = array_filter(explode("\n", trim($output)));
        $count = 0;

        foreach ($lines as $line) {
            $parts = explode('|||', $line);
            if (count($parts) < 4) continue;

            $hash = trim($parts[0]);
            $author = trim($parts[1]);
            $email = trim($parts[2]);
            $message = trim($parts[3]);
            $date = isset($parts[4]) ? trim($parts[4]) : now()->toDateTimeString();

            if (Changelog::where('commit_hash', $hash)->exists()) continue;

            $section = $this->detectSection($message);

            Changelog::create([
                'author_name' => $author,
                'author_email' => $email,
                'commit_hash' => $hash,
                'commit_message' => $message,
                'description' => $this->getCommitDescription($hash),
                'type' => $this->detectType($message),
                'section' => $section,
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

    private function getCommitDescription(string $hash): string
    {
        $basePath = base_path();
        $cmd = 'git -C ' . escapeshellarg($basePath) . ' log -1 --format="%b" ' . escapeshellarg($hash);
        return trim(shell_exec($cmd) ?? '');
    }
}
