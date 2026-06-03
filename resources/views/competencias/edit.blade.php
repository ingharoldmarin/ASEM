@extends('layouts.app')
@section('title', 'Editar Competencia')
@section('header', 'Editar Competencia')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('competencias.update', $competencia) }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $competencia->nombre) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="descripcion" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('descripcion', $competencia->descripcion) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Programa</label>
                <select name="program_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ $competencia->program_id == $program->id ? 'selected' : '' }}>
                        {{ $program->code }} — {{ $program->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary text-sm px-5 py-2 rounded-lg transition-colors">Actualizar</button>
                <a href="{{ route('competencias.index') }}" class="text-sm text-gray-600 hover:text-gray-800 px-3 py-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

