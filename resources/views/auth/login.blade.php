@extends('layouts.guest')
@section('title', 'Iniciar Sesión')

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-6">Iniciar Sesión</h2>

<form action="{{ route('login.post') }}" method="POST" class="space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
        <input type="password" name="password" required
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
        <label for="remember" class="text-sm text-gray-600">Recordarme</label>
    </div>

    <button type="submit"
            class="w-full btn-primary font-medium py-2 rounded-lg text-sm transition-colors">
        Ingresar
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-6">
    ¿No tienes cuenta?
    <a href="{{ route('register') }}" class="link-primary hover:underline font-medium">Regístrate como aprendiz</a>
</p>
@endsection

