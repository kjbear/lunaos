<div class="team-create space-y-6">
    {{-- Page Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center gap-5 p-6">
            <div class="group relative">
                <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">➕</div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Create Team Member</h1>
                <p class="text-sm text-slate-400 font-medium mt-0.5">Add a new worker, persona, or board member</p>
            </div>
        </div>
    </header>
    
    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('team') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to Team</span>
        </a>
    </div>
    
    {{-- Form Card --}}
    <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
        <form action="{{ route('team.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('name') border-red-500/50 @enderror" 
                           placeholder="Enter member name">
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Email --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('email') border-red-500/50 @enderror" 
                           placeholder="member@example.com">
                    @error('email')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Role --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Role *</label>
                    <select name="role" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors @error('role') border-red-500/50 @enderror" required>
                        <option value="worker" {{ old('role') === 'worker' ? 'selected' : '' }}>🤖 Worker</option>
                        <option value="persona" {{ old('role') === 'persona' ? 'selected' : '' }}>🎭 Persona</option>
                        <option value="board_member" {{ old('role') === 'board_member' ? 'selected' : '' }}>👔 Board Member</option>
                    </select>
                    @error('role')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Title --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., Senior Developer">
                </div>
                
                {{-- Status --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Status</label>
                    <select name="status" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>🟢 Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>⚫ Inactive</option>
                        <option value="online" {{ old('status') === 'online' ? 'selected' : '' }}>🟢 Online</option>
                        <option value="offline" {{ old('status') === 'offline' ? 'selected' : '' }}>⚪ Offline</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Model --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">AI Model</label>
                    <input type="text" name="model" value="{{ old('model') }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., gpt-4, claude-3">
                </div>
                
                {{-- Provider --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Provider</label>
                    <input type="text" name="provider" value="{{ old('provider') }}" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., openai, anthropic">
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="flex gap-3 pt-4 border-t border-white/10">
                <a href="{{ route('team') }}" 
                   class="px-4 py-2.5 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all font-medium shadow-lg shadow-purple-500/20">
                    Create Member
                </button>
            </div>
        </form>
    </div>
</div>