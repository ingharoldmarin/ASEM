@extends('layouts.app')
@section('title', 'Informes Mensuales')
@section('header', 'Informes Mensuales')

@section('content')
<div class="flex justify-between items-center mb-6">
    <p class="text-sm text-gray-500">Ejecución general por mes</p>
    @if(auth()->user()->isInstructor())
    <a href="{{ route('reports.create') }}"
       class="btn-primary text-sm px-4 py-2 rounded-lg transition-colors">
        + Subir informe
    </a>
    @endif
</div>

<div class="space-y-4">
    @forelse($reports as $report)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-start justify-between px-5 py-4">
            <div>
                <p class="font-semibold text-gray-800 text-base">
                    {{ $report->month_name }} {{ $report->year }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    Instructor: <span class="font-medium">{{ $report->instructor->name }}</span>
                </p>
                @if($report->comment)
                <p class="text-xs text-gray-400 mt-1 italic">{{ $report->comment }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $report->status === 'aprobado'  ? 'bg-green-100 text-green-700'  :
                       ($report->status === 'rechazado' ? 'bg-red-100 text-red-700'    :
                       ($report->status === 'revisado'  ? 'bg-yellow-100 text-yellow-700' :
                       'bg-gray-100 text-gray-600')) }}">
                    {{ ucfirst($report->status) }}
                </span>
                @if(auth()->user()->canManage())
                <form action="{{ route('reports.destroy', $report) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este informe?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-400 hover:text-red-600">Eliminar</button>
                </form>
                @endif
            </div>
        </div>

        {{-- Archivos --}}
        @if($report->files->isNotEmpty())
        <div class="px-5 pb-3 border-t border-gray-50 pt-3">
            <p class="text-xs font-medium text-gray-500 mb-2">Archivos ({{ $report->files->count() }}/4)</p>
            <ul class="space-y-1.5">
                @foreach($report->files as $file)
                <li class="flex items-center justify-between text-xs">
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank"
                       class="link-primary hover:underline flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ $file->original_name }}
                    </a>
                    <form action="{{ route('reports.files.delete', $file) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar archivo?')">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-600 ml-3">Eliminar</button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Agregar archivos (instructor dueño, si quedan slots) --}}
        @if(auth()->user()->isInstructor() && $report->instructor_id === auth()->id() && $report->files->count() < 4)
        <div class="px-5 pb-4 pt-2 border-t border-gray-50">
            <form action="{{ route('reports.add-files', $report) }}" method="POST" enctype="multipart/form-data"
                  class="flex gap-2 items-center">
                @csrf
                <input type="file" name="files[]" multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                       class="flex-1 text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700">
                <button type="submit"
                        class="shrink-0 text-white text-xs px-3 py-1.5 rounded-lg transition-colors"
                        style="background-color:#007832;">
                    + Agregar archivo
                </button>
            </form>
        </div>
        @endif

        {{-- Cambiar estado (coordinacion / admin) --}}
        @if(auth()->user()->canManage())
        <div class="px-5 pb-4 pt-2 border-t border-gray-50">
            <form action="{{ route('reports.status', $report) }}" method="POST"
                  class="flex flex-wrap gap-2 items-center">
                @csrf @method('PATCH')
                <select name="status"
                        class="border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="revisado"  {{ $report->status === 'revisado'  ? 'selected' : '' }}>Revisado</option>
                    <option value="aprobado"  {{ $report->status === 'aprobado'  ? 'selected' : '' }}>Aprobado</option>
                    <option value="rechazado" {{ $report->status === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
                <input type="text" name="comment" placeholder="Comentario (opcional)"
                       value="{{ $report->comment }}"
                       class="flex-1 min-w-40 border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="shrink-0 btn-primary text-xs px-3 py-1.5 rounded-lg transition-colors">
                    Guardar
                </button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-400 text-sm">No hay informes registrados.</p>
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $reports->links() }}</div>
@endsection
