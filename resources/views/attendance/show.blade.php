@extends('layouts.app')
@section('title', 'Asistencia ' . $session->date->format('d/m/Y'))
@section('header', 'Asistencia del ' . $session->date->format('d/m/Y'))

@section('content')
<div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('attendance.index', $ficha) }}" class="hover:link-primary">← Volver</a>
    @if($session->topic)
    <span>|</span>
    <span>{{ $session->topic }}</span>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex gap-6 text-sm">
        <span class="text-green-700 font-medium">Presentes: {{ $session->records->where('status','presente')->count() }}</span>
        <span class="text-red-600">Ausentes: {{ $session->records->where('status','ausente')->count() }}</span>
        <span class="text-yellow-700">Excusas: {{ $session->records->where('status','excusa')->count() }}</span>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Aprendiz</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Documento</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($session->records as $record)
            <tr>
                <td class="px-5 py-3 text-gray-800">{{ $record->aprendiz->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $record->aprendiz->numero_documento }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $record->status === 'presente' ? 'bg-green-100 text-green-700' :
                           ($record->status === 'excusa' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ ucfirst($record->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
