<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts (Bootstrap via Vite) -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg" style="width: 100%; max-width: 450px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-primary">TONTINE APP</h2>
                <p class="text-muted">Gestion communautaire</p>
            </div>

            <!-- Ici s'insère le formulaire (Login ou Register) -->
            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
