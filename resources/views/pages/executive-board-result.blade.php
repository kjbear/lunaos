@extends('components.layouts.app')

@section('title', 'Board Session Results')

@section('content')
<div class="executive-board-result-page">
    <div class="page-container max-w-7xl mx-auto">
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

        <!-- Session Header -->
        <header class="mb-8">
            <div class="flex items-start justify-between mb-4">
                <h1 class="text-3xl font-bold text-white">Board Session Results</h1>
                <span class="px-4 py-2 rounded-full text-sm font-medium 
                    {{ $session->status === 'decided' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400' }}">
                    {{ ucfirst($session->status) }}
                </span>
            </div>
            <p class="text-slate-400">{{ $session->created_at->format('F j, Y \a\t g:i A') }}</p>
        </header>

        <!-- Question Card -->
        <div class="bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-2xl border border-purple-500/20 p-6 mb-8">
            <h2 class="text-lg font-semibold text-purple-300 mb-3">Question Asked</h2>
            <p class="text-white text-lg leading-relaxed">{{ $session->question }}</p>
            @if($session->context)
                <div class="mt-4 pt-4 border-t border-purple-500/20">
                    <p class="text-slate-300 text-sm"><strong class="text-purple-300">Context:</strong> {{ $session->context }}</p>
                </div>
            @endif
        </div>

        <!-- CEO Recommendation -->
        @if($session->final_decision)
        <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-2xl border border-amber-500/20 p-8 mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-xl">
                    🎯
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Board Recommendation</h2>
                    <p class="text-amber-300 text-sm">Synthesized by CEO</p>
                </div>
                @if($session->confidence)
                <div class="ml-auto">
                    <span class="px-4 py-2 bg-amber-500/20 rounded-lg text-amber-300 font-semibold">
                        {{ $session->confidence }}% Confidence
                    </span>
                </div>
                @endif
            </div>
            <div class="prose prose-invert max-w-none">
                <p class="text-white text-lg leading-relaxed">{{ $session->final_decision }}</p>
            </div>
            @if($session->risks_benefits)
            <div class="mt-6 pt-6 border-t border-amber-500/20 grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-red-400 font-semibold mb-2">⚠️ Risks</h3>
                    <div class="text-slate-300 text-sm whitespace-pre-line">{{ $session->risks_benefits }}</div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Executive Responses -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-6">Executive Perspectives</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($session->responses as $response)
                <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 p-6 hover:border-purple-500/30 transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($response->member_name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">{{ $response->member_name }}</h3>
                            <p class="text-slate-400 text-sm">{{ $response->member_role }}</p>
                        </div>
                    </div>
                    <div class="prose prose-invert prose-sm max-w-none">
                        <p class="text-slate-300 leading-relaxed">{{ $response->response }}</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <p class="text-slate-500 text-xs">Model: {{ $response->model_used }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="{{ route('tasks.executive.board') }}" 
               class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-blue-500 transition-all shadow-lg">
                Start New Session
            </a>
            <button onclick="window.print()" 
                    class="px-6 py-3 bg-slate-800 text-white font-semibold rounded-xl hover:bg-slate-700 transition-all border border-white/10">
                Print Results
            </button>
        </div>
    </div>
</div>
@endsection
