<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use App\Models\Folder;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class FichaController extends Controller
{
    // [name, responsible_role]
    private const FOLDER_DEFAULTS = [
        1  => ['Plantilla de Minuta Articulación con la Media',                                              'coordinacion'],
        2  => ['Plan operativo',                                                                             'coordinacion'],
        3  => ['Ficha de Apertura',                                                                         'coordinacion'],
        4  => ['Relación de Estudiantes Que Ingresan al Programa - Comunicado Radicado para Matricula',     'instructor'],
        5  => ['Plantilla Plan de Trabajo Concertado / Desarrollo de la Ruta de Aprendizaje (Macro)',       'instructor'],
        6  => ['GOR-F-084 V2: Acta Transferencia Institucional',                                            'coordinacion'],
        7  => ['GOR-F-084: Acta Articulación Currículo Educación Media',                                    'instructor'],
        8  => ['GFPI-F-035: Formato Articulación Currículo Educación Media',                                'instructor'],
        9  => ['GFPI-F-134: Planeación Pedagógica del Proyecto Formativo',                                  'instructor'],
        10 => ['GOR-F-084: Acta Inducción y Reinducción Aprendices',                                        'instructor'],
        11 => ['GOR-F-084 V2: Acta Inducción Instructor',                                                   'coordinacion'],
        12 => ['Lineamientos de Articulación / Acta de Compromiso',                                         'coordinacion'],
        13 => ['GFPI-PL-001: Planilla Asistencia de Matricula',                                             'instructor'],
        14 => ['GFPI-F-015: Formato Compromiso del Aprendiz V2',                                            'instructor'],
        15 => ['Fotocopia del documento de identidad',                                                      'instructor'],
        16 => ['Acta de Evaluación y Seguimiento',                                                          'instructor'],
        17 => ['GOR-F-084 V2: Acta Socialización Inicio Etapa Productiva',                                  'instructor'],
        18 => ['Plantilla Alternativa Etapa Práctica',                                                      'instructor'],
        19 => ['GFPI-F-147: Formato Bitácora Etapa Productiva',                                             'instructor'],
        20 => ['GOR-F-084 V2: Acta Socialización Encadenamiento - Cronograma Divulgación en IE',            'instructor'],
        21 => ['Acta de socialización del programa - Sensibilización',                                      'instructor'],
        22 => ['Verificación del cumplimiento - GOR-F-084 V2: Acta Concepto Técnico',                       'instructor'],
        23 => ['GFPI-F-033: Autodiagnóstico de la Institución Educativa',                                   'instructor'],
        24 => ['Paz y Salvo del Aprendiz - Certificación Etapa Productiva - Carta Solicitud Certificación', 'instructor'],
        25 => ['Informe Estadístico Certificación y Deserción',                                              'coordinacion'],
        26 => ['Plantilla Juicios Áreas Transversales como Promover - Inglés',                              'instructor'],
        27 => ['Plantilla Juicios Áreas Transversales Programas Versión Nueva',                             'instructor'],
        28 => ['GOR-F-084 V2: Acta Evaluación de la Ejecución Once',                                       'instructor'],
        29 => ['GFPI-F-135 V04: Guía de aprendizaje',                                                      'instructor'],
        30 => ['GOR-F-084 V2: Formato Acta, Planes de Mejoramiento',                                       'instructor'],
        31 => ['GFPI-F-023 V04: Formato Planeación, Seguimiento y Evaluación Etapa Productiva',            'instructor'],
        32 => ['Planilla Registro de Asistencia',                                                           'instructor'],
        33 => ['Reporte de Juicios Evaluativos',                                                            'instructor'],
    ];

    public function index()
    {
        $user = auth()->user();

        $query = Ficha::with('program')->latest();

        // Instructor solo ve sus fichas asignadas
        if ($user->isInstructor()) {
            $query->whereHas('instructors', fn($q) => $q->where('users.id', $user->id));
        }

        $fichas = $query->paginate(15);
        return view('fichas.index', compact('fichas'));
    }

    public function create()
    {
        $user = auth()->user();

        // Instructor: solo sus programas asignados
        $programs = $user->isInstructor()
            ? $user->programs()->orderBy('name')->get()
            : Program::orderBy('name')->get();

        if ($user->isInstructor() && $programs->isEmpty()) {
            return redirect()->route('fichas.index')
                ->with('error', 'No tienes programas asignados. Contacta al coordinador.');
        }

        return view('fichas.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $allowedProgramIds = $user->isInstructor()
            ? $user->programs()->pluck('programs.id')->toArray()
            : Program::pluck('id')->toArray();

        $data = $request->validate([
            'numero'      => 'required|string|unique:fichas',
            'program_id'  => ['required', 'exists:programs,id', function($attr, $val, $fail) use ($allowedProgramIds) {
                if (!in_array($val, $allowedProgramIds)) {
                    $fail('No tienes permiso para ese programa.');
                }
            }],
            'institucion' => 'required|string|max:255',
            'municipio'   => 'required|string|max:255',
        ]);

        $ficha = Ficha::create($data);

        // Auto-crear 33 carpetas
        foreach (self::FOLDER_DEFAULTS as $position => [$name, $responsible]) {
            Folder::create([
                'name'             => $name,
                'position'         => $position,
                'ficha_id'         => $ficha->id,
                'status'           => 'sin_subir',
                'responsible_role' => $responsible,
            ]);
        }

        // Si es instructor, se auto-asigna a la ficha
        if ($user->isInstructor()) {
            $ficha->instructors()->syncWithoutDetaching([$user->id]);
        }

        return redirect()->route('fichas.show', $ficha)
            ->with('success', 'Ficha creada con 33 carpetas.' . ($user->isInstructor() ? ' Quedaste asignado como instructor.' : ''));
    }

    public function show(Ficha $ficha)
    {
        $ficha->load(['program', 'folders', 'instructors', 'aprendices']);

        $assignedIds = $ficha->instructors->pluck('id');
        $availableInstructors = User::where('role', 'instructor')
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        $availableAprendices = User::where('role', 'aprendiz')
            ->whereNotIn('id', $ficha->aprendices->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('fichas.show', compact('ficha', 'availableInstructors', 'availableAprendices'));
    }

    public function downloadZip(Ficha $ficha)
    {
        $ficha->load(['folders.documents', 'program', 'instructors']);

        $instructorNombre = $ficha->instructors->isNotEmpty()
            ? $ficha->instructors->pluck('name')->join('-')
            : 'sin_instructor';

        $zipName = $this->sanitizeName($ficha->institucion)
            . '_' . $ficha->numero
            . '_' . $this->sanitizeName($instructorNombre)
            . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);

        // Asegurar que existe el directorio temporal
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        foreach ($ficha->folders as $folder) {
            $folderName = sprintf('%02d - %s', $folder->position, $this->sanitizeName($folder->name));

            if ($folder->documents->isEmpty()) {
                $zip->addEmptyDir($folderName);
            } else {
                foreach ($folder->documents as $doc) {
                    $filePath = storage_path('app/public/' . $doc->file_path);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, "{$folderName}/{$doc->original_name}");
                    }
                }
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function sanitizeName(string $name): string
    {
        // Elimina caracteres no válidos para nombres de carpeta en Windows/Linux
        return preg_replace('/[\/\\\\:*?"<>|]/', '-', $name);
    }

    public function edit(Ficha $ficha)
    {
        $programs = Program::orderBy('name')->get();
        return view('fichas.edit', compact('ficha', 'programs'));
    }

    public function update(Request $request, Ficha $ficha)
    {
        $data = $request->validate([
            'numero'     => 'required|string|unique:fichas,numero,' . $ficha->id,
            'program_id' => 'required|exists:programs,id',
            'institucion' => 'required|string|max:255',
            'municipio'   => 'required|string|max:255',
        ]);

        $ficha->update($data);
        return redirect()->route('fichas.index')->with('success', 'Ficha actualizada.');
    }

    public function destroy(Ficha $ficha)
    {
        $ficha->delete();
        return back()->with('success', 'Ficha eliminada.');
    }

    public function assignInstructor(Request $request, Ficha $ficha)
    {
        $data = $request->validate(['instructor_id' => 'required|exists:users,id']);
        $instructor = User::findOrFail($data['instructor_id']);
        abort_if($instructor->role !== 'instructor', 422);
        $ficha->instructors()->syncWithoutDetaching([$data['instructor_id']]);
        return back()->with('success', 'Instructor asignado.');
    }

    public function removeInstructor(Ficha $ficha, User $instructor)
    {
        $ficha->instructors()->detach($instructor->id);
        return back()->with('success', 'Instructor removido.');
    }
}
