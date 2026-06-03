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
        $fichas = $user->fichasAsInstructor()->with('program')->get();
        $myPrograms = $user->programs()->orderBy('name')->get();

        $pendingFolders = Folder::whereIn('ficha_id', $fichas->pluck('id'))
            ->where('status', 'sin_subir')
            ->count();

        $rejectedFolders = Folder::whereIn('ficha_id', $fichas->pluck('id'))
            ->where('status', 'rechazado')
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
