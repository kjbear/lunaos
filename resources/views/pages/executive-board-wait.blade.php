@extends('components.layouts.app')

@section('title', 'Board in Session')

@section('content')
<div class="executive-board-wait-page"
     x-data="{
         sessionId: '{{ $sessionId }}',
         polling: true,
         elapsed: 0,
         checkStatus() {
             if (!this.polling) return;
             
             this.elapsed += 2;
             
             // Fetch status every 2 seconds
             fetch(`/api/board/${sessionId}/status`)
                 .then(res => res.json())
                 .then(data => {
                     if (data.status === 'decided') {
                         this.polling = false;
                         window.location.href = `/tasks/executive/board/${sessionId}`;
                     }
                     if (data.status === 'failed' || data.status === 'cancelled') {
                         this.polling = false;
                         alert('Board session ' + data.status);
                         window.location.href = '/tasks/executive/board';
                     }
                 })
                 .catch(err => {
                     console.error('Status check failed:', err);
                     this.polling = false;
                 });
         }
     }"
     x-init="
         const timer = setInterval(() => checkStatus(), 2000);
         $watch('polling', value => { if (!value) clearInterval(timer); });
     "
>
    <div class="page-container max-w-3xl mx-auto">
        <!-- Header -->
        <header class="text-center mb-12">
            <div class="text-6xl mb-6 animate-bounce">🎙</div>
            <h1 class="text-3xl font-bold text-white mb-2">Board in Session</h1>
            <p class="text-slate-400">The executives are debating your question...</p>
            
            <!-- Session ID -->
            <div class="mt-4 text-xs text-slate-500 font-mono">
                Session: {{ $sessionId }}
            </div>
        </header>

        <!-- Progress -->
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-8 mb-8">
            <!-- Animated Dots -->
            <div class="flex items-center justify-center gap-3 mb-6">
                <div class="w-4 h-4 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-4 h-4 bg-orange-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-4 h-4 bg-pink-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
            
            <!-- Status Message -->
            <div class="text-center">
                <p class="text-white text-lg mb-2">Processing with AI executives</p>
                <p class="text-slate-400 text-sm">This typically takes 3-5 minutes</p>
            </div>
            
            <!-- Timer -->
            <div class="mt-6 pt-6 border-t border-white/10 text-center">
                <p class="text-slate-400 text-sm">
                    Waiting time: <span class="text-amber-400 font-mono" x-text="elapsed + 's'">0s</span>
                </p>
            </div>
        </div>

        <!-- What's Happening -->
        <div class="bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-2xl border border-purple-500/20 p-6 mb-8">
            <h2 class="text-lg font-semibold text-purple-300 mb-4">What's happening:</h2>
            <ul class="space-y-3 text-slate-300 text-sm">
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>CEO</strong> is analyzing your question and forming initial recommendation</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>CFO, CTO, COO, CMO, CPO</strong> are providing their expert perspectives</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>Debate continues</strong> for 2-3 rounds to explore all angles</span>
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span><strong>CEO synthesizes</strong> final recommendation with confidence score</span>
                </li>
            </ul>
        </div>

        <!-- Actions -->
        <div class="text-center">
            <a href="{{ route('tasks.executive.board') }}" 
               class="text-slate-400 hover:text-white text-sm transition-colors"
               @click.prevent="polling = false">
                ← Cancel and return to board
            </a>
        </div>

        <!-- Hidden Status Element for Livewire Polling -->
        <div wire:poll.3s="checkSessionStatus" class="hidden"></div>
    </div>
</div>

@endsection
