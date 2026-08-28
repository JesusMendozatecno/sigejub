<?php

namespace App\Services;

use App\Models\Trabajador;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class NominaImportService
{
    protected array $sheetConfig = [
        'ADM' => ['headerRow' => 4, 'dataStart' => 5, 'cols' => 'Y'],
        'DOC' => ['headerRow' => 6, 'dataStart' => 7, 'cols' => 'AA'],
        'OBREROS' => ['headerRow' => 4, 'dataStart' => 5, 'cols' => 'Z'],
    ];

    public function importar(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);

        $results = [
            'registrados' => 0,
            'actualizados' => 0,
            'omitidos' => 0,
            'errores' => [],
        ];

        foreach ($this->sheetConfig as $sheetName => $cfg) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $dataStart = $cfg['dataStart'];
            $lastRow = $sheet->getHighestRow();

            for ($row = $dataStart; $row <= $lastRow; $row++) {
                $cedula = trim((string) ($sheet->getCell("B{$row}")->getValue() ?? ''));
                if (empty($cedula)) {
                    $results['omitidos']++;
                    continue;
                }

                $nombreCompleto = trim((string) ($sheet->getCell("C{$row}")->getValue() ?? ''));
                if (empty($nombreCompleto)) {
                    $results['errores'][] = "{$sheetName} Fila {$row}: Sin nombre para cédula {$cedula}";
                    continue;
                }

                try {
                    $parsed = $this->parseNombreCompleto($nombreCompleto);
                    if (!$parsed) {
                        $results['omitidos']++;
                        continue;
                    }

                    $data = $this->mapearFila($sheet, $row, $sheetName, $cedula, $parsed['nombres'], $parsed['apellidos']);

                    $existente = Trabajador::where('cedula', $cedula)->first();
                    if ($existente) {
                        $existente->update($data);
                        $results['actualizados']++;
                    } else {
                        Trabajador::create($data);
                        $results['registrados']++;
                    }
                } catch (\Exception $e) {
                    $results['errores'][] = "{$sheetName} Fila {$row} ({$cedula}): " . $e->getMessage();
                }
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $results;
    }

    protected function parseNombreCompleto(string $nombreCompleto): ?array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));
        $partes = array_values(array_filter($partes));

        if (empty($partes)) return null;

        $total = count($partes);

        if ($total === 1) {
            return ['nombres' => $partes[0], 'apellidos' => ''];
        }

        if ($total === 2) {
            return ['apellidos' => $partes[0], 'nombres' => $partes[1]];
        }

        if ($total === 3) {
            return ['apellidos' => $partes[0] . ' ' . $partes[1], 'nombres' => $partes[2]];
        }

        $mitad = (int) ceil($total / 2);
        $apellidos = implode(' ', array_slice($partes, 0, $mitad));
        $nombres = implode(' ', array_slice($partes, $mitad));

        return ['apellidos' => $apellidos, 'nombres' => $nombres];
    }

    protected function mapearFila($sheet, int $row, string $sheetName, string $cedula, string $nombres, string $apellidos): array
    {
        // ===== SECCION AZUL: Datos Personales Básicos =====
        $genero = $this->normalizarGenero($sheet->getCell("D{$row}")->getValue());
        $numHijos = (int) ($sheet->getCell("E{$row}")->getValue() ?? 0);
        $hijosDisc = (int) ($sheet->getCell("F{$row}")->getValue() ?? 0);
        $gradoInstruccion = trim((string) ($sheet->getCell("G{$row}")->getValue() ?? ''));
        $codigoGrado = (int) ($sheet->getCell("H{$row}")->getValue() ?? 0);

        // ===== SECCION CELESTE: Datos Laborales y Antigüedad =====
        $fechaIngreso = $this->parseFecha($sheet->getCell("I{$row}")->getValue());
        $anosServInst = (int) ($sheet->getCell("J{$row}")->getValue() ?? 0);
        $anosServExt = (int) ($sheet->getCell("K{$row}")->getValue() ?? 0);
        $totalAnos = (int) ($sheet->getCell("L{$row}")->getValue() ?? 0);
        $porcentajeAntiguedad = $this->parseDecimal($sheet->getCell("M{$row}")->getValue());
        $codigoPrimaResp = trim((string) ($sheet->getCell("N{$row}")->getValue() ?? ''));
        $categoriaCargo = trim((string) ($sheet->getCell("O{$row}")->getValue() ?? ''));

        $gradoCargo = match($sheetName) {
            'OBREROS' => trim((string) ($sheet->getCell("P{$row}")->getValue() ?? '')),
            default => '',
        };

        $dedicacion = match($sheetName) {
            'DOC' => trim((string) ($sheet->getCell("P{$row}")->getValue() ?? '')),
            default => '',
        };

        // ===== SECCION VERDE: Remuneración y Primas =====
        $sueldoCol = match($sheetName) {
            'DOC' => 'Q', 'OBREROS' => 'Q',
            default => 'P',
        };
        $sueldoMensual = $this->parseDecimal($sheet->getCell("{$sueldoCol}{$row}")->getValue());

        list($colPF, $colPH, $colPHD, $colPAU, $colPP, $colPCR, $colCPCR, $colPA, $colTA) = $this->getPrimasColumns($sheetName);

        $primaFamiliar = $this->parseDecimal($sheet->getCell("{$colPF}{$row}")->getValue());
        $primaHijo = $this->parseDecimal($sheet->getCell("{$colPH}{$row}")->getValue());
        $primaHijosDisc = $this->parseDecimal($sheet->getCell("{$colPHD}{$row}")->getValue());
        $primaActUniv = $this->parseDecimal($sheet->getCell("{$colPAU}{$row}")->getValue());
        $primaProf = $this->parseDecimal($sheet->getCell("{$colPP}{$row}")->getValue());
        $primaCargoResp = $this->parseDecimal($sheet->getCell("{$colPCR}{$row}")->getValue());
        $compPrimaResp = $this->parseDecimal($sheet->getCell("{$colCPCR}{$row}")->getValue());
        $primaAntiguedad = $this->parseDecimal($sheet->getCell("{$colPA}{$row}")->getValue());
        $totalAsignacion = $this->parseDecimal($sheet->getCell("{$colTA}{$row}")->getValue());

        if ($codigoGrado && !$gradoInstruccion) {
            $mapaGrados = [1 => 'TSU', 2 => 'Universitario', 3 => 'Especialización', 4 => 'Maestría', 5 => 'Doctorado'];
            $gradoInstruccion = $mapaGrados[$codigoGrado] ?? '';
        }

        $totalAnosCalculado = $totalAnos ?: ($anosServInst + $anosServExt);
        $edadEstimada = 22 + $totalAnosCalculado;
        $fechaNacimientoEstimada = now()->subYears($edadEstimada)->format('Y-m-d');
        $nivelGrado = $gradoCargo ?: $this->inferirGradoNivel($codigoGrado);

        return [
            'cedula' => $cedula,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'genero' => $genero,
            'numero_hijos' => $numHijos,
            'hijos_discapacidad' => $hijosDisc,
            'nivel_instruccion' => $codigoGrado ?: $this->inferirNivelInstruccion($gradoInstruccion),
            'nivel_educativo_texto' => $gradoInstruccion,
            'grado_nivel' => $nivelGrado,
            'fecha_nacimiento' => $fechaNacimientoEstimada,
            'edad' => $edadEstimada,
            'fecha_ingreso' => $fechaIngreso,
            'anos_servicio_inst' => $anosServInst,
            'anos_servicio_externo' => $anosServExt,
            'total_anos_servicio' => $totalAnosCalculado,
            'porcentaje_antiguedad' => $porcentajeAntiguedad,
            'es_jefe_coordinador' => !empty($codigoPrimaResp),
            'cargo' => $categoriaCargo,
            'unidad_departamento' => $categoriaCargo ?: 'Sin especificar',
            'sueldo_base' => $sueldoMensual,
            'prima_profesionalizacion' => $primaProf,
            'cesta_ticket' => $primaFamiliar,
            'dedicacion' => $dedicacion,
            'grado_cargo' => $gradoCargo,
            'asignacion' => 'Nomina',
        ];
    }

    protected function getPrimasColumns(string $sheetName): array
    {
        return match($sheetName) {
            'DOC' => ['S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'],
            'OBREROS' => ['R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
            default => ['Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'],
        };
    }

    protected function parseFecha($value): ?string
    {
        if (empty($value)) return null;
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        $str = trim((string) $value);
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $str)) {
            $parsed = Carbon::createFromFormat('d/m/Y', $str);
            return $parsed ? $parsed->format('Y-m-d') : null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return $str;
        }
        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function normalizarGenero($value): string
    {
        $v = strtoupper(trim((string) $value));
        if (in_array($v, ['M', 'MASCULINO', 'HOMBRE', 'MALE'])) return 'M';
        if (in_array($v, ['F', 'FEMENINO', 'MUJER', 'FEMALE'])) return 'F';
        return '';
    }

    protected function parseDecimal($value): float
    {
        if ($value === null || $value === '') return 0.0;
        if (is_numeric($value)) return (float) $value;
        $str = str_replace(',', '.', trim((string) $value));
        return is_numeric($str) ? (float) $str : 0.0;
    }

    protected function inferirNivelInstruccion($value): int
    {
        $text = strtolower(trim((string) $value));
        if (str_contains($text, 'doctor')) return 5;
        if (str_contains($text, 'master') || str_contains($text, 'magister')) return 4;
        if (str_contains($text, 'especial')) return 3;
        if (str_contains($text, 'licenc') || str_contains($text, 'ingenier') || str_contains($text, 'prof')) return 2;
        if (str_contains($text, 'tsu') || str_contains($text, 'tecnico')) return 1;
        if (str_contains($text, 'media') || str_contains($text, 'bachiller')) return 0;
        return 2;
    }

    protected function inferirGradoNivel(int $codigoGrado): string
    {
        $mapa = [1 => 'TEC', 2 => 'P1', 3 => 'P2', 4 => 'P3', 5 => 'P4'];
        return $mapa[$codigoGrado] ?? 'P1';
    }
}
