<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-theme="lunaos">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Chat - LunaOS')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Prevent flash of unstyled content -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="antialiased bg-slate-950 text-white" x-data x-init>
    {{ $slot }}
    
    <!-- Livewire Config -->
    <script>
        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: '{{ csrf_token() }}'
        };
    </script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Explicit Livewire Start -->
    <script>
        (function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.start();
            }
        })();
    </script>
</body>
</html>