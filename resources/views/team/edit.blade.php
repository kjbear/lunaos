@extends('components.layouts.app')

@section('title', 'Edit ' . $member->name . ' - Team - LunaOS')

@section('content')
<div class="team-edit space-y-6">
    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('team.show', $member->id) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to {{ $member->name }}</span>
        </a>
    </div>
    
    {{-- Page Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center gap-5 p-6">
            @php
                $gradients = [
                    'workers' => ['from-blue-500/20 to-cyan-500/20', 'border-blue-500/30'],
                    'personas' => ['from-purple-500/20 to-pink-500/20', 'border-purple-500/30'],
                    'board-members' => ['from-amber-500/20 to-orange-500/20', 'border-amber-500/30'],
                ];
                $g = $gradients[$member->type] ?? $gradients['workers'];
            @endphp
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $g[0] }} {{ $g[1] }} flex items-center justify-center text-3xl shadow-lg">
                {{ $member->emoji ?? '👤' }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Edit {{ $member->name }}</h1>
                <p class="text-sm text-slate-400 font-medium mt-0.5">Update member details and settings</p>
            </div>
        </div>
    </header>
    
    {{-- Form Card --}}
    <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
        <form action="{{ route('team.update', $member->id) }}" method="POST" class="space-y-6">
            @method('PUT')
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $member->name) }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('name') border-red-500/50 @enderror" 
                           placeholder="Enter member name">
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $member->email) }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('email') border-red-500/50 @enderror" 
                           placeholder="member@example.com">
                    @error('email')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Title --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Title</label>
                    <input type="text" name="title" value="{{ old('title', $member->title) }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., Senior Developer">
                </div>
                
                {{-- Role --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Role *</label>
                    <select name="role" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('role') border-red-500/50 @enderror" required>
                        <option value="worker" {{ old('role', $member->role) === 'worker' ? 'selected' : '' }}>🤖 Worker</option>
                        <option value="persona" {{ old('role', $member->role) === 'persona' ? 'selected' : '' }}>🎭 Persona</option>
                        <option value="board_member" {{ old('role', $member->role) === 'board_member' ? 'selected' : '' }}>👔 Board Member</option>
                    </select>
                    @error('role')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Type --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Type *</label>
                    <select name="type" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('type') border-red-500/50 @enderror" required>
                        <option value="workers" {{ old('type', $member->type) === 'workers' ? 'selected' : '' }}>🤖 Workers</option>
                        <option value="personas" {{ old('type', $member->type) === 'personas' ? 'selected' : '' }}>🎭 Personas</option>
                        <option value="board-members" {{ old('type', $member->type) === 'board-members' ? 'selected' : '' }}>👔 Board Members</option>
                    </select>
                    @error('type')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Status</label>
                    <select name="status" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors">
                        <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>🟢 Active</option>
                        <option value="inactive" {{ old('status', $member->status) === 'inactive' ? 'selected' : '' }}>⚫ Inactive</option>
                        <option value="online" {{ old('status', $member->status) === 'online' ? 'selected' : '' }}>🟢 Online</option>
                        <option value="offline" {{ old('status', $member->status) === 'offline' ? 'selected' : '' }}>⚪ Offline</option>
                        <option value="busy" {{ old('status', $member->status) === 'busy' ? 'selected' : '' }}>🟡 Busy</option>
                        <option value="error" {{ old('status', $member->status) === 'error' ? 'selected' : '' }}>🔴 Error</option>
                    </select>
                </div>
                
                {{-- Model --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">AI Model</label>
                    <input type="text" name="model" value="{{ old('model', $member->model) }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., gpt-4, claude-3">
                </div>
                
                {{-- Provider --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Provider</label>
                    <input type="text" name="provider" value="{{ old('provider', $member->provider) }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., openai, anthropic">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Emoji --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Avatar Emoji</label>
                    <input type="text" name="emoji" value="{{ old('emoji', $member->emoji) }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., 🤖, 🎭, 👔"
                           maxlength="10">
                    <p class="text-xs text-slate-500 mt-1">A single emoji to represent this member</p>
                </div>
                
                {{-- Parent --}}
                @if(isset($parents) && $parents->count() > 0)
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Reports To</label>
                    <select name="parent_id" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors">
                        <option value="">— None —</option>
                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $member->parent_id) === $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }} ({{ ucfirst(str_replace('-', ' ', $parent->type)) }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            
            {{-- System Prompt --}}
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">System Prompt</label>
                <textarea name="system_prompt" rows="4"
                          class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors font-mono text-sm"
                          placeholder="Enter the system prompt for AI-powered members...">{{ old('system_prompt', $member->system_prompt) }}</textarea>
                <p class="text-xs text-slate-500 mt-1">For personas and workers, this defines their behavior and personality</p>
            </div>
            
            {{-- Actions --}}
            <div class="flex gap-3 pt-4 border-t border-white/10">
                <a href="{{ route('team.show', $member->id) }}" 
                   class="px-4 py-2.5 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all font-medium shadow-lg shadow-purple-500/20">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection