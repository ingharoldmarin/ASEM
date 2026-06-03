@extends('layouts.app')
@section('title', 'Programas por Instructor')
@section('header', 'Asignación de Programas a Instructores')

@section('content')
<p class="text-sm text-gray-500 mb-6">
    Asigna uno o más programas a cada instructor. El instructor solo podrá crear competencias
    y ver contenido de los programas que tenga asignados.
</p>

<div class="space-y-4">
    @forelse($instructors as $instructor)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
            <div>
                <p class="font-semibold text-gray-800">{{ $instructor->name }}</p>
                <p class="text-xs text-gray-400">{{ $instructor->email }}
                    @if($instructor->numero_documento)
                        · {{ $instructor->numero_documento }}
                    @endif
                </p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-primary-100 font-medium"
                  style="background:#d4edda;color:#007832;">
                {{ $instructor->programs->count() }} programa(s)
            </span>
        </div>

        <div class="px-5 py-4">
            {{-- Programas asignados --}}
            @if($instructor->programs->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($instructor->programs as $prog)
                <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border"
                      style="background:#f0fce8;border-color:#39A900;color:#007832;">
                    <span>{{ $prog->code }} — {{ $prog->name }}</span>
                    <form action="{{ route('instructors.programs.remove', [$instructor, $prog]) }}"
                          method="POST" class="inline"
                          onsubmit="return confirm('¿Remover {{ $prog->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="hover:text-red-500 font-bold leading-none">×</button>
                    </form>
                </span>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-400 mb-4 italic">Sin programas asignados.</p>
            @endif

            {{-- Asignar nuevo programa --}}
            @php
                $assignedIds = $instructor->programs->pluck('id')->toArray();
                $available   = $programs->whereNotIn('id', $assignedIds);
            @endphp
            @if($available->isNotEmpty())
            <form action="{{ route('instructors.programs.assign', $instructor) }}"
                  method="POST" class="flex gap-2">
                @csrf
                <select name="program_id" required
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Agregar programa...</option>
                    @foreach($available as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->code }} — {{ $prog->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="btn-primary text-xs px-4 py-1.5 rounded-lg transition-colors">
                    Asignar
                </button>
            </form>
            @else
            <p class="text-xs text-gray-400 italic">Ya tiene todos los programas asignados.</p>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-400 text-sm">No hay instructores registrados.</p>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $instructors->links() }}</div>
@endsection
