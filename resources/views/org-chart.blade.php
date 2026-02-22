@extends('layouts.app')

@section('title', 'Org Chart')

@section('content')
<div class="space-y-8">
    <!-- Navigation Tabs -->
    <div class="flex space-x-4 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ route('home') }}" class="px-4 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-medium">
            📋 Tasks
        </a>
        <a href="{{ route('org-chart') }}" class="px-4 py-2 text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-500 font-medium">
            🏢 Org Chart
        </a>
    </div>

    <!-- Org Chart -->
    <livewire:org-chart />
</div>
@endsection