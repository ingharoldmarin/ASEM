@extends('layouts.app')
@section('title', 'Instructores')
@section('header', 'Gestión de Instructores')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">{{ $instructors->total() }} instructor(es)</p>
    <a href="{{ route('admin.instructors.create') }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        + Registrar instructor
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Documento</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Teléfono</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($instructors as $instructor)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $instructor->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $instructor->email }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $instructor->numero_documento }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $instructor->telefono ?? '—' }}</td>
                <td class="px-5 py-3 text-right">
                    <form action="{{ route('admin.instructors.delete', $instructor) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar instructor?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">No hay instructores registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $instructors->links() }}</div>
@endsection

