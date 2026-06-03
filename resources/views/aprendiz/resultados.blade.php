@extends('layouts.app')
@section('title', 'Mis Resultados')
@section('header', 'Mis Resultados de Aprendizaje')

@section('content')
@if($competencias->isEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-400">No hay competencias definidas para tu programa aún.</p>
    </div>
@else
    <div class="space-y-6">
        @foreach($competencias as $comp)
        @php
            $total     = $comp->resultados->count();
            $aprobados = $comp->resultados->filter(fn($r) =>
                $r->aprendices->first()?->pivot->status === 'aprobado'
            )->count();
            $noAprobados = $comp->resultados->filter(fn($r) =>
                $r->aprendices->first()?->pivot->status === 'no_aprobado'
            )->count();
        @endphp
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-blue-50 border-b border-blue-100">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-navy-900">{{ $comp->nombre }}</p>
                        @if($comp->descripcion)
                            <p class="text-xs text-blue-600 mt-0.5">{{ $comp->descripcion }}</p>
                        @endif
                        <p class="text-xs text-blue-400 mt-1">{{ $comp->program->name }}</p>
                    </div>
                    <div class="flex gap-2 shrink-0 ml-4">
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">
                            {{ $aprobados }} aprobado(s)
                        </span>
                        @if($noAprobados > 0)
                        <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 font-medium">
                            {{ $noAprobados }} no aprobado(s)
                        </span>
                        @endif
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500 font-medium">
                            {{ $total - $aprobados - $noAprobados }} pendiente(s)
                        </span>
                    </div>
                </div>
            </div>

            @if($comp->resultados->isEmpty())
                <p class="px-5 py-4 text-sm text-gray-400">Sin resultados definidos.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase">Resultado de Aprendizaje</th>
                            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase w-32">Estado</th>
                            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase w-32">Fecha evaluación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
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
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <p class="text-gray-800 font-medium">{{ $resultado->nombre }}</p>
                                @if($resultado->descripcion)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $resultado->descripcion }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-400">
                                {{ $pivot?->evaluated_at ? \Carbon\Carbon::parse($pivot->evaluated_at)->format('d/m/Y') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @endforeach
    </div>
@endif
@endsection
