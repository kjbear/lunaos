<div class="ai-config space-y-6">
    @if(!$member)
    <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
        <div class="text-5xl mb-4 opacity-50">⚙️</div>
        <p class="text-slate-400 font-semibold">No team member selected</p>
        <p class="text-slate-500 text-sm mt-2">Select a team member to configure their AI settings</p>
    </div>
    @else
    
    <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10">
        <form wire:submit="save" class="divide-y divide-white/10">
            
            {{-- Model Settings Section --}}
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/30 flex items-center justify-center text-lg">
                        🧠
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Model Settings</h3>
                        <p class="text-sm text-slate-400">Configure the AI model behavior</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Model Selection --}}
                    <div class="space-y-2">
                        <label for="model" class="block text-sm font-medium text-slate-300">
                            Model
                        </label>
                        <select 
                            id="model" 
                            wire:model="model"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-white/10 rounded-lg text-white focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors"
                        >
                            @foreach($availableModels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Max Tokens --}}
                    <div class="space-y-2">
                        <label for="maxTokens" class="block text-sm font-medium text-slate-300">
                            Max Tokens
                        </label>
                        <input 
                            type="number" 
                            id="maxTokens"
                            wire:model="maxTokens"
                            min="1" 
                            max="128000"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-white/10 rounded-lg text-white focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors"
                        >
                        <p class="text-xs text-slate-500">Maximum response length</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Temperature --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-300">Temperature</label>
                            <span class="text-sm font-mono text-purple-400">{{ number_format($temperature, 1) }}</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="temperature"
                            min="0" 
                            max="2" 
                            step="0.1"
                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer"
                        >
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Precise</span>
                            <span>Creative</span>
                        </div>
                    </div>
                    
                    {{-- Top P --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-300">Top P</label>
                            <span class="text-sm font-mono text-purple-400">{{ number_format($topP, 2) }}</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="topP"
                            min="0" 
                            max="1" 
                            step="0.05"
                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer"
                        >
                    </div>
                    
                    {{-- Frequency Penalty --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-300">Frequency Penalty</label>
                            <span class="text-sm font-mono text-purple-400">{{ number_format($frequencyPenalty, 1) }}</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="frequencyPenalty"
                            min="-2" 
                            max="2" 
                            step="0.1"
                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer"
                        >
                    </div>
                    
                    {{-- Presence Penalty --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-300">Presence Penalty</label>
                            <span class="text-sm font-mono text-purple-400">{{ number_format($presencePenalty, 1) }}</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="presencePenalty"
                            min="-2" 
                            max="2" 
                            step="0.1"
                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer"
                        >
                    </div>
                </div>
            </div>
            
            {{-- Persona & Prompt Section --}}
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 flex items-center justify-center text-lg">
                        🎭
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Persona & Prompt</h3>
                        <p class="text-sm text-slate-400">Define the AI's personality and behavior</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Persona Name --}}
                    <div class="space-y-2">
                        <label for="personaName" class="block text-sm font-medium text-slate-300">
                            Persona Name
                        </label>
                        <input 
                            type="text" 
                            id="personaName"
                            wire:model="personaName"
                            placeholder="e.g., Code Review Expert"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors"
                        >
                    </div>
                    
                    {{-- Response Style --}}
                    <div class="space-y-2">
                        <label for="responseStyle" class="block text-sm font-medium text-slate-300">
                            Response Style
                        </label>
                        <select 
                            id="responseStyle" 
                            wire:model="responseStyle"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-white/10 rounded-lg text-white focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors"
                        >
                            @foreach($responseStyles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                {{-- System Prompt --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="systemPrompt" class="block text-sm font-medium text-slate-300">
                            System Prompt
                        </label>
                        <span class="text-xs text-slate-500">Markdown supported</span>
                    </div>
                    <textarea 
                        id="systemPrompt"
                        wire:model="systemPrompt"
                        rows="5"
                        placeholder="Define the AI's role, personality, and behavior guidelines..."
                        class="w-full px-4 py-3 bg-slate-800/50 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors font-mono text-sm resize-y"
                    >{{ $systemPrompt }}</textarea>
                </div>
                
                {{-- Special Instructions --}}
                <div class="space-y-2">
                    <label for="specialInstructions" class="block text-sm font-medium text-slate-300">
                        Special Instructions
                    </label>
                    <textarea 
                        id="specialInstructions"
                        wire:model="specialInstructions"
                        rows="3"
                        placeholder="Additional behavioral instructions or constraints..."
                        class="w-full px-4 py-3 bg-slate-800/50 border border-white/10 rounded-lg text-white placeholder-slate-500 focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors resize-y"
                    >{{ $specialInstructions }}</textarea>
                </div>
            </div>
            
            {{-- Capabilities Section --}}
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border border-emerald-500/30 flex items-center justify-center text-lg">
                        ⚡
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Capabilities</h3>
                        <p class="text-sm text-slate-400">Define what this AI can do</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Skills Tag Input --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">Skills</label>
                        <div 
                            class="tag-input"
                            x-data="{
                                tags: {{ json_encode($skills) }},
                                newTag: '',
                                addTag() {
                                    const trimmed = this.newTag.trim();
                                    if (trimmed && !this.tags.includes(trimmed)) {
                                        this.tags.push(trimmed);
                                        this.newTag = '';
                                        @this.set('skills', this.tags);
                                    }
                                },
                                removeTag(index) {
                                    this.tags.splice(index, 1);
                                    @this.set('skills', this.tags);
                                }
                            }"
                        >
                            <div class="flex flex-wrap gap-2 p-3 min-h-[48px] bg-slate-800/50 border border-white/10 rounded-lg focus-within:border-purple-500/50 transition-colors">
                                <template x-for="(tag, index) in tags" :key="index">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-full text-sm">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(index)" class="ml-1 hover:text-red-400 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <input 
                                    type="text" 
                                    x-model="newTag" 
                                    @keydown.enter.prevent="addTag()"
                                    placeholder="Add skill..."
                                    class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-white placeholder-slate-500 text-sm outline-none"
                                >
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Press Enter to add</p>
                        </div>
                    </div>
                    
                    {{-- Specializations Tag Input --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">Specializations</label>
                        <div 
                            class="tag-input"
                            x-data="{
                                tags: {{ json_encode($specializations) }},
                                newTag: '',
                                addTag() {
                                    const trimmed = this.newTag.trim();
                                    if (trimmed && !this.tags.includes(trimmed)) {
                                        this.tags.push(trimmed);
                                        this.newTag = '';
                                        @this.set('specializations', this.tags);
                                    }
                                },
                                removeTag(index) {
                                    this.tags.splice(index, 1);
                                    @this.set('specializations', this.tags);
                                }
                            }"
                        >
                            <div class="flex flex-wrap gap-2 p-3 min-h-[48px] bg-slate-800/50 border border-white/10 rounded-lg focus-within:border-purple-500/50 transition-colors">
                                <template x-for="(tag, index) in tags" :key="index">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-full text-sm">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(index)" class="ml-1 hover:text-red-400 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <input 
                                    type="text" 
                                    x-model="newTag" 
                                    @keydown.enter.prevent="addTag()"
                                    placeholder="Add specialization..."
                                    class="flex-1 min-w-[120px] bg-transparent border-none focus:ring-0 text-white placeholder-slate-500 text-sm outline-none"
                                >
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Press Enter to add</p>
                        </div>
                    </div>
                    
                    {{-- Capabilities Checkboxes --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">Capabilities</label>
                        <div class="grid grid-cols-2 gap-2 p-3 bg-slate-800/50 border border-white/10 rounded-lg max-h-[200px] overflow-y-auto">
                            @foreach($availableCapabilities as $value => $label)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white/5 p-1.5 rounded transition-colors">
                                <input 
                                    type="checkbox" 
                                    value="{{ $value }}"
                                    wire:model="capabilities"
                                    class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-purple-500 focus:ring-purple-500/25 focus:ring-offset-0"
                                >
                                <span class="text-sm text-slate-300">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Operations Section --}}
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500/20 to-orange-500/20 border border-amber-500/30 flex items-center justify-center text-lg">
                        🔧
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Operations</h3>
                        <p class="text-sm text-slate-400">Configure operational parameters</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Availability Toggle --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">Availability</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model="isAvailable"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-500/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-3 text-sm {{ $isAvailable ? 'text-emerald-400' : 'text-slate-400' }}">
                                {{ $isAvailable ? 'Available' : 'Unavailable' }}
                            </span>
                        </label>
                    </div>
                    
                    {{-- Auto-Assign Toggle --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-300">Auto-Assign</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model="autoAssign"
                                class="sr-only peer"
                            >
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-500/25 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                            <span class="ml-3 text-sm {{ $autoAssign ? 'text-purple-400' : 'text-slate-400' }}">
                                {{ $autoAssign ? 'Enabled' : 'Disabled' }}
                            </span>
                        </label>
                    </div>
                    
                    {{-- Priority Level --}}
                    <div class="space-y-2">
                        <label for="priorityLevel" class="block text-sm font-medium text-slate-300">Priority Level</label>
                        <select 
                            id="priorityLevel" 
                            wire:model="priorityLevel"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-white/10 rounded-lg text-white focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors"
                        >
                            @foreach($priorityLevels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Capacity --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-300">Capacity</label>
                            <span class="text-sm font-mono text-purple-400">{{ $capacity }}%</span>
                        </div>
                        <input 
                            type="range" 
                            wire:model.live="capacity"
                            min="0" 
                            max="100" 
                            step="5"
                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer"
                        >
                    </div>
                    
                    {{-- Max Concurrent Tasks --}}
                    <div class="space-y-2">
                        <label for="maxConcurrentTasks" class="block text-sm font-medium text-slate-300">Max Concurrent Tasks</label>
                        <input 
                            type="number" 
                            id="maxConcurrentTasks"
                            wire:model="maxConcurrentTasks"
                            min="1" 
                            max="100"
                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-white/10 rounded-lg text-white focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors"
                        >
                    </div>
                </div>
            </div>
            
            {{-- Custom Metadata Section --}}
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-500/20 to-gray-500/20 border border-slate-500/30 flex items-center justify-center text-lg">
                        📋
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">Custom Metadata</h3>
                        <p class="text-sm text-slate-400">Additional configuration in JSON format</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="customMetadata" class="block text-sm font-medium text-slate-300">JSON Configuration</label>
                        @error('customMetadata')
                        <span class="text-xs text-red-400">{{ $message }}</span>
                        @enderror
                    </div>
                    <textarea 
                        id="customMetadata"
                        wire:model="customMetadata"
                        rows="6"
                        placeholder='{"custom_field": "value"}'
                        class="w-full px-4 py-3 bg-slate-800/50 border {{ $errors->has('customMetadata') ? 'border-red-500/50' : 'border-white/10' }} rounded-lg text-white placeholder-slate-500 focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/25 transition-colors font-mono text-sm resize-y"
                    >{{ $customMetadata }}</textarea>
                    <p class="text-xs text-slate-500">Enter valid JSON for additional configuration options</p>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="p-6 bg-slate-900/30 flex items-center justify-end gap-3">
                <button 
                    type="button"
                    wire:click="resetToDefaults"
                    class="px-4 py-2.5 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium"
                >
                    ↩️ Reset to Defaults
                </button>
                <button 
                    type="button"
                    onclick="if(confirm('Discard changes?')) { window.location.href = '{{ route('team.show', $member->id) }}' }"
                    class="px-4 py-2.5 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium"
                >
                    ✖️ Cancel
                </button>
                <button 
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 text-sm bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all font-medium shadow-lg shadow-purple-500/20 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>💾 Save Configuration</span>
                    <span wire:loading>Saving...</span>
                </button>
            </div>
        </form>
    </div>
    @endif
</div>