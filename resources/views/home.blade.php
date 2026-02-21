@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="flex items-center space-x-4">
            <span class="text-5xl">🌙</span>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Welcome to LunaOS
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Dashboard for AI assistant team visibility
                </p>
            </div>
        </div>
    </div>

    <!-- Test Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Livewire Test -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                🔄 Livewire Test
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Counter component with reactive updates
            </p>
            <livewire:counter />
        </div>

        <!-- HTMX Test -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                ⚡ HTMX Test
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Click to load content via AJAX
            </p>
            <button 
                hx-get="/api/status"
                hx-target="#htmx-result"
                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg font-medium transition-colors"
            >
                Load Status
            </button>
            <div id="htmx-result" class="mt-4 text-sm"></div>
        </div>

        <!-- Dark Mode Test -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                🌙 Dark Mode Test
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Toggle dark mode using the button in the navbar
            </p>
            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <p class="text-gray-700 dark:text-gray-300">
                    This box changes with dark mode
                </p>
            </div>
        </div>
    </div>

    <!-- Stack Info -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            ⚙️ Tech Stack
        </h2>
        <div class="flex flex-wrap gap-2">
            <span class="px-3 py-1 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-full text-sm font-medium">
                Laravel 12
            </span>
            <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded-full text-sm font-medium">
                Livewire 3
            </span>
            <span class="px-3 py-1 bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300 rounded-full text-sm font-medium">
                Tailwind CSS 4
            </span>
            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                HTMX 2
            </span>
            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full text-sm font-medium">
                SQLite
            </span>
        </div>
    </div>
</div>
@endsection