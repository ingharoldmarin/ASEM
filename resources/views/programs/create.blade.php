@extends('layouts.app')
@section('title', 'Nuevo Programa')
@section('header', 'Crear Programa')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('programs.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código del programa</label>
                <input type="text" name="code" value="{{ old('code') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-400 @enderror">
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del programa</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary text-sm px-5 py-2 rounded-lg transition-colors">
                    Guardar
                </button>
                <a href="{{ route('programs.index') }}" class="text-sm text-gray-600 hover:text-gray-800 px-3 py-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

