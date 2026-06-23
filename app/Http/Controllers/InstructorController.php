<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $fichas = $user->fichasAsInstructor()
            ->with('program')
            ->withCount('folders')
            ->withCount(['folders as folders_pendientes_count' => function ($q) {
                $q->where('status', 'sin_subir');
            }])
            ->withCount(['folders as folders_observadas_count' => function ($q) {
                $q->whereIn('status', ['pendiente_subir', 'rechazado']);
            }])
            ->get();
        $myPrograms = $user->programs()->orderBy('name')->get();

        $pendingFolders = Folder::whereIn('ficha_id', $fichas->pluck('id'))
            ->where('status', 'sin_subir')
            ->count();

        $rejectedFolders = Folder::whereIn('ficha_id', $fichas->pluck('id'))
            ->whereIn('status', ['rechazado', 'pendiente_subir'])
            ->count();

        return view('instructor.dashboard', compact('fichas', 'pendingFolders', 'rejectedFolders', 'myPrograms'));
    }

    public function myFichas()
    {
        $fichas = auth()->user()->fichasAsInstructor()->with('program')->paginate(15);
        return view('instructor.fichas', compact('fichas'));
    }

    public function enrollAprendiz(Request $request, Ficha $ficha)
    {
        $data = $request->validate(['aprendiz_id' => 'required|exists:users,id']);
        $aprendiz = User::findOrFail($data['aprendiz_id']);
        abort_if($aprendiz->role !== 'aprendiz', 422);

        $ficha->aprendices()->syncWithoutDetaching([
            $data['aprendiz_id'] => ['enrolled_by' => auth()->id()],
        ]);

        return back()->with('success', 'Aprendiz matriculado.');
    }

    public function removeAprendiz(Ficha $ficha, User $aprendiz)
    {
        $ficha->aprendices()->detach($aprendiz->id);
        return back()->with('success', 'Aprendiz removido de la ficha.');
    }
}
