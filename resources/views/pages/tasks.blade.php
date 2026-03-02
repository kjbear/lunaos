@extends('components.layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="tasks-page" x-data="{
    currentView: localStorage.getItem('taskViewMode') || 'list',
    setView(view) {
        this.currentView = view;
        localStorage.setItem('taskViewMode', view);
        history.pushState(null, '', `/tasks/${view}`);
    }
}">
    
    {{-- View Mode Tabs --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 p-1 bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 w-fit">
            {{-- List View --}}
            <button
                x-on:click="setView('list')"
                class="flex items-center gap-2.5 px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
                :class="currentView === 'list' 
                    ? 'bg-gradient-to-r from-cyan-500/20 to-purple-500/20 text-white border border-cyan-400/50 shadow-lg' 
                    : 'text-slate-400 hover:text-white hover:bg-white/5'"
            >
                <span class="text-lg">📋</span>
                <span>List</span>
            </button>
            
            {{-- Board View --}}
            <button
                x-on:click="setView('board')"
                class="flex items-center gap-2.5 px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
                :class="currentView === 'board' 
                    ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/20 text-white border border-purple-400/50 shadow-lg' 
                    : 'text-slate-400 hover:text-white hover:bg-white/5'"
            >
                <span class="text-lg">📊</span>
                <span>Board</span>
            </button>
            
            {{-- Executive View --}}
            <button
                x-on:click="setView('executive')"
                class="flex items-center gap-2.5 px-5 py-2.5 rounded-lg text-sm font-medium transition-all"
                :class="currentView === 'executive' 
                    ? 'bg-gradient-to-r from-amber-500/20 to-orange-500/20 text-white border border-amber-400/50 shadow-lg' 
                    : 'text-slate-400 hover:text-white hover:bg-white/5'"
            >
                <span class="text-lg">🎯</span>
                <span>Executive</span>
            </button>
        </div>
    </div>
    
    {{-- Action Bar (always visible) --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Task Management</h1>
            <p class="text-sm text-slate-400 mt-1">Comprehensive task orchestration across all agents</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a 
                href="{{ route('tasks.create') }}"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-medium hover:from-cyan-400 hover:to-purple-400 transition-all shadow-lg hover:shadow-xl"
            >
                <span>➕</span>
                <span>New Task</span>
            </a>
            
            <button 
                class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                title="Settings"
            >
                ⚙️
            </button>
        </div>
    </div>

    {{-- View Content --}}
    <div x-show="currentView === 'list'" x-cloak>
        <livewire:task-list />
    </div>
    
    <div x-show="currentView === 'board'" x-cloak>
        <livewire:task-board-unified />
    </div>
    
    <div x-show="currentView === 'executive'" x-cloak>
        <livewire:task-executive />
    </div>
</div>

@endsection

@push('head')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
