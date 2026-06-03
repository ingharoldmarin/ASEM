@extends('layouts.app')
@section('title', 'Nueva Ficha')
@section('header', 'Crear Ficha')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('fichas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número de ficha</label>
                <input type="text" name="numero" value="{{ old('numero') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('numero') border-red-400 @enderror">
                @error('numero')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Programa</label>
                <select name="program_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('program_id') border-red-400 @enderror">
                    <option value="">Seleccionar programa...</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->code }} — {{ $program->name }}
                        </option>
                    @endforeach
                </select>
                @error('program_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Institución educativa</label>
                <input type="text" name="institucion" value="{{ old('institucion') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('institucion') border-red-400 @enderror">
                @error('institucion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Municipio</label>
                <input type="text" name="municipio" value="{{ old('municipio') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('municipio') border-red-400 @enderror">
                @error('municipio')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <p class="text-xs text-gray-400">Se crearán automáticamente 33 carpetas documentales.</p>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary text-sm px-5 py-2 rounded-lg transition-colors">
                    Crear ficha
                </button>
                <a href="{{ route('fichas.index') }}" class="text-sm text-gray-600 hover:text-gray-800 px-3 py-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

