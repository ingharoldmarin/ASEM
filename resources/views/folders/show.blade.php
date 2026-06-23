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
                'sin_subir'       => 'bg-gray-100 text-gray-700',
                'en_revision'     => 'bg-yellow-100 text-yellow-800',
                'aprobado'        => 'bg-green-100 text-green-800',
                'rechazado'       => 'bg-red-100 text-red-800',
                'pendiente_subir' => 'bg-orange-100 text-orange-800',
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
                @if(auth()->user()->canManage() && in_array($folder->status, ['en_revision', 'rechazado', 'pendiente_subir']))
                <form action="{{ route('folders.approve', $folder) }}" method="POST">
                    @csrf
                    <button class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                        Aprobar
                    </button>
                </form>
                @endif
            </div>

            @if($folder->rejection_comment)
            @php
                $isPendiente = $folder->status === 'pendiente_subir';
                $obsBoxClass  = $isPendiente ? 'bg-orange-50 border-orange-200' : 'bg-red-50 border-red-200';
                $obsIconClass = $isPendiente ? 'text-orange-500' : 'text-red-500';
                $obsTitleClass = $isPendiente ? 'text-orange-700' : 'text-red-700';
                $obsAuthorClass = $isPendiente ? 'text-orange-500' : 'text-red-500';
                $obsTextClass = $isPendiente ? 'text-orange-800' : 'text-red-800';
            @endphp
            <div class="mt-3 {{ $obsBoxClass }} border rounded-lg p-3 flex items-start gap-2">
                <svg class="w-4 h-4 {{ $obsIconClass }} mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-8.18 14.14A1 1 0 003 19.5h18a1 1 0 00.89-1.5L13.71 3.86a1 1 0 00-1.42 0z"/>
                </svg>
                <div>
                    <p class="text-xs {{ $obsTitleClass }} font-medium">
                        {{ $isPendiente ? 'Observación — Pendiente por subir documentación' : 'Observación — Carpeta rechazada' }}
                        @if($folder->reviewer)
                            <span class="{{ $obsAuthorClass }} font-normal">(por {{ $folder->reviewer->name }})</span>
                        @endif
                    </p>
                    <p class="text-sm {{ $obsTextClass }} mt-1">{{ $folder->rejection_comment }}</p>
                </div>
            </div>
            @endif
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
                            <button type="button"
                                    onclick="previewDocument('{{ asset('storage/' . $doc->file_path) }}', '{{ strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION)) }}', @js($doc->original_name))"
                                    class="text-xs link-primary hover:underline">
                                Previsualizar
                            </button>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                               class="text-xs text-gray-500 hover:underline">Descargar</a>
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

        {{-- Observación: marcar qué falta --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-1 text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-8.18 14.14A1 1 0 003 19.5h18a1 1 0 00.89-1.5L13.71 3.86a1 1 0 00-1.42 0z"/>
                </svg>
                Observación
            </h3>
            <p class="text-xs text-gray-400 mb-3">Indica qué falta o qué está mal. La carpeta se marcará en rojo y el mensaje se mostrará en su tarjeta.</p>
            <form action="{{ route('folders.reject', $folder) }}" method="POST">
                @csrf
                <textarea name="rejection_comment" rows="3" required
                          placeholder="Ej: Falta el documento de consentimiento informado..."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">{{ $folder->rejection_comment }}</textarea>
                <button type="submit" class="mt-2 w-full bg-red-500 hover:bg-red-600 text-white text-sm py-2 rounded-lg transition-colors">
                    Guardar observación (marca en rojo)
                </button>
            </form>
            @if($folder->rejection_comment)
            <form action="{{ route('folders.clear-observation', $folder) }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-xs text-gray-500 hover:text-gray-700 py-1">
                    Quitar observación
                </button>
            </form>
            @endif
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

{{-- ── Modal de previsualización de documentos ── --}}
<div id="previewModal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-2 sm:p-4"
     onclick="if(event.target === this) closePreview()">
    <div class="bg-white rounded-xl shadow-xl w-full h-full sm:h-[90vh] sm:max-w-2xl md:max-w-4xl lg:max-w-5xl flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-4 sm:px-5 py-3 border-b border-gray-100 shrink-0">
            <p id="previewModalTitle" class="text-sm font-semibold text-gray-800 truncate pr-4"></p>
            <div class="flex items-center gap-4 shrink-0">
                <a id="previewDownloadLink" href="#" target="_blank" class="text-xs link-primary hover:underline">Descargar</a>
                <button type="button" onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="previewModalBody" class="flex-1 min-h-0 flex flex-col overflow-hidden bg-gray-50"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
    document.getElementById('previewModalBody').innerHTML = '';
}

function previewFallback(url, msg) {
    return '<div class="flex-1 min-h-0 flex items-center justify-center p-10 text-center">' +
        '<div>' +
        '<p class="text-sm text-gray-500 mb-3">' + msg + '</p>' +
        '<a href="' + url + '" target="_blank" class="btn-primary text-sm px-4 py-2 rounded-lg inline-block">Descargar archivo</a>' +
        '</div></div>';
}

function previewDocument(url, ext, name) {
    document.getElementById('previewDownloadLink').href = url;
    document.getElementById('previewModalTitle').textContent = name;
    document.getElementById('previewModal').classList.remove('hidden');

    const body = document.getElementById('previewModalBody');
    body.innerHTML = '<div class="flex-1 min-h-0 flex items-center justify-center p-10"><p class="text-sm text-gray-400">Cargando vista previa...</p></div>';

    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    if (ext === 'pdf') {
        body.innerHTML = '<iframe src="' + url + '" class="flex-1 min-h-0 w-full border-0"></iframe>';
    } else if (imageExts.includes(ext)) {
        body.innerHTML = '<div class="flex-1 min-h-0 overflow-auto flex items-center justify-center p-4">' +
            '<img src="' + url + '" class="max-w-full w-auto h-auto">' +
            '</div>';
    } else if (ext === 'docx') {
        fetch(url)
            .then(r => r.arrayBuffer())
            .then(buf => mammoth.convertToHtml({ arrayBuffer: buf }))
            .then(result => {
                body.innerHTML = '<div class="flex-1 min-h-0 overflow-auto"><div class="prose prose-sm max-w-none p-4 sm:p-6 bg-white w-full">' + result.value + '</div></div>';
            })
            .catch(() => {
                body.innerHTML = previewFallback(url, 'No se pudo generar la vista previa de este documento Word.');
            });
    } else if (ext === 'xlsx' || ext === 'xls') {
        fetch(url)
            .then(r => r.arrayBuffer())
            .then(buf => renderExcelPreview(XLSX.read(buf, { type: 'array' })))
            .catch(() => {
                body.innerHTML = previewFallback(url, 'No se pudo generar la vista previa de esta hoja de cálculo.');
            });
    } else {
        body.innerHTML = previewFallback(url, 'La vista previa no está disponible para archivos .' + ext + '. Descárgalo para verlo.');
    }
}

function renderExcelPreview(wb) {
    const body = document.getElementById('previewModalBody');

    const tabsHtml = wb.SheetNames.map((name, idx) =>
        '<button type="button" data-sheet="' + idx + '" ' +
        'class="excel-tab-btn shrink-0 text-xs px-3 py-1.5 rounded-t-lg border-b-2 whitespace-nowrap transition-colors ' +
        (idx === 0 ? 'border-green-600 text-green-700 font-semibold bg-white' : 'border-transparent text-gray-500 hover:text-gray-700') +
        '">' + name + '</button>'
    ).join('');

    body.innerHTML =
        '<div class="flex-1 min-h-0 flex flex-col">' +
            '<div class="flex gap-1 px-2 pt-2 border-b border-gray-200 bg-gray-100 overflow-x-auto shrink-0">' + tabsHtml + '</div>' +
            '<div id="excelSheetContainer" class="flex-1 min-h-0 overflow-auto bg-white"></div>' +
        '</div>';

    const container = document.getElementById('excelSheetContainer');

    function showSheet(idx) {
        const sheet = wb.Sheets[wb.SheetNames[idx]];
        container.innerHTML = '<div class="p-2">' + XLSX.utils.sheet_to_html(sheet, { id: 'sheet-' + idx }) + '</div>';
        container.querySelectorAll('table').forEach(t => {
            t.classList.add('text-xs', 'border-collapse');
            t.querySelectorAll('td, th').forEach(c => c.classList.add('border', 'border-gray-200', 'px-2', 'py-1', 'whitespace-nowrap'));
        });

        body.querySelectorAll('.excel-tab-btn').forEach(btn => {
            const active = btn.dataset.sheet === String(idx);
            btn.classList.toggle('border-green-600', active);
            btn.classList.toggle('text-green-700', active);
            btn.classList.toggle('font-semibold', active);
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('border-transparent', !active);
            btn.classList.toggle('text-gray-500', !active);
        });
    }

    body.querySelectorAll('.excel-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => showSheet(parseInt(btn.dataset.sheet, 10)));
    });

    showSheet(0);
}
</script>
@endsection

