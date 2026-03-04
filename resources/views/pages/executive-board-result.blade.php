@extends('components.layouts.app')

@section('title', 'Board Session')

@section('content')
@php
    // $session is already passed from the route, but ensure it's loaded with responses
    if (!$session instanceof \App\Models\BoardSession) {
        $session = \App\Models\BoardSession::with(['responses'])->find($sessionId);
    } else {
        $session->load(['responses']);
    }
@endphp

<div class="executive-board-result-page">
    <div class="page-container max-w-5xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('tasks.executive.board') }}" 
               class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Executive Board
            </a>
        </div>

        <!-- Page Header -->
        <header class="mb-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-1">Board Session</h1>
                    <p class="text-slate-400">
                        @if($session->status === 'decided')
                            Session Complete • {{ $session->decided_at?->format('F j, Y g:i A') }}
                        @elseif($session->status === 'failed' || $session->status === 'cancelled')
                            Session {{ ucfirst($session->status) }}
                        @else
                            Live Discussion • Started {{ $session->created_at->format('F j, Y g:i A') }}
                        @endif
                    </p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-semibold
                    @if($session->status === 'decided') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                    @elseif($session->status === 'failed') bg-red-500/20 text-red-400 border border-red-500/30
                    @elseif($session->status === 'cancelled') bg-slate-500/20 text-slate-400 border border-slate-500/30
                    @elseif($session->status === 'debating' || $session->status === 'pending') 
                        bg-amber-500/20 text-amber-400 border border-amber-500/30
                    @else bg-slate-500/20 text-slate-400 border border-slate-500/30
                    @endif">
                    @if(in_array($session->status, ['debating', 'pending']))
                        <span class="inline-flex items-center gap-2">
                            <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                            Live
                        </span>
                    @else
                        {{ ucfirst($session->status) }}
                    @endif
                </span>
            </div>
        </header>

        <!-- Question Card -->
        <div class="bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-2xl border border-purple-500/20 p-6 mb-8">
            <h2 class="text-lg font-semibold text-purple-300 mb-2">Question Asked</h2>
            <p class="text-white text-lg leading-relaxed">{{ $session->question }}</p>
            @if($session->context)
                <div class="mt-4 pt-4 border-t border-purple-500/20">
                    <p class="text-slate-300 text-sm">
                        <strong class="text-purple-300">Context:</strong> {{ $session->context }}
                    </p>
                </div>
            @endif
        </div>

        <!-- CEO Recommendation -->
        @if($session->final_decision && $session->status === 'decided')
        <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-2xl border border-amber-500/20 p-8 mb-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-2xl">
                    🎯
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Board Recommendation</h2>
                    <p class="text-amber-300 text-sm">Synthesized by CEO</p>
                </div>
                @if($session->confidence_score)
                <div class="ml-auto text-right">
                    <div class="text-xs text-slate-500 uppercase font-semibold">Confidence</div>
                    <div class="text-2xl font-bold 
                        {{ $session->confidence_percentage >= 80 ? 'text-emerald-400' : ($session->confidence_percentage >= 60 ? 'text-amber-400' : 'text-red-400') }}">
                        {{ $session->confidence_percentage }}%
                    </div>
                </div>
                @endif
            </div>
            <div class="prose prose-invert max-w-none">
                <p class="text-white text-lg leading-relaxed">{{ $session->final_decision }}</p>
            </div>
            @if($session->risks_benefits)
            <div class="mt-6 pt-6 border-t border-amber-500/20">
                <h3 class="text-red-400 font-semibold mb-2">⚠️ Risks & Considerations</h3>
                <div class="text-slate-300 text-sm whitespace-pre-line">{{ $session->risks_benefits }}</div>
            </div>
            @endif
        </div>
        @endif

        <!-- Live Discussion Feed -->
        @livewire('board.live-discussion-feed', ['sessionId' => $session->id, 'isComplete' => in_array($session->status, ['decided', 'failed', 'cancelled'])])

        <!-- Actions -->
        @if($session->status === 'decided')
        <div class="mt-8 flex gap-4">
            <a href="{{ route('tasks.executive.board') }}" 
               class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-blue-500 transition-all shadow-lg">
                Start New Session
            </a>
            <button onclick="window.print()" 
                    class="px-6 py-3 bg-slate-800 text-white font-semibold rounded-xl hover:bg-slate-700 transition-all border border-white/10">
                Print Results
            </button>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
@endpush
@endsection
