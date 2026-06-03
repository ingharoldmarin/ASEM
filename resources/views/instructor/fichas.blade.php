@extends('layouts.app')
@section('title', 'Mis Fichas')
@section('header', 'Mis Fichas Asignadas')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($fichas as $ficha)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="font-semibold text-gray-800">Ficha {{ $ficha->numero }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $ficha->program->name }}</p>
        <p class="text-xs text-gray-400">{{ $ficha->institucion }} — {{ $ficha->municipio }}</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('fichas.show', $ficha) }}"
               class="text-xs bg-primary-50 text-primary-600 hover:bg-blue-100 rounded px-2 py-1 transition-colors">
                Carpetas
            </a>
            <a href="{{ route('attendance.create', $ficha) }}"
               class="text-xs bg-green-50 text-green-600 hover:bg-green-100 rounded px-2 py-1 transition-colors">
                + Asistencia
            </a>
            <a href="{{ route('reports.create', $ficha) }}"
               class="text-xs bg-purple-50 text-purple-600 hover:bg-purple-100 rounded px-2 py-1 transition-colors">
                + Informe
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-3 bg-white rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-400 text-sm">No tienes fichas asignadas.</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $fichas->links() }}</div>
@endsection
