<?php

namespace App\Http\Controllers;

use App\Models\ActividadNota;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Competencia;
use App\Models\Ficha;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ConsolidadoController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Ficha::with(['program', 'instructors'])
            ->withCount('aprendices')
            ->withCount(['folders as folders_sin_subir_count' => function ($q) {
                $q->where('status', 'sin_subir');
            }])
            ->withCount(['folders as folders_observadas_count' => function ($q) {
                $q->whereIn('status', ['pendiente_subir', 'rechazado']);
            }]);

        if ($user->isInstructor()) {
            $query->whereHas('instructors', fn($q) => $q->where('users.id', $user->id));
        }

        $fichas = $query->orderByDesc('id')->paginate(20);
        return view('consolidado.index', compact('fichas'));
    }

    public function show(Ficha $ficha)
    {
        $user = auth()->user();
        if ($user->isInstructor()) {
            abort_unless($ficha->instructors()->where('users.id', $user->id)->exists(), 403);
        }

        [
            $aprendices, $attendanceStats, $attendanceSummary, $totalSessions,
            $notasStats, $notasSummary, $competencias, $folderStats, $folderPct
        ] = $this->buildStats($ficha);

        return view('consolidado.show', compact(
            'ficha', 'aprendices',
            'attendanceStats', 'attendanceSummary', 'totalSessions',
            'notasStats', 'notasSummary', 'competencias',
            'folderStats', 'folderPct'
        ));
    }

    public function exportPdf(Ficha $ficha)
    {
        $user = auth()->user();
        if ($user->isInstructor()) {
            abort_unless($ficha->instructors()->where('users.id', $user->id)->exists(), 403);
        }

        [
            $aprendices, $attendanceStats, $attendanceSummary, $totalSessions,
            $notasStats, $notasSummary, $competencias, $folderStats, $folderPct
        ] = $this->buildStats($ficha);

        $pdf = Pdf::loadView('consolidado.pdf', compact(
            'ficha', 'aprendices',
            'attendanceStats', 'attendanceSummary', 'totalSessions',
            'notasStats', 'notasSummary', 'competencias',
            'folderStats', 'folderPct'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('consolidado_ficha_' . $ficha->numero . '.pdf');
    }

    public function exportExcel(Ficha $ficha)
    {
        $user = auth()->user();
        if ($user->isInstructor()) {
            abort_unless($ficha->instructors()->where('users.id', $user->id)->exists(), 403);
        }

        [
            $aprendices, $attendanceStats, $attendanceSummary, $totalSessions,
            $notasStats, $notasSummary, $competencias, $folderStats, $folderPct
        ] = $this->buildStats($ficha);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Consolidado Ficha ' . $ficha->numero)
            ->setCreator('ASEM');

        $navyFill  = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00304D']];
        $greenFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '39A900']];
        $whiteFont = ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11];
        $boldFont  = ['bold' => true];
        $center    = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER];

        // ─── Hoja 1: Asistencia ───────────────────────────────────────────
        $sh = $spreadsheet->getActiveSheet()->setTitle('Asistencia');

        $sh->mergeCells('A1:G1');
        $sh->setCellValue('A1', 'CONSOLIDADO DE ASISTENCIA — FICHA ' . $ficha->numero);
        $sh->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => $navyFill, 'alignment' => $center]);
        $sh->getRowDimension(1)->setRowHeight(24);

        $sh->mergeCells('A2:G2');
        $sh->setCellValue('A2', 'Programa: ' . ($ficha->program->name ?? '—') . '   |   Institución: ' . $ficha->institucion . '   |   Sesiones totales: ' . $totalSessions);
        $sh->getStyle('A2')->applyFromArray(['font' => $boldFont, 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']]]);

        $headers = ['Aprendiz', 'Documento', 'Presentes', 'Ausentes', 'Excusas', 'Total Sesiones', '% Asistencia'];
        foreach ($headers as $i => $h) {
            $sh->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '4', $h);
        }
        $sh->getStyle('A4:G4')->applyFromArray(['font' => $whiteFont, 'fill' => $greenFill, 'alignment' => $center]);
        $sh->getRowDimension(4)->setRowHeight(20);

        $row = 5;
        foreach ($attendanceStats as $s) {
            $sh->setCellValue('A' . $row, $s['aprendiz']->name);
            $sh->setCellValue('B' . $row, $s['aprendiz']->numero_documento);
            $sh->setCellValue('C' . $row, $s['presentes']);
            $sh->setCellValue('D' . $row, $s['ausentes']);
            $sh->setCellValue('E' . $row, $s['excusas']);
            $sh->setCellValue('F' . $row, $s['total']);
            $sh->setCellValue('G' . $row, $s['pct'] . '%');
            $sh->getStyle("C{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $bg = $s['pct'] >= 80 ? 'E8F5E9' : ($s['pct'] >= 60 ? 'FFF9C4' : 'FFEBEE');
            $sh->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $row++;
        }
        foreach (range(1, 7) as $col) {
            $sh->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        // ─── Hoja 2: Notas ────────────────────────────────────────────────
        $sh2 = $spreadsheet->createSheet()->setTitle('Notas');

        $sh2->mergeCells('A1:E1');
        $sh2->setCellValue('A1', 'CONSOLIDADO DE NOTAS — FICHA ' . $ficha->numero);
        $sh2->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => $navyFill, 'alignment' => $center]);
        $sh2->getRowDimension(1)->setRowHeight(24);

        $sh2->mergeCells('A2:E2');
        $sh2->setCellValue('A2', 'Programa: ' . ($ficha->program->name ?? '—') . '   |   Aprobación ≥ 3.5   |   ' . $notasSummary['aprendices_aprobados'] . ' aprobados / ' . $notasSummary['aprendices_reprobados'] . ' reprobados');
        $sh2->getStyle('A2')->applyFromArray(['font' => $boldFont, 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']]]);

        $headers2 = ['Aprendiz', 'Documento', 'Promedio General', 'Estado', '# Competencias'];
        foreach ($headers2 as $i => $h) {
            $sh2->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '4', $h);
        }
        $sh2->getStyle('A4:E4')->applyFromArray(['font' => $whiteFont, 'fill' => $greenFill, 'alignment' => $center]);

        $row2 = 5;
        foreach ($notasStats as $s) {
            $sh2->setCellValue('A' . $row2, $s['aprendiz']->name);
            $sh2->setCellValue('B' . $row2, $s['aprendiz']->numero_documento);
            $sh2->setCellValue('C' . $row2, $s['promedio_general'] ?? 'Sin notas');
            $sh2->setCellValue('D' . $row2, $s['aprobado'] === null ? 'Pendiente' : ($s['aprobado'] ? 'Aprobado' : 'Reprobado'));
            $sh2->setCellValue('E' . $row2, $notasSummary['total_competencias']);
            $sh2->getStyle("C{$row2}:E{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $bg2 = $s['aprobado'] === true ? 'E8F5E9' : ($s['aprobado'] === false ? 'FFEBEE' : 'F5F5F5');
            $sh2->getStyle("A{$row2}:E{$row2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg2);
            $row2++;
        }
        foreach (range(1, 5) as $col) {
            $sh2->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        // ─── Hoja 3: Documentos ───────────────────────────────────────────
        $sh3 = $spreadsheet->createSheet()->setTitle('Documentos');

        $sh3->mergeCells('A1:C1');
        $sh3->setCellValue('A1', 'ESTADO DE CARPETAS DOCUMENTALES — FICHA ' . $ficha->numero);
        $sh3->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']], 'fill' => $navyFill, 'alignment' => $center]);

        $headers3 = ['Estado', 'Carpetas', 'Porcentaje'];
        foreach ($headers3 as $i => $h) {
            $sh3->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '3', $h);
        }
        $sh3->getStyle('A3:C3')->applyFromArray(['font' => $whiteFont, 'fill' => $greenFill, 'alignment' => $center]);

        $docRows = [
            ['Sin subir',   $folderStats['sin_subir'],   'F5F5F5'],
            ['En revisión', $folderStats['en_revision'], 'FFF9C4'],
            ['Aprobado',    $folderStats['aprobado'],    'E8F5E9'],
            ['Rechazado',   $folderStats['rechazado'],   'FFEBEE'],
        ];
        $r3 = 4;
        foreach ($docRows as [$label, $count, $bg]) {
            $pct = $folderStats['total'] > 0 ? round($count / $folderStats['total'] * 100, 1) . '%' : '0%';
            $sh3->setCellValue('A' . $r3, $label);
            $sh3->setCellValue('B' . $r3, $count);
            $sh3->setCellValue('C' . $r3, $pct);
            $sh3->getStyle("A{$r3}:C{$r3}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
            $sh3->getStyle("B{$r3}:C{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r3++;
        }
        $sh3->setCellValue('A' . $r3, 'TOTAL');
        $sh3->setCellValue('B' . $r3, $folderStats['total']);
        $sh3->setCellValue('C' . $r3, '100%');
        $sh3->getStyle("A{$r3}:C{$r3}")->applyFromArray(['font' => $boldFont]);
        $sh3->getStyle("B{$r3}:C{$r3}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range(1, 3) as $col) {
            $sh3->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'consolidado_ficha_' . $ficha->numero . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        ob_end_clean();
        $writer->save('php://output');
        exit;
    }

    // ─── Shared data builder ─────────────────────────────────────────────────
    private function buildStats(Ficha $ficha): array
    {
        $ficha->load(['program', 'instructors', 'aprendices', 'folders']);
        $aprendices = $ficha->aprendices()->orderBy('name')->get();

        // Attendance
        $sessions      = AttendanceSession::where('ficha_id', $ficha->id)->orderBy('date')->get();
        $totalSessions = $sessions->count();
        $sessionIds    = $sessions->pluck('id');

        $attendanceStats = $aprendices->map(function ($aprendiz) use ($sessionIds) {
            $records = AttendanceRecord::where('aprendiz_id', $aprendiz->id)
                ->whereIn('session_id', $sessionIds)
                ->selectRaw('status, COUNT(*) as cnt')
                ->groupBy('status')
                ->pluck('cnt', 'status');

            $presentes = (int) $records->get('presente', 0);
            $ausentes  = (int) $records->get('ausente', 0);
            $excusas   = (int) $records->get('excusa', 0);
            $total     = $presentes + $ausentes + $excusas;

            return [
                'aprendiz'  => $aprendiz,
                'presentes' => $presentes,
                'ausentes'  => $ausentes,
                'excusas'   => $excusas,
                'total'     => $total,
                'pct'       => $total > 0 ? round($presentes / $total * 100, 1) : 0,
            ];
        });

        $attendanceSummary = [
            'total_sessions' => $totalSessions,
            'pct_promedio'   => $attendanceStats->isNotEmpty() ? round($attendanceStats->avg('pct'), 1) : 0,
        ];

        // Grades
        $competencias = Competencia::where('program_id', $ficha->program_id)
            ->with('resultados.actividades')
            ->get();

        $allActividadIds = $competencias->flatMap(
            fn($c) => $c->resultados->flatMap(fn($r) => $r->actividades->pluck('id'))
        );
        $allNotas = $allActividadIds->isNotEmpty()
            ? ActividadNota::whereIn('actividad_id', $allActividadIds)->get()
            : collect();

        $notasStats = $aprendices->map(function ($aprendiz) use ($competencias, $allNotas) {
            $aprendizNotas = $allNotas->where('aprendiz_id', $aprendiz->id);

            $competenciasData = $competencias->map(function ($comp) use ($aprendizNotas) {
                $compNotaValues = collect();
                $resultadosData = $comp->resultados->map(function ($resultado) use ($aprendizNotas, &$compNotaValues) {
                    $actIds   = $resultado->actividades->pluck('id');
                    $notas    = $aprendizNotas->whereIn('actividad_id', $actIds)->pluck('nota');
                    $compNotaValues = $compNotaValues->merge($notas);
                    $promedio = $notas->isNotEmpty() ? round($notas->avg(), 2) : null;
                    return [
                        'nombre'   => $resultado->nombre,
                        'promedio' => $promedio,
                        'aprobado' => $promedio !== null ? $promedio >= 3.5 : null,
                    ];
                });
                $promComp = $compNotaValues->isNotEmpty() ? round($compNotaValues->avg(), 2) : null;
                return [
                    'nombre'     => $comp->nombre,
                    'promedio'   => $promComp,
                    'aprobado'   => $promComp !== null ? $promComp >= 3.5 : null,
                    'resultados' => $resultadosData,
                ];
            });

            $allValues      = $aprendizNotas->pluck('nota');
            $promedioGeneral = $allValues->isNotEmpty() ? round($allValues->avg(), 2) : null;

            return [
                'aprendiz'        => $aprendiz,
                'promedio_general' => $promedioGeneral,
                'aprobado'        => $promedioGeneral !== null ? $promedioGeneral >= 3.5 : null,
                'competencias'    => $competenciasData,
            ];
        });

        $notasSummary = [
            'total_competencias'    => $competencias->count(),
            'aprendices_aprobados'  => $notasStats->where('aprobado', true)->count(),
            'aprendices_reprobados' => $notasStats->where('aprobado', false)->count(),
            'pct_aprobacion'        => $aprendices->count() > 0
                ? round($notasStats->where('aprobado', true)->count() / $aprendices->count() * 100, 1) : 0,
        ];

        // Folders
        $folders    = $ficha->folders;
        $folderStats = [
            'total'       => $folders->count(),
            'sin_subir'   => $folders->where('status', 'sin_subir')->count(),
            'en_revision' => $folders->where('status', 'en_revision')->count(),
            'aprobado'    => $folders->where('status', 'aprobado')->count(),
            'rechazado'   => $folders->where('status', 'rechazado')->count(),
        ];
        $folderPct = $folderStats['total'] > 0
            ? round($folderStats['aprobado'] / $folderStats['total'] * 100, 1) : 0;

        return [
            $aprendices, $attendanceStats, $attendanceSummary, $totalSessions,
            $notasStats, $notasSummary, $competencias, $folderStats, $folderPct,
        ];
    }
}
