@extends('components.layouts.app')

@section('title', 'Executive Board')

@section('content')
<div class="executive-board-page" x-data="{ activeTab: 'ask' }">
    <div class="page-container max-w-6xl mx-auto">
        <!-- Page Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-white mb-2">Executive Board</h1>
            <p class="text-slate-400">Strategic decision-making with AI-powered board members</p>
        </header>

        <!-- Tabs -->
        <div class="mb-8">
            <div class="flex gap-2 border-b border-white/10">
                <button 
                    @click="activeTab = 'ask'"
                    :class="activeTab === 'ask' ? 'text-white border-purple-500' : 'text-slate-400 border-transparent hover:text-slate-300'"
                    class="px-4 py-2 font-medium text-sm border-b-2 transition-colors"
                >
                    Ask Question
                </button>
                <button 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'text-white border-purple-500' : 'text-slate-400 border-transparent hover:text-slate-300'"
                    class="px-4 py-2 font-medium text-sm border-b-2 transition-colors"
                >
                    Previous Sessions
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Ask Tab -->
            <div x-show="activeTab === 'ask'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <livewire:board.executive-board />
            </div>

            <!-- History Tab -->
            <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <livewire:board.board-session-history />
            </div>
        </div>
    </div>
</div>
@endsection
