<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('fichas')->latest()->paginate(15);
        return view('programs.index', compact('programs'));
    }

    public function create()
    {
        return view('programs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:programs',
            'name' => 'required|string|max:255',
        ]);

        Program::create($data);
        return redirect()->route('programs.index')->with('success', 'Programa creado exitosamente.');
    }

    public function edit(Program $program)
    {
        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:programs,code,' . $program->id,
            'name' => 'required|string|max:255',
        ]);

        $program->update($data);
        return redirect()->route('programs.index')->with('success', 'Programa actualizado.');
    }

    public function destroy(Program $program)
    {
        $program->delete();
        return back()->with('success', 'Programa eliminado.');
    }
}
