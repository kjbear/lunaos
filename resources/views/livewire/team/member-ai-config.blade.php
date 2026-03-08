<div class="member-ai-config space-y-6">
    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('team.show', $member->id) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to Member</span>
        </a>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">AI Configuration</h1>
            <p class="text-slate-400 mt-1">Configure AI behavior for {{ $member->name }}</p>
        </div>
        <button type="button" wire:click="resetToDefaults" 
                class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
            ↻ Reset to Defaults
        </button>
    </div>

    <form wire:submit="save" class="space-y-8">
        {{-- Model Settings --}}
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>🧠</span> Model Settings
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Model</label>
                    <select wire:model="model" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                        @foreach($availableModels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('model')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Response Style</label>
                    <select wire:model="responseStyle" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                        @foreach($responseStyles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('responseStyle')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Generation Parameters --}}
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>⚙️</span> Generation Parameters
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Temperature
                        <span class="text-slate-500 text-xs ml-2">({{ $temperature }})</span>
                    </label>
                    <input type="range" wire:model.live="temperature" min="0" max="2" step="0.1"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    <div class="flex justify-between text-xs text-slate-500 mt-1">
                        <span>Precise</span>
                        <span>Creative</span>
                    </div>
                    @error('temperature')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Max Tokens
                    </label>
                    <input type="number" wire:model="maxTokens" min="1" max="128000"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                    @error('maxTokens')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Top P
                        <span class="text-slate-500 text-xs ml-2">({{ $topP }})</span>
                    </label>
                    <input type="range" wire:model.live="topP" min="0" max="1" step="0.05"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    @error('topP')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Frequency Penalty
                        <span class="text-slate-500 text-xs ml-2">({{ $frequencyPenalty }})</span>
                    </label>
                    <input type="range" wire:model.live="frequencyPenalty" min="-2" max="2" step="0.1"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    @error('frequencyPenalty')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Presence Penalty
                        <span class="text-slate-500 text-xs ml-2">({{ $presencePenalty }})</span>
                    </label>
                    <input type="range" wire:model.live="presencePenalty" min="-2" max="2" step="0.1"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    @error('presencePenalty')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Prompts & Instructions --}}
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>📝</span> Prompts & Instructions
            </h2>
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">System Prompt</label>
                    <textarea wire:model="systemPrompt" rows="4"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors font-mono text-sm"
                              placeholder="Enter the base system prompt for this AI team member..."></textarea>
                    @error('systemPrompt')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Persona Description</label>
                    <textarea wire:model="personaDescription" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors text-sm"
                              placeholder="Describe the persona, character traits, and communication style..."></textarea>
                    @error('personaDescription')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Special Instructions</label>
                    <textarea wire:model="specialInstructions" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors text-sm"
                              placeholder="Any specific instructions or edge cases..."></textarea>
                    @error('specialInstructions')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Capabilities --}}
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>🛠️</span> Capabilities
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($availableCapabilities as $key => $label)
                    <button type="button" wire:click="toggleCapability('{{ $key }}')"
                            @class([
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all border',
                                in_array($key, $capabilities) 
                                    ? 'bg-purple-500/20 text-purple-300 border-purple-500/30' 
                                    : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10'
                            ])>
                        @if(in_array($key, $capabilities)) ✓ @endif
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @error('capabilities')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        {{-- Task Settings --}}
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>📋</span> Task Settings
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Max Concurrent Tasks</label>
                    <input type="number" wire:model="maxConcurrentTasks" min="1" max="10"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                    @error('maxConcurrentTasks')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Priority Level</label>
                    <select wire:model="priorityLevel" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                        @foreach($priorityLevels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priorityLevel')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Auto-Assign</label>
                    <div class="flex items-center gap-3 mt-3">
                        <button type="button" wire:click="$toggle('autoAssign')"
                                @class([
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    $autoAssign ? 'bg-purple-500' : 'bg-white/20'
                                ])>
                            <span @class([
                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                $autoAssign ? 'translate-x-6' : 'translate-x-1'
                            ])></span>
                        </button>
                        <span class="text-slate-400 text-sm">
                            {{ $autoAssign ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('team.show', $member->id) }}" 
               class="px-6 py-2 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
                Cancel
            </a>
            <button type="submit" wire:loading.attr="disabled"
                    @class([
                        'px-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-all font-medium',
                        'opacity-50 cursor-not-allowed' => false
                    ])>
                <span wire:loading.remove>💾 Save Configuration</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </form>
</div>