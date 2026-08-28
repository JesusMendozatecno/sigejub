<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Trabajador;
use App\Models\NominaTrabajador;

class NominaExportService
{
    protected string $templatePath;
    protected string $outputPath;

    protected array $mapTipoNomina = [
        1 => 'DOC',
    ];

    public function __construct()
    {
        $this->templatePath = base_path('documentos/Formatos - Encabezados de nominas1.xlsx');
        $this->outputPath = storage_path('app/templates/export_temp.xlsx');
    }

    public function exportar(?string $periodo = null, ?string $tipo = null): string
    {
        $spreadsheet = IOFactory::load($this->templatePath);

        $trabajadores = Trabajador::with('tipoContrato')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $pivotQuery = NominaTrabajador::query()
            ->join('nominas', 'nominas.id', '=', 'nomina_trabajador.nomina_id')
            ->select('nomina_trabajador.*', 'nominas.periodo');

        if ($periodo) {
            $pivotQuery->where('nominas.periodo', $periodo);
        }

        $pivots = $pivotQuery->get()->groupBy('trabajador_id');

        $grupos = [
            'ADM' => [],
            'DOC' => [],
            'OBREROS' => [],
        ];

        foreach ($trabajadores as $t) {
            $nominaPivot = $pivots->get($t->id)?->sortByDesc('periodo')->first();
            $sheetName = $this->determinarTipoNomina($t);
            $grupos[$sheetName][] = ['trabajador' => $t, 'nomina' => $nominaPivot];
        }

        foreach ($grupos as $sheetName => $items) {
            if ($tipo && $sheetName !== $tipo) continue;

            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) continue;

            $headerRow = $sheetName === 'DOC' ? 6 : 4;
            $dataStartRow = $sheetName === 'DOC' ? 7 : 5;

            $row = $dataStartRow;
            $contador = 1;

            foreach ($items as $item) {
                $t = $item['trabajador'];
                $n = $item['nomina'];

                $fechaIngreso = $t->fecha_ingreso ? Carbon::parse($t->fecha_ingreso)->format('d/m/Y') : '';
                $sueldoBase = $n ? (float) $n->sueldo_base : (float) ($t->sueldo_base ?? 0);

                $colSueldo = $sheetName === 'DOC' ? 'Q' : ($sheetName === 'OBREROS' ? 'Q' : 'P');
                $colPF = $sheetName === 'DOC' ? 'S' : ($sheetName === 'OBREROS' ? 'R' : 'Q');
                $colPH = $sheetName === 'DOC' ? 'T' : ($sheetName === 'OBREROS' ? 'S' : 'R');
                $colPHD = $sheetName === 'DOC' ? 'U' : ($sheetName === 'OBREROS' ? 'T' : 'S');
                $colPAU = $sheetName === 'DOC' ? 'V' : ($sheetName === 'OBREROS' ? 'U' : 'T');
                $colPP = $sheetName === 'DOC' ? 'W' : ($sheetName === 'OBREROS' ? 'V' : 'U');
                $colPCR = $sheetName === 'DOC' ? 'X' : ($sheetName === 'OBREROS' ? 'W' : 'V');
                $colCPCR = $sheetName === 'DOC' ? 'Y' : ($sheetName === 'OBREROS' ? 'X' : 'W');
                $colPA = $sheetName === 'DOC' ? 'Z' : ($sheetName === 'OBREROS' ? 'Y' : 'X');
                $colTA = $sheetName === 'DOC' ? 'AA' : ($sheetName === 'OBREROS' ? 'Z' : 'Y');

                $sheet->setCellValue("A{$row}", $contador);
                $sheet->setCellValue("B{$row}", $t->cedula ?? '');
                $sheet->setCellValue("C{$row}", trim(($t->apellidos ?? '') . ' ' . ($t->nombres ?? '')));
                $sheet->setCellValue("D{$row}", $t->genero ?? '');
                $sheet->setCellValue("E{$row}", $t->numero_hijos ?? 0);
                $sheet->setCellValue("F{$row}", $t->hijos_discapacidad ?? 0);
                $sheet->setCellValue("G{$row}", $t->nivel_educativo_texto ?? '');
                $mapaGrados = [5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 1];
                $sheet->setCellValue("H{$row}", $mapaGrados[(int) ($t->nivel_instruccion ?? 2)] ?? 2);
                $sheet->setCellValue("I{$row}", $fechaIngreso);
                $sheet->setCellValue("J{$row}", $t->anos_servicio_inst ?? 0);
                $sheet->setCellValue("K{$row}", $t->anos_servicio_externo ?? 0);
                $sheet->setCellValue("L{$row}", $t->total_anos_servicio ?? 0);
                $sheet->setCellValue("M{$row}", $t->porcentaje_antiguedad ?? 0);
                $sheet->setCellValue("N{$row}", $t->es_jefe_coordinador ? '7' : '');

                if ($sheetName === 'DOC') {
                    $sheet->setCellValue("O{$row}", $t->cargo ?? '');
                    $sheet->setCellValue("P{$row}", $t->dedicacion ?? '');
                } elseif ($sheetName === 'OBREROS') {
                    $sheet->setCellValue("O{$row}", $t->cargo ?? '');
                    $sheet->setCellValue("P{$row}", $t->grado_cargo ?? '');
                } else {
                    $sheet->setCellValue("O{$row}", $t->cargo ?? '');
                }
                $sheet->setCellValue("{$colSueldo}{$row}", $sueldoBase);

                if ($n) {
                    $sheet->setCellValue("{$colPF}{$row}", (float) ($n->prima_familiar ?? 0));
                    $sheet->setCellValue("{$colPH}{$row}", (float) ($n->prima_hijo ?? 0));
                    $sheet->setCellValue("{$colPHD}{$row}", (float) ($n->prima_hijos_discapacidad ?? 0));
                    $sheet->setCellValue("{$colPAU}{$row}", (float) ($n->prima_actividad_universitaria ?? 0));
                    $sheet->setCellValue("{$colPP}{$row}", (float) ($n->prima_profesionalizacion ?? 0));
                    $sheet->setCellValue("{$colPCR}{$row}", (float) ($n->prima_responsabilidad ?? 0));
                    $sheet->setCellValue("{$colCPCR}{$row}", (float) ($n->complemento_prima_responsabilidad ?? 0));
                    $sheet->setCellValue("{$colPA}{$row}", (float) ($n->prima_antiguedad ?? 0));
                    $sheet->setCellValue("{$colTA}{$row}", (float) ($n->total_asignacion ?? 0));
                } else {
                    foreach ([$colPF, $colPH, $colPHD, $colPAU, $colPP, $colPCR, $colCPCR, $colPA, $colTA] as $c) {
                        $sheet->setCellValue("{$c}{$row}", 0);
                    }
                }

                $contador++;
                $row++;
            }

            $maxCol = match($sheetName) { 'DOC' => 'AA', 'OBREROS' => 'Z', default => 'Y' };
            $lastRow = $sheet->getHighestRow();
            for ($r = $row; $r <= $lastRow; $r++) {
                for ($c = 'A'; $c <= $maxCol; $c++) {
                    $sheet->setCellValue("{$c}{$r}", '');
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->outputPath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->outputPath;
    }

    protected function determinarTipoNomina($trabajador): string
    {
        return ($this->mapTipoNomina[$trabajador->tipo_contrato_id] ?? 'ADM');
    }
}
