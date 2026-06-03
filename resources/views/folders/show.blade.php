@extends('layouts.app')
@section('title', $folder->name)
@section('header', '#' . $folder->position . ' — ' . $folder->name)

@section('content')
<div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('fichas.show', $ficha) }}" class="hover:link-primary">Ficha {{ $ficha->numero }}</a>
    <span>/</span>
    <span class="text-gray-800">{{ $folder->name }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-4">

        {{-- Estado y acciones de revisión --}}
        @php
            $statusColors = [
                'sin_subir'   => 'bg-gray-100 text-gray-700',
                'en_revision' => 'bg-yellow-100 text-yellow-800',
                'aprobado'    => 'bg-green-100 text-green-800',
                'rechazado'   => 'bg-red-100 text-red-800',
            ];
        @endphp
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Estado actual</p>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$folder->status] }}">
                            {{ $folder->status_label }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Responsable</p>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            {{ $folder->responsible_role === 'coordinacion' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $folder->responsible_role === 'coordinacion' ? 'Coordinación' : 'Instructor' }}
                        </span>
                    </div>
                </div>
                @if(auth()->user()->canManage() && $folder->status === 'en_revision')
                <div class="flex gap-2">
                    <form action="{{ route('folders.approve', $folder) }}" method="POST">
                        @csrf
                        <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                            Aprobar
                        </button>
                    </form>
                    <button onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                            class="bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                        Rechazar
                    </button>
                </div>
                @endif
            </div>

            @if($folder->rejection_comment)
            <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="text-xs text-red-700 font-medium">Motivo de rechazo:</p>
                <p class="text-sm text-red-800 mt-1">{{ $folder->rejection_comment }}</p>
            </div>
            @endif

            <form id="reject-form" action="{{ route('folders.reject', $folder) }}" method="POST"
                  class="mt-3 hidden">
                @csrf
                <textarea name="rejection_comment" rows="2" required placeholder="Motivo del rechazo..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
                <button type="submit" class="mt-2 bg-red-500 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                    Confirmar rechazo
                </button>
            </form>
        </div>

        {{-- Documentos --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Documentos ({{ $folder->documents->count() }})</h3>

            @if($folder->documents->isEmpty())
                <p class="text-sm text-gray-400 py-4 text-center">No hay documentos en esta carpeta.</p>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($folder->documents as $doc)
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $doc->original_name }}</p>
                                <p class="text-xs text-gray-400">{{ $doc->uploader->name }} — {{ $doc->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                               class="text-xs link-primary hover:underline">Descargar</a>
                            <form action="{{ route('documents.delete', $doc) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar este documento?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600">Eliminar</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        {{-- Subir documentos (múltiple) --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-1 text-sm">Subir documentos</h3>
            <p class="text-xs text-gray-400 mb-3">Puedes seleccionar varios archivos a la vez.</p>
            <form action="{{ route('folders.upload', $folder) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="files[]" required multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif"
                       class="w-full text-sm text-gray-500
                              file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                              file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700
                              hover:file:bg-gray-200 file:cursor-pointer">
                @error('files')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                @error('files.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-2">PDF, Word, Excel, PPT, imágenes. Máx. 20MB por archivo.</p>
                <button type="submit"
                        class="mt-3 w-full btn-primary text-sm py-2 rounded-lg transition-colors">
                    Subir archivos
                </button>
            </form>
        </div>

        {{-- Renombrar carpeta --}}
        @if(auth()->user()->canManage())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Renombrar carpeta</h3>
            <form action="{{ route('folders.rename', $folder) }}" method="POST">
                @csrf @method('PATCH')
                <input type="text" name="name" value="{{ $folder->name }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3">
                <button type="submit"
                        class="w-full bg-gray-600 hover:bg-gray-700 text-white text-sm py-2 rounded-lg transition-colors">
                    Renombrar
                </button>
            </form>
        </div>
        @endif
    </div>

</div>
@endsection

