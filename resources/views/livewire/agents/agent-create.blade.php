<div>
    <!-- Header with Gradient -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('agents.index') }}" class="text-slate-400 hover:text-purple-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 bg-clip-text text-transparent">Create New Agent</h1>
                <p class="text-slate-400">Configure a new AI worker agent</p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 p-4 bg-green-900/30 border border-green-500/30 rounded-xl text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <!-- Create Form -->
    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-gradient-to-br from-[#1a1a2e] to-[#12121f] rounded-2xl border border-[#2a2a40]/50 p-6 shadow-xl">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center text-purple-400 border border-purple-500/30">📝</span>
                Basic Information
            </h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Name *</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" placeholder="e.g., alex">
                    @error('name') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Role *</label>
                    <input type="text" wire:model="role" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" placeholder="e.g., API Architect">
                    @error('role') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Type *</label>
                    <select wire:model="type" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="worker">Worker</option>
                        <option value="board">Board</option>
                        <option value="executive">Executive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Emoji</label>
                    <input type="text" wire:model="emoji" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white text-center text-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" placeholder="🤖">
                </div>
            </div>
        </div>

        <!-- Strategy & Workflow -->
        <div class="bg-gradient-to-br from-[#1a1a2e] to-[#12121f] rounded-2xl border border-[#2a2a40]/50 p-6 shadow-xl">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center text-blue-400 border border-blue-500/30">⚙️</span>
                Strategy & Workflow
            </h2>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Strategy *</label>
                    <select wire:model="strategy_class" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="">Select a strategy...</option>
                        @foreach($strategies as $strategyName => $strategyClass)
                        <option value="{{ $strategyName }}">{{ ucfirst($strategyName) }}</option>
                        @endforeach
                    </select>
                    @error('strategy_class') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Workflow Steps</label>
                    <input type="text" wire:model="step_filter" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" placeholder="e.g., develop or staging,production">
                    <p class="mt-2 text-xs text-slate-500">Auto-populated based on strategy selection</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Skill Definition Path</label>
                    <input type="text" wire:model="skill_doc_path" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" placeholder="skills/api-architect/SKILL.md">
                    <p class="mt-2 text-xs text-slate-500">Path to markdown skill definition (e.g., skills/laravel-specialist/SKILL.md)</p>
                </div>
            </div>
        </div>

        <!-- AI Model Configuration -->
        <div class="bg-gradient-to-br from-[#1a1a2e] to-[#12121f] rounded-2xl border border-[#2a2a40]/50 p-6 shadow-xl">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center text-green-400 border border-green-500/30">🧠</span>
                AI Model Configuration
            </h2>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Model *</label>
                    <input type="text" wire:model="model" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all" placeholder="qwen3-coder:latest">
                    @error('model') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Provider *</label>
                    <select wire:model="provider" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="ollama">Ollama (Local)</option>
                        <option value="openrouter">OpenRouter</option>
                        <option value="anthropic">Anthropic</option>
                        <option value="openai">OpenAI</option>
                    </select>
                    @error('provider') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <!-- Model Settings -->
            <div class="bg-slate-900/50 rounded-xl p-6 border border-white/5">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Model Settings</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Temperature</label>
                        <input type="number" wire:model="modelSettings.temperature" step="0.1" min="0" max="1" class="w-full px-3 py-2 bg-slate-800 border border-white/10 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Max Tokens</label>
                        <input type="number" wire:model="modelSettings.max_tokens" class="w-full px-3 py-2 bg-slate-800 border border-white/10 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5">Poll Interval (s)</label>
                        <input type="number" wire:model="modelSettings.poll_interval" min="10" class="w-full px-3 py-2 bg-slate-800 border border-white/10 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
            </div>
        </div>

        <!-- Runtime & Status -->
        <div class="bg-gradient-to-br from-[#1a1a2e] to-[#12121f] rounded-2xl border border-[#2a2a40]/50 p-6 shadow-xl">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center text-amber-400 border border-amber-500/30">🖥️</span>
                Runtime & Status
            </h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Runtime Location</label>
                    <select wire:model="runtime_location" class="w-full px-4 py-2.5 bg-slate-900/50 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="php">PHP/Laravel (Local)</option>
                        <option value="openclaw">OpenClaw Gateway</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-3 pt-8">
                    <input type="checkbox" wire:model="is_online" id="is_online" class="w-5 h-5 rounded bg-slate-900 border-white/10 text-purple-600 focus:ring-purple-500">
                    <label for="is_online" class="text-sm font-medium text-slate-300">
                        Start Online (Active)
                    </label>
                </div>
            </div>
        </div>

        <!-- System Prompt -->
        <div class="bg-slate-800/50 rounded-2xl border border-white/10 p-6">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-pink-600/20 flex items-center justify-center text-pink-400">💬</span>
                System Prompt
            </h2>
            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Custom System Prompt</label>
                <textarea wire:model="system_prompt" rows="6" class="w-full px-4 py-3 bg-slate-900/50 border border-white/10 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all font-mono text-sm" placeholder="You are a senior API architect specializing in REST and GraphQL..."></textarea>
                <p class="mt-2 text-xs text-slate-500">This will be combined with skill doc constraints automatically</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4 pt-6 border-t border-white/10">
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white rounded-xl font-semibold transition-all shadow-lg shadow-purple-500/25">
                Create Agent
            </button>
            <a href="{{ route('agents.index') }}" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-xl font-semibold transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
