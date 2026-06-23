@extends('layouts.app')
@section('title', $competencia->nombre)
@section('header', 'Competencia: ' . $competencia->nombre)

@section('content')
<div class="mb-4 flex items-center gap-3">
    <a href="{{ route('competencias.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
    <span class="text-gray-300">|</span>
    <span class="text-xs text-gray-400">{{ $competencia->program->name ?? '' }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ── Panel izquierdo: Resultados de aprendizaje ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Resultados de Aprendizaje</h3>

        @if($competencia->resultados->isEmpty())
            <p class="text-sm text-gray-400 mb-4">No hay resultados definidos.</p>
        @else
            <ul class="space-y-2 mb-5">
                @foreach($competencia->resultados as $resultado)
                <li class="border border-gray-100 rounded-lg p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $resultado->nombre }}</p>
                            @if($resultado->descripcion)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $resultado->descripcion }}</p>
                            @endif
                        </div>
                        <form action="{{ route('resultados.destroy', $resultado) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este resultado?')" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Eliminar</button>
                        </form>
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <a href="{{ route('resultados.detalle', $resultado) }}"
                           class="inline-flex items-center gap-1 text-xs btn-primary px-3 py-1 rounded-lg transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Guías, actividades y notas
                        </a>
                    </div>
                </li>
                @endforeach
            </ul>
        @endif

        {{-- Agregar resultado --}}
        <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-600 mb-2">Agregar resultado</p>
            <form action="{{ route('resultados.store', $competencia) }}" method="POST" class="space-y-2">
                @csrf
                <input type="text" name="nombre" placeholder="Nombre del resultado..." required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <textarea name="descripcion" rows="2" placeholder="Descripción (opcional)"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                <button type="submit"
                        class="btn-primary text-xs px-4 py-2 rounded-lg transition-colors">
                    Agregar resultado
                </button>
            </form>
        </div>
    </div>

    {{-- ── Panel derecho: Evaluación por ficha ── --}}
    <div class="space-y-4">

        {{-- Selector de ficha --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Evaluar Aprendices por Ficha</h3>

            @if($fichas->isEmpty())
                <p class="text-sm text-gray-400">No hay fichas asociadas al programa de esta competencia.</p>
            @else
                <form method="GET" action="{{ route('competencias.show', $competencia) }}" class="flex gap-2">
                    <select name="ficha_id"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="this.form.submit()">
                        <option value="">Seleccionar ficha...</option>
                        @foreach($fichas as $ficha)
                            <option value="{{ $ficha->id }}"
                                {{ $fichaSeleccionada?->id == $ficha->id ? 'selected' : '' }}>
                                Ficha {{ $ficha->numero }} — {{ $ficha->aprendices->count() }} aprendice(s)
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        {{-- Tabla de evaluación --}}
        @if($fichaSeleccionada)
            @if($competencia->resultados->isEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
                    Define primero los resultados de aprendizaje para poder evaluar.
                </div>
            @elseif($aprendicesConEstados->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <p class="text-sm text-gray-400">No hay aprendices matriculados en la Ficha {{ $fichaSeleccionada->numero }}.</p>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between"
                         style="background:#00304D;">
                        <p class="text-sm font-semibold text-white">
                            Ficha {{ $fichaSeleccionada->numero }}
                            <span class="font-normal ml-1" style="color:rgba(255,255,255,0.6);">
                                ({{ $aprendicesConEstados->count() }} aprendices)
                            </span>
                        </p>
                        <span class="text-xs" style="color:rgba(255,255,255,0.5);">
                            Calculado automáticamente · Aprobación ≥ 3.5
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50 min-w-44">
                                        Aprendiz
                                    </th>
                                    @foreach($competencia->resultados as $resultado)
                                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase text-center min-w-40">
                                        <a href="{{ route('resultados.detalle', $resultado) }}?ficha_id={{ $fichaSeleccionada->id }}"
                                           class="block truncate max-w-36 link-primary hover:underline"
                                           title="{{ $resultado->nombre }}">
                                            {{ Str::limit($resultado->nombre, 30) }}
                                        </a>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($aprendicesConEstados as $aprendiz)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 sticky left-0 bg-white">
                                        <p class="font-medium text-gray-800 text-sm">{{ $aprendiz->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $aprendiz->numero_documento }}</p>
                                    </td>
                                    @foreach($competencia->resultados as $resultado)
                                    @php
                                        $data     = $aprendiz->estados_resultado[$resultado->id] ?? ['status' => 'pendiente', 'promedio' => null];
                                        $status   = $data['status'];
                                        $promedio = $data['promedio'];
                                    @endphp
                                    <td class="px-3 py-3 text-center">
                                        @if($promedio !== null)
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-base font-bold"
                                                  style="color:{{ $status === 'aprobado' ? '#39A900' : '#e74c3c' }}">
                                                {{ number_format($promedio, 1) }}
                                            </span>
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                                {{ $status === 'aprobado' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $status === 'aprobado' ? 'Aprobado' : 'Reprobado' }}
                                            </span>
                                        </div>
                                        @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">
                                            Sin notas
                                        </span>
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-2 border-t border-gray-50">
                        <p class="text-xs text-gray-400 italic">
                            El estado se actualiza automáticamente al registrar notas en las actividades de cada resultado.
                            Para ver o ingresar notas, haz clic en el nombre del resultado.
                        </p>
                    </div>
                </div>
            @endif
        @endif

    </div>
</div>

@endsection

