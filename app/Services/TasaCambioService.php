<?php

namespace App\Services;

use App\Models\TasaCambio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TasaCambioService
{
    private const TIMEOUT_SECONDS = 10;

    private static function getConfig(): array
    {
        return [
            'api_url' => config('services.tasas_cambio.url', ''),
            'api_enabled' => config('services.tasas_cambio.enabled', false),
            'moneda_origen' => config('services.tasas_cambio.moneda_origen', 'USD'),
            'moneda_destino' => config('services.tasas_cambio.moneda_destino', 'VES'),
        ];
    }

    public static function obtenerTasaAutomatica(): ?array
    {
        $config = self::getConfig();

        if (!$config['api_enabled'] || empty($config['api_url'])) {
            Log::info('TasaCambioService: Consulta automática deshabilitada o URL no configurada.');
            return null;
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders(['Accept' => 'application/json'])
                ->get($config['api_url']);

            if ($response->failed()) {
                Log::warning('TasaCambioService: HTTP ' . $response->status() . ' al consultar API.');
                return null;
            }

            $body = $response->json();

            $tasa = self::extraerTasaDeRespuesta($body);

            if ($tasa === null || $tasa <= 0) {
                Log::warning('TasaCambioService: Tasa inválida en respuesta de API.');
                return null;
            }

            return [
                'tasa' => $tasa,
                'moneda_origen' => $config['moneda_origen'],
                'moneda_destino' => $config['moneda_destino'],
                'fuente' => 'API Automática',
                'tipo' => 'automatica',
            ];
        } catch (\Exception $e) {
            Log::error('TasaCambioService: Error al consultar API - ' . $e->getMessage());
            return null;
        }
    }

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

        foreach ($body as $v) {
            if (is_numeric($v) && (float) $v > 1) {
                return (float) $v;
            }
        }

        return null;
    }

    public static function sincronizarTasa(?int $usuarioId = null): array
    {
        $resultadoAutomatica = self::obtenerTasaAutomatica();

        if ($resultadoAutomatica) {
            $tasa = TasaCambio::create([
                'tasa' => $resultadoAutomatica['tasa'],
                'moneda_origen' => $resultadoAutomatica['moneda_origen'],
                'moneda_destino' => $resultadoAutomatica['moneda_destino'],
                'fuente' => $resultadoAutomatica['fuente'],
                'tipo' => $resultadoAutomatica['tipo'],
                'usuario_id' => $usuarioId,
                'activa' => true,
            ]);

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
}
