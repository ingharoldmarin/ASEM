@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('header', 'Panel de Administración')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Instructores</p>
        <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['instructores'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Aprendices</p>
        <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['aprendices'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Fichas</p>
        <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['fichas'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Programas</p>
        <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['programas'] }}</p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wide">Carpetas en revisión</p>
        <p class="text-3xl font-bold text-red-500 mt-1">{{ $stats['pendientes'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Acciones rápidas</h3>
        <div class="space-y-2">
            <a href="{{ route('programs.create') }}" class="flex items-center gap-2 text-sm link-primary">
                <span class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center text-xs">+</span>
                Crear programa
            </a>
            <a href="{{ route('fichas.create') }}" class="flex items-center gap-2 text-sm link-primary">
                <span class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center text-xs">+</span>
                Crear ficha
            </a>
            <a href="{{ route('admin.instructors.create') }}" class="flex items-center gap-2 text-sm link-primary">
                <span class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center text-xs">+</span>
                Registrar instructor
            </a>
            <a href="{{ route('competencias.create') }}" class="flex items-center gap-2 text-sm link-primary">
                <span class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center text-xs">+</span>
                Crear competencia
            </a>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Módulos del sistema</h3>
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('programs.index') }}" class="text-xs bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg p-3 text-center transition-colors">Programas</a>
            <a href="{{ route('fichas.index') }}" class="text-xs bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg p-3 text-center transition-colors">Fichas</a>
            <a href="{{ route('admin.instructors') }}" class="text-xs bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg p-3 text-center transition-colors">Instructores</a>
            <a href="{{ route('admin.users') }}" class="text-xs bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg p-3 text-center transition-colors">Todos los usuarios</a>
            <a href="{{ route('competencias.index') }}" class="text-xs bg-gray-50 hover:bg-blue-50 text-gray-700 hover:text-blue-700 rounded-lg p-3 text-center transition-colors">Competencias</a>
        </div>
    </div>
</div>
@endsection
