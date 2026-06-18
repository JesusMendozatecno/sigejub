<?php
// Servicio de exportación de nómina a Excel.
// Lee una plantilla .xlsx predefinida (ADM.CONT. NOVIEMBRE 2025.xlsx) y llena las filas con datos
// de trabajadores y sus cálculos de nómina. Preserva fórmulas y formato del template.

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Trabajador;
use App\Models\Nomina;

class NominaExportService
{
    protected string $templatePath;
    protected string $outputPath;

    public function __construct()
    {
        $this->templatePath = storage_path('app/templates/plantilla_nomina.xlsx');
        $this->outputPath = storage_path('app/templates/export_temp.xlsx');
    }

    public function exportar(?string $periodo = null): string
    {
        $spreadsheet = IOFactory::load($this->templatePath);
        $sheet = $spreadsheet->getSheetByName('ADM.CONT.') ?? $spreadsheet->getSheet(1);

        $dataRowStart = 8;

        $trabajadores = Trabajador::with('nominas')->get();

        $row = $dataRowStart;
        $contador = 1;

        foreach ($trabajadores as $t) {
            $nomina = $t->nominas->first();

            $sheet->setCellValueExplicit("A{$row}", $contador, 's');
            $sheet->setCellValueExplicit("B{$row}", $t->cedula, 's');
            $sheet->setCellValue("C{$row}", trim($t->apellidos . ' ' . $t->nombres));
            $sheet->setCellValue("D{$row}", $t->apellidos);
            $sheet->setCellValue("E{$row}", $t->nombres);
            $sheet->setCellValueExplicit("F{$row}", $t->cuenta_bancaria ?? '', 's');
            $sheet->setCellValue("G{$row}", $t->genero);
            $sheet->setCellValue("H{$row}", $t->grado_nivel);
            $sheet->setCellValue("I{$row}", '');
            $sheet->setCellValue("J{$row}", $t->tabulador ?? '');
            $sheet->setCellValue("K{$row}", $t->cargo);
            $sheet->setCellValue("L{$row}", $t->es_jefe_coordinador ? 'SI' : 'NO');
            $sheet->setCellValue("M{$row}", $t->porcentaje_prima_cargo ?? '');
            $sheet->setCellValue("N{$row}", $t->fecha_nacimiento ? Carbon::parse($t->fecha_nacimiento)->format('d/m/Y') : '');
            $sheet->setCellValue("O{$row}", $t->edad);
            $sheet->setCellValue("P{$row}", $t->fecha_ingreso ? Carbon::parse($t->fecha_ingreso)->format('d/m/Y') : '');
            $sheet->setCellValue("Q{$row}", $t->anos_servicio_inst);
            $sheet->setCellValue("R{$row}", $t->anos_servicio_externo);
            $sheet->setCellValue("S{$row}", $t->total_anos_servicio);
            $sheet->setCellValue("T{$row}", $t->porcentaje_antiguedad);
            $sheet->setCellValue("U{$row}", $t->porcentaje_caja_ahorro);
            $sheet->setCellValue("V{$row}", $t->numero_hijos);
            $sheet->setCellValue("W{$row}", $t->hijos_discapacidad);
            $sheet->setCellValue("X{$row}", '');
            $sheet->setCellValue("Y{$row}", $t->nivel_educativo_texto ?? '');
            $sheet->setCellValue("Z{$row}", '');
            $sheet->setCellValue("AA{$row}", $t->afiliacion_sifaiuty ?? '');

            if ($nomina) {
                $sheet->setCellValue("AB{$row}", $nomina->isr ?? 0);
                $sheet->setCellValue("AC{$row}", $nomina->horas_extras ?? 0);
                $sheet->setCellValue("AD{$row}", $nomina->sueldo_base ?? 0);
                $sheet->setCellValue("AE{$row}", $nomina->prima_familiar ?? 0);
                $sheet->setCellValue("AF{$row}", $nomina->prima_hijo ?? 0);
                $sheet->setCellValue("AG{$row}", $nomina->prima_hijos_discapacidad ?? 0);
                $sheet->setCellValue("AH{$row}", $nomina->prima_actividad_universitaria ?? 0);
                $sheet->setCellValue("AI{$row}", $nomina->prima_profesionalizacion ?? 0);
                $sheet->setCellValue("AJ{$row}", $nomina->prima_responsabilidad ?? 0);
                $sheet->setCellValue("AK{$row}", $nomina->complemento_prima_responsabilidad ?? 0);
                $sheet->setCellValue("AL{$row}", $nomina->prima_antiguedad ?? 0);
                $sheet->setCellValue("AM{$row}", $nomina->horas_extras ?? 0);
                $sheet->setCellValue("AN{$row}", $nomina->total_asignacion ?? 0);
                $sheet->setCellValue("AO{$row}", $nomina->sso ?? 0);
                $sheet->setCellValue("AP{$row}", $nomina->lpf ?? 0);
                $sheet->setCellValue("AQ{$row}", $nomina->faov ?? 0);
                $sheet->setCellValue("AR{$row}", $nomina->aporte_ipasme ?? 0);
                $sheet->setCellValue("AS{$row}", $nomina->aporte_caja_ahorro ?? 0);
                $sheet->setCellValue("AT{$row}", $nomina->prestamo_caja_ahorro ?? 0);
                $sheet->setCellValue("AU{$row}", $t->afiliacion_sifaiuty ?? '');
                $sheet->setCellValue("AV{$row}", $nomina->total_deduccion ?? 0);
                $sheet->setCellValue("AW{$row}", $nomina->neto_a_cobrar ?? 0);
            }

            $contador++;
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->outputPath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->outputPath;
    }
}
