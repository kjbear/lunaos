@extends('components.layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('projects') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Projects
        </a>
        <h1 class="text-3xl font-bold text-white mb-2">Create New Project</h1>
        @if($boardSession)
        <p class="text-amber-400 text-sm">🎯 AI-powered project creation from Board Decision</p>
        @endif
    </div>

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-300">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST" class="space-y-6">
        @csrf
        
        @if($boardSession)
        <input type="hidden" name="board_session_id" value="{{ $boardSession->id }}">
        
        <!-- AI Analysis Results -->
        @if($analysis)
        <div class="bg-gradient-to-r from-purple-500/10 to-blue-500/10 rounded-2xl border border-purple-500/20 p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center text-lg">✨</div>
                <div>
                    <h2 class="text-lg font-semibold text-white">AI Analysis Complete</h2>
                    <p class="text-sm text-slate-400">Generated from board discussion</p>
                </div>
            </div>
            
            @if($analysis['error'] ?? false)
            <div class="text-amber-400 text-sm mb-4">⚠️ AI analysis partially failed: {{ $analysis['error'] }}</div>
            @endif
        </div>
        @endif
        @endif

        <!-- Project Name -->
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Project Name *</label>
            <input type="text" 
                   name="name"
                   value="{{ old('name', $analysis['name'] ?? '') }}"
                   placeholder="e.g., LunaOS Unified Agent Dashboard"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:border-purple-400 focus:outline-none transition-colors"
                   required>
            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            @if($boardSession && $analysis)
            <p class="text-xs text-slate-500 mt-1">✨ AI-generated based on board discussion</p>
            @endif
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Description *</label>
            <textarea name="description"
                      rows="5"
                      placeholder="Describe the project's purpose, goals, and scope..."
                      class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:border-purple-400 focus:outline-none transition-colors resize-none"
                      required>{{ old('description', $analysis['description'] ?? '') }}</textarea>
            @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Repository URL -->
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Repository URL (optional)</label>
            <input type="url" 
                   name="repo_url"
                   value="{{ old('repo_url') }}"
                   placeholder="https://github.com/your-org/your-project"
                   class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-slate-500 focus:border-purple-400 focus:outline-none transition-colors">
        </div>

        <!-- Status -->
        <div>
            <label class="block text-sm font-semibold text-slate-300 mb-2">Status</label>
            <select name="status"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-400 focus:outline-none transition-colors">
                <option value="planning" {{ old('status') === 'planning' ? 'selected' : '' }}>📝 Planning</option>
                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>✅ Active</option>
                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>🎉 Completed</option>
            </select>
        </div>

        <!-- AI-Generated Requirements -->
        @if($boardSession && !empty($analysis['requirements']))
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">📋 AI-Generated Initial Requirements</h3>
            <p class="text-sm text-slate-400 mb-4">These will be created as project artifacts. Edit or remove as needed.</p>
            
            <div class="space-y-3">
                @foreach($analysis['requirements'] as $index => $req)
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 text-xs flex items-center justify-center font-semibold">{{ $index + 1 }}</span>
                    <textarea name="requirements[{{ $index }}]"
                              rows="2"
                              class="flex-1 bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:border-purple-400 focus:outline-none transition-colors resize-none">{{ $req }}</textarea>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-4 pt-6 border-t border-white/10">
            <button type="submit"
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25">
                Create Project
            </button>
            <a href="{{ route('projects') }}"
               class="px-6 py-3 bg-white/5 text-slate-300 font-semibold rounded-xl hover:bg-white/10 transition-all border border-white/10 text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
