@extends('layouts.app')
@section('title', 'Usuarios')
@section('header', 'Todos los Usuarios')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Documento</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Rol</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Registrado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $user->numero_documento ?? '—' }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full capitalize
                        {{ $user->role === 'instructor' ? 'bg-blue-100 text-blue-700' :
                           ($user->role === 'coordinacion' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700') }}">
                        {{ $user->role }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-400 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">No hay usuarios.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
