<div class="team-index space-y-6" x-data="{ teamView: $wire.entangle('view') }" x-init="
    // Restore view preference from localStorage
    const stored = localStorage.getItem('lunaos.team.view');
    if (stored && (stored === 'card' || stored === 'list')) {
        $wire.set('view', stored);
    }
">
    {{-- Polished Page Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">👥</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Team</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Manage your team of agents, personas, and board members</p>
                </div>
            </div>
            <a href="{{ route('team.create') }}" 
               class="group flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold hover:from-purple-500 hover:to-pink-500 transition-all duration-300 shadow-lg shadow-purple-500/25">
                <span>+</span>
                <span>Add Member</span>
            </a>
        </div>
    </header>
    
    {{-- Stats Bar --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="group relative overflow-hidden bg-gradient-to-br from-slate-500/10 to-gray-500/10 backdrop-blur-sm rounded-2xl p-5 border border-slate-500/20 hover:border-slate-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-slate-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-slate-500/20 border border-slate-500/30 flex items-center justify-center text-xl">👥</div>
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
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl">🟢</div>
                    <div>
                        <p class="text-xs text-emerald-300 font-semibold uppercase tracking-wider mb-0.5">Active</p>
                        <p class="text-2xl font-bold text-white">{{ ($stats['by_status']['active'] ?? 0) + ($stats['by_status']['online'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm rounded-2xl p-5 border border-blue-500/20 hover:border-blue-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-xl">🤖</div>
                    <div>
                        <p class="text-xs text-blue-300 font-semibold uppercase tracking-wider mb-0.5">Agents</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['workers'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm rounded-2xl p-5 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-xl">🎭</div>
                    <div>
                        <p class="text-xs text-purple-300 font-semibold uppercase tracking-wider mb-0.5">Personas</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['personas'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-sm rounded-2xl p-5 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-xl">👔</div>
                    <div>
                        <p class="text-xs text-amber-300 font-semibold uppercase tracking-wider mb-0.5">Board</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['board_members'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Tab Filters --}}
    <section class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Filter by Type</h2>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex gap-1">
                    <button wire:click="switchTab('all')"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeTab === 'all' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                        All
                    </button>
                    <button wire:click="switchTab('agents')"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeTab === 'agents' || $activeTab === 'workers' ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                        🤖 Agents
                    </button>
                    <button wire:click="switchTab('personas')"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeTab === 'personas' ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                        🎭 Personas
                    </button>
                    <button wire:click="switchTab('board-members')"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeTab === 'board-members' ? 'bg-gradient-to-r from-amber-600 to-orange-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                        👔 Board
                    </button>
                </div>
                
                {{-- Status Filter --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Status:</span>
                    <select wire:model.live="filter" class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:border-purple-400 focus:outline-none">
                        <option value="all">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Not Active</option>
                        <option value="online">Online Only</option>
                    </select>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Search Bar + View Toggle + Pagination --}}
    <section class="mb-6">
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
            <div class="flex flex-wrap items-center gap-4">
                {{-- Search --}}
                <div class="flex-1 min-w-[200px] relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search team members by name, title, or email..."
                           class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2.5 text-slate-300 placeholder-slate-500 focus:border-cyan-400/50 focus:outline-none transition-colors">
                </div>
                
                {{-- View Toggle --}}
                <div class="flex items-center gap-1 bg-white/5 rounded-lg p-1 border border-white/10">
                    <button x-on:click="$wire.setView('card')" 
                            :class="view === 'card' ? 'bg-purple-600 text-white' : 'text-slate-400 hover:text-white hover:bg-white/10'"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-all flex items-center gap-1.5">
                        <span>▤</span>
                        <span>Card</span>
                    </button>
                    <button x-on:click="$wire.setView('list')" 
                            :class="view === 'list' ? 'bg-purple-600 text-white' : 'text-slate-400 hover:text-white hover:bg-white/10'"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-all flex items-center gap-1.5">
                        <span>☰</span>
                        <span>List</span>
                    </button>
                </div>
                
                {{-- Per Page Dropdown --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">Show:</span>
                    <select wire:model.live="perPage" class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:border-purple-400 focus:outline-none">
                        <option value="10">10/page</option>
                        <option value="20">20/page</option>
                        <option value="50">50/page</option>
                        <option value="100">100/page</option>
                    </select>
                </div>
            </div>
        </div>
    </section>
    
    {{-- Members Grid/List --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                {{ $activeTab === 'all' ? 'All Members' : ($activeTab === 'agents' || $activeTab === 'workers' ? 'Agents' : ($activeTab === 'personas' ? 'Personas' : 'Board Members')) }}
            </h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">{{ $members->total() }} members</span>
        </div>
        
        {{-- Card View (3 per row) --}}
        <template x-if="view === 'card'">
            <div>
                {{-- Agents grid layout --}}
                @if($activeTab === 'agents' || $activeTab === 'workers' || $activeTab === 'all')
                @if($activeTab !== 'all')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($members as $member)
                        @include('livewire.team._member-card-worker', ['member' => $member])
                    @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                        <div class="text-5xl mb-4 opacity-50">👥</div>
                        <p class="text-slate-400 font-semibold">No team members</p>
                        @if($search)
                        <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filter.</p>
                        @endif
                    </div>
                    @endforelse
                </div>
                @else
                {{-- All members - card view --}}
                @php
                    $workers = $members->where('type', 'workers');
                    $personas = $members->where('type', 'personas');
                    $boardMembers = $members->where('type', 'board-members');
                @endphp
                
                @if($workers->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                        <span>🤖</span> Agents
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($workers as $member)
                            @include('livewire.team._member-card-worker', ['member' => $member])
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($personas->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                        <span>🎭</span> Personas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($personas as $member)
                            @include('livewire.team._member-card-persona', ['member' => $member])
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($boardMembers->count() > 0)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                        <span>👔</span> Board Members
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($boardMembers as $member)
                            @include('livewire.team._member-card-board', ['member' => $member])
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($workers->isEmpty() && $personas->isEmpty() && $boardMembers->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                    <div class="text-5xl mb-4 opacity-50">👥</div>
                    <p class="text-slate-400 font-semibold">No team members</p>
                    @if($search)
                    <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filter.</p>
                    @else
                    <p class="text-sm text-slate-500 mt-2">Add your first team member to get started!</p>
                    @endif
                </div>
                @endif
                @endif
                @endif
                
                {{-- Personas card grid --}}
                @if($activeTab === 'personas')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($members as $member)
                        @include('livewire.team._member-card-persona', ['member' => $member])
                    @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                        <div class="text-5xl mb-4 opacity-50">👥</div>
                        <p class="text-slate-400 font-semibold">No team members</p>
                        @if($search)
                        <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filter.</p>
                        @endif
                    </div>
                    @endforelse
                </div>
                @endif
                
                {{-- Board members card grid --}}
                @if($activeTab === 'board-members')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($members as $member)
                        @include('livewire.team._member-card-board', ['member' => $member])
                    @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                        <div class="text-5xl mb-4 opacity-50">👥</div>
                        <p class="text-slate-400 font-semibold">No team members</p>
                        @if($search)
                        <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filter.</p>
                        @endif
                    </div>
                    @endforelse
                </div>
                @endif
            </div>
        </template>
        
        {{-- List View --}}
        <template x-if="view === 'list'">
            <div>
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
                    {{-- Table Header --}}
                    <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-slate-800/50 border-b border-white/10 text-sm font-semibold text-slate-300">
                        <div class="col-span-4">Member</div>
                        <div class="col-span-2">Type</div>
                        <div class="col-span-2">Status</div>
                        <div class="col-span-2">Model</div>
                        <div class="col-span-2 text-right">Actions</div>
                    </div>
                    
                    {{-- Table Rows --}}
                    @forelse($members as $member)
                    <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-white/5 hover:bg-slate-800/30 transition-colors">
                        <div class="col-span-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br 
                                {{ $member->type === 'workers' ? 'from-blue-500/20 to-cyan-500/20 border-blue-500/30' : 
                                   ($member->type === 'personas' ? 'from-purple-500/20 to-pink-500/20 border-purple-500/30' : 
                                    'from-amber-500/20 to-orange-500/20 border-amber-500/30') }} 
                                border flex items-center justify-center text-xl flex-shrink-0">
                                {{ $member->emoji ?? ($member->type === 'workers' ? '🤖' : ($member->type === 'personas' ? '🎭' : '👔')) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-white truncate">{{ $member->name }}</p>
                                @if($member->title)
                                <p class="text-xs text-slate-400 truncate">{{ $member->title }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-span-2 flex items-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $member->type === 'workers' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 
                                   ($member->type === 'personas' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 
                                    'bg-amber-500/20 text-amber-400 border border-amber-500/30') }}">
                                {{ $member->type === 'workers' ? 'Agent' : ($member->type === 'personas' ? 'Persona' : 'Board') }}
                            </span>
                        </div>
                        <div class="col-span-2 flex items-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ in_array($member->status, ['active', 'online']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30' }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </div>
                        <div class="col-span-2 flex items-center text-sm text-slate-400">
                            {{ $member->model ? ucfirst($member->model) : '—' }}
                        </div>
                        <div class="col-span-2 flex items-center justify-end gap-2">
                            <a href="{{ route('team.show', $member->id) }}" 
                               class="px-3 py-1.5 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10">
                                View
                            </a>
                            @if(!in_array($member->name, ['dave', 'sam', 'chen']))
                            <button wire:click="delete('{{ $member->id }}')" 
                                    wire:confirm="Are you sure?"
                                    class="px-3 py-1.5 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30">
                                🗑️
                            </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="text-5xl mb-4 opacity-50">👥</div>
                        <p class="text-slate-400 font-semibold">No team members</p>
                        @if($search)
                        <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filter.</p>
                        @endif
                    </div>
                    @endforelse
                </div>
            </div>
        </template>
        
        {{-- Pagination --}}
        @if($members->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-slate-400">
                Showing {{ $members->firstItem() ?? 0 }} - {{ $members->lastItem() ?? 0 }} of {{ $members->total() }} members
            </div>
            {{ $members->links('pagination::tailwind') }}
        </div>
        @endif
    </section>
</div>