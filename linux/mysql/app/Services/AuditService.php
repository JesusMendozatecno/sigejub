<?php
// AuditService — Servicio exclusivo de la Caja Negra / Historial.
// Centraliza el registro de auditoría con sanitización de datos sensibles
// y la traducción a español de acciones y tipos de entidad para la UI.

namespace App\Services;

use App\Models\Activity;
use Illuminate\Http\Request;

class AuditService
{
    // Campos que NUNCA deben persistir en la auditoría.
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', '_token', '_method', 'remember',
        'confirm_password', 'secreto_2fa', 'clave', 'token', 'api_key',
        'apikey', 'secret', 'access_token', 'refresh_token', 'session',
        'csrf', 'credential', 'cookies',
    ];

    // Mapa de acciones técnicas -> etiqueta en español (visible al usuario).
    public const ACCION_ETIQUETAS = [
        'created'     => 'Creado',
        'updated'     => 'Actualizado',
        'deleted'     => 'Eliminado',
        'consulted'   => 'Consultado',
        'exported'    => 'Exportado',
        'downloaded'  => 'Descargado',
        'imported'    => 'Importado',
        'login'       => 'Inicio de sesión',
        'logout'      => 'Cierre de sesión',
        'login_failed'=> 'Inicio de sesión fallido',
        'unauthorized'=> 'Acceso no autorizado',
        'generated'   => 'Generado',
        'verified'    => 'Verificado',
        'restored'    => 'Restaurado',
        'rejected'    => 'Rechazado',
        'failed'      => 'Fallido',
        'backup_created'   => 'Copia de seguridad creada',
        'backup_verified'  => 'Copia de seguridad verificada',
        'backup_downloaded'=> 'Copia de seguridad descargada',
        'backup_deleted'   => 'Copia de seguridad eliminada',
        'backup_restored'  => 'Copia de seguridad restaurada',
        'backup_failed'    => 'Copia de seguridad fallida',
        'restore_failed'   => 'Restauración fallida',
        'backup_invalid'   => 'Integridad de copia no válida',
    ];

    // Mapa de tipos de entidad técnicos -> etiqueta en español (visible al usuario).
    public const ENTIDAD_ETIQUETAS = [
        'trabajador'          => 'Trabajador',
        'solicitud'           => 'Solicitud',
        'expediente'          => 'Expediente',
        'documento'           => 'Documento',
        'usuario'             => 'Usuario',
        'notificacion'        => 'Notificación',
        'prestacion'          => 'Prestación',
        'nomina'              => 'Nómina',
        'cargo'               => 'Cargo',
        'grado'               => 'Grado',
        'nivel-instruccion'   => 'Nivel de instrucción',
        'tipo-contrato'       => 'Tipo de contrato',
        'prima'               => 'Prima',
        'sueldo'              => 'Sueldo',
        'formula_prestacion'  => 'Fórmula',
        'tasa_cambio'         => 'Tasa de cambio',
        'tipo_jubilacion'     => 'Tipo de jubilación',
        'area'                => 'Área',
        'backup'              => 'Copia de seguridad',
    ];

    /**
     * Traduce una acción técnica a su etiqueta en español.
     */
    public static function accionHumana(?string $accion): string
    {
        return self::ACCION_ETIQUETAS[$accion] ?? ($accion ?: '—');
    }

    /**
     * Traduce un tipo de entidad técnico a su etiqueta en español.
     */
    public static function entidadHumana(?string $tipo): string
    {
        return self::ENTIDAD_ETIQUETAS[$tipo] ?? ($tipo ?: '—');
    }

    /**
     * Sanitiza un arreglo de datos eliminando campos sensibles y recursivo.
     * Devuelve solo datos seguros para persistir.
     */
    public static function sanitizar(array $datos): array
    {
        $resultado = [];
        foreach ($datos as $clave => $valor) {
            $k = strtolower((string) $clave);
            if (in_array($k, self::SENSITIVE_KEYS, true)) {
                continue;
            }
            if (is_array($valor)) {
                $resultado[$clave] = self::sanitizar($valor);
            } else {
                $resultado[$clave] = $valor;
            }
        }
        return $resultado;
    }

    /**
     * Extrae metadatos de petición (IP, navegador, método, ruta) saneados.
     */
    public static function datosPeticion(?Request $request): array
    {
        if (!$request) {
            return ['metodo' => 'CLI', 'ruta' => 'consola', 'ip' => null, 'navegador' => null];
        }
        return [
            'metodo'    => $request->method(),
            'ruta'      => $request->path(),
            'ip'        => $request->ip(),
            'navegador' => substr((string) $request->userAgent(), 0, 500),
        ];
    }

    /**
     * Registra un evento en la Caja Negra sanitizando la petición.
     */
    public static function registrar(
        string $accion,
        string $tipoEntidad,
        ?int $entidadId,
        string $descripcion,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?array $datosPeticion = null
    ): Activity {
        $request = request();
        // Metadatos de petición siempre se derivan de la petición real (método, ruta, IP).
        // El argumento $datosPeticion es contexto extra a persistir en datos_peticion.
        $datos = array_merge(self::datosPeticion($request), $datosPeticion ?? []);

        return Activity::create([
            'user_id' => auth()->id(),
            'accion' => $accion,
            'tipo_entidad' => $tipoEntidad,
            'entidad_id' => $entidadId,
            'descripcion' => mb_substr($descripcion, 0, 1000),
            'direccion_ip' => $datos['ip'] ?? null,
            'navegador' => $datos['navegador'] ?? null,
            'metodo' => $datos['metodo'] ?? null,
            'ruta' => $datos['ruta'] ?? null,
            'valores_anteriores' => $valoresAnteriores !== null ? self::sanitizar($valoresAnteriores) : null,
            'valores_nuevos' => $valoresNuevos !== null ? self::sanitizar($valoresNuevos) : null,
            'datos_peticion' => self::sanitizar($datosPeticion ?? []),
        ]);
    }
}