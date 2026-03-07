<div class="projects-index space-y-6">
    {{-- Polished Page Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">📊</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Projects</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Manage projects, requirements, and agent assignments</p>
                </div>
            </div>
            <button wire:click="$set('showNewProjectModal', true)" 
                    class="group flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold hover:from-purple-500 hover:to-pink-500 transition-all duration-300 shadow-lg shadow-purple-500/25">
                <span>+</span>
                <span>New Project</span>
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
                    <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl">✅</div>
                    <div>
                        <p class="text-xs text-emerald-300 font-semibold uppercase tracking-wider mb-0.5">Active</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['active'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-sm rounded-2xl p-5 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-xl">📝</div>
                    <div>
                        <p class="text-xs text-amber-300 font-semibold uppercase tracking-wider mb-0.5">Planning</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['planning'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm rounded-2xl p-5 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-xl">🎉</div>
                    <div>
                        <p class="text-xs text-purple-300 font-semibold uppercase tracking-wider mb-0.5">Completed</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['completed'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-500/10 to-blue-500/10 backdrop-blur-sm rounded-2xl p-5 border border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-xl">❤️</div>
                    <div>
                        <p class="text-xs text-indigo-300 font-semibold uppercase tracking-wider mb-0.5">By Health</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5 ml-14">
                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-lg text-xs font-semibold border border-emerald-500/30">{{ $stats['by_health']['healthy'] ?? 0 }} ✓</span>
                    <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-lg text-xs font-semibold border border-amber-500/30">{{ $stats['by_health']['at_risk'] ?? 0 }} ⚠</span>
                    <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded-lg text-xs font-semibold border border-red-500/30">{{ $stats['by_health']['blocked'] ?? 0 }} ✗</span>
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
                <div class="flex-1 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search projects by name or description..."
                           class="w-full bg-white/5 border border-white/10 rounded-lg pl-10 pr-4 py-2.5 text-slate-300 placeholder-slate-500 focus:border-cyan-400/50 focus:outline-none transition-colors">
                </div>
                <div class="flex gap-1">
                    @foreach(['all', 'active', 'planning', 'completed', 'archived'] as $f)
                    <button wire:click="filterBy('{{ $f }}')"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $filter === $f ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'bg-white/5 text-slate-400 hover:bg-white/10' }}">
                        {{ ucfirst($f) }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    
    {{-- Project Cards --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Projects</h2>
            <span class="px-2.5 py-0.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-400">{{ count($projects) }} projects</span>
        </div>
        
        <div class="space-y-4">
            @forelse($projects as $project)
            <div class="project-card group bg-slate-900/60 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-purple-400/30 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400/20 to-purple-500/20 border border-white/10 flex items-center justify-center text-xl">📊</div>
                            <a href="{{ route('projects.show', $project['id']) }}" class="text-lg font-bold text-white hover:text-purple-400 transition-colors">
                                {{ $project['name'] }}
                            </a>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $project['status'] === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($project['status'] === 'planning' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-purple-500/20 text-purple-400 border border-purple-500/30') }}">
                                {{ ucfirst($project['status']) }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $project['health'] === 'healthy' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($project['health'] === 'at_risk' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30') }}">
                                {{ ucfirst(str_replace('_', ' ', $project['health'])) }}
                            </span>
                        </div>
                        @if($project['description'])
                        <p class="text-sm text-slate-400 mt-2">{{ $project['description'] }}</p>
                        @endif
                    </div>
                    <div class="text-right ml-6">
                        <div class="text-3xl font-bold text-white mb-1">{{ $project['progress'] }}%</div>
                        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Progress</div>
                    </div>
                </div>
                
                {{-- Progress Bar --}}
                <div class="w-full bg-white/5 rounded-full h-2.5 mb-4 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 {{ $project['health'] === 'healthy' ? 'bg-gradient-to-r from-emerald-500 to-green-500' : ($project['health'] === 'at_risk' ? 'bg-gradient-to-r from-amber-500 to-orange-500' : 'bg-gradient-to-r from-red-500 to-pink-500') }}" style="width: {{ $project['progress'] }}%"></div>
                </div>
                
                {{-- Meta Info --}}
                <div class="flex items-center justify-between text-sm pt-4 border-t border-white/5">
                    <div class="flex items-center gap-4 text-slate-500">
                        <span class="flex items-center gap-1.5">📋 <span class="font-medium">{{ $project['requirements_count'] }} requirements</span></span>
                        <span class="flex items-center gap-1.5">⏱ <span class="font-medium">{{ $project['created_at'] }}</span></span>
                    </div>
                    @if($project['repo_url'])
                    <a href="{{ $project['repo_url'] }}" target="_blank" class="flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors font-medium">
                        🔗 <span>Repository</span>
                    </a>
                    @endif
                </div>
                
                {{-- Actions --}}
                <div class="flex gap-2 mt-4 pt-4 border-t border-white/5">
                    <a href="{{ route('projects.show', $project['id']) }}" 
                       class="px-4 py-2 text-sm bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all font-medium shadow-lg shadow-purple-500/20">
                        View Details
                    </a>
                    <button wire:click="editProject('{{ $project['id'] }}')"
                            class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                        Edit
                    </button>
                    <a href="{{ route('projects.requirements', $project['id']) }}" 
                       class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                        Requirements
                    </a>
                    @if($project['status'] !== 'archived')
                    <button wire:click="archiveProject('{{ $project['id'] }}')" 
                            wire:confirm="Archive this project?"
                            class="px-4 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all font-medium border border-red-500/30">
                        Archive
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-16 bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10">
                <div class="text-5xl mb-4 opacity-50">📭</div>
                <p class="text-slate-400 font-semibold">No projects found</p>
                @if($search)
                <p class="text-sm text-slate-500 mt-2">Try adjusting your search or filter.</p>
                @else
                <p class="text-sm text-slate-500 mt-2">Create your first project to get started!</p>
                @endif
            </div>
            @endforelse
        </div>
    </section>

    {{-- New Project Modal --}}
    @if($showNewProjectModal)	
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl border border-white/10 shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white">Create New Project</h2>
                        @if(session('board_decision'))
                        <p class="text-sm text-emerald-400 mt-1">🎯 Pre-filled from Board Decision</p>
                        @endif
                    </div>
                    <button wire:click="resetNewProjectForm" class="text-slate-400 hover:text-white transition-colors text-2xl">&times;</button>
                </div>
            </div>
            
            <form wire:submit="createProject" class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Project Name *</label>
                    <input type="text" wire:model="newProjectName" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none"
                           placeholder="e.g., LunaOS Dashboard"
                           {{ session('board_decision') ? 'autofocus' : '' }}>
                    @error('newProjectName') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Description</label>
                    <textarea wire:model="newProjectDescription" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none"
                              placeholder="Describe the project goals and scope..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Repository URL</label>
                    <input type="url" wire:model="newProjectRepo"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none"
                           placeholder="https://github.com/username/repo">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Initial Status</label>
                    <select wire:model="newProjectStatus"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none">
                        <option value="planning">📝 Planning</option>
                        <option value="active">✅ Active</option>
                        <option value="completed">🎉 Completed</option>
                    </select>
                </div>
                
                <div class="flex gap-3 pt-4 border-t border-white/10">
                    <button type="button" wire:click="resetNewProjectForm"
                            class="flex-1 px-4 py-2.5 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all font-medium shadow-lg shadow-purple-500/20">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit Project Modal --}}
    @if($showEditModal && $editingProject)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl border border-white/10 shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">Edit Project</h2>
                    <button wire:click="closeEditModal" class="text-slate-400 hover:text-white transition-colors text-2xl">&times;</button>
                </div>
            </div>
            
            <form wire:submit="updateProject" class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Project Name</label>
                        <input type="text" wire:model="editName"
                               class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Status</label>
                        <select wire:model="editStatus"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none">
                            <option value="planning">📝 Planning</option>
                            <option value="active">✅ Active</option>
                            <option value="completed">🎉 Completed</option>
                            <option value="archived">🗄️ Archived</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Description</label>
                    <textarea wire:model="editDescription" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Architecture Type</label>
                        <select wire:model="editArchitectureType"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none">
                            <option value="">Select...</option>
                            <option value="monolith">🏢 Monolith</option>
                            <option value="microservices">🔗 Microservices</option>
                            <option value="serverless">⚡ Serverless</option>
                            <option value="hybrid">🔄 Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-300 mb-2">Health</label>
                        <select wire:model="editHealth"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none">
                            <option value="healthy">✅ Healthy</option>
                            <option value="at_risk">⚠️ At Risk</option>
                            <option value="blocked">🚫 Blocked</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Technologies</label>
                    <input type="text" wire:model="editTechnologiesStr"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none"
                           placeholder="Laravel, Vue, PostgreSQL (comma-separated)">
                    <p class="text-xs text-slate-500 mt-1">Separate technologies with commas</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Repository URL</label>
                    <input type="url" wire:model="editRepoUrl"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none">
                </div>
                
                <div class="flex gap-3 pt-4 border-t border-white/10">
                    <button type="button" wire:click="closeEditModal"
                            class="flex-1 px-4 py-2.5 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all font-medium shadow-lg shadow-purple-500/20">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
