@extends('layouts.app')
@section('title', 'Registrar Asistencia')
@section('header', 'Registrar Asistencia — Ficha ' . $ficha->numero)

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('attendance.index', $ficha) }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('attendance.store', $ficha) }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tema / Actividad</label>
                    <input type="text" name="topic" value="{{ old('topic') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Opcional">
                </div>
            </div>

            @if($aprendices->isEmpty())
                <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    No hay aprendices matriculados en esta ficha.
                </p>
            @else
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-700">Aprendices ({{ $aprendices->count() }})</p>
                        <div class="flex gap-2 text-xs">
                            <button type="button" onclick="setAll('presente')" class="text-green-600 hover:underline">Todos presentes</button>
                            <button type="button" onclick="setAll('ausente')" class="text-red-500 hover:underline">Todos ausentes</button>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-50 border border-gray-100 rounded-lg overflow-hidden">
                        @foreach($aprendices as $aprendiz)
                        <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $aprendiz->name }}</p>
                                <p class="text-xs text-gray-400">{{ $aprendiz->numero_documento }}</p>
                            </div>
                            <div class="flex gap-3">
                                @foreach(['presente' => 'Presente', 'ausente' => 'Ausente', 'excusa' => 'Excusa'] as $val => $label)
                                <label class="flex items-center gap-1 text-xs cursor-pointer">
                                    <input type="radio" name="attendance[{{ $aprendiz->id }}]"
                                           value="{{ $val }}" class="attendance-radio"
                                           {{ old("attendance.{$aprendiz->id}", 'ausente') === $val ? 'checked' : '' }}>
                                    <span class="{{ $val === 'presente' ? 'text-green-700' : ($val === 'ausente' ? 'text-red-600' : 'text-yellow-700') }}">
                                        {{ $label }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary text-sm px-5 py-2 rounded-lg transition-colors">
                        Guardar asistencia
                    </button>
                    <a href="{{ route('attendance.index', $ficha) }}" class="text-sm text-gray-600 hover:text-gray-800 px-3 py-2">Cancelar</a>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
function setAll(val) {
    document.querySelectorAll('.attendance-radio[value="' + val + '"]').forEach(r => r.checked = true);
}
</script>
@endsection

