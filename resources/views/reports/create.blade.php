@extends('layouts.app')
@section('title', 'Subir Informe Mensual')
@section('header', 'Subir Informe Mensual')

@section('content')
<div class="max-w-lg">
    <div class="mb-4">
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500 mb-5">
            Sube el informe de ejecución general del mes. Puedes adjuntar hasta <strong>4 archivos</strong>.
        </p>

        <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                    <select name="month" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('month') border-red-400 @enderror">
                        <option value="">Seleccionar...</option>
                        @foreach(['Enero'=>1,'Febrero'=>2,'Marzo'=>3,'Abril'=>4,'Mayo'=>5,'Junio'=>6,'Julio'=>7,'Agosto'=>8,'Septiembre'=>9,'Octubre'=>10,'Noviembre'=>11,'Diciembre'=>12] as $nombre => $num)
                        <option value="{{ $num }}" {{ old('month') == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    @error('month')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <input type="number" name="year" value="{{ old('year', date('Y')) }}"
                           min="2020" max="2099" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Archivos del informe
                    <span class="text-gray-400 font-normal">(máx. 4)</span>
                </label>
                <input type="file" name="files[]" required multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                       class="w-full text-sm text-gray-500
                              file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0
                              file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 file:cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, PPT, imágenes. Máx. 20MB por archivo.</p>
                @error('files')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                @error('files.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary text-sm px-5 py-2 rounded-lg transition-colors">
                    Subir informe
                </button>
                <a href="{{ route('reports.index') }}" class="text-sm text-gray-600 hover:text-gray-800 px-3 py-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
