<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Consolidado Ficha {{ $ficha->numero }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }

    .header { background: #00304D; color: #fff; padding: 10px 14px; margin-bottom: 8px; }
    .header h1 { font-size: 16px; font-weight: bold; }
    .header p  { font-size: 9px; color: rgba(255,255,255,0.75); margin-top: 2px; }

    .section { margin-bottom: 14px; }
    .section-title {
        background: #39A900; color: #fff; font-weight: bold;
        font-size: 11px; padding: 5px 10px; border-radius: 3px 3px 0 0;
    }

    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    th { background: #f3f4f6; font-weight: bold; text-align: center;
         border: 1px solid #d1d5db; padding: 5px 6px; font-size: 8.5px; }
    td { border: 1px solid #e5e7eb; padding: 4px 6px; }
    tr:nth-child(even) td { background: #f9fafb; }

    .td-left { text-align: left; }
    .td-center { text-align: center; }

    .badge { display: inline-block; padding: 1px 6px; border-radius: 20px; font-size: 8px; font-weight: bold; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-yellow { background: #fef9c3; color: #713f12; }
    .badge-gray   { background: #f3f4f6; color: #6b7280; }

    .summary-row { display: flex; gap: 10px; margin-bottom: 8px; }
    .summary-card {
        flex: 1; background: #f9fafb; border: 1px solid #e5e7eb;
        border-radius: 4px; padding: 6px 10px; text-align: center;
    }
    .summary-card .num { font-size: 18px; font-weight: bold; color: #39A900; }
    .summary-card .lbl { font-size: 8px; color: #6b7280; margin-top: 1px; }

    .folder-grid { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; }
    .folder-item {
        font-size: 8px; padding: 2px 6px; border-radius: 3px;
        border: 1px solid #e5e7eb; background: #f9fafb;
    }
    .folder-item.aprobado    { background: #dcfce7; border-color: #86efac; color: #166534; }
    .folder-item.en_revision { background: #fef9c3; border-color: #fde047; color: #713f12; }
    .folder-item.rechazado   { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
    .folder-item.sin_subir   { background: #f3f4f6; border-color: #d1d5db; color: #6b7280; }

    .bar-container { background: #e5e7eb; border-radius: 3px; height: 6px; margin-top: 3px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 3px; }

    .page-break { page-break-before: always; }
    .text-right { text-align: right; }
    .font-bold  { font-weight: bold; }
</style>
</head>
<body>

{{-- ── Encabezado ── --}}
<div class="header">
    <h1>Consolidado — Ficha {{ $ficha->numero }}</h1>
    <p>
        Programa: {{ $ficha->program->name ?? '—' }} &nbsp;|&nbsp;
        Institución: {{ $ficha->institucion }}, {{ $ficha->municipio }} &nbsp;|&nbsp;
        Aprendices: {{ $aprendices->count() }} &nbsp;|&nbsp;
        Generado: {{ now()->format('d/m/Y H:i') }}
    </p>
</div>

{{-- ── Tarjetas resumen ── --}}
<div class="summary-row">
    <div class="summary-card">
        <div class="num" style="color:#39A900">{{ $attendanceSummary['pct_promedio'] }}%</div>
        <div class="lbl">Asistencia promedio<br>{{ $totalSessions }} sesiones</div>
    </div>
    <div class="summary-card">
        <div class="num" style="color:#00304D">{{ $notasSummary['aprendices_aprobados'] }}/{{ $aprendices->count() }}</div>
        <div class="lbl">Aprendices aprobados<br>{{ $notasSummary['pct_aprobacion'] }}% aprobación</div>
    </div>
    <div class="summary-card">
        <div class="num" style="color:#39A900">{{ $folderStats['aprobado'] }}/{{ $folderStats['total'] }}</div>
        <div class="lbl">Carpetas aprobadas<br>{{ $folderPct }}% del total</div>
    </div>
</div>

{{-- ── Sección Asistencia ── --}}
<div class="section">
    <div class="section-title">1. Asistencia por Aprendiz ({{ $totalSessions }} sesiones)</div>
    <table>
        <thead>
            <tr>
                <th class="td-left" style="width:32%">Aprendiz</th>
                <th style="width:18%">Documento</th>
                <th style="width:10%">Presentes</th>
                <th style="width:10%">Ausentes</th>
                <th style="width:10%">Excusas</th>
                <th style="width:10%">Total</th>
                <th style="width:10%">% Asist.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendanceStats as $s)
            <tr>
                <td class="td-left font-bold">{{ $s['aprendiz']->name }}</td>
                <td class="td-center">{{ $s['aprendiz']->numero_documento }}</td>
                <td class="td-center" style="color:#166534;font-weight:bold">{{ $s['presentes'] }}</td>
                <td class="td-center" style="color:#991b1b">{{ $s['ausentes'] }}</td>
                <td class="td-center" style="color:#713f12">{{ $s['excusas'] }}</td>
                <td class="td-center">{{ $s['total'] }}</td>
                <td class="td-center">
                    <span class="badge {{ $s['pct'] >= 80 ? 'badge-green' : ($s['pct'] >= 60 ? 'badge-yellow' : 'badge-red') }}">
                        {{ $s['pct'] }}%
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="td-center" style="color:#9ca3af;padding:10px">Sin registros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Sección Notas ── --}}
<div class="section">
    <div class="section-title">2. Consolidado de Notas (Aprobación ≥ 3.5)</div>
    <table>
        <thead>
            <tr>
                <th class="td-left" style="width:30%">Aprendiz</th>
                <th style="width:18%">Documento</th>
                <th style="width:14%">Promedio General</th>
                <th style="width:12%">Estado</th>
                <th class="td-left" style="width:26%">Competencias</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notasStats as $s)
            <tr>
                <td class="td-left font-bold">{{ $s['aprendiz']->name }}</td>
                <td class="td-center">{{ $s['aprendiz']->numero_documento }}</td>
                <td class="td-center" style="font-size:13px;font-weight:bold;color:{{ $s['aprobado'] ? '#166534' : ($s['aprobado'] === false ? '#991b1b' : '#6b7280') }}">
                    {{ $s['promedio_general'] !== null ? number_format($s['promedio_general'], 1) : '—' }}
                </td>
                <td class="td-center">
                    <span class="badge {{ $s['aprobado'] === true ? 'badge-green' : ($s['aprobado'] === false ? 'badge-red' : 'badge-gray') }}">
                        {{ $s['aprobado'] === true ? 'Aprobado' : ($s['aprobado'] === false ? 'Reprobado' : 'Pendiente') }}
                    </span>
                </td>
                <td class="td-left">
                    @foreach($s['competencias'] as $comp)
                        @if($comp['promedio'] !== null)
                        <div>{{ Str::limit($comp['nombre'], 30) }}: <strong style="color:{{ $comp['aprobado'] ? '#166534' : '#991b1b' }}">{{ number_format($comp['promedio'], 1) }}</strong></div>
                        @endif
                    @endforeach
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="td-center" style="color:#9ca3af;padding:10px">Sin aprendices.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Sección Documentos ── --}}
<div class="section">
    <div class="section-title">3. Estado de Carpetas Documentales</div>

    <table style="width:50%;margin-bottom:8px;">
        <thead>
            <tr>
                <th class="td-left">Estado</th>
                <th>Carpetas</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            @foreach([
                ['Sin subir',   'sin_subir',   'badge-gray'],
                ['En revisión', 'en_revision', 'badge-yellow'],
                ['Aprobado',    'aprobado',    'badge-green'],
                ['Rechazado',   'rechazado',   'badge-red'],
            ] as [$lbl, $key, $badge])
            <tr>
                <td class="td-left"><span class="badge {{ $badge }}">{{ $lbl }}</span></td>
                <td class="td-center font-bold">{{ $folderStats[$key] }}</td>
                <td class="td-center">{{ $folderStats['total'] > 0 ? round($folderStats[$key]/$folderStats['total']*100,1) : 0 }}%</td>
            </tr>
            @endforeach
            <tr>
                <td class="td-left font-bold">TOTAL</td>
                <td class="td-center font-bold">{{ $folderStats['total'] }}</td>
                <td class="td-center font-bold">100%</td>
            </tr>
        </tbody>
    </table>

    <div class="folder-grid">
        @foreach($ficha->folders->sortBy('position') as $folder)
        <div class="folder-item {{ $folder->status }}">{{ $folder->position }}. {{ Str::limit($folder->name, 35) }}</div>
        @endforeach
    </div>
</div>

</body>
</html>
