<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ASEM')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center">
<div class="w-full max-w-md px-4">
    <div class="text-center mb-8">
        <img src="{{ asset('images/sena-logo.png') }}" alt="SENA"
             class="h-16 w-auto mx-auto mb-3">
        <h1 class="text-xl font-bold" style="color:#00304D;">ASEM</h1>
        <p class="text-gray-500 text-sm mt-1">Sistema de Gestión Académica</p>
    </div>
    <div class="bg-white rounded-2xl shadow-lg p-8">
        @yield('content')
    </div>
</div>
</body>
</html>
