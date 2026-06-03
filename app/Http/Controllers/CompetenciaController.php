<?php

namespace App\Http\Controllers;

use App\Models\Competencia;
use App\Models\Program;
use App\Models\ResultadoAprendizaje;
use Illuminate\Http\Request;

class CompetenciaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Competencia::with(['program', 'instructor', 'resultados']);

        if ($user->isInstructor()) {
            $query->where('instructor_id', $user->id);
        }

        $competencias = $query->latest()->paginate(15);
        return view('competencias.index', compact('competencias'));
    }

    public function create()
    {
        $user = auth()->user();

        // Instructor: solo sus programas asignados
        $programs = $user->isInstructor()
            ? $user->programs()->orderBy('name')->get()
            : Program::orderBy('name')->get();

        if ($user->isInstructor() && $programs->isEmpty()) {
            return back()->with('error', 'No tienes programas asignados. Contacta al coordinador.');
        }

        return view('competencias.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Validar que el instructor tenga ese programa asignado
        $allowedIds = $user->isInstructor()
            ? $user->programs()->pluck('programs.id')->toArray()
            : Program::pluck('id')->toArray();

        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'program_id'  => ['required', 'exists:programs,id', function($attr, $val, $fail) use ($allowedIds) {
                if (!in_array($val, $allowedIds)) {
                    $fail('No tienes permiso para crear competencias en ese programa.');
                }
            }],
        ]);

        Competencia::create([
            ...$data,
            'instructor_id' => auth()->id(),
        ]);

        return redirect()->route('competencias.index')->with('success', 'Competencia creada.');
    }

    public function show(Competencia $competencia, \Illuminate\Http\Request $request)
    {
        $competencia->load('resultados');

        // Fichas del programa de esta competencia que tienen aprendices
        $fichas = \App\Models\Ficha::with('aprendices')
            ->where('program_id', $competencia->program_id)
            ->get();

        $fichaSeleccionada = null;
        $aprendicesConEstados = collect();

        if ($request->filled('ficha_id')) {
            $fichaSeleccionada = $fichas->firstWhere('id', $request->ficha_id);

            if ($fichaSeleccionada) {
                // Para cada aprendiz de la ficha, carga su estado en cada resultado
                $aprendicesConEstados = $fichaSeleccionada->aprendices->map(function ($aprendiz) use ($competencia) {
                    $estados = [];
                    foreach ($competencia->resultados as $resultado) {
                        $pivot = \App\Models\ResultadoAprendizaje::find($resultado->id)
                            ->aprendices()
                            ->where('users.id', $aprendiz->id)
                            ->first();
                        $estados[$resultado->id] = $pivot?->pivot->status ?? 'pendiente';
                    }
                    $aprendiz->estados_resultado = $estados;
                    return $aprendiz;
                });
            }
        }

        return view('competencias.show', compact('competencia', 'fichas', 'fichaSeleccionada', 'aprendicesConEstados'));
    }

    public function edit(Competencia $competencia)
    {
        $programs = Program::orderBy('name')->get();
        return view('competencias.edit', compact('competencia', 'programs'));
    }

    public function update(Request $request, Competencia $competencia)
    {
        $data = $request->validate([
            'nombre'     => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
        ]);

        $competencia->update($data);
        return redirect()->route('competencias.index')->with('success', 'Competencia actualizada.');
    }

    public function destroy(Competencia $competencia)
    {
        $competencia->delete();
        return back()->with('success', 'Competencia eliminada.');
    }

    // --- Resultados de Aprendizaje ---

    public function storeResultado(Request $request, Competencia $competencia)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $competencia->resultados()->create($data);
        return back()->with('success', 'Resultado de aprendizaje creado.');
    }

    public function updateResultado(Request $request, ResultadoAprendizaje $resultado)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $resultado->update($data);
        return back()->with('success', 'Resultado actualizado.');
    }

    public function destroyResultado(ResultadoAprendizaje $resultado)
    {
        $resultado->delete();
        return back()->with('success', 'Resultado eliminado.');
    }

    public function evaluateAprendiz(Request $request, ResultadoAprendizaje $resultado)
    {
        $data = $request->validate([
            'aprendiz_id' => 'required|exists:users,id',
            'status'      => 'required|in:aprobado,no_aprobado,pendiente',
        ]);

        $resultado->aprendices()->syncWithoutDetaching([
            $data['aprendiz_id'] => [
                'status'       => $data['status'],
                'evaluated_at' => $data['status'] !== 'pendiente' ? now() : null,
            ],
        ]);

        return back()->with('success', 'Evaluación guardada.');
    }

    // Guarda evaluaciones masivas por ficha: evaluaciones[resultado_id][aprendiz_id] = status
    public function evaluateFicha(Request $request, Competencia $competencia)
    {
        $request->validate([
            'ficha_id'         => 'required|exists:fichas,id',
            'evaluaciones'     => 'required|array',
            'evaluaciones.*.*' => 'required|in:aprobado,no_aprobado,pendiente',
        ]);

        foreach ($request->evaluaciones as $resultadoId => $aprendices) {
            $resultado = ResultadoAprendizaje::find($resultadoId);
            if (!$resultado || $resultado->competencia_id !== $competencia->id) {
                continue;
            }
            foreach ($aprendices as $aprendizId => $status) {
                $resultado->aprendices()->syncWithoutDetaching([
                    $aprendizId => [
                        'status'       => $status,
                        'evaluated_at' => $status !== 'pendiente' ? now() : null,
                    ],
                ]);
            }
        }

        return redirect()->route('competencias.show', [
            'competencia' => $competencia->id,
            'ficha_id'    => $request->ficha_id,
        ])->with('success', 'Evaluaciones guardadas correctamente.');
    }
}
