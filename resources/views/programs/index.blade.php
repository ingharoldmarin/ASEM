@extends('layouts.app')
@section('title', 'Programas')
@section('header', 'Programas de Formación')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">{{ $programs->total() }} programa(s) registrados</p>
    <a href="{{ route('programs.create') }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        + Nuevo programa
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Código</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nombre</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fichas</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($programs as $program)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-mono font-medium text-gray-800">{{ $program->code }}</td>
                <td class="px-5 py-3 text-gray-700">{{ $program->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $program->fichas_count }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('programs.edit', $program) }}"
                           class="text-xs link-primary">Editar</a>
                        <form action="{{ route('programs.destroy', $program) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este programa?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">No hay programas registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $programs->links() }}</div>
@endsection

