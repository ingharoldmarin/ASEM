<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users',
            'numero_documento' => 'required|string|unique:users',
            'telefono'         => 'nullable|string|max:20',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'numero_documento' => $data['numero_documento'],
            'telefono'         => $data['telefono'] ?? null,
            'password'         => Hash::make($data['password']),
            'role'             => 'aprendiz',
        ]);

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboard()
    {
        $user = auth()->user();
        return match($user->role) {
            'admin'        => redirect()->route('admin.dashboard'),
            'coordinacion' => redirect()->route('coordinacion.dashboard'),
            'instructor'   => redirect()->route('instructor.dashboard'),
            'aprendiz'     => redirect()->route('aprendiz.dashboard'),
        };
    }
}
