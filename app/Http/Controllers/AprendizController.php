<?php

namespace App\Http\Controllers;

class AprendizController extends Controller
{
    public function dashboard()
    {
        $user   = auth()->user();
        $fichas = $user->fichasAsAprendiz()->with('program')->get();

        // IDs de programas en los que está matriculado
        $programIds = $fichas->pluck('program_id')->unique();

        // Competencias de esos programas con sus resultados y el estado del aprendiz
        $competencias = \App\Models\Competencia::with([
            'program',
            'resultados.aprendices' => fn ($q) => $q->where('users.id', $user->id),
        ])->whereIn('program_id', $programIds)->get();

        // IDs de resultados ya evaluados para este aprendiz
        $misResultadosIds = $user->resultadosAprendizaje()->pluck('resultado_id')->toArray();

        return view('aprendiz.dashboard', compact('fichas', 'competencias', 'misResultadosIds'));
    }

    public function misResultados()
    {
        $user       = auth()->user();
        $programIds = $user->fichasAsAprendiz()->pluck('program_id')->unique();

        $competencias = \App\Models\Competencia::with([
            'program',
            'resultados.aprendices' => fn ($q) => $q->where('users.id', $user->id),
        ])->whereIn('program_id', $programIds)->get();

        return view('aprendiz.resultados', compact('competencias'));
    }
}
