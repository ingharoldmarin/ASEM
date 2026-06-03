<?php

namespace App\Http\Controllers;

use App\Models\Ficha;
use App\Models\Folder;
use App\Models\Program;
use App\Models\User;

class AdminController extends Controller
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
        return view('admin.dashboard', compact('stats'));
    }

    public function instructors()
    {
        $instructors = User::where('role', 'instructor')->latest()->paginate(15);
        return view('admin.instructors.index', compact('instructors'));
    }

    public function createInstructor()
    {
        return view('admin.instructors.create');
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

        return redirect()->route('admin.instructors')->with('success', 'Instructor creado exitosamente.');
    }

    public function deleteInstructor(User $user)
    {
        abort_if($user->role !== 'instructor', 403);
        $user->delete();
        return back()->with('success', 'Instructor eliminado.');
    }

    public function users()
    {
        $users = User::whereNotIn('role', ['admin'])->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }
}
