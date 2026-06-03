<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class InstructorProgramController extends Controller
{
    public function index()
    {
        $instructors = User::where('role', 'instructor')
            ->with('programs')
            ->orderBy('name')
            ->paginate(20);

        $programs = Program::orderBy('name')->get();

        return view('instructors.programs', compact('instructors', 'programs'));
    }

    public function assign(Request $request, User $instructor)
    {
        abort_if($instructor->role !== 'instructor', 403);

        $data = $request->validate([
            'program_id' => 'required|exists:programs,id',
        ]);

        $instructor->programs()->syncWithoutDetaching([$data['program_id']]);

        return back()->with('success', 'Programa asignado a ' . $instructor->name . '.');
    }

    public function remove(User $instructor, Program $program)
    {
        abort_if($instructor->role !== 'instructor', 403);
        $instructor->programs()->detach($program->id);
        return back()->with('success', 'Programa removido.');
    }
}
