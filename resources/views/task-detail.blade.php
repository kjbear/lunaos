@extends('components.layouts.app')

@section('title', 'Task Details')

@section('content')
<div class="task-detail-page">
    <div class="mb-6">
        <a 
            href="{{ route('tasks') }}" 
            class="text-purple-400 hover:text-purple-300 hover:underline flex items-center gap-2 inline-flex"
        >
            <span>←</span>
            <span>Back to Tasks</span>
        </a>
    </div>
    
    <livewire:task-detail />
</div>
@endsection
