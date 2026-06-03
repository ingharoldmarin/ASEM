@extends('layouts.app')
@section('title', 'Reporte de Asistencia')
@section('header', 'Reporte de Asistencia')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div class="flex items-start gap-3 mb-6 p-4 rounded-lg" style="background:#f0fce8;border:1px solid #39A900;">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                 style="color:#007832;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold" style="color:#00304D;">Reporte Excel de Asistencia</p>
                <p class="text-xs text-gray-600 mt-0.5">
                    Genera un Excel con la asistencia de todos los aprendices de la ficha seleccionada,
                    con columnas por sesión y totales de presencia, ausencia y porcentaje de asistencia.
                </p>
            </div>
        </div>

        <form action="{{ route('reportes.asistencia.generate') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ficha</label>
                <select name="ficha_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ficha_id') border-red-400 @enderror">
                    <option value="">Seleccionar ficha...</option>
                    @foreach($fichas as $ficha)
                    <option value="{{ $ficha->id }}" {{ old('ficha_id') == $ficha->id ? 'selected' : '' }}>
                        Ficha {{ $ficha->numero }} — {{ $ficha->program->name }} — {{ $ficha->institucion }}
                    </option>
                    @endforeach
                </select>
                @error('ficha_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                <select name="year" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($years as $year)
                    <option value="{{ $year }}" {{ old('year', date('Y')) == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                    @endforeach
                </select>
                @error('year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full btn-primary text-sm py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Generar y descargar Excel
            </button>
        </form>
    </div>

    <div class="mt-4 bg-white rounded-xl border border-gray-100 p-4">
        <p class="text-xs font-semibold text-gray-600 mb-2">El reporte incluye:</p>
        <ul class="text-xs text-gray-500 space-y-1">
            <li class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" style="background:#39A900;"></span>
                Información de la ficha: institución, programa, municipio, instructor, año
            </li>
            <li class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" style="background:#39A900;"></span>
                Una columna por cada sesión registrada en el año
            </li>
            <li class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" style="background:#39A900;"></span>
                P = Presente (verde) / A = Ausente (rojo) / E = Excusa (amarillo)
            </li>
            <li class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" style="background:#39A900;"></span>
                Totales de presencia, ausencia, excusas y porcentaje de asistencia por aprendiz
            </li>
            <li class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" style="background:#39A900;"></span>
                Fila de totales de presentes por sesión al final
            </li>
        </ul>
    </div>
</div>
@endsection
