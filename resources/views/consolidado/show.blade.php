@extends('layouts.app')
@section('title', 'Consolidado Ficha ' . $ficha->numero)
@section('header', 'Consolidado — Ficha ' . $ficha->numero)

@section('content')

{{-- Encabezado y exportaciones --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('consolidado.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
        <div class="mt-2 space-y-0.5">
            <p class="text-sm text-gray-600"><span class="font-medium">Programa:</span> {{ $ficha->program->name ?? '—' }}</p>
            <p class="text-sm text-gray-600"><span class="font-medium">Institución:</span> {{ $ficha->institucion }} — {{ $ficha->municipio }}</p>
            <p class="text-sm text-gray-600"><span class="font-medium">Instructores:</span> {{ $ficha->instructors->pluck('name')->join(', ') ?: '—' }}</p>
            <p class="text-sm text-gray-600"><span class="font-medium">Aprendices:</span> {{ $aprendices->count() }}</p>
        </div>
    </div>
    <div class="flex gap-2 shrink-0">
        <a href="{{ route('consolidado.excel', $ficha) }}"
           class="inline-flex items-center gap-2 text-sm px-4 py-2 rounded-lg border transition-colors
                  border-green-600 text-green-700 hover:bg-green-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Excel
        </a>
        <a href="{{ route('consolidado.pdf', $ficha) }}"
           class="inline-flex items-center gap-2 text-sm px-4 py-2 rounded-lg border transition-colors
                  border-red-500 text-red-600 hover:bg-red-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            PDF
        </a>
    </div>
</div>

{{-- ── Tarjetas resumen ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Asistencia --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Asistencia</p>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $attendanceSummary['pct_promedio'] >= 80 ? 'bg-green-100 text-green-700' :
                   ($attendanceSummary['pct_promedio'] >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ $attendanceSummary['pct_promedio'] }}%
            </span>
        </div>
        <p class="text-3xl font-bold" style="color:#39A900;">{{ $attendanceSummary['pct_promedio'] }}%</p>
        <p class="text-xs text-gray-400 mt-1">Promedio de asistencia · {{ $totalSessions }} sesiones</p>
        <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all"
                 style="width:{{ $attendanceSummary['pct_promedio'] }}%;
                        background:{{ $attendanceSummary['pct_promedio'] >= 80 ? '#39A900' : ($attendanceSummary['pct_promedio'] >= 60 ? '#f59e0b' : '#ef4444') }};">
            </div>
        </div>
    </div>

    {{-- Notas --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Notas</p>
            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">
                {{ $notasSummary['pct_aprobacion'] }}% aprobados
            </span>
        </div>
        <p class="text-3xl font-bold" style="color:#00304D;">{{ $notasSummary['aprendices_aprobados'] }}/{{ $aprendices->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">Aprendices aprobados · {{ $notasSummary['total_competencias'] }} competencias</p>
        <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all" style="width:{{ $notasSummary['pct_aprobacion'] }}%; background:#00304D;"></div>
        </div>
    </div>

    {{-- Documentos --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Documentos</p>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $folderPct >= 80 ? 'bg-green-100 text-green-700' : ($folderPct >= 40 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                {{ $folderPct }}% aprobadas
            </span>
        </div>
        <p class="text-3xl font-bold" style="color:#39A900;">{{ $folderStats['aprobado'] }}/{{ $folderStats['total'] }}</p>
        <p class="text-xs text-gray-400 mt-1">Carpetas aprobadas de {{ $folderStats['total'] }} totales</p>
        <div class="mt-3 flex h-2 bg-gray-100 rounded-full overflow-hidden">
            @php $t = max($folderStats['total'], 1); @endphp
            <div class="h-full bg-green-500" style="width:{{ round($folderStats['aprobado']/$t*100) }}%;"></div>
            <div class="h-full bg-yellow-400" style="width:{{ round($folderStats['en_revision']/$t*100) }}%;"></div>
            <div class="h-full bg-red-400" style="width:{{ round($folderStats['rechazado']/$t*100) }}%;"></div>
        </div>
        <div class="flex gap-3 mt-2 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Aprobado {{ $folderStats['aprobado'] }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>En revisión {{ $folderStats['en_revision'] }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Rechazado {{ $folderStats['rechazado'] }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>Sin subir {{ $folderStats['sin_subir'] }}</span>
        </div>
    </div>
</div>

{{-- ── Tabs ─────────────────────────────────────────────────────────────────── --}}
<div x-data="{ tab: 'asistencia' }">

    <div class="flex gap-1 mb-0 border-b border-gray-200">
        @foreach(['asistencia' => 'Asistencia', 'notas' => 'Notas', 'documentos' => 'Documentos'] as $key => $label)
        <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}'
                    ? 'border-b-2 text-sm font-semibold px-5 py-3 -mb-px transition-colors'
                    : 'border-b-2 border-transparent text-sm font-semibold px-5 py-3 -mb-px transition-colors text-gray-500 hover:text-gray-700'"
                :style="tab === '{{ $key }}' ? 'border-color:#39A900;color:#39A900;' : ''">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── Tab Asistencia ── --}}
    <div x-show="tab === 'asistencia'" class="bg-white rounded-b-xl rounded-tr-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between" style="background:#00304D;">
            <p class="text-sm font-semibold text-white">Registro de Asistencia por Aprendiz</p>
            <span class="text-xs" style="color:rgba(255,255,255,0.55);">{{ $totalSessions }} sesiones · Aprobación ≥ 80%</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Aprendiz</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-32">Documento</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-green-600 uppercase w-24">Presentes</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-red-500 uppercase w-24">Ausentes</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-yellow-600 uppercase w-24">Excusas</th>
                        <th class="text-center px-3 py-3 text-xs font-semibold text-gray-500 uppercase w-28">Sesiones</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-32">% Asistencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($attendanceStats as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $s['aprendiz']->name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $s['aprendiz']->numero_documento }}</td>
                        <td class="px-3 py-3 text-center font-semibold text-green-700">{{ $s['presentes'] }}</td>
                        <td class="px-3 py-3 text-center text-red-600">{{ $s['ausentes'] }}</td>
                        <td class="px-3 py-3 text-center text-yellow-700">{{ $s['excusas'] }}</td>
                        <td class="px-3 py-3 text-center text-gray-500">{{ $s['total'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full"
                                         style="width:{{ $s['pct'] }}%;
                                                background:{{ $s['pct'] >= 80 ? '#39A900' : ($s['pct'] >= 60 ? '#f59e0b' : '#ef4444') }};"></div>
                                </div>
                                <span class="text-xs font-semibold w-10 text-right
                                    {{ $s['pct'] >= 80 ? 'text-green-700' : ($s['pct'] >= 60 ? 'text-yellow-700' : 'text-red-600') }}">
                                    {{ $s['pct'] }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">Sin registros de asistencia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Tab Notas ── --}}
    <div x-show="tab === 'notas'" class="bg-white rounded-b-xl rounded-tr-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between" style="background:#00304D;">
            <p class="text-sm font-semibold text-white">Consolidado de Notas por Aprendiz</p>
            <span class="text-xs" style="color:rgba(255,255,255,0.55);">Aprobación ≥ 3.5 · {{ $notasSummary['total_competencias'] }} competencias</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Aprendiz</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-32">Documento</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-36">Promedio General</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-28">Estado</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-56">Detalle por Competencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($notasStats as $s)
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $s['aprendiz']->name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $s['aprendiz']->numero_documento }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($s['promedio_general'] !== null)
                                <span class="text-xl font-bold"
                                      style="color:{{ $s['aprobado'] ? '#39A900' : '#ef4444' }}">
                                    {{ number_format($s['promedio_general'], 1) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Sin notas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($s['aprobado'] === null)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Pendiente</span>
                            @elseif($s['aprobado'])
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-semibold">Aprobado</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">Reprobado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="space-y-1">
                                @foreach($s['competencias'] as $comp)
                                    @if($comp['promedio'] !== null)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 flex-1 truncate" title="{{ $comp['nombre'] }}">
                                            {{ Str::limit($comp['nombre'], 28) }}
                                        </span>
                                        <span class="text-xs font-semibold shrink-0 w-8 text-right"
                                              style="color:{{ $comp['aprobado'] ? '#39A900' : '#ef4444' }}">
                                            {{ number_format($comp['promedio'], 1) }}
                                        </span>
                                    </div>
                                    @endif
                                @endforeach
                                @if($s['competencias']->filter(fn($c) => $c['promedio'] !== null)->isEmpty())
                                    <span class="text-xs text-gray-400 italic">Sin notas registradas</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">Sin aprendices matriculados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Tab Documentos ── --}}
    <div x-show="tab === 'documentos'" class="space-y-4">
        {{-- Resumen estadístico --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100" style="background:#00304D;">
                <p class="text-sm font-semibold text-white">Estado de Carpetas Documentales</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-gray-100">
                @foreach([
                    ['label' => 'Sin subir',   'key' => 'sin_subir',   'color' => 'text-gray-500',  'bg' => 'bg-gray-50'],
                    ['label' => 'En revisión', 'key' => 'en_revision', 'color' => 'text-yellow-700', 'bg' => 'bg-yellow-50'],
                    ['label' => 'Aprobadas',   'key' => 'aprobado',    'color' => 'text-green-700',  'bg' => 'bg-green-50'],
                    ['label' => 'Rechazadas',  'key' => 'rechazado',   'color' => 'text-red-600',    'bg' => 'bg-red-50'],
                ] as $item)
                <div class="px-5 py-4 {{ $item['bg'] }} text-center">
                    <p class="text-2xl font-bold {{ $item['color'] }}">{{ $folderStats[$item['key']] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $item['label'] }}</p>
                    @if($folderStats['total'] > 0)
                    <p class="text-xs font-medium {{ $item['color'] }} mt-1">
                        {{ round($folderStats[$item['key']] / $folderStats['total'] * 100, 1) }}%
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Barra de progreso visual --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-3">Progreso documental</p>
            @if($folderStats['total'] > 0)
            <div class="flex h-6 rounded-full overflow-hidden text-xs font-medium">
                @if($folderStats['aprobado'] > 0)
                <div class="flex items-center justify-center bg-green-500 text-white transition-all"
                     style="width:{{ round($folderStats['aprobado']/$folderStats['total']*100) }}%">
                    {{ round($folderStats['aprobado']/$folderStats['total']*100) }}%
                </div>
                @endif
                @if($folderStats['en_revision'] > 0)
                <div class="flex items-center justify-center bg-yellow-400 text-yellow-900 transition-all"
                     style="width:{{ round($folderStats['en_revision']/$folderStats['total']*100) }}%">
                    {{ round($folderStats['en_revision']/$folderStats['total']*100) }}%
                </div>
                @endif
                @if($folderStats['rechazado'] > 0)
                <div class="flex items-center justify-center bg-red-400 text-white transition-all"
                     style="width:{{ round($folderStats['rechazado']/$folderStats['total']*100) }}%">
                    {{ round($folderStats['rechazado']/$folderStats['total']*100) }}%
                </div>
                @endif
                @if($folderStats['sin_subir'] > 0)
                <div class="flex items-center justify-center bg-gray-200 text-gray-600 flex-1">
                    {{ round($folderStats['sin_subir']/$folderStats['total']*100) }}%
                </div>
                @endif
            </div>
            <div class="flex flex-wrap gap-4 mt-3 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-500 inline-block"></span>Aprobado ({{ $folderStats['aprobado'] }})</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-yellow-400 inline-block"></span>En revisión ({{ $folderStats['en_revision'] }})</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-400 inline-block"></span>Rechazado ({{ $folderStats['rechazado'] }})</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-200 inline-block"></span>Sin subir ({{ $folderStats['sin_subir'] }})</span>
            </div>
            @else
            <p class="text-sm text-gray-400">No hay carpetas en esta ficha.</p>
            @endif
        </div>

        {{-- Listado de carpetas --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-700">Detalle por carpeta ({{ $ficha->folders->count() }})</p>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($ficha->folders->sortBy('position') as $folder)
                @php
                    $statusConfig = [
                        'sin_subir'   => ['label' => 'Sin subir',   'class' => 'bg-gray-100 text-gray-500'],
                        'en_revision' => ['label' => 'En revisión', 'class' => 'bg-yellow-100 text-yellow-700'],
                        'aprobado'    => ['label' => 'Aprobado',    'class' => 'bg-green-100 text-green-700'],
                        'rechazado'   => ['label' => 'Rechazado',   'class' => 'bg-red-100 text-red-700'],
                    ][$folder->status] ?? ['label' => $folder->status, 'class' => 'bg-gray-100 text-gray-500'];
                @endphp
                <div class="flex items-center justify-between px-5 py-2.5 hover:bg-gray-50">
                    <span class="text-sm text-gray-700">{{ $folder->position }}. {{ $folder->name }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusConfig['class'] }}">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">Sin carpetas.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
