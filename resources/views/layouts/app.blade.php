<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ASEM') — ASEM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 flex flex-col shadow-xl" style="background-color:#00304D;">
        <div class="px-6 py-5 border-b flex items-center gap-3" style="border-color:rgba(255,255,255,0.1);">
            <img src="{{ asset('images/sena-logo.png') }}" alt="SENA"
                 class="h-8 w-auto"
                 style="filter: brightness(0) invert(1);">
            <div>
                <h1 class="text-base font-bold tracking-wide text-white leading-tight">ASEM</h1>
                <p class="text-xs leading-tight" style="color:rgba(255,255,255,0.55);">Gestión Académica</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            @if(auth()->user()->isAdmin())
                <x-nav-link :href="route('admin.dashboard')"         label="Dashboard"       icon="home"/>
                <x-nav-link :href="route('programs.index')"          label="Programas"       icon="academic-cap"/>
                <x-nav-link :href="route('fichas.index')"            label="Fichas"          icon="folder"/>
                <x-nav-link :href="route('admin.instructors')"       label="Instructores"    icon="users"/>
                <x-nav-link :href="route('instructors.programs')"    label="Prog. Instructor"icon="academic-cap"/>
                <x-nav-link :href="route('admin.users')"             label="Usuarios"        icon="user-group"/>
                <x-nav-link :href="route('competencias.index')"      label="Competencias"    icon="chart-bar"/>
            @endif

            @if(auth()->user()->isCoordinacion())
                <x-nav-link :href="route('coordinacion.dashboard')"          label="Dashboard"        icon="home"/>
                <x-nav-link :href="route('programs.index')"                  label="Programas"        icon="academic-cap"/>
                <x-nav-link :href="route('fichas.index')"                    label="Fichas"           icon="folder"/>
                <x-nav-link :href="route('coordinacion.instructors.create')" label="Nuevo Instructor" icon="user-plus"/>
                <x-nav-link :href="route('instructors.programs')"            label="Prog. Instructor" icon="academic-cap"/>
                <x-nav-link :href="route('competencias.index')"              label="Competencias"     icon="chart-bar"/>
            @endif

            @if(auth()->user()->isInstructor())
                <x-nav-link :href="route('instructor.dashboard')"    label="Dashboard"       icon="home"/>
                <x-nav-link :href="route('fichas.index')"            label="Mis Fichas"      icon="folder"/>
                <x-nav-link :href="route('fichas.create')"           label="Nueva Ficha"     icon="folder"/>
                <x-nav-link :href="route('reports.index')"           label="Mis Informes"    icon="document"/>
                <x-nav-link :href="route('reportes.asistencia')"     label="Rep. Asistencia" icon="chart-bar"/>
                <x-nav-link :href="route('competencias.index')"      label="Competencias"    icon="chart-bar"/>
            @endif

            @if(auth()->user()->isAdmin())
                <x-nav-link :href="route('reports.index')"       label="Informes"         icon="document"/>
                <x-nav-link :href="route('reportes.asistencia')" label="Rep. Asistencia"  icon="chart-bar"/>
            @endif
            @if(auth()->user()->isCoordinacion())
                <x-nav-link :href="route('reports.index')"       label="Informes"         icon="document"/>
                <x-nav-link :href="route('reportes.asistencia')" label="Rep. Asistencia"  icon="chart-bar"/>
            @endif

            @if(auth()->user()->isAprendiz())
                <x-nav-link :href="route('aprendiz.dashboard')"  label="Dashboard"      icon="home"/>
                <x-nav-link :href="route('aprendiz.resultados')" label="Mis Resultados" icon="check-circle"/>
            @endif
        </nav>

        <div class="px-4 py-4 border-t" style="border-color:rgba(255,255,255,0.1);">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white"
                     style="background-color:#39A900;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="text-sm">
                    <p class="font-medium text-white truncate w-36">{{ auth()->user()->name }}</p>
                    <p class="text-xs capitalize" style="color:rgba(255,255,255,0.55);">{{ auth()->user()->role }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left text-sm transition-colors"
                        style="color:rgba(255,255,255,0.55);"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
            <div class="text-sm text-gray-400">{{ now()->format('d/m/Y') }}</div>
        </header>

        <div class="flex-1 p-8 overflow-y-auto">
            @if(session('success'))
                <div class="mb-4 border rounded-lg px-4 py-3 text-sm"
                     style="background:#f0fce8;border-color:#39A900;color:#007832;">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
