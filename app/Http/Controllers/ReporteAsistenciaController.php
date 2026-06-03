<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReporteAsistenciaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $fichas = $user->isInstructor()
            ? $user->fichasAsInstructor()->with('program')->get()
            : Ficha::with(['program', 'instructors'])->get();

        $years = range(date('Y'), 2024);

        return view('reportes.asistencia', compact('fichas', 'years'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|exists:fichas,id',
            'year'     => 'required|integer|min:2024|max:2099',
        ]);

        $ficha = Ficha::with([
            'program',
            'instructors',
            'aprendices',
            'attendanceSessions' => fn($q) => $q->whereYear('date', $request->year)->orderBy('date'),
            'attendanceSessions.records',
        ])->findOrFail($request->ficha_id);

        $sessions   = $ficha->attendanceSessions;
        $aprendices = $ficha->aprendices->sortBy('name');
        $instructor = $ficha->instructors->first()?->name ?? 'Sin asignar';

        // Indexar: [session_id][aprendiz_id] => status
        $registros = [];
        foreach ($sessions as $session) {
            foreach ($session->records as $record) {
                $registros[$session->id][$record->aprendiz_id] = $record->status;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencia');

        $NAVY  = '00304D';
        $GREEN = '39A900';
        $WHITE = 'FFFFFF';
        $cP    = 'D4EDDA'; // verde claro
        $cA    = 'F8D7DA'; // rojo claro
        $cE    = 'FFF3CD'; // amarillo claro

        // Helper: letra de columna desde índice numérico (1-based)
        $col = fn(int $n): string => Coordinate::stringFromColumnIndex($n);

        // ── Fila 1: Título ────────────────────────────────────────
        $totalSessions = $sessions->count();
        $lastColIdx    = 2 + $totalSessions + 4; // doc + sesiones + P/A/E/%
        $lastColLetter = $col($lastColIdx);

        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'REPORTE DE ASISTENCIA — SENA');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Filas 2-4: Info ───────────────────────────────────────
        $info = [
            2 => ['Institución:', $ficha->institucion,     'Ficha N°:',  $ficha->numero],
            3 => ['Programa:',    $ficha->program->name,   'Municipio:', $ficha->municipio],
            4 => ['Instructor:',  $instructor,             'Año:',       $request->year],
        ];
        foreach ($info as $r => [$la, $va, $lb, $vb]) {
            $sheet->setCellValue("A{$r}", $la); $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->setCellValue("B{$r}", $va);
            $sheet->setCellValue("D{$r}", $lb); $sheet->getStyle("D{$r}")->getFont()->setBold(true);
            $sheet->setCellValue("E{$r}", $vb);
        }

        // ── Fila 5: Leyenda ───────────────────────────────────────
        $sheet->setCellValue('A5', 'P = Presente');
        $sheet->setCellValue('C5', 'A = Ausente');
        $sheet->setCellValue('E5', 'E = Excusa/Justificado');
        $sheet->getStyle('A5')->getFont()->setItalic(true)->setSize(8);
        $sheet->getStyle('A5')->getFont()->getColor()->setRGB('007832');
        $sheet->getStyle('C5')->getFont()->setItalic(true)->setSize(8);
        $sheet->getStyle('C5')->getFont()->getColor()->setRGB('C0392B');
        $sheet->getStyle('E5')->getFont()->setItalic(true)->setSize(8);
        $sheet->getStyle('E5')->getFont()->getColor()->setRGB('856404');

        // ── Fila 6: Encabezados de tabla ──────────────────────────
        $HR = 6; // header row
        $sheet->setCellValue("A{$HR}", 'APRENDIZ');
        $sheet->setCellValue("B{$HR}", 'DOCUMENTO');

        foreach ($sessions as $i => $session) {
            $c = $col(3 + $i);
            $sheet->setCellValue("{$c}{$HR}", $session->date->format('d/m/Y'));
            $sheet->getColumnDimension($c)->setWidth(11);
        }

        $cTotalP = $col(3 + $totalSessions);
        $cTotalA = $col(3 + $totalSessions + 1);
        $cTotalE = $col(3 + $totalSessions + 2);
        $cPct    = $col(3 + $totalSessions + 3);

        $sheet->setCellValue("{$cTotalP}{$HR}", 'PRES.');
        $sheet->setCellValue("{$cTotalA}{$HR}", 'AUS.');
        $sheet->setCellValue("{$cTotalE}{$HR}", 'EXCUS.');
        $sheet->setCellValue("{$cPct}{$HR}",    '% ASIST.');

        $sheet->getStyle("A{$HR}:{$lastColLetter}{$HR}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $GREEN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['rgb' => 'CCCCCC']]],
        ]);
        $sheet->getRowDimension($HR)->setRowHeight(22);

        // ── Filas de datos ────────────────────────────────────────
        $row = $HR + 1;
        foreach ($aprendices as $aprendiz) {
            $sheet->setCellValue("A{$row}", $aprendiz->name);
            $sheet->setCellValue("B{$row}", $aprendiz->numero_documento);

            $totalP = $totalA = $totalE = 0;

            foreach ($sessions as $i => $session) {
                $c      = $col(3 + $i);
                $status = $registros[$session->id][$aprendiz->id] ?? null;

                [$letra, $bg] = match($status) {
                    'presente' => ['P', $cP],
                    'ausente'  => ['A', $cA],
                    'excusa'   => ['E', $cE],
                    default    => ['-', 'F9F9F9'],
                };

                if ($status === 'presente') $totalP++;
                elseif ($status === 'ausente') $totalA++;
                elseif ($status === 'excusa') $totalE++;

                $sheet->setCellValue("{$c}{$row}", $letra);
                $sheet->getStyle("{$c}{$row}")->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                                     'color'       => ['rgb' => 'E0E0E0']]],
                ]);
            }

            $pct     = $totalSessions > 0 ? round(($totalP / $totalSessions) * 100, 1) : 0;
            $pctBg   = $pct >= 80 ? $cP : ($pct >= 60 ? $cE : $cA);

            $sheet->setCellValue("{$cTotalP}{$row}", $totalP);
            $sheet->setCellValue("{$cTotalA}{$row}", $totalA);
            $sheet->setCellValue("{$cTotalE}{$row}", $totalE);
            $sheet->setCellValue("{$cPct}{$row}",    $pct . '%');

            $sheet->getStyle("{$cPct}{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $pctBg]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'font'      => ['bold' => true],
            ]);
            $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                               'color'       => ['rgb' => 'DDDDDD']]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        // ── Fila de totales por sesión ────────────────────────────
        if ($aprendices->isNotEmpty()) {
            $sheet->setCellValue("A{$row}", 'TOTAL PRESENTES POR SESIÓN');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);

            foreach ($sessions as $i => $session) {
                $c = $col(3 + $i);
                $presentes = collect($registros[$session->id] ?? [])->filter(fn($s) => $s === 'presente')->count();
                $sheet->setCellValue("{$c}{$row}", $presentes);
                $sheet->getStyle("{$c}{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => $WHITE]],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $GREEN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            }
            $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM,
                                            'color'       => ['rgb' => $NAVY]]],
            ]);
        }

        // ── Anchos fijos ──────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(34);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension($cTotalP)->setWidth(8);
        $sheet->getColumnDimension($cTotalA)->setWidth(8);
        $sheet->getColumnDimension($cTotalE)->setWidth(8);
        $sheet->getColumnDimension($cPct)->setWidth(10);

        // Congelar aprendiz + documento
        $sheet->freezePane("C{$HR}");

        // ── Descarga ──────────────────────────────────────────────
        $fileName = 'Asistencia_Ficha' . $ficha->numero . '_' . $request->year . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
