<?php

namespace App\Http\Controllers;

use App\Models\ActividadNota;
use App\Models\ResultadoActividad;
use App\Models\ResultadoAprendizaje;
use App\Models\ResultadoGuia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResultadoRecursoController extends Controller
{
    // ── Guías ────────────────────────────────────────────────────

    public function uploadGuia(Request $request, ResultadoAprendizaje $resultado)
    {
        $request->validate([
            'guia' => 'required|file|mimes:pdf|max:20480',
        ]);

        $file = $request->file('guia');
        $path = $file->store('guias/' . $resultado->id, 'public');

        ResultadoGuia::create([
            'resultado_id'  => $resultado->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'uploaded_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Guía de aprendizaje subida correctamente.');
    }

    public function deleteGuia(ResultadoGuia $guia)
    {
        Storage::disk('public')->delete($guia->file_path);
        $guia->delete();
        return back()->with('success', 'Guía eliminada.');
    }

    // ── Actividades ───────────────────────────────────────────────

    public function storeActividad(Request $request, ResultadoAprendizaje $resultado)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $resultado->actividades()->create($data);
        return back()->with('success', 'Actividad creada.');
    }

    public function destroyActividad(ResultadoActividad $actividad)
    {
        $actividad->delete();
        return back()->with('success', 'Actividad eliminada.');
    }

    // ── Notas ─────────────────────────────────────────────────────

    public function calificar(Request $request, ResultadoActividad $actividad)
    {
        $data = $request->validate([
            'aprendiz_id' => 'required|exists:users,id',
            'nota'        => 'required|numeric|min:0|max:5',
        ]);

        $nota = round($data['nota'] * 10) / 10; // redondear a 1 decimal

        ActividadNota::updateOrCreate(
            ['actividad_id' => $actividad->id, 'aprendiz_id' => $data['aprendiz_id']],
            ['nota' => $nota]
        );

        return back()->with('success', 'Nota guardada.');
    }

    // Calificación masiva: notas[aprendiz_id] = nota
    public function calificarMasivo(Request $request, ResultadoActividad $actividad)
    {
        $request->validate([
            'notas'   => 'required|array',
            'notas.*' => 'nullable|numeric|min:0|max:5',
        ]);

        $resultado   = $actividad->resultado;
        $aprendizIds = [];

        foreach ($request->notas as $aprendizId => $nota) {
            if ($nota === null || $nota === '') continue;

            ActividadNota::updateOrCreate(
                ['actividad_id' => $actividad->id, 'aprendiz_id' => $aprendizId],
                ['nota' => round((float)$nota * 10) / 10]
            );

            $aprendizIds[] = (int) $aprendizId;
        }

        // Recalcular estado del resultado para cada aprendiz calificado
        foreach (array_unique($aprendizIds) as $aprendizId) {
            $this->recalcularResultado($resultado, $aprendizId);
        }

        // Preservar ficha y resultado en el redirect
        $fichaId     = $request->query('ficha_id');
        $resultadoId = $request->query('resultado', $actividad->resultado_id);

        $redirectUrl = route('resultados.detalle', $resultadoId) .
            ($fichaId ? '?ficha_id=' . $fichaId : '');

        return redirect($redirectUrl)->with('success', 'Notas guardadas correctamente.');
    }

    // Vista completa de un resultado (guías + actividades + notas)
    public function show(ResultadoAprendizaje $resultado, \Illuminate\Http\Request $request)
    {
        $resultado->load([
            'competencia.program',
            'guias.uploader',
            'actividades.notas',
        ]);

        $user = auth()->user();

        // Fichas del programa — instructor solo ve las suyas
        $fichasQuery = \App\Models\Ficha::with('aprendices')
            ->where('program_id', $resultado->competencia->program_id);

        if ($user->isInstructor()) {
            $fichasQuery->whereHas('instructors', fn($q) => $q->where('users.id', $user->id));
        }

        $fichas = $fichasQuery->get();

        // Ficha seleccionada
        $fichaSeleccionada = null;
        $aprendices = collect();

        if ($request->filled('ficha_id')) {
            $fichaSeleccionada = $fichas->firstWhere('id', $request->ficha_id);
            if ($fichaSeleccionada) {
                $aprendices = $fichaSeleccionada->aprendices->sortBy('name');
            }
        }

        return view('resultados.show', compact('resultado', 'fichas', 'fichaSeleccionada', 'aprendices'));
    }

    // ── Recalcula el estado del resultado según el promedio de actividades ──
    private function recalcularResultado(ResultadoAprendizaje $resultado, int $aprendizId): void
    {
        // Cargar todas las actividades del resultado
        $actividades = $resultado->actividades()->pluck('id');

        if ($actividades->isEmpty()) return;

        // Notas del aprendiz en esas actividades
        $notas = ActividadNota::whereIn('actividad_id', $actividades)
            ->where('aprendiz_id', $aprendizId)
            ->pluck('nota');

        // Solo recalcula si hay al menos una nota
        if ($notas->isEmpty()) return;

        $promedio = round($notas->avg(), 1);
        $status   = $promedio >= 3.5 ? 'aprobado' : 'no_aprobado';

        // Actualizar la tabla pivote aprendiz_resultado
        $resultado->aprendices()->syncWithoutDetaching([
            $aprendizId => [
                'status'       => $status,
                'evaluated_at' => now(),
            ],
        ]);
    }
}
