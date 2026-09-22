<?php
// Servicio de caché para el dashboard.
// Centraliza la gestión de claves de caché y su invalidación para estadísticas, solicitudes y notificaciones.

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class DashboardCache
{
    const TTL_STATS = 600;
    const TTL_LISTS = 300;

    public static function key(string $name, ?int $userId = null): string
    {
        return 'sigejub.' . $name . ($userId ? '.' . $userId : '');
    }

    public static function flushSolicitudes(): void
    {
        Cache::forget(self::key('solicitudes.por_mes'));
        Cache::forget(self::key('solicitudes.vencimientos'));
    }

    public static function flushStats(): void
    {
        Cache::forget(self::key('stats.trabajadores'));
    }

    public static function flushNotifications(int $userId): void
    {
        Cache::forget(self::key('notificaciones', $userId));
        Cache::forget(self::key('notificaciones.no_leidas', $userId));
    }
}
