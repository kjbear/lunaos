<div class="project-detail space-y-6 pb-12">
    
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-[#6b6b80]">
        <a href="/projects" class="hover:text-[#7c3aed] transition-colors">Projects</a>
        <span>/</span>
        <span class="text-[#e4e4f0]">{{ $projectData['name'] }}</span>
    </nav>
    
    {{-- Project Header Card --}}
    <div class="relative overflow-hidden rounded-2xl border border-white/10" 
         style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.15), rgba(6, 182, 212, 0.1)); backdrop-filter: blur(20px);">
        <div class="absolute top-0 right-0 p-6 opacity-20">
            <span class="text-9xl">🚀</span>
        </div>
        
        <div class="relative p-8">
            <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-4xl">🚀</span>
                        <h1 class="text-3xl font-bold text-[#e4e4f0]">{{ $projectData['name'] }}</h1>
                    </div>
                    <p class="text-[#a1a1b8] text-lg leading-relaxed max-w-4xl">
                        {{ $projectData['description'] }}
                    </p>
                </div>
                
                {{-- Status & Health Badges --}}
                <div class="flex flex-col gap-2 items-end">
                    <select wire:model.live="projectData.status" 
                            class="px-4 py-2 bg-[#1a1a2e] border border-[#2a2a40] rounded-lg text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]">
                        <option value="planning">📋 Planning</option>
                        <option value="active">🔥 Active</option>
                        <option value="completed">✅ Completed</option>
                        <option value="archived">📦 Archived</option>
                    </select>
                    
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium
                        @if($projectData['health'] === 'healthy') bg-emerald-500/20 text-emerald-400
                        @elseif($projectData['health'] === 'at_risk') bg-amber-500/20 text-amber-400
                        @else bg-red-500/20 text-red-400
                        @endif">
                        @if($projectData['health'] === 'healthy') ✓
                        @elseif($projectData['health'] === 'at_risk') ⚠
                        @else ✗ @endif
                        {{ ucfirst($projectData['health']) }}
                    </div>
                </div>
            </div>
            
            {{-- Progress Bar --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-[#6b6b80]">Overall Progress</span>
                    <span class="text-sm font-semibold text-[#7c3aed]">{{ $projectData['progress'] }}%</span>
                </div>
                <div class="h-3 bg-[#1a1a2e] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ease-out"
                         style="width: {{ $projectData['progress'] }}%; background: linear-gradient(90deg, #7c3aed, #06b6d4);">
                    </div>
                </div>
                <div class="mt-2">
                    <input type="range" min="0" max="100" wire:model.live="projectData.progress"
                           class="w-full h-2 bg-[#1a1a2e] rounded-lg appearance-none cursor-pointer accent-[#7c3aed]">
                </div>
            </div>
            
            {{-- Meta Information --}}
            <div class="grid grid-cols-4 gap-4 pt-6 border-t border-white/10">
                <div>
                    <div class="text-xs text-[#6b6b80] mb-1">Owner</div>
                    <div class="text-sm font-medium text-[#e4e4f0] capitalize">{{ $projectData['owner'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#6b6b80] mb-1">Created</div>
                    <div class="text-sm text-[#e4e4f0]">{{ $projectData['created_at'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#6b6b80] mb-1">Updated</div>
                    <div class="text-sm text-[#e4e4f0]">{{ $projectData['updated_at'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#6b6b80] mb-1">Repository</div>
                    @if($projectData['repo_url'])
                        <a href="{{ $projectData['repo_url'] }}" target="_blank" 
                           class="text-sm text-[#06b6d4] hover:underline">View Repo →</a>
                    @else
                        <div class="text-sm text-[#6b6b80]">Not configured</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Stats Bar - Horizontal Row --}}
    <div class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[140px] group relative overflow-hidden bg-gradient-to-br from-slate-500/10 to-gray-500/10 backdrop-blur-sm rounded-2xl p-4 border border-slate-500/20 hover:border-slate-500/40 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-500/20 border border-slate-500/30 flex items-center justify-center text-xl flex-shrink-0">📋</div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-white">{{ $stats['total_requirements'] }}</p>
                    <p class="text-xs text-slate-300 font-semibold uppercase tracking-wider truncate">Total</p>
                </div>
            </div>
        </div>
        <div class="flex-1 min-w-[140px] group relative overflow-hidden bg-gradient-to-br from-emerald-500/10 to-green-500/10 backdrop-blur-sm rounded-2xl p-4 border border-emerald-500/20 hover:border-emerald-500/40 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl flex-shrink-0">✓</div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-white">{{ $stats['completed_requirements'] }}</p>
                    <p class="text-xs text-emerald-300 font-semibold uppercase tracking-wider truncate">Done</p>
                </div>
            </div>
        </div>
        <div class="flex-1 min-w-[140px] group relative overflow-hidden bg-gradient-to-br from-amber-500/10 to-orange-500/10 backdrop-blur-sm rounded-2xl p-4 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-xl flex-shrink-0">⚙️</div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-white">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-amber-300 font-semibold uppercase tracking-wider truncate">Active</p>
                </div>
            </div>
        </div>
        <div class="flex-1 min-w-[140px] group relative overflow-hidden bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm rounded-2xl p-4 border border-blue-500/20 hover:border-blue-500/40 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-xl flex-shrink-0">📌</div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-white">{{ $stats['ready'] }}</p>
                    <p class="text-xs text-blue-300 font-semibold uppercase tracking-wider truncate">Ready</p>
                </div>
            </div>
        </div>
        <div class="flex-1 min-w-[140px] group relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm rounded-2xl p-4 border border-purple-500/20 hover:border-purple-500/40 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-xl flex-shrink-0">👥</div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold text-white">{{ $stats['team_size'] }}</p>
                    <p class="text-xs text-purple-300 font-semibold uppercase tracking-wider truncate">Team</p>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Main Content Grid --}}
    <div class="grid grid-cols-3 gap-6">
        
        {{-- Left Column: Requirements (2/3 width) --}}
        <div class="col-span-2 space-y-4">
            
            {{-- Requirements Section --}}
            <div class="bg-[#1a1a2e]/60 backdrop-blur-sm border border-[#2a2a40] rounded-xl overflow-hidden">
                <div class="flex items-center justify-between p-5 border-b border-[#2a2a40]">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(6, 182, 212, 0.2));">
                            <span class="text-sm">📋</span>
                        </div>
                        <h2 class="text-lg font-semibold text-[#e4e4f0]">Requirements</h2>
                        <span class="px-2.5 py-0.5 bg-[#7c3aed]/20 text-[#7c3aed] rounded-full text-xs font-medium">
                            {{ count($requirements) }}
                        </span>
                    </div>
                    <button wire:click="$set('showNewRequirementModal', true)"
                            class="px-4 py-2 bg-[#7c3aed] text-white rounded-lg hover:bg-[#6d28d9] transition-colors text-sm font-medium">
                        + Add Requirement
                    </button>
                </div>
                
                <div class="divide-y divide-[#2a2a40]">
                    @forelse($requirements as $req)
                        <div class="p-5 hover:bg-[#2a2a40]/30 transition-colors">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-[#e4e4f0] font-medium mb-1">{{ $req['title'] }}</h3>
                                    @if($req['description'])
                                        <p class="text-[#6b6b80] text-sm">{{ $req['description'] }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    {{-- Priority Badge --}}
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        @if($req['priority'] === 'critical') bg-red-500/20 text-red-400
                                        @elseif($req['priority'] === 'high') bg-amber-500/20 text-amber-400
                                        @elseif($req['priority'] === 'medium') bg-blue-500/20 text-blue-400
                                        @else bg-gray-500/20 text-gray-400
                                        @endif">
                                        {{ ucfirst($req['priority']) }}
                                    </span>
                                    
                                    {{-- Status Badge --}}
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        @if($req['status'] === 'completed') bg-emerald-500/20 text-emerald-400
                                        @elseif($req['status'] === 'in_progress') bg-amber-500/20 text-amber-400
                                        @elseif($req['status'] === 'ready') bg-blue-500/20 text-blue-400
                                        @else bg-gray-500/20 text-gray-400
                                        @endif">
                                        {{ str_replace('_', ' ', ucfirst($req['status'])) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-[#6b6b80]">
                                <span>By {{ $req['created_by'] ?? 'Unknown' }}</span>
                                <span>•</span>
                                <span>{{ $req['created_at'] ?? 'Just now' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <div class="text-5xl mb-4 opacity-50">📋</div>
                            <p class="text-[#6b6b80]">No requirements yet. Add the first one!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        {{-- Right Column: Team & Activity (1/3 width) --}}
        <div class="space-y-4">
            
            {{-- Team Assignments --}}
            <div class="bg-[#1a1a2e]/60 backdrop-blur-sm border border-[#2a2a40] rounded-xl overflow-hidden">
                <div class="p-5 border-b border-[#2a2a40]">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(6, 182, 212, 0.2));">
                            <span class="text-sm">👥</span>
                        </div>
                        <h2 class="text-lg font-semibold text-[#e4e4f0]">Team</h2>
                    </div>
                </div>
                
                <div class="divide-y divide-[#2a2a40]">
                    @forelse($assignments as $assignment)
                        <div class="p-4 flex items-center gap-3 hover:bg-[#2a2a40]/30 transition-colors">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold
                                @if(str_contains($assignment['persona_name'], 'Dave')) bg-purple-500/20 text-purple-400
                                @elseif(str_contains($assignment['persona_name'], 'Sam')) bg-emerald-500/20 text-emerald-400
                                @elseif(str_contains($assignment['persona_name'], 'Chen')) bg-cyan-500/20 text-cyan-400
                                @elseif(str_contains($assignment['persona_name'], 'Maya')) bg-pink-500/20 text-pink-400
                                @else bg-[#2a2a40] text-[#6b6b80]
                                @endif">
                                {{ substr($assignment['persona_name'], 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[#e4e4f0] truncate">{{ $assignment['persona_name'] }}</div>
                                <div class="text-xs text-[#6b6b80] truncate">{{ $assignment['role'] }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="text-4xl mb-3 opacity-50">👥</div>
                            <p class="text-[#6b6b80] text-sm">No team members assigned</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            {{-- Recent Activity --}}
            <div class="bg-[#1a1a2e]/60 backdrop-blur-sm border border-[#2a2a40] rounded-xl overflow-hidden">
                <div class="p-5 border-b border-[#2a2a40]">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(6, 182, 212, 0.2));">
                            <span class="text-sm">⚡</span>
                        </div>
                        <h2 class="text-lg font-semibold text-[#e4e4f0]">Recent Activity</h2>
                    </div>
                </div>
                
                <div class="divide-y divide-[#2a2a40]">
                    @forelse($activities as $activity)
                        <div class="p-4 hover:bg-[#2a2a40]/30 transition-colors">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-2 h-2 rounded-full 
                                    @if($activity['action'] === 'completed') bg-emerald-400
                                    @elseif($action['action'] === 'failed') bg-red-400
                                    @else bg-blue-400
                                    @endif">
                                </div>
                                <span class="text-sm text-[#e4e4f0]">{{ ucfirst($activity['action']) }}</span>
                            </div>
                            <div class="text-xs text-[#6b6b80] pl-5">
                                by {{ $activity['agent_name'] }} • {{ $activity['created_at'] }}
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="text-4xl mb-3 opacity-50">⚡</div>
                            <p class="text-[#6b6b80] text-sm">No recent activity</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    {{-- New Requirement Modal --}}
    @if($showNewRequirementModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             wire:click="$set('showNewRequirementModal', false)">
            <div class="bg-[#1a1a2e] border border-[#2a2a40] rounded-2xl w-full max-w-lg shadow-2xl"
                 wire:click.stop>
                <div class="p-6 border-b border-[#2a2a40]">
                    <h3 class="text-xl font-semibold text-[#e4e4f0]">Add New Requirement</h3>
                    <p class="text-sm text-[#6b6b80] mt-1">Create a new requirement for this project</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[#e4e4f0] mb-2">Title</label>
                        <input type="text" wire:model="newRequirementTitle" 
                               class="w-full px-4 py-2.5 bg-[#2a2a40] border border-[#3a3a50] rounded-lg text-[#e4e4f0] 
                                      focus:outline-none focus:border-[#7c3aed] transition-colors"
                               placeholder="e.g., Install Durable Workflow package">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#e4e4f0] mb-2">Description</label>
                        <textarea wire:model="newRequirementDescription" rows="4"
                                  class="w-full px-4 py-2.5 bg-[#2a2a40] border border-[#3a3a50] rounded-lg text-[#e4e4f0] 
                                         focus:outline-none focus:border-[#7c3aed] transition-colors resize-none"
                                  placeholder="Detailed description of what needs to be done..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#e4e4f0] mb-2">Priority</label>
                        <select wire:model="newRequirementPriority"
                                class="w-full px-4 py-2.5 bg-[#2a2a40] border border-[#3a3a50] rounded-lg text-[#e4e4f0] 
                                       focus:outline-none focus:border-[#7c3aed] transition-colors">
                            <option value="low">🔽 Low</option>
                            <option value="medium">➖ Medium</option>
                            <option value="high">⬆️ High</option>
                            <option value="critical">🔥 Critical</option>
                        </select>
                    </div>
                </div>
                
                <div class="p-6 border-t border-[#2a2a40] flex justify-end gap-3">
                    <button wire:click="$set('showNewRequirementModal', false)"
                            class="px-5 py-2.5 text-[#e4e4f0] hover:bg-[#2a2a40] rounded-lg transition-colors font-medium">
                        Cancel
                    </button>
                    <button wire:click="createRequirement"
                            class="px-5 py-2.5 bg-[#7c3aed] text-white rounded-lg hover:bg-[#6d28d9] transition-colors font-medium">
                        Create Requirement
                    </button>
                </div>
            </div>
        </div>
    @endif
    
</div>
