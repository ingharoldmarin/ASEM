@extends('layouts.app')
@section('title', 'Competencias')
@section('header', 'Competencias')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">{{ $competencias->total() }} competencia(s)</p>
    <a href="{{ route('competencias.create') }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        + Nueva competencia
    </a>
</div>

<div class="space-y-4">
    @forelse($competencias as $comp)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-semibold text-gray-800">{{ $comp->nombre }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $comp->program->name }} — Instructor: {{ $comp->instructor->name }}
                </p>
                @if($comp->descripcion)
                <p class="text-sm text-gray-600 mt-1">{{ $comp->descripcion }}</p>
                @endif
                <p class="text-xs text-blue-600 mt-2">{{ $comp->resultados->count() }} resultado(s) de aprendizaje</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('competencias.show', $comp) }}"
                   class="text-xs bg-primary-50 text-primary-600 hover:bg-blue-100 px-3 py-1.5 rounded transition-colors">
                    Ver resultados
                </a>
                <a href="{{ route('competencias.edit', $comp) }}"
                   class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1.5">Editar</a>
                <form action="{{ route('competencias.destroy', $comp) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar esta competencia?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-400 hover:text-red-600 px-2 py-1.5">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-400 text-sm">No hay competencias registradas.</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $competencias->links() }}</div>
@endsection

