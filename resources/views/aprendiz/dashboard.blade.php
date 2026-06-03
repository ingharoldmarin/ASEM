@extends('layouts.app')
@section('title', 'Mi Panel')
@section('header', 'Mi Panel')

@section('content')

{{-- Mis Fichas --}}
<div class="mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-3">Mis Fichas</h3>
    @if($fichas->isEmpty())
        <p class="text-sm text-gray-500">Aún no estás matriculado en ninguna ficha.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($fichas as $ficha)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="font-semibold text-gray-800">Ficha {{ $ficha->numero }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $ficha->program->name }}</p>
                <p class="text-xs text-gray-400">{{ $ficha->institucion }} — {{ $ficha->municipio }}</p>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Competencias del programa --}}
<div>
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-base font-semibold text-gray-800">Competencias de mi Programa</h3>
        <a href="{{ route('aprendiz.resultados') }}" class="text-sm link-primary hover:underline">
            Ver detalle completo →
        </a>
    </div>

    @if($competencias->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
            <p class="text-sm text-gray-400">No hay competencias definidas para tu programa aún.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($competencias as $comp)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                {{-- Encabezado competencia --}}
                <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-navy-900 text-sm">{{ $comp->nombre }}</p>
                        <p class="text-xs text-blue-500 mt-0.5">{{ $comp->program->name }}</p>
                    </div>
                    @php
                        $total    = $comp->resultados->count();
                        $aprobados = $comp->resultados->filter(fn($r) =>
                            $r->aprendices->first()?->pivot->status === 'aprobado'
                        )->count();
                    @endphp
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-white border border-blue-200 text-blue-700">
                        {{ $aprobados }}/{{ $total }} aprobados
                    </span>
                </div>

                {{-- Resultados de aprendizaje --}}
                @if($comp->resultados->isEmpty())
                    <p class="px-5 py-3 text-xs text-gray-400">Sin resultados definidos.</p>
                @else
                    <ul class="divide-y divide-gray-50">
                        @foreach($comp->resultados as $resultado)
                        @php
                            $pivot  = $resultado->aprendices->first()?->pivot;
                            $status = $pivot?->status ?? 'pendiente';
                            $badgeClass = match($status) {
                                'aprobado'    => 'bg-green-100 text-green-700',
                                'no_aprobado' => 'bg-red-100 text-red-700',
                                default       => 'bg-gray-100 text-gray-500',
                            };
                            $label = match($status) {
                                'aprobado'    => 'Aprobado',
                                'no_aprobado' => 'No aprobado',
                                default       => 'Pendiente',
                            };
                        @endphp
                        <li class="flex items-center justify-between px-5 py-3">
                            <div>
                                <p class="text-sm text-gray-800">{{ $resultado->nombre }}</p>
                                @if($resultado->descripcion)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $resultado->descripcion }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 ml-4 text-xs font-medium px-2.5 py-1 rounded-full {{ $badgeClass }}">
                                {{ $label }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
