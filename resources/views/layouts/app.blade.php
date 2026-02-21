<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LunaOS') - Dashboard</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@2.0.4" defer></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('head')
</head>
<body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        <span class="text-2xl">🌙</span>
                        <span class="font-bold text-xl text-indigo-600 dark:text-indigo-400">LunaOS</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button 
                        id="dark-mode-toggle"
                        class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                        onclick="toggleDarkMode()"
                    >
                        <span class="dark:hidden">🌙</span>
                        <span class="hidden dark:inline">☀️</span>
                    </button>
                    
                    <!-- User Menu (placeholder) -->
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ config('app.name') }}
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400">
            LunaOS — AI Assistant Dashboard
        </div>
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <script>
        // Dark mode toggle
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }
        
        // Load dark mode preference
        if (localStorage.getItem('darkMode') === 'false') {
            document.documentElement.classList.remove('dark');
        }
        
        // HTMX configuration
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        });
    </script>
    
    @stack('scripts')
</body>
</html>