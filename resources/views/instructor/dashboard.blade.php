@extends('layouts.app')
@section('title', 'Dashboard Instructor')
@section('header', 'Panel del Instructor')

@section('content')

{{-- Programas asignados --}}
@if($myPrograms->isNotEmpty())
<div class="mb-5 flex flex-wrap gap-2">
    @foreach($myPrograms as $prog)
    <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-medium"
          style="background:#f0fce8;border:1px solid #39A900;color:#007832;">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 14l9-5-9-5-9 5 9 5z"/>
        </svg>
        {{ $prog->code }} — {{ $prog->name }}
    </span>
    @endforeach
</div>
@else
<div class="mb-5 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
    <strong>Sin programas asignados.</strong> Contacta al coordinador para que te asigne un programa.
</div>
@endif

@if($pendingFolders > 0 || $rejectedFolders > 0)
<div class="mb-6 space-y-2">
    @if($pendingFolders > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
        <strong>Atención:</strong> Tienes {{ $pendingFolders }} carpeta(s) sin documentos en tus fichas.
    </div>
    @endif
    @if($rejectedFolders > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3 text-sm text-orange-800">
        <strong>Acción requerida:</strong> {{ $rejectedFolders }} carpeta(s) tienen observación y deben ser corregidas.
    </div>
    @endif
</div>
@endif

<div class="mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-3">Mis Fichas Asignadas</h3>
    @if($fichas->isEmpty())
        <p class="text-sm text-gray-500">No tienes fichas asignadas aún.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($fichas as $ficha)
            @php
                $completa = $ficha->folders_pendientes_count === 0 && $ficha->folders_observadas_count === 0;
            @endphp
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-gray-800">Ficha {{ $ficha->numero }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $ficha->program->name }}</p>
                        <p class="text-xs text-gray-400">{{ $ficha->municipio }}</p>
                    </div>
                </div>

                <div class="mt-3">
                    @if($completa)
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Carpetas completas ({{ $ficha->folders_count }}/{{ $ficha->folders_count }})
                    </span>
                    @else
                    <div class="flex flex-wrap gap-1.5">
                        @if($ficha->folders_pendientes_count > 0)
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 font-medium">
                            {{ $ficha->folders_pendientes_count }} por subir
                        </span>
                        @endif
                        @if($ficha->folders_observadas_count > 0)
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-700 font-medium">
                            {{ $ficha->folders_observadas_count }} con observación
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('fichas.show', $ficha) }}"
                       class="text-xs bg-primary-50 text-primary-600 hover:bg-blue-100 rounded px-2 py-1 transition-colors">
                        Carpetas
                    </a>
                    <a href="{{ route('attendance.index', $ficha) }}"
                       class="text-xs bg-green-50 text-green-600 hover:bg-green-100 rounded px-2 py-1 transition-colors">
                        Asistencia
                    </a>
                    <a href="{{ route('reports.index', $ficha) }}"
                       class="text-xs bg-purple-50 text-purple-600 hover:bg-purple-100 rounded px-2 py-1 transition-colors">
                        Informes
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<div class="flex gap-3 flex-wrap">
    <a href="{{ route('instructor.fichas') }}"
       class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm px-4 py-2 rounded-lg transition-colors">
        Ver todas mis fichas
    </a>
    <a href="{{ route('competencias.index') }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        Mis Competencias
    </a>
</div>
@endsection

