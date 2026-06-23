@extends('layouts.app')
@section('title', 'Asistencia')
@section('header', 'Control de Asistencia — Ficha ' . $ficha->numero)

@section('content')
<div class="flex justify-between items-center mb-6">
    <a href="{{ route('fichas.show', $ficha) }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
    @if(auth()->user()->isInstructor())
    <a href="{{ route('attendance.create', $ficha) }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        + Registrar asistencia
    </a>
    @endif
</div>

<div class="space-y-4">
    @forelse($sessions as $session)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
            <div>
                <p class="font-semibold text-gray-800">{{ $session->date->format('d/m/Y') }}</p>
                @if($session->topic)
                <p class="text-xs text-gray-500 mt-0.5">{{ $session->topic }}</p>
                @endif
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="text-green-600 font-medium">{{ $session->records->where('status', 'presente')->count() }} presentes</span>
                <span class="text-red-500">{{ $session->records->where('status', 'ausente')->count() }} ausentes</span>
                <a href="{{ route('attendance.show', [$ficha, $session]) }}" class="link-primary hover:underline ml-2">Ver detalle</a>
                @if(auth()->user()->isInstructor() || auth()->user()->canManage())
                <a href="{{ route('attendance.edit', [$ficha, $session]) }}" class="text-blue-500 hover:underline ml-1">Editar</a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-400 text-sm">No hay registros de asistencia.</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $sessions->links() }}</div>
@endsection

