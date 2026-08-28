<?php
// Servicio de consulta y sincronización de tasa de cambio USD/VES.
// Consulta API externa (BCV/Monitor), cachea resultado, maneja fallback a última tasa válida.
// Compatible con MySQL y PostgreSQL.

namespace App\Services;

use App\Models\TasaCambio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TasaCambioService
{
    private const TIMEOUT_SECONDS = 15;
    private const CACHE_KEY = 'sigejub.tasa_dolar.actual';

    private static function getConfig(): array
    {
        return [
            'api_url' => config('services.tasas_cambio.url', ''),
            'api_key' => config('services.tasas_cambio.api_key', ''),
            'api_enabled' => config('services.tasas_cambio.enabled', false),
            'moneda_origen' => config('services.tasas_cambio.moneda_origen', 'USD'),
            'moneda_destino' => config('services.tasas_cambio.moneda_destino', 'VES'),
            'cache_ttl' => config('services.tasas_cambio.cache_ttl', 600),
        ];
    }

    /**
     * Consultar tasa automática desde la API externa.
     * Retorna array con datos de la tasa o null si falla.
     */
    public static function obtenerTasaAutomatica(): ?array
    {
        $config = self::getConfig();

        if (!$config['api_enabled'] || empty($config['api_url'])) {
            Log::info('TasaCambioService: Consulta automática deshabilitada o URL no configurada.');
            return null;
        }

        try {
            $headers = ['Accept' => 'application/json'];
            if (!empty($config['api_key'])) {
                $headers['Authorization'] = 'Bearer ' . $config['api_key'];
                $headers['X-API-Key'] = $config['api_key'];
            }

            $request = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders($headers)
                ->retry(2, 500)
                ->withoutVerifying(); // XAMPP/Windows: evita fallo SSL por CA bundle local

            $response = $request->get($config['api_url']);

            if ($response->failed()) {
                Log::warning('TasaCambioService: HTTP ' . $response->status() . ' al consultar API.');
                return null;
            }

            $body = $response->json();

            if (!is_array($body)) {
                Log::warning('TasaCambioService: Respuesta no es un array válido.');
                return null;
            }

            $tasa = self::extraerTasaDeRespuesta($body);
            $fuente = self::extraerFuente($body);

            if ($tasa === null || $tasa <= 0) {
                Log::warning('TasaCambioService: Tasa inválida en respuesta de API.');
                return null;
            }

            return [
                'tasa' => round($tasa, 4),
                'moneda_origen' => $config['moneda_origen'],
                'moneda_destino' => $config['moneda_destino'],
                'fuente' => $fuente,
                'tipo' => 'automatica',
                'fecha_consulta' => now()->toDateTimeString(),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('TasaCambioService: Timeout de conexión - ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('TasaCambioService: Error al consultar API - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extraer la tasa numérica de la respuesta JSON de la API.
     * Soporta múltiples formatos de API comunes (dolarapi, pydolarmonitor, etc.).
     */
    private static function extraerTasaDeRespuesta(array $body): ?float
    {
        if (isset($body['rate'])) {
            return (float) $body['rate'];
        }

        if (isset($body['rates'])) {
            $rates = $body['rates'];
            if (isset($rates['VES'])) {
                return (float) $rates['VES'];
            }
            if (is_array($rates) && count($rates) > 0) {
                return (float) reset($rates);
            }
        }

        if (isset($body['dollar']['parallel'])) {
            return (float) $body['dollar']['parallel'];
        }
        if (isset($body['dollar']['official'])) {
            return (float) $body['dollar']['official'];
        }

        if (isset($body['result'])) {
            return (float) $body['result'];
        }
        if (isset($body['conversion_rate'])) {
            return (float) $body['conversion_rate'];
        }
        if (isset($body['value'])) {
            return (float) $body['value'];
        }

        if (isset($body['data'])) {
            $data = $body['data'];
            if (is_array($data)) {
                if (isset($data['rate'])) return (float) $data['rate'];
                if (isset($data['value'])) return (float) $data['value'];
                if (isset($data['promedio'])) return (float) $data['promedio'];
            }
        }

        if (isset($body['monitors'])) {
            foreach ($body['monitors'] as $monitor) {
                if (isset($monitor['price'])) {
                    return (float) $monitor['price'];
                }
            }
        }

        // Formato array de objetos: [{'promedio': ..., 'fuente': ...}, ...]
        if (is_array($body) && !empty($body) && array_is_list($body)) {
            foreach ($body as $item) {
                if (is_array($item) && isset($item['promedio'])) {
                    return (float) $item['promedio'];
                }
            }
        }

        foreach ($body as $v) {
            if (is_numeric($v) && (float) $v > 1) {
                return (float) $v;
            }
        }

        return null;
    }

    /**
     * Extraer la fuente de la tasa desde la respuesta.
     */
    private static function extraerFuente(array $body): string
    {
        if (isset($body['source'])) return (string) $body['source'];
        if (isset($body['fuente'])) return (string) $body['fuente'];
        if (isset($body['provider'])) return (string) $body['provider'];

        if (isset($body['dollar'])) return 'BCV/Monitor';

        if (is_array($body)) {
            foreach ($body as $item) {
                if (is_array($item) && isset($item['fuente'])) {
                    return 'BCV (' . $item['fuente'] . ')';
                }
            }
        }

        return 'API Automática';
    }

    /**
     * Sincronizar tasa: consultar API y guardar en DB.
     * Retorna resultado con éxito/fallo y datos de la tasa.
     */
    public static function sincronizarTasa(?int $usuarioId = null): array
    {
        $resultadoAutomatica = self::obtenerTasaAutomatica();

        if ($resultadoAutomatica) {
            $ultima = TasaCambio::obtenerTasaActual();

            if ($ultima) {
                $ultima->update(['activa' => false]);
            }

            $tasa = TasaCambio::create([
                'tasa' => $resultadoAutomatica['tasa'],
                'moneda_origen' => $resultadoAutomatica['moneda_origen'],
                'moneda_destino' => $resultadoAutomatica['moneda_destino'],
                'fuente' => $resultadoAutomatica['fuente'],
                'tipo' => $resultadoAutomatica['tipo'],
                'usuario_id' => $usuarioId,
                'activa' => true,
                'fecha_consulta' => $resultadoAutomatica['fecha_consulta'],
            ]);

            Cache::forget(self::CACHE_KEY);

            return [
                'success' => true,
                'tipo' => 'automatica',
                'tasa' => $tasa,
                'mensaje' => 'Tasa automática actualizada: ' . number_format($tasa->tasa, 4, ',', '.') . ' ' .
                    $tasa->moneda_destino . '/' . $tasa->moneda_origen,
            ];
        }

        $ultima = TasaCambio::obtenerTasaActual();
        return [
            'success' => false,
            'tipo' => 'automatica',
            'tasa' => $ultima,
            'mensaje' => 'No se pudo obtener la tasa automática. ' .
                ($ultima ? 'Se conserva la última tasa registrada: ' . number_format($ultima->tasa, 4, ',', '.') : 'No hay tasas registradas.'),
        ];
    }

    /**
     * Obtener la tasa actual con cache.
     */
    public static function obtenerTasaConCache(): ?TasaCambio
    {
        $config = self::getConfig();
        $cacheTtl = $config['cache_ttl'];

        return Cache::remember(self::CACHE_KEY, $cacheTtl, function () {
            return TasaCambio::obtenerTasaActual();
        });
    }

    /**
     * Obtener estado actual de la tasa para el frontend.
     * Incluye indicador de frescura y estado (🟢🟡🔴).
     */
    public static function obtenerEstadoTasa(): array
    {
        $tasa = TasaCambio::obtenerTasaActual();

        if (!$tasa) {
            return [
                'disponible' => false,
                'estado' => 'no_disponible',
                'mensaje' => 'No hay tasas de cambio registradas.',
                'tasa' => null,
            ];
        }

        $fechaConsulta = $tasa->fecha_consulta ?? $tasa->created_at;
        $minutosDesdeConsulta = now()->diffInMinutes($fechaConsulta);

        if ($minutosDesdeConsulta <= 120) {
            $estado = 'actualizada';
            $etiqueta = 'Actualizada';
        } elseif ($minutosDesdeConsulta <= 1440) {
            $estado = 'disponible';
            $etiqueta = 'Última tasa disponible';
        } else {
            $estado = 'desactualizada';
            $etiqueta = 'Tasa desactualizada';
        }

        return [
            'disponible' => true,
            'estado' => $estado,
            'etiqueta' => $etiqueta,
            'tasa' => [
                'id' => $tasa->id,
                'valor' => (float) $tasa->tasa,
                'moneda_origen' => $tasa->moneda_origen,
                'moneda_destino' => $tasa->moneda_destino,
                'fuente' => $tasa->fuente,
                'tipo' => $tasa->tipo,
                'fecha' => $fechaConsulta->format('d/m/Y h:i A'),
                'fecha_raw' => $fechaConsulta->toIso8601String(),
                'minutos_desde' => $minutosDesdeConsulta,
                'api_configurada' => (bool) config('services.tasas_cambio.enabled', false),
            ],
        ];
    }

    /**
     * Invalidar cache de tasa (llamar después de registro manual/sincronización).
     */
    public static function limpiarCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
