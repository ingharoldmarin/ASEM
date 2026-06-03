<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use App\Models\Folder;
use App\Models\Program;
use App\Models\User;

class CoordinacionController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'instructores' => User::where('role', 'instructor')->count(),
            'aprendices'   => User::where('role', 'aprendiz')->count(),
            'fichas'       => Ficha::count(),
            'programas'    => Program::count(),
            'pendientes'   => Folder::where('status', 'en_revision')->count(),
        ];
        return view('coordinacion.dashboard', compact('stats'));
    }

    public function createInstructor()
    {
        return view('coordinacion.instructors.create');
    }

    public function storeInstructor(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users',
            'numero_documento' => 'required|string|unique:users',
            'telefono'         => 'nullable|string|max:20',
            'password'         => 'required|min:8|confirmed',
        ]);

        User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'numero_documento' => $data['numero_documento'],
            'telefono'         => $data['telefono'] ?? null,
            'password'         => \Illuminate\Support\Facades\Hash::make($data['password']),
            'role'             => 'instructor',
        ]);

        return redirect()->route('coordinacion.dashboard')->with('success', 'Instructor creado exitosamente.');
    }
}
