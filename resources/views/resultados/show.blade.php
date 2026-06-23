@extends('layouts.app')
@section('title', $resultado->nombre)
@section('header', 'Resultado: ' . $resultado->nombre)

@section('content')
<div class="mb-4 flex items-center gap-2 text-sm text-gray-500 flex-wrap">
    <a href="{{ route('competencias.show', $resultado->competencia) }}" class="hover:text-gray-700">
        ← {{ $resultado->competencia->nombre }}
    </a>
    <span class="text-gray-300">|</span>
    <span class="text-xs text-gray-400">{{ $resultado->competencia->program->name }}</span>
</div>

@if($resultado->descripcion)
<p class="text-sm text-gray-600 mb-5 bg-white rounded-xl border border-gray-100 px-5 py-3">
    {{ $resultado->descripcion }}
</p>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Col izquierda: Guías + Nueva actividad ── --}}
    <div class="space-y-5">

        {{-- Guías de aprendizaje --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                </svg>
                Guías de Aprendizaje (PDF)
            </h3>

            @if($resultado->guias->isEmpty())
                <p class="text-xs text-gray-400 mb-3">Sin guías subidas.</p>
            @else
                <ul class="space-y-2 mb-3">
                    @foreach($resultado->guias as $guia)
                    <li class="flex items-center justify-between gap-2">
                        <a href="{{ asset('storage/' . $guia->file_path) }}" target="_blank"
                           class="text-xs link-primary hover:underline flex items-center gap-1.5 min-w-0">
                            <svg class="w-3.5 h-3.5 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                            </svg>
                            <span class="truncate">{{ $guia->original_name }}</span>
                        </a>
                        @if(!auth()->user()->isAprendiz())
                        <form action="{{ route('resultados.guias.delete', $guia) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar guía?')" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">✕</button>
                        </form>
                        @endif
                    </li>
                    @endforeach
                </ul>
            @endif

            @if(!auth()->user()->isAprendiz())
            <form action="{{ route('resultados.guias.upload', $resultado) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                <input type="file" name="guia" required accept=".pdf"
                       class="w-full text-xs text-gray-500 mb-2
                              file:mr-2 file:py-1 file:px-2 file:rounded file:border-0
                              file:text-xs file:font-medium file:bg-red-50 file:text-red-700
                              hover:file:bg-red-100 file:cursor-pointer">
                <p class="text-xs text-gray-400 mb-2">Solo PDF. Máx. 20MB.</p>
                <button type="submit" class="w-full btn-primary text-xs py-1.5 rounded-lg transition-colors">
                    Subir guía
                </button>
            </form>
            @endif
        </div>

        {{-- Nueva actividad --}}
        @if(!auth()->user()->isAprendiz())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Nueva Actividad</h3>
            <form action="{{ route('resultados.actividades.store', $resultado) }}" method="POST" class="space-y-2">
                @csrf
                <input type="text" name="nombre" placeholder="Nombre de la actividad..." required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                <textarea name="descripcion" rows="2" placeholder="Descripción (opcional)"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                <button type="submit" class="w-full btn-primary text-xs py-1.5 rounded-lg transition-colors">
                    Crear actividad
                </button>
            </form>
        </div>
        @endif

        {{-- Selector de ficha (para instructor/admin/coord) --}}
        @if(!auth()->user()->isAprendiz())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Seleccionar Ficha</h3>
            @if($fichas->isEmpty())
                <p class="text-xs text-gray-400">No hay fichas de este programa.</p>
            @else
                <form method="GET" action="{{ route('resultados.detalle', $resultado) }}">
                    <select name="ficha_id" onchange="this.form.submit()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar ficha...</option>
                        @foreach($fichas as $ficha)
                        <option value="{{ $ficha->id }}"
                            {{ $fichaSeleccionada?->id == $ficha->id ? 'selected' : '' }}>
                            Ficha {{ $ficha->numero }}
                            ({{ $ficha->aprendices->count() }} aprendices)
                        </option>
                        @endforeach
                    </select>
                </form>

                @if($fichaSeleccionada)
                <div class="mt-3 pt-3 border-t border-gray-50 text-xs text-gray-500">
                    <p><span class="font-medium">Institución:</span> {{ $fichaSeleccionada->institucion }}</p>
                    <p><span class="font-medium">Municipio:</span> {{ $fichaSeleccionada->municipio }}</p>
                    <p><span class="font-medium">Aprendices:</span> {{ $aprendices->count() }}</p>
                </div>
                @endif
            @endif
        </div>
        @endif
    </div>

    {{-- ── Col derecha: Actividades con notas ── --}}
    <div class="lg:col-span-2 space-y-5">

        @if(auth()->user()->isAprendiz())
            {{-- Vista aprendiz: sus propias notas --}}
            @forelse($resultado->actividades as $actividad)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-50" style="background:#f0fce8;">
                    <p class="font-semibold text-sm" style="color:#00304D;">{{ $actividad->nombre }}</p>
                    @if($actividad->descripcion)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $actividad->descripcion }}</p>
                    @endif
                </div>
                <div class="px-5 py-4">
                    @php $miNota = $actividad->notaDelAprendiz(auth()->id()); @endphp
                    @if($miNota)
                    <div class="flex items-center gap-5">
                        <div class="text-center">
                            <p class="text-4xl font-bold leading-none"
                               style="color:{{ $miNota->nota >= 3.5 ? '#39A900' : '#e74c3c' }}">
                                {{ number_format($miNota->nota, 1) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">de 5.0</p>
                        </div>
                        <div>
                            <span class="text-sm font-bold px-4 py-1.5 rounded-full
                                {{ $miNota->nota >= 3.5 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $miNota->aprobado_label }}
                            </span>
                            <p class="text-xs text-gray-400 mt-2">Mínimo para aprobar: <strong>3.5</strong></p>
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-gray-400">Aún no tienes nota en esta actividad.</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
                <p class="text-sm text-gray-400">No hay actividades en este resultado aún.</p>
            </div>
            @endforelse

        @else
            {{-- Vista instructor/admin/coord: tabla de notas por ficha --}}
            @if(!$fichaSeleccionada)
            <div class="bg-white rounded-xl border border-gray-100 p-8 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-400">Selecciona una ficha para ver y calificar los aprendices.</p>
            </div>

            @elseif($resultado->actividades->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
                <p class="text-sm text-gray-400">No hay actividades creadas. Usa el panel izquierdo para crear la primera.</p>
            </div>

            @elseif($aprendices->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
                <p class="text-sm text-gray-400">La Ficha {{ $fichaSeleccionada->numero }} no tiene aprendices matriculados.</p>
            </div>

            @else
                @foreach($resultado->actividades as $actividad)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50"
                         style="background:#f0fce8;">
                        <div>
                            <p class="font-semibold text-sm" style="color:#00304D;">{{ $actividad->nombre }}</p>
                            @if($actividad->descripcion)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $actividad->descripcion }}</p>
                            @endif
                        </div>
                        <form action="{{ route('resultados.actividades.destroy', $actividad) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar actividad y todas sus notas?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Eliminar</button>
                        </form>
                    </div>

                    <form action="{{ route('actividades.calificar-masivo', $actividad) }}?ficha_id={{ $fichaSeleccionada->id }}&resultado={{ $resultado->id }}"
                          method="POST">
                        @csrf
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Aprendiz</th>
                                    <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase w-32">Nota (0–5)</th>
                                    <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase w-32">Estado actividad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($aprendices as $aprendiz)
                                @php $nota = $actividad->notaDelAprendiz($aprendiz->id); @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5">
                                        <p class="text-gray-800 font-medium text-sm">{{ $aprendiz->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $aprendiz->numero_documento }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <input type="number"
                                               name="notas[{{ $aprendiz->id }}]"
                                               value="{{ $nota ? number_format($nota->nota, 1) : '' }}"
                                               min="0" max="5" step="0.1"
                                               placeholder="—"
                                               class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center
                                                      focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               style="{{ $nota ? ($nota->nota >= 3.5 ? 'border-color:#86efac;background:#f0fce8;' : 'border-color:#fca5a5;background:#fef2f2;') : '' }}"
                                               onchange="actualizarEstado(this, '{{ $actividad->id }}', '{{ $aprendiz->id }}')">
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span id="estado-{{ $actividad->id }}-{{ $aprendiz->id }}"
                                              class="text-xs font-semibold px-2.5 py-1 rounded-full
                                                  {{ $nota ? ($nota->nota >= 3.5 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') : 'bg-gray-100 text-gray-500' }}">
                                            @if($nota)
                                                {{ $nota->aprobado_label }}
                                                — {{ number_format($nota->nota, 1) }}
                                            @else
                                                Sin nota
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-4 py-3 border-t border-gray-50 flex items-center justify-between">
                            <p class="text-xs text-gray-400">Mínimo para aprobar: <strong>3.5</strong></p>
                            <button type="submit" class="btn-primary text-xs px-5 py-2 rounded-lg transition-colors">
                                Guardar notas
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach
            {{-- Resumen de promedios por aprendiz --}}
            @if($fichaSeleccionada && $resultado->actividades->isNotEmpty() && $aprendices->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100" style="background:#00304D;">
                    <p class="font-semibold text-sm text-white">Resumen del Resultado — Promedio de actividades</p>
                    <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6);">
                        Calculado automáticamente · Aprobación ≥ 3.5
                    </p>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase">Aprendiz</th>
                            <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Promedio</th>
                            <th class="text-center px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Resultado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($aprendices as $aprendiz)
                        @php
                            $actividadIds = $resultado->actividades->pluck('id');
                            $notasAprendiz = \App\Models\ActividadNota::whereIn('actividad_id', $actividadIds)
                                ->where('aprendiz_id', $aprendiz->id)
                                ->pluck('nota');
                            $promedio = $notasAprendiz->isNotEmpty() ? round($notasAprendiz->avg(), 1) : null;
                            $pivotStatus = $resultado->aprendices()->where('users.id', $aprendiz->id)->first()?->pivot->status ?? 'pendiente';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800">{{ $aprendiz->name }}</p>
                                <p class="text-xs text-gray-400">{{ $aprendiz->numero_documento }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($promedio !== null)
                                <span class="text-lg font-bold"
                                      style="color:{{ $promedio >= 3.5 ? '#39A900' : '#e74c3c' }}">
                                    {{ number_format($promedio, 1) }}
                                </span>
                                <span class="text-xs text-gray-400"> / 5.0</span>
                                @else
                                <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs font-bold px-3 py-1 rounded-full
                                    {{ $pivotStatus === 'aprobado' ? 'bg-green-100 text-green-700' :
                                       ($pivotStatus === 'no_aprobado' ? 'bg-red-100 text-red-700' :
                                       'bg-gray-100 text-gray-500') }}">
                                    {{ $pivotStatus === 'aprobado' ? 'Aprobado' :
                                       ($pivotStatus === 'no_aprobado' ? 'Reprobado' : 'Pendiente') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @endif
        @endif
    </div>
</div>

<script>
function actualizarEstado(input, actividadId, aprendizId) {
    const val  = parseFloat(input.value);
    const span = document.getElementById('estado-' + actividadId + '-' + aprendizId);
    if (!span) return;

    if (isNaN(val) || input.value === '') {
        span.className = 'text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500';
        span.textContent = 'Sin nota';
        input.style.borderColor = '';
        input.style.backgroundColor = '';
        return;
    }

    const aprobado = val >= 3.5;
    span.className = 'text-xs font-semibold px-2.5 py-1 rounded-full ' +
        (aprobado ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700');
    span.textContent = (aprobado ? 'Aprobado' : 'Reprobado') + ' — ' + val.toFixed(1);
    input.style.borderColor = aprobado ? '#86efac' : '#fca5a5';
    input.style.backgroundColor = aprobado ? '#f0fce8' : '#fef2f2';
}
</script>
@endsection
