<div class="personas-index space-y-6">
    
    {{-- Polished Page Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-amber-500 flex items-center justify-center text-3xl shadow-xl">👥</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">HR — Personas</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Manage AI personas, subagents, and board members</p>
                </div>
            </div>
            
            <button wire:click="$set('showCreateModal', true)" 
                    class="group flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold hover:from-purple-500 hover:to-pink-500 transition-all duration-300 shadow-lg shadow-purple-500/25">
                <span>+ New Persona</span>
            </button>
        </div>
    </header>
    
    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="group relative overflow-hidden bg-gradient-to-br from-slate-500/10 to-gray-500/10 backdrop-blur-sm rounded-2xl p-5 border border-slate-500/20 hover:border-slate-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-slate-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-slate-500/20 border border-slate-500/30 flex items-center justify-center text-xl">📊</div>
                    <div>
                        <p class="text-xs text-slate-300 font-semibold uppercase tracking-wider mb-0.5">Total</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="group relative overflow-hidden bg-gradient-to-br from-emerald-500/10 to-green-500/10 backdrop-blur-sm rounded-2xl p-5 border border-emerald-500/20 hover:border-emerald-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl">✓</div>
                    <div>
                        <p class="text-xs text-emerald-300 font-semibold uppercase tracking-wider mb-0.5">Active</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['active'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm rounded-2xl p-5 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-xl">🤖</div>
                    <div>
                        <p class="text-xs text-purple-300 font-semibold uppercase tracking-wider mb-0.5">Subagents</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['subagents'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="group relative overflow-hidden bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-sm rounded-2xl p-5 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-xl">🎯</div>
                    <div>
                        <p class="text-xs text-amber-300 font-semibold uppercase tracking-wider mb-0.5">Board Members</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['board_members'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-500/10 to-blue-500/10 backdrop-blur-sm rounded-2xl p-5 border border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-xl">🧠</div>
                    <div>
                        <p class="text-xs text-indigo-300 font-semibold uppercase tracking-wider mb-0.5">By Model</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5 ml-14">
                    @foreach(($stats['by_model'] ?? []) as $model => $count)
                        <span class="px-2 py-1 bg-indigo-500/20 text-indigo-400 rounded-lg text-xs font-semibold border border-indigo-500/30">
                            {{ $count }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    {{-- Search and Filter Bar --}}
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Search & Filter</h2>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
            <div class="flex items-center gap-4">
                {{-- Search --}}
                <div class="flex-1 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search personas by name, inspiration, or prompt..."
                           class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2.5 text-sm text-slate-300 placeholder-slate-500 focus:border-purple-500/50 focus:outline-none transition-colors">
                </div>
                
                {{-- Filter Tabs --}}
                <div class="flex gap-1">
                    @foreach(['all', 'active', 'subagents', 'board', 'custom'] as $f)
                    <button wire:click="filterBy('{{ $f }}')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 {{ $filter === $f ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg shadow-purple-500/25' : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white' }}">
                        {{ ucfirst($f) }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    
    {{-- Persona Cards Section --}}
    <section class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Personas</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">{{ count($personas) }} personas</span>
        </div>
        
        <div class="space-y-4">
            @forelse($personas as $persona)
            <div class="persona-card group relative overflow-hidden bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10 hover:border-purple-500/40 hover:shadow-2xl hover:shadow-purple-500/10 transition-all duration-300">
                <div class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:from-purple-500/15 group-hover:to-pink-500/15 transition-all duration-500"></div>
                
                <div class="relative flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 flex items-center justify-center text-3xl shadow-lg">
                                {{ $persona['avatar'] }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-xl font-bold text-white">{{ $persona['name'] }}</h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $persona['status'] === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-gray-500/20 text-gray-400 border border-gray-500/30' }}">
                                        {{ ucfirst($persona['status']) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $persona['role'] === 'subagent' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : ($persona['role'] === 'board_member' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30') }}">
                                        {{ ucfirst(str_replace('_', ' ', $persona['role'])) }}
                                    </span>
                                </div>
                                @if($persona['inspiration'])
                                <p class="text-sm text-slate-400 italic">{{ $persona['inspiration'] }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $persona['model'] === 'dolphin' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' : ($persona['model'] === 'haiku' ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30' : 'bg-purple-500/20 text-purple-400 border border-purple-500/30') }}">
                                🧠 {{ ucfirst($persona['model']) }}
                            </span>
                        </div>
                    </div>
                
                @if($persona['metrics'])
                <div class="relative">
                    <div class="text-right">
                        @if($persona['role'] === 'subagent')
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-xs text-slate-400">Projects:</span>
                                <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded-lg text-sm font-semibold border border-purple-500/30">{{ $persona['metrics']['projects_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-xs text-slate-400">Tasks:</span>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-lg text-sm font-semibold border border-emerald-500/30">{{ $persona['metrics']['tasks_completed'] }}</span>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-xs text-slate-400">Success:</span>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-lg text-sm font-semibold border border-emerald-500/30">{{ $persona['metrics']['success_rate'] }}%</span>
                            </div>
                        </div>
                        @elseif($persona['role'] === 'board_member')
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-xs text-slate-400">Sessions:</span>
                                <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-lg text-sm font-semibold border border-amber-500/30">{{ $persona['metrics']['sessions_count'] }}</span>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <span class="text-xs text-slate-400">Decisions:</span>
                                <span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-sm font-semibold border border-blue-500/30">{{ $persona['metrics']['decisions_count'] }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            
            @if($persona['metrics'] || $persona['status'] === 'active')
            </div>
            <div class="flex gap-2 mt-6 pt-4 border-t border-white/10">
                <a href="{{ route('hr.workspace', $persona['id']) }}" 
                   class="flex items-center gap-1.5 px-4 py-2 text-sm bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-lg hover:from-cyan-500 hover:to-blue-500 transition-all shadow-lg shadow-purple-500/20">
                    <span>📊</span>
                    <span>View Workspace</span>
                </a>
                <button wire:click="edit('{{ $persona['id'] }}')" 
                        class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10">
                    ✏️ Edit
                </button>
                @if($persona['status'] === 'active')
                <button wire:click="deactivate('{{ $persona['id'] }}')" 
                        wire:confirm="Are you sure you want to deactivate {{ $persona['name'] }}?"
                        class="px-4 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30">
                    ✗ Deactivate
                </button>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12 text-[#6b6b80]">
            <p>No personas found.</p>
            @if($search)
            <p class="text-sm mt-2">Try adjusting your search or filter.</p>
            @endif
        </div>
        @endforelse
    </div>
    
    {{-- Create/Edit Modal --}}
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="resetForm">
        <div class="bg-[#1a1a2e] rounded-xl p-6 w-[90%] max-w-lg border border-[#2a2a40]" wire:click.stop>
            <h3 class="text-lg font-semibold text-[#e4e4f0] mb-4">
                {{ $showCreateModal ? 'Create New Persona' : 'Edit Persona' }}
            </h3>
            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">Name</label>
                        <input type="text" wire:model="personaName" 
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none">
                        @error('personaName')<span class="text-[#ef4444] text-sm">{{ $message }}</span>@enderror
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-[#6b6b80] mb-1">Role</label>
                            <select wire:model="personaRole" 
                                    class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none">
                                <option value="subagent">Subagent</option>
                                <option value="board_member">Board Member</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-[#6b6b80] mb-1">Model</label>
                            <select wire:model="personaModel" 
                                    class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none">
                                <option value="dolphin">Dolphin 3.0 (Local)</option>
                                <option value="haiku">Claude Haiku</option>
                                <option value="glm-5">GLM-5</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">Avatar Emoji</label>
                        <input type="text" wire:model="personaAvatar" maxlength="4"
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] text-2xl text-center focus:border-purple-500 focus:outline-none">
                    </div>
                    
                    @if($personaRole === 'board_member')
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">Inspiration</label>
                        <input type="text" wire:model="personaInspiration" 
                               placeholder="e.g., Steve Jobs - visionary, product-obsessed"
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none">
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">System Prompt</label>
                        <textarea wire:model="personaSystemPrompt" rows="4"
                                  class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] font-mono text-sm focus:border-purple-500 focus:outline-none"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" wire:click="resetForm" 
                            class="px-4 py-2 bg-[#6b6b80] text-white rounded-lg hover:bg-[#7b7b90] transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        {{ $showCreateModal ? 'Create' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>