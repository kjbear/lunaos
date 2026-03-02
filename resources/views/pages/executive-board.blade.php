@extends('components.layouts.app')

@section('title', 'Executive Board')

@section('content')
<div class="executive-board-page">
    <div class="page-container max-w-7xl mx-auto">
        <!-- Page Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Executive Board</h1>
            <p class="text-slate-400">Strategic decision-making with AI-powered board members</p>
        </header>

        <!-- Main Content -->
        <main>
            <livewire:board-meeting-manager />
        </main>
    </div>
</div>
@endsection
