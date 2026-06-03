@extends('layouts.app')
@section('title', 'Ficha ' . $ficha->numero)
@section('header', 'Ficha ' . $ficha->numero)

@section('content')
{{-- Info general --}}
<div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
        <p class="text-xs text-gray-400">Programa</p>
        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $ficha->program->name }}</p>
        <p class="text-xs text-gray-400 font-mono">{{ $ficha->program->code }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
        <p class="text-xs text-gray-400">Institución</p>
        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $ficha->institucion }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
        <p class="text-xs text-gray-400">Municipio</p>
        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $ficha->municipio }}</p>
    </div>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
        <p class="text-xs text-gray-400 mb-1">Módulos</p>
        <div class="flex flex-wrap gap-1">
            <a href="{{ route('attendance.index', $ficha) }}"
               class="text-xs bg-green-50 text-green-700 hover:bg-green-100 px-2 py-1 rounded transition-colors">Asistencia</a>
            <a href="{{ route('reports.index', $ficha) }}"
               class="text-xs bg-purple-50 text-purple-700 hover:bg-purple-100 px-2 py-1 rounded transition-colors">Informes</a>
            <a href="{{ route('fichas.download', $ficha) }}"
               class="text-xs text-white px-2 py-1 rounded transition-colors flex items-center gap-1"
               style="background-color:#39A900;"
               title="Descargar todas las carpetas en ZIP">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Descargar ZIP
            </a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Carpetas documentales --}}
    <div class="lg:col-span-2">
        <h3 class="font-semibold text-gray-800 mb-3">Módulo Documental — 33 carpetas</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($ficha->folders as $folder)
            @php
                $cardClass = match($folder->status) {
                    'en_revision' => 'border-yellow-200 bg-yellow-50',
                    'aprobado'    => 'border-green-200 bg-green-50',
                    'rechazado'   => 'border-red-200 bg-red-50',
                    default       => 'border-gray-200 bg-gray-50',
                };
                $badgeClass = match($folder->status) {
                    'en_revision' => 'bg-yellow-100 text-yellow-700',
                    'aprobado'    => 'bg-green-100 text-green-700',
                    'rechazado'   => 'bg-red-100 text-red-700',
                    default       => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <a href="{{ route('folders.show', [$ficha, $folder]) }}"
               class="block border rounded-lg p-3 hover:shadow-sm transition-shadow {{ $cardClass }}">
                <div class="flex items-start justify-between gap-1 mb-2">
                    <p class="text-xs font-medium text-gray-700 leading-tight">{{ $folder->name }}</p>
                    <span class="text-xs shrink-0 text-gray-400">#{{ $folder->position }}</span>
                </div>
                <div class="flex items-center gap-1 flex-wrap">
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $badgeClass }}">
                        {{ $folder->status_label }}
                    </span>
                    <span class="text-xs px-1.5 py-0.5 rounded
                        {{ $folder->responsible_role === 'coordinacion' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }}">
                        {{ $folder->responsible_role === 'coordinacion' ? 'Coord.' : 'Inst.' }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Panel lateral: instructores + aprendices --}}
    <div class="space-y-4">

        {{-- Instructores asignados --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">
                Instructores asignados
                <span class="ml-1 text-xs font-normal text-gray-400">({{ $ficha->instructors->count() }})</span>
            </h3>

            @if($ficha->instructors->isNotEmpty())
            <ul class="space-y-2 mb-4">
                @foreach($ficha->instructors as $instructor)
                <li class="flex items-center justify-between text-sm">
                    <div>
                        <p class="text-gray-800 font-medium">{{ $instructor->name }}</p>
                        <p class="text-xs text-gray-400">{{ $instructor->email }}</p>
                    </div>
                    @if(auth()->user()->canManage())
                    <form action="{{ route('fichas.remove-instructor', [$ficha, $instructor]) }}" method="POST"
                          onsubmit="return confirm('¿Remover a {{ $instructor->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition-colors">
                            Remover
                        </button>
                    </form>
                    @endif
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-xs text-gray-400 mb-3">Sin instructores asignados.</p>
            @endif

            @if(auth()->user()->canManage())
            @if($availableInstructors->isNotEmpty())
            <form action="{{ route('fichas.assign-instructor', $ficha) }}" method="POST">
                @csrf
                <label class="block text-xs text-gray-500 mb-1">Asignar instructor</label>
                <div class="flex gap-2">
                    <select name="instructor_id" required
                            class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar...</option>
                        @foreach($availableInstructors as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="btn-primary text-xs px-3 py-1.5 rounded-lg transition-colors">
                        Asignar
                    </button>
                </div>
            </form>
            @else
            <p class="text-xs text-gray-400 italic">No hay instructores disponibles para asignar.</p>
            @endif
            @endif
        </div>

        {{-- Aprendices matriculados --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">
                Aprendices
                <span class="ml-1 text-xs font-normal text-gray-400">({{ $ficha->aprendices->count() }})</span>
            </h3>

            @if($ficha->aprendices->isNotEmpty())
            <ul class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                @foreach($ficha->aprendices as $aprendiz)
                <li class="flex items-center justify-between text-sm">
                    <div>
                        <p class="text-gray-800 font-medium">{{ $aprendiz->name }}</p>
                        <p class="text-xs text-gray-400">{{ $aprendiz->numero_documento }}</p>
                    </div>
                    @if(auth()->user()->isInstructor() || auth()->user()->canManage())
                    <form action="{{ route('instructor.fichas.remove-aprendiz', [$ficha, $aprendiz]) }}" method="POST"
                          onsubmit="return confirm('¿Remover a {{ $aprendiz->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition-colors">
                            Remover
                        </button>
                    </form>
                    @endif
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-xs text-gray-400 mb-3">Sin aprendices matriculados.</p>
            @endif

            @if(auth()->user()->isInstructor() || auth()->user()->canManage())
            @if($availableAprendices->isNotEmpty())
            <form action="{{ route('instructor.fichas.enroll', $ficha) }}" method="POST">
                @csrf
                <label class="block text-xs text-gray-500 mb-1">Matricular individualmente</label>
                <div class="flex gap-2">
                    <select name="aprendiz_id" required
                            class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar...</option>
                        @foreach($availableAprendices as $apr)
                        <option value="{{ $apr->id }}">{{ $apr->name }} — {{ $apr->numero_documento }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg transition-colors">
                        Matricular
                    </button>
                </div>
            </form>
            @endif

            {{-- Matrícula masiva por Excel --}}
            <div class="mt-4 pt-3 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-600 mb-2">Matrícula masiva por Excel</p>

                {{-- Descargar plantilla --}}
                <a href="{{ route('matricula.template') }}"
                   class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg text-white mb-3 transition-colors"
                   style="background-color:#007832;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar plantilla Excel
                </a>

                {{-- Subir archivo --}}
                <form action="{{ route('matricula.import', $ficha) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-2 items-center">
                        <input type="file" name="archivo" required accept=".xlsx,.xls"
                               class="flex-1 text-xs text-gray-500
                                      file:mr-2 file:py-1 file:px-2 file:rounded file:border-0
                                      file:text-xs file:font-medium file:text-white
                                      hover:file:opacity-90"
                               style="--file-bg:#39A900;"
                               onchange="this.style.setProperty('--file-bg','#39A900')">
                        <button type="submit"
                                class="shrink-0 btn-primary text-xs px-3 py-1.5 rounded-lg transition-colors">
                            Importar
                        </button>
                    </div>
                    @error('archivo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </form>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

