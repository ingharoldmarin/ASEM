<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MatriculaExcelController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Matrícula');

        // Encabezados
        $headers = [
            'A1' => 'numero_documento *',
            'B1' => 'nombre_completo *',
            'C1' => 'correo_electronico *',
            'D1' => 'telefono',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo encabezado
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00304D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        // Ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(18);

        // Filas de ejemplo en gris claro
        $ejemplos = [
            ['1234567890', 'Juan Pérez López', 'juan.perez@email.com', '3001234567'],
            ['0987654321', 'María García Torres', 'maria.garcia@email.com', '3109876543'],
        ];
        foreach ($ejemplos as $i => $row) {
            $fila = $i + 2;
            $sheet->setCellValue("A{$fila}", $row[0]);
            $sheet->setCellValue("B{$fila}", $row[1]);
            $sheet->setCellValue("C{$fila}", $row[2]);
            $sheet->setCellValue("D{$fila}", $row[3]);
            $sheet->getStyle("A{$fila}:D{$fila}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
            ]);
        }

        // Nota al pie
        $sheet->setCellValue('A5', '* Campos obligatorios. La contraseña inicial del aprendiz será su número de documento.');
        $sheet->getStyle('A5')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A5')->getFont()->getColor()->setRGB('6B7280');
        $sheet->mergeCells('A5:D5');

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="plantilla_matricula.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function import(Request $request, Ficha $ficha)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file        = $request->file('archivo');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        $creados    = 0;
        $matriculados = 0;
        $errores    = [];
        $yaMatriculados = 0;

        foreach ($rows as $index => $row) {
            if ($index === 1) continue; // saltar encabezado
            if ($index === 5 && empty(trim((string)($row['A'] ?? '')))) continue; // saltar nota

            $documento = trim((string)($row['A'] ?? ''));
            $nombre    = trim((string)($row['B'] ?? ''));
            $correo    = trim((string)($row['C'] ?? ''));
            $telefono  = trim((string)($row['D'] ?? ''));

            if (empty($documento)) continue;

            // Saltar filas de ejemplo
            if (in_array($documento, ['1234567890', '0987654321'])) continue;

            if (empty($nombre) || empty($correo)) {
                $errores[] = "Fila {$index}: nombre o correo vacío para documento {$documento}.";
                continue;
            }

            // Buscar o crear el aprendiz
            $aprendiz = User::where('numero_documento', $documento)->first();

            if (!$aprendiz) {
                // Verificar que el correo no esté en uso
                if (User::where('email', $correo)->exists()) {
                    $errores[] = "Fila {$index}: el correo {$correo} ya está registrado con otro documento.";
                    continue;
                }

                $aprendiz = User::create([
                    'name'             => $nombre,
                    'email'            => $correo,
                    'numero_documento' => $documento,
                    'telefono'         => $telefono ?: null,
                    'password'         => Hash::make($documento),
                    'role'             => 'aprendiz',
                ]);
                $creados++;
            }

            // Verificar si ya está matriculado
            if ($ficha->aprendices()->where('users.id', $aprendiz->id)->exists()) {
                $yaMatriculados++;
                continue;
            }

            $ficha->aprendices()->attach($aprendiz->id, ['enrolled_by' => auth()->id()]);
            $matriculados++;
        }

        $mensaje = "Importación completada: {$matriculados} aprendiz(ces) matriculados";
        if ($creados > 0)       $mensaje .= ", {$creados} usuario(s) nuevo(s) creados";
        if ($yaMatriculados > 0) $mensaje .= ", {$yaMatriculados} ya estaban matriculados";
        if (!empty($errores))   $mensaje .= '. Errores: ' . implode(' | ', $errores);

        return back()->with('success', $mensaje);
    }
}
