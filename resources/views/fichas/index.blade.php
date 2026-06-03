@extends('layouts.app')
@section('title', 'Fichas')
@section('header', 'Fichas de Formación')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">{{ $fichas->total() }} ficha(s) registradas</p>
    <a href="{{ route('fichas.create') }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        + Nueva ficha
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Número</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Programa</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Institución</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Municipio</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($fichas as $ficha)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $ficha->numero }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $ficha->program->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $ficha->institucion }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $ficha->municipio }}</td>
                <td class="px-5 py-3">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('fichas.show', $ficha) }}"
                           class="text-xs link-primary">Ver</a>
                        <a href="{{ route('fichas.edit', $ficha) }}"
                           class="text-xs text-gray-600 hover:text-gray-800">Editar</a>
                        <form action="{{ route('fichas.destroy', $ficha) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta ficha?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">No hay fichas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $fichas->links() }}</div>
@endsection

