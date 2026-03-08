@extends('components.layouts.app')

@section('title', $member->name . ' - Team - LunaOS')

@section('content')
<div class="team-show space-y-6" x-data="{ activeTab: 'overview' }">
    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('team') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to Team</span>
        </a>
    </div>
    
    {{-- Member Header Card --}}
    <div class="group relative overflow-hidden bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 hover:border-purple-500/30 transition-all duration-300">
        @php
            $gradients = [
                'workers' => ['from-blue-500/20 to-cyan-500/20', 'from-blue-500/10 to-cyan-500/10', 'border-blue-500/30', 'from-blue-600 to-cyan-600', 'shadow-blue-500/20'],
                'personas' => ['from-purple-500/20 to-pink-500/20', 'from-purple-500/10 to-pink-500/10', 'border-purple-500/30', 'from-purple-600 to-pink-600', 'shadow-purple-500/20'],
                'board-members' => ['from-amber-500/20 to-orange-500/20', 'from-amber-500/10 to-orange-500/10', 'border-amber-500/30', 'from-amber-600 to-orange-600', 'shadow-amber-500/20'],
            ];
            $g = $gradients[$member->type] ?? $gradients['workers'];
        @endphp
        
        {{-- Background Glow --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br {{ $g[1] }} rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        
        {{-- Header --}}
        <div class="relative flex items-start gap-6 p-6 border-b border-white/10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br {{ $g[0] }} {{ $g[2] }} flex items-center justify-center text-4xl shadow-lg flex-shrink-0">
                {{ $member->emoji ?? ($member->type === 'workers' ? '🤖' : ($member->type === 'personas' ? '🎭' : '👔')) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-white">{{ $member->name }}</h1>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">
                        {{ ucfirst(str_replace('-', ' ', $member->type)) }}
                    </span>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ $member->status === 'active' || $member->status === 'online' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30' }}">
                        {{ ucfirst($member->status) }}
                    </span>
                </div>
                @if($member->title)
                    <p class="text-slate-400 text-lg">{{ $member->title }}</p>
                @endif
                @if($member->email)
                    <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                        <span>📧</span>
                        <span>{{ $member->email }}</span>
                    </p>
                @endif
            </div>
            
            {{-- Actions --}}
            <div class="flex gap-2">
                <a href="{{ route('team.edit', $member->id) }}" 
                   class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
                    ✏️ Edit
                </a>
                <form action="{{ route('team.destroy', $member->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30 font-medium">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>
        
        {{-- Tab Navigation --}}
        <div class="relative border-b border-white/10">
            <nav class="flex gap-1 px-6" role="tablist">
                <button 
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'bg-white/10 text-white border-b-2 border-purple-500' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                    class="px-4 py-3 text-sm font-medium transition-colors rounded-t-lg"
                    role="tab"
                    :aria-selected="activeTab === 'overview'"
                >
                    📋 Overview
                </button>
                <button 
                    @click="activeTab = 'ai-config'"
                    :class="activeTab === 'ai-config' ? 'bg-white/10 text-white border-b-2 border-purple-500' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                    class="px-4 py-3 text-sm font-medium transition-colors rounded-t-lg"
                    role="tab"
                    :aria-selected="activeTab === 'ai-config'"
                >
                    🤖 AI Config
                </button>
            </nav>
        </div>
    </div>
    
    {{-- Tab Content --}}
    <div class="tab-content">
        {{-- Overview Tab --}}
        <div x-show="activeTab === 'overview'" x-cloak>
            <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                    {{-- Column 1: Basic Info --}}
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Information</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Role</span>
                                <span class="text-white font-medium">{{ ucfirst($member->role) }}</span>
                            </div>
                            @if($member->model)
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Model</span>
                                <span class="text-white font-medium">{{ $member->model }}</span>
                            </div>
                            @endif
                            @if($member->provider)
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Provider</span>
                                <span class="text-white font-medium">{{ $member->provider }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Member Since</span>
                                <span class="text-white font-medium">{{ $member->created_at?->diffForHumans() ?? 'Recently' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Column 2: Stats --}}
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Stats</h2>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Tasks</p>
                                <p class="text-2xl font-bold text-white mt-1">{{ $member->tasks->count() }}</p>
                            </div>
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Reports</p>
                                <p class="text-2xl font-bold text-white mt-1">{{ $member->children->count() }}</p>
                            </div>
                        </div>
                        @if($member->parent)
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                            <p class="text-xs text-slate-400 uppercase tracking-wider">Reports To</p>
                            <a href="{{ route('team.show', $member->parent->id) }}" class="text-purple-400 hover:text-purple-300 font-medium mt-1 block">
                                {{ $member->parent->name }}
                            </a>
                        </div>
                        @endif
                    </div>
                    
                    {{-- Column 3: Direct Reports or Tasks --}}
                    <div class="space-y-4">
                        @if($member->children->count() > 0)
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Direct Reports</h2>
                        <div class="space-y-2">
                            @foreach($member->children as $child)
                            <a href="{{ route('team.show', $child->id) }}" 
                               class="flex items-center gap-3 p-3 bg-white/5 rounded-lg border border-white/10 hover:bg-white/10 transition-colors">
                                <span class="text-lg">{{ $child->emoji ?? '👤' }}</span>
                                <span class="text-white font-medium">{{ $child->name }}</span>
                                <span class="ml-auto text-xs text-slate-500">{{ ucfirst($child->type) }}</span>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Tasks Assigned</h2>
                        @if($member->tasks->count() > 0)
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($member->tasks->take(5) as $task)
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10">
                                <span class="text-white text-sm truncate flex-1">{{ $task->title }}</span>
                                <span class="text-xs px-2 py-1 rounded ml-2 {{ $task->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>
                            @endforeach
                            @if($member->tasks->count() > 5)
                            <p class="text-xs text-slate-500 text-center">+{{ $member->tasks->count() - 5 }} more</p>
                            @endif
                        </div>
                        @else
                        <div class="text-center py-6 text-slate-500">
                            <p>No tasks assigned</p>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- AI Config Tab --}}
        <div x-show="activeTab === 'ai-config'" x-cloak>
            @livewire('team.member-ai-config', ['memberId' => $member->id])
        </div>
    </div>
</div>

{{-- Add x-cloak style to hide elements until Alpine loads --}}
<style>
    [x-cloak] { display: none !important; }
</style>
@endsection