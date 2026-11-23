<?php
// Controlador de expedientes de jubilación.
// Gestiona la creación de expedientes, vinculación con trabajador y solicitud,
// carga y revisión de documentos, y cálculo automático del estado global (%).

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Trabajador;
use App\Models\Solicitud;
use App\Models\ArchivoBd;
use App\Models\Activity;
use App\Services\NotificationService;
use App\Services\ValidationService;
use App\Services\ImageStorageService;
use App\Services\ArchivoDbService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpedienteController extends Controller
{
    private const INICIALES_ALFABETICAS = [
        'A' => ['A', 'Á', 'À', 'Ä', 'Â'],
        'B' => ['B'],
        'C' => ['C'],
        'D' => ['D'],
        'E' => ['E', 'É', 'È', 'Ë', 'Ê'],
        'F' => ['F'],
        'G' => ['G'],
        'H' => ['H'],
        'I' => ['I', 'Í', 'Ì', 'Ï', 'Î'],
        'J' => ['J'],
        'K' => ['K'],
        'L' => ['L'],
        'M' => ['M'],
        'N' => ['N', 'Ñ'],
        'O' => ['O', 'Ó', 'Ò', 'Ö', 'Ô'],
        'P' => ['P'],
        'Q' => ['Q'],
        'R' => ['R'],
        'S' => ['S'],
        'T' => ['T'],
        'U' => ['U', 'Ú', 'Ù', 'Ü', 'Û'],
        'V' => ['V'],
        'W' => ['W'],
        'X' => ['X'],
        'Y' => ['Y'],
        'Z' => ['Z'],
    ];

    public const DOCS_REQUERIDOS = [
        'Cédula de Identidad',
        'Oficio de Solicitud',
        'Punto de Cuenta',
        'Constancia de Trabajo',
        'Recibo de Nómina',
        'Informe Médico',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'letra' => ['sometimes', 'string', 'regex:/^[A-Za-z#]$/'],
        ]);

        $perPage = min((int) $request->get('per_page', 10), 100);

        $query = Expediente::query()
            ->leftJoin('trabajadores as t', 'expedientes.trabajador_id', '=', 't.id')
            ->select('expedientes.*')
            ->with('trabajador', 'solicitud', 'documentos')
            ->orderByRaw("LOWER(COALESCE(t.nombres, ''))")
            ->orderByRaw("LOWER(COALESCE(t.apellidos, ''))")
            ->orderBy('expedientes.id');

        $this->aplicarFiltrosListado($query, $request);

        $letra = mb_strtoupper(trim((string) $request->get('letra')), 'UTF-8');
        if ($letra !== '') {
            $this->aplicarFiltroLetra($query, $letra);
        }

        return response()->json($query->paginate($perPage));
    }

    public function letras(Request $request)
    {
        $inicialSql = $this->inicialExpedienteSql('t.');
        $expedientesPorInicial = Expediente::query()
            ->join('trabajadores as t', 'expedientes.trabajador_id', '=', 't.id')
            ->selectRaw($inicialSql . ' as inicial');

        $this->aplicarFiltrosListado($expedientesPorInicial, $request);

        $conteos = DB::query()
            ->fromSub($expedientesPorInicial->toBase(), 'expedientes_por_inicial')
            ->select('inicial')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('inicial')
            ->get();
        $totalesPorLetra = [];

        foreach ($conteos as $conteo) {
            $letra = $this->normalizarInicial($conteo->inicial);
            $totalesPorLetra[$letra] = ($totalesPorLetra[$letra] ?? 0) + (int) $conteo->total;
        }

        uksort($totalesPorLetra, static function ($a, $b) {
            if ($a === '#') {
                return $b === '#' ? 0 : 1;
            }
            if ($b === '#') {
                return -1;
            }

            return strcmp($a, $b);
        });

        $letras = [];
        foreach ($totalesPorLetra as $letra => $total) {
            $letras[] = ['letra' => $letra, 'total' => $total];
        }

        return response()->json(['letras' => $letras]);
    }

    private function aplicarFiltrosListado(Builder $query, Request $request): void
    {
        // Búsqueda en servidor: antes solo se filtraba la página ya cargada,
        // por lo que un trabajador en otra página aparecía como "sin resultados".
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->whereHas('trabajador', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('cedula', 'like', "%{$search}%");
                });
            });
        }

        // Filtro por estado global: 0 = sin iniciar, 50 = en proceso, 100 = completo.
        $estado = $request->get('estado');
        if ($estado !== null && $estado !== '') {
            switch ((string) $estado) {
                case '0':
                    $query->where('expedientes.estado_global', 0);
                    break;
                case '50':
                    $query->whereBetween('expedientes.estado_global', [1, 99]);
                    break;
                case '100':
                    $query->where('expedientes.estado_global', 100);
                    break;
                default:
                    if (is_numeric($estado)) {
                        $query->where('expedientes.estado_global', (int) $estado);
                    }
                    break;
            }
        }
    }

    private function aplicarFiltroLetra(Builder $query, string $letra): void
    {
        $inicialSql = $this->inicialExpedienteSql();
        $variantes = self::INICIALES_ALFABETICAS[$letra] ?? null;

        $query->whereHas('trabajador', function ($trabajador) use ($inicialSql, $variantes) {
            if ($variantes === null) {
                $alfabeto = array_merge(...array_values(self::INICIALES_ALFABETICAS));
                $marcadores = implode(',', array_fill(0, count($alfabeto), '?'));
                $trabajador->whereRaw($inicialSql . ' NOT IN (' . $marcadores . ')', $alfabeto);
                return;
            }

            $marcadores = implode(',', array_fill(0, count($variantes), '?'));
            $trabajador->whereRaw($inicialSql . ' IN (' . $marcadores . ')', $variantes);
        });
    }

    private function inicialExpedienteSql(string $prefijo = ''): string
    {
        return "UPPER(SUBSTR(COALESCE(NULLIF(TRIM({$prefijo}nombres), ''), NULLIF(TRIM({$prefijo}apellidos), ''), ''), 1, 1))";
    }

    private function normalizarInicial(?string $inicial): string
    {
        $inicial = mb_strtoupper(mb_substr(trim((string) $inicial), 0, 1, 'UTF-8'), 'UTF-8');
        $inicial = strtr($inicial, [
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I',
            'Ñ' => 'N',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U',
        ]);

        return preg_match('/^[A-Z]$/', $inicial) ? $inicial : '#';
    }

    public function buscarTrabajador(Request $request)
    {
        $cedula = trim((string) $request->get('cedula'));

        // Buscar coincidencia exacta primero
        $trabajador = Trabajador::where('cedula', $cedula)->first();

        // Si no encontró, intentar sin el prefijo (V-, E-, etc.) y solo por el número
        if (!$trabajador) {
            $trabajador = Trabajador::where('cedula', 'like', "%{$cedula}%")->first();
        }
        if (!$trabajador) {
            $soloNumeros = preg_replace('/[^0-9]/', '', $cedula);
            if ($soloNumeros !== '') {
                $trabajador = Trabajador::where('cedula', 'like', "%{$soloNumeros}%")->first();
            }
        }

        if (!$trabajador) {
            return response()->json(['error' => 'Trabajador no encontrado'], 404);
        }

        // La solicitud es opcional: el expediente se puede abrir incluso si el
        // trabajador todavía no tiene solicitud registrada.
        $solicitud = Solicitud::where('trabajador_id', $trabajador->id)
            ->orderByDesc('created_at')
            ->first();

        $yaTieneExpediente = Expediente::where('trabajador_id', $trabajador->id)->exists();
        if ($yaTieneExpediente) {
            return response()->json(['error' => 'Este trabajador ya tiene un expediente creado'], 409);
        }

        return response()->json([
            'trabajador' => $trabajador,
            'solicitud' => $solicitud,
            'tiene_solicitud' => (bool) $solicitud,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'trabajador_id' => 'required|exists:trabajadores,id',
            // La solicitud dejó de ser obligatoria: el expediente puede abrirse
            // antes de que el trabajador tenga una solicitud aprobada.
            'solicitud_id' => 'nullable|exists:solicitudes,id',
            'foto_carnet' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if (empty($validated['solicitud_id'])) {
            $validated['solicitud_id'] = null;
        } elseif (!$this->solicitudPerteneceAlTrabajador((int) $validated['solicitud_id'], (int) $validated['trabajador_id'])) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'La solicitud indicada no pertenece al trabajador seleccionado.',
            ], 422);
        }

        // Validación de negocio: trabajador no debe tener expediente
        if (!ValidationService::trabajadorSinExpediente($validated['trabajador_id'])) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Este trabajador ya tiene un expediente creado.',
            ], 422);
        }

        $validated['estado_global'] = 0;

        // Se crea primero el expediente para conocer su ID y nombrar la foto
        // como expedientes/fotos/exp_{id}.{ext} (nombre determinista).
        $foto = $request->file('foto_carnet');
        unset($validated['foto_carnet']);

        $expediente = Expediente::create($validated);

        // Si el trabajador ya traía cláusulas de la convención colectiva
        // seleccionadas desde su ficha, se aplican al nuevo expediente.
        $this->copiarClausulasDelTrabajador($expediente);

        if ($foto) {
            $expediente->foto_carnet = app(ImageStorageService::class)
                ->guardar($foto, 'expedientes/fotos', 'exp_' . $expediente->id);
            $expediente->save();
        }

        $trabajador = $expediente->trabajador;
        Activity::log('created', 'expediente', $expediente->id,
            "Se creó el expediente de {$trabajador->nombres} {$trabajador->apellidos}");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Expediente creado exitosamente.',
            'expediente' => $expediente->load('trabajador'),
        ]);
    }

    public function show($id)
    {
        $expediente = Expediente::with('trabajador', 'solicitud', 'documentos', 'recibos', 'clausulas')
            ->findOrFail($id);
        return response()->json($expediente);
    }

    public function exportar($id)
    {
        $expediente = Expediente::with('trabajador', 'solicitud', 'documentos', 'recibos')
            ->findOrFail($id);
        $trabajador = $expediente->trabajador;
        abort_unless($trabajador, 404, 'El trabajador asociado a este expediente no está disponible.');

        $nombreTrabajador = trim($trabajador->nombres . ' ' . $trabajador->apellidos);
        $nombreSeguro = Str::slug($nombreTrabajador, '_') ?: 'trabajador';
        $carpeta = 'Expediente_' . $expediente->id . '_' . $nombreSeguro;
        $archivoZip = tempnam(sys_get_temp_dir(), 'sigejub_');
        if ($archivoZip === false) {
            throw new \RuntimeException('No se pudo preparar la descarga del expediente.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($archivoZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($archivoZip);
            throw new \RuntimeException('No se pudo crear el archivo ZIP del expediente.');
        }

        try {
            $fotoPath = $expediente->getRawOriginal('foto_carnet');
            $foto = $fotoPath ? $this->contenidoArchivoExpediente($fotoPath) : null;
            $fotoBase64 = null;
            if ($foto !== null) {
                $extensionFoto = strtolower(pathinfo($fotoPath, PATHINFO_EXTENSION)) ?: 'jpg';
                $mime = match ($extensionFoto) {
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    default => 'image/jpeg',
                };
                $fotoParaPdf = $foto;
                if ($extensionFoto === 'webp' && function_exists('imagecreatefromstring')) {
                    $imagen = imagecreatefromstring($foto);
                    if ($imagen !== false) {
                        ob_start();
                        imagepng($imagen);
                        $png = ob_get_clean();
                        imagedestroy($imagen);
                        if (is_string($png)) {
                            $fotoParaPdf = $png;
                            $mime = 'image/png';
                        }
                    }
                }
                $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode($fotoParaPdf);
                $zip->addFromString($carpeta . '/Foto carnet.' . $extensionFoto, $foto);
            }

            $pdfPerfil = Pdf::loadView('pdf.expediente', [
                'expediente' => $expediente,
                'trabajador' => $trabajador,
                'solicitud' => $expediente->solicitud,
                'foto_base64' => $fotoBase64,
            ])->setPaper('a4')->output();
            $zip->addFromString($carpeta . '/Perfil del trabajador.pdf', $pdfPerfil);

            $archivosNoDisponibles = [];
            foreach ($expediente->documentos as $documento) {
                if (!$documento->archivo) {
                    continue;
                }
                $contenido = $this->contenidoArchivoExpediente($documento->archivo);
                if ($contenido === null) {
                    $archivosNoDisponibles[] = 'Documento: ' . $documento->nombre;
                    continue;
                }
                $extension = strtolower(pathinfo($documento->archivo, PATHINFO_EXTENSION)) ?: 'bin';
                $nombre = sprintf('%03d_%s.%s', $documento->id, Str::slug($documento->nombre, '_') ?: 'documento', $extension);
                $zip->addFromString($carpeta . '/Documentos/' . $nombre, $contenido);
            }

            foreach ($expediente->recibos as $recibo) {
                if (!$recibo->archivo) {
                    continue;
                }
                $contenido = $this->contenidoArchivoExpediente($recibo->archivo);
                if ($contenido === null) {
                    $archivosNoDisponibles[] = 'Recibo de nómina: ' . ($recibo->periodo ?: $recibo->id);
                    continue;
                }
                $extension = strtolower(pathinfo($recibo->archivo, PATHINFO_EXTENSION)) ?: 'bin';
                $nombre = sprintf('%03d_%s.%s', $recibo->id, Str::slug($recibo->periodo ?: 'recibo', '_'), $extension);
                $zip->addFromString($carpeta . '/Recibos de nómina/' . $nombre, $contenido);
            }

            $cartaPath = $expediente->getRawOriginal('carta_aprobacion');
            if ($cartaPath) {
                $contenido = $this->contenidoArchivoExpediente($cartaPath);
                if ($contenido !== null) {
                    $extension = strtolower(pathinfo($cartaPath, PATHINFO_EXTENSION)) ?: 'bin';
                    $zip->addFromString($carpeta . '/Carta de aprobación.' . $extension, $contenido);
                } else {
                    $archivosNoDisponibles[] = 'Carta de aprobación';
                }
            }

            if ($archivosNoDisponibles) {
                $zip->addFromString(
                    $carpeta . '/Archivos no disponibles.txt',
                    "No se pudieron incluir estos archivos porque no están disponibles en el almacenamiento ni en su respaldo:\r\n- "
                        . implode("\r\n- ", $archivosNoDisponibles)
                );
            }

            $zip->close();
        } catch (\Throwable $error) {
            $zip->close();
            @unlink($archivoZip);
            throw $error;
        }

        return response()->download(
            $archivoZip,
            $carpeta . '.zip',
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(true);
    }

    private function contenidoArchivoExpediente(string $ruta): ?string
    {
        $storage = Storage::disk('public');
        if ($storage->exists($ruta)) {
            return $storage->get($ruta);
        }

        return ArchivoBd::where('disco', 'public')
            ->where('ruta', trim($ruta, '/'))
            ->first()
            ?->binario();
    }

    /**
     * Cláusulas de la convención colectiva del expediente: listado y selección.
     */
    public function clausulas($id)
    {
        $expediente = Expediente::with('clausulas')->findOrFail($id);

        return response()->json([
            'expediente_id' => $expediente->id,
            'disponibles' => $this->catalogoClausulas(),
            'seleccionadas' => $expediente->clausulas->map(fn($c) => [
                'id' => $c->id,
                'numero' => $c->numero,
                'nombre' => $c->nombre,
                'tipo_calculo' => $c->tipo_calculo,
                'valor' => (float) $c->valor,
                'por_anio' => (bool) $c->por_anio,
                'observaciones' => $c->pivot->observaciones ?? null,
            ])->values(),
        ]);
    }

    public function actualizarClausulas(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $validated = $request->validate([
            'clausulas' => 'nullable|array',
            'clausulas.*' => 'integer|exists:clausulas_convencion,id',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $ids = collect($validated['clausulas'] ?? [])
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values();

        $sync = $ids->mapWithKeys(fn($clausulaId) => [
            $clausulaId => ['observaciones' => $validated['observaciones'] ?? null],
        ])->all();

        $expediente->clausulas()->sync($sync);

        $trabajador = $expediente->trabajador;
        Activity::log('updated', 'expediente', $expediente->id,
            'Se actualizaron las cláusulas de convención colectiva del expediente de '
            . ($trabajador ? "{$trabajador->nombres} {$trabajador->apellidos}" : "ID {$expediente->trabajador_id}"));

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Cláusulas de la convención colectiva actualizadas.',
            'seleccionadas' => $expediente->clausulas()->get()->map(fn($c) => $c->id)->values(),
        ]);
    }

    /**
     * Catálogo de cláusulas activas para los selectores.
     */
    private function catalogoClausulas()
    {
        return \App\Models\ClausulaConvencion::activas()
            ->orderBy('numero')
            ->get(['id', 'numero', 'nombre', 'descripcion', 'tipo_calculo', 'valor', 'por_anio']);
    }

    private function solicitudPerteneceAlTrabajador(int $solicitudId, int $trabajadorId): bool
    {
        return Solicitud::where('id', $solicitudId)
            ->where('trabajador_id', $trabajadorId)
            ->exists();
    }

    /**
     * Copia al expediente las cláusulas que el trabajador tenía seleccionadas
     * en su ficha antes de tener expediente digital.
     */
    private function copiarClausulasDelTrabajador(Expediente $expediente): void
    {
        $ids = $expediente->trabajador?->clausulas_convencion ?? [];
        $ids = collect(is_array($ids) ? $ids : [])
            ->map(fn($v) => (int) (is_array($v) ? ($v['id'] ?? 0) : $v))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $validas = \App\Models\ClausulaConvencion::whereIn('id', $ids)->pluck('id');
        $expediente->clausulas()->syncWithoutDetaching($validas->all());
    }

    public function updateDocumentoStatus(Request $request, $id)
    {
        $documento = \App\Models\Documento::findOrFail($id);
        $validated = $request->validate([
            'estado' => 'required|in:en_revision,aprobado,rechazado',
            'nota_rechazo' => 'nullable|string',
        ]);

        $previo = $documento->estado;

        if ($validated['estado'] === 'rechazado') {
            $expediente = Expediente::with('trabajador')->find($documento->expediente_id);
            if ($expediente && $expediente->trabajador) {
                $nombre = "{$expediente->trabajador->nombres} {$expediente->trabajador->apellidos}";
                NotificationService::documentoRechazado(
                    $expediente->id,
                    $documento->nombre,
                    $nombre
                );
            }
            $expedienteId = $documento->expediente_id;
            if ($documento->archivo) {
                Storage::disk('public')->delete($documento->archivo);
                app(ArchivoDbService::class)->eliminar($documento->archivo, 'public');
            }
            $documento->delete();
            $this->recalcularEstadoGlobal($expedienteId);
            return response()->json([
                'estado' => 'success',
                'mensaje' => 'Documento rechazado y eliminado.',
            ]);
        }

        $documento->update($validated);
        $this->recalcularEstadoGlobal($documento->expediente_id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Estado del documento actualizado.',
        ]);
    }

    public function updateFotoCarnet(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $request->validate([
            'foto_carnet' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = app(ImageStorageService::class)->guardar(
            $request->file('foto_carnet'),
            'expedientes/fotos',
            'exp_' . $expediente->id,
            $expediente->foto_carnet
        );
        $expediente->foto_carnet = $path;
        $expediente->save();

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Foto carnet actualizada.',
            'foto_carnet' => $path,
        ]);
    }

    public function subirDocumento(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:102400',
        ]);

        $nombre = $validated['nombre'];

        // Solo se permiten los 6 documentos requeridos
        if (!in_array($nombre, self::DOCS_REQUERIDOS, true)) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => "Tipo de documento no válido. Solo se admiten: " . implode(', ', self::DOCS_REQUERIDOS) . '.',
            ], 422);
        }

        // No se puede subir un documento del mismo tipo mientras exista uno en revisión o aprobado
        $duplicado = $expediente->documentos()
            ->where('nombre', $nombre)
            ->whereIn('estado', ['en_revision', 'aprobado'])
            ->exists();

        if ($duplicado) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => "Ya existe un documento de tipo \"{$nombre}\" en revisión o aprobado. Debe esperar a que sea rechazado para subirlo de nuevo.",
            ], 422);
        }

        $path = $request->file('archivo')->store('expedientes/documentos', 'public');
        app(ArchivoDbService::class)->respaldar($path, 'public', $request->file('archivo')->getClientOriginalName());

        $expediente->documentos()->create([
            'nombre' => $nombre,
            'archivo' => $path,
            'estado' => 'en_revision',
        ]);

        $this->recalcularEstadoGlobal($expediente->id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Documento subido correctamente.',
        ]);
    }

    public function reemplazarDocumento(Request $request, $id)
    {
        $documento = \App\Models\Documento::findOrFail($id);

        $validated = $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $anterior = $documento->archivo;
        $path = $request->file('archivo')->store('expedientes/documentos', 'public');
        app(ArchivoDbService::class)->respaldar($path, 'public', $request->file('archivo')->getClientOriginalName());
        $documento->update([
            'archivo' => $path,
            'estado' => 'en_revision',
            'nota_rechazo' => null,
        ]);
        if ($anterior && $anterior !== $path) {
            Storage::disk('public')->delete($anterior);
            app(ArchivoDbService::class)->eliminar($anterior, 'public');
        }

        $this->recalcularEstadoGlobal($documento->expediente_id);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Documento reemplazado correctamente.',
        ]);
    }

    public function listosParaAprobacion()
    {
        abort_unless(in_array(auth()->user()?->rol, ['admin', 'superadmin']), 403);

        $expedientes = Expediente::with('trabajador', 'solicitud', 'documentos')
            ->where('estado_global', 100)
            ->whereNull('carta_aprobacion')
            ->latest()
            ->take(20)
            ->get();

        return response()->json($expedientes);
    }

    public function subirCartaAprobacion(Request $request, $id)
    {
        abort_unless(auth()->user()?->rol === 'superadmin', 403);

        $expediente = Expediente::findOrFail($id);

        $request->validate([
            'carta' => 'required|file|mimes:pdf,jpg,jpeg,png|max:102400',
        ]);

        $cartaAnterior = $expediente->carta_aprobacion;
        $path = $request->file('carta')->store('expedientes/cartas', 'public');
        app(ArchivoDbService::class)->respaldar($path, 'public', $request->file('carta')->getClientOriginalName());
        $expediente->update(['carta_aprobacion' => $path]);
        if ($cartaAnterior && $cartaAnterior !== $path) {
            Storage::disk('public')->delete($cartaAnterior);
            app(ArchivoDbService::class)->eliminar($cartaAnterior, 'public');
        }

        $trabajador = $expediente->trabajador;
        $nombre = "{$trabajador->nombres} {$trabajador->apellidos}";
        Activity::log('updated', 'expediente', $expediente->id,
            "Se subió la carta de aprobación del consejo para {$nombre}");

        NotificationService::cartaAprobacionSubida($expediente->id, $nombre);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Carta de aprobación subida correctamente.',
            'archivo' => $path,
        ]);
    }

    public function updateNotas(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);
        $validated = $request->validate([
            'notas_admin' => 'nullable|string',
        ]);

        $expediente->update($validated);

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Notas actualizadas.',
        ]);
    }

    /**
     * LISTAR recibos de nómina adjuntos al expediente (R3)
     */
    public function listarRecibos($id)
    {
        $expediente = Expediente::with('recibos')->findOrFail($id);

        return response()->json([
            'estado' => 'success',
            'recibos' => $expediente->recibos,
        ]);
    }

    /**
     * ADJUNTAR un recibo de nómina al expediente (R3)
     */
    public function subirRecibo(Request $request, $id)
    {
        $expediente = Expediente::findOrFail($id);

        $validated = $request->validate([
            'periodo' => 'nullable|string|date_format:Y-m',
            'concepto' => 'nullable|string|max:150',
            'monto_usd' => 'nullable|numeric|min:0|max:999999999999',
            'observaciones' => 'nullable|string|max:2000',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:102400',
        ]);

        $validated['archivo'] = $request->file('archivo')->store('expedientes/recibos', 'public');
        app(ArchivoDbService::class)->respaldar($validated['archivo'], 'public', $request->file('archivo')->getClientOriginalName());
        $validated['expediente_id'] = $expediente->id;

        $recibo = \App\Models\ReciboPago::create($validated);

        Activity::log('created', 'recibo_pago', $recibo->id,
            "Se adjuntó un recibo de nómina al expediente #{$expediente->id}");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Recibo de nómina adjuntado correctamente.',
            'recibo' => $recibo,
        ]);
    }

    /**
     * ELIMINAR un recibo de nómina (y su archivo)
     */
    public function eliminarRecibo($id)
    {
        $recibo = \App\Models\ReciboPago::findOrFail($id);

        if ($recibo->archivo) {
            Storage::disk('public')->delete($recibo->archivo);
            app(ArchivoDbService::class)->eliminar($recibo->archivo, 'public');
        }
        $expedienteId = $recibo->expediente_id;
        $recibo->delete();

        Activity::log('deleted', 'recibo_pago', $id,
            "Se eliminó un recibo de nómina del expediente #{$expedienteId}");

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'Recibo eliminado correctamente.',
        ]);
    }

    private function recalcularEstadoGlobal($expedienteId)
    {
        $expediente = Expediente::with('documentos', 'trabajador')->findOrFail($expedienteId);

        $aprobados = 0;
        foreach (self::DOCS_REQUERIDOS as $tipo) {
            $doc = $expediente->documentos->firstWhere('nombre', $tipo);
            if ($doc && $doc->estado === 'aprobado') {
                $aprobados++;
            }
        }

        $total = count(self::DOCS_REQUERIDOS);
        $porcentaje = round(($aprobados / $total) * 100);

        $previo = $expediente->estado_global;
        $expediente->update(['estado_global' => $porcentaje]);

        // Notificar cuando el expediente alcanza 100% por primera vez
        if ($previo < 100 && $porcentaje === 100) {
            $t = $expediente->trabajador;
            $nombre = $t ? "{$t->nombres} {$t->apellidos}" : "ID {$expediente->trabajador_id}";
            NotificationService::expedienteListo($expediente->id, $nombre);
        }
    }
}
