@extends('layouts.app')
@section('title', 'Consolidados')
@section('header', 'Consolidados por Ficha')

@section('content')
<div class="mb-6">
    <p class="text-sm text-gray-500">Selecciona una ficha para ver el consolidado de asistencia, notas y documentos.</p>
</div>

@if($fichas->isEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
        <p class="text-gray-400">No hay fichas disponibles.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($fichas as $ficha)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            <div class="px-5 py-4 border-b" style="background:#00304D;">
                <p class="text-white font-semibold text-lg">Ficha {{ $ficha->numero }}</p>
                <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $ficha->program->name ?? '—' }}</p>
            </div>
            <div class="px-5 py-4 space-y-2">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ $ficha->institucion }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $ficha->aprendices_count }} aprendice(s)</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ $ficha->instructors->pluck('name')->join(', ') ?: '—' }}</span>
                </div>

                @if($ficha->folders_sin_subir_count > 0 || $ficha->folders_observadas_count > 0)
                <div class="flex items-center gap-2 flex-wrap pt-1">
                    @if($ficha->folders_sin_subir_count > 0)
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                        </svg>
                        {{ $ficha->folders_sin_subir_count }} por subir
                    </span>
                    @endif
                    @if($ficha->folders_observadas_count > 0)
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-700 font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-8.18 14.14A1 1 0 003 19.5h18a1 1 0 00.89-1.5L13.71 3.86a1 1 0 00-1.42 0z"/>
                        </svg>
                        {{ $ficha->folders_observadas_count }} con observación
                    </span>
                    @endif
                </div>
                @endif
            </div>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('consolidado.show', $ficha) }}"
                   class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ver consolidado
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $fichas->links() }}</div>
@endif
@endsection
