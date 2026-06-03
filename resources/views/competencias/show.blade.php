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
                <li class="flex items-start justify-between gap-2 border border-gray-100 rounded-lg p-3">
                    <div>
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
                    <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700">
                            Ficha {{ $fichaSeleccionada->numero }}
                            <span class="font-normal text-gray-400 ml-1">({{ $aprendicesConEstados->count() }} aprendices)</span>
                        </p>
                    </div>

                    <form action="{{ route('competencias.evaluate-ficha', $competencia) }}" method="POST">
                        @csrf
                        <input type="hidden" name="ficha_id" value="{{ $fichaSeleccionada->id }}">

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-100">
                                    <tr>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50 min-w-40">
                                            Aprendiz
                                        </th>
                                        @foreach($competencia->resultados as $resultado)
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase text-center min-w-36">
                                            <span class="block truncate max-w-32" title="{{ $resultado->nombre }}">
                                                {{ Str::limit($resultado->nombre, 28) }}
                                            </span>
                                        </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($aprendicesConEstados as $aprendiz)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 sticky left-0 bg-white hover:bg-gray-50">
                                            <p class="font-medium text-gray-800 text-sm">{{ $aprendiz->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $aprendiz->numero_documento }}</p>
                                        </td>
                                        @foreach($competencia->resultados as $resultado)
                                        @php $estadoActual = $aprendiz->estados_resultado[$resultado->id] ?? 'pendiente'; @endphp
                                        <td class="px-3 py-3 text-center">
                                            <select name="evaluaciones[{{ $resultado->id }}][{{ $aprendiz->id }}]"
                                                    class="w-full border rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500
                                                        {{ $estadoActual === 'aprobado' ? 'border-green-300 bg-green-50 text-green-800' :
                                                           ($estadoActual === 'no_aprobado' ? 'border-red-300 bg-red-50 text-red-800' :
                                                           'border-gray-300 bg-white text-gray-600') }}
                                                    " onchange="colorSelect(this)">
                                                <option value="pendiente"    {{ $estadoActual === 'pendiente'    ? 'selected' : '' }}>Pendiente</option>
                                                <option value="aprobado"     {{ $estadoActual === 'aprobado'     ? 'selected' : '' }}>Aprobado</option>
                                                <option value="no_aprobado"  {{ $estadoActual === 'no_aprobado'  ? 'selected' : '' }}>No aprobado</option>
                                            </select>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                            <button type="submit"
                                    class="btn-primary text-sm px-6 py-2 rounded-lg transition-colors font-medium">
                                Guardar evaluaciones
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endif

    </div>
</div>

<script>
function colorSelect(el) {
    el.className = el.className.replace(/border-(green|red|gray)-300|bg-(green|red|white|gray)-\w+|text-(green|red|gray)-\w+/g, '');
    const v = el.value;
    if (v === 'aprobado') {
        el.classList.add('border-green-300', 'bg-green-50', 'text-green-800');
    } else if (v === 'no_aprobado') {
        el.classList.add('border-red-300', 'bg-red-50', 'text-red-800');
    } else {
        el.classList.add('border-gray-300', 'bg-white', 'text-gray-600');
    }
}
</script>
@endsection

