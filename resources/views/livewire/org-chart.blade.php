<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">🏢 Organization Chart</h1>
            <p class="text-sm text-[#6b6b80] mt-1">Team hierarchy and model health status</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-xs text-[#6b6b80]">
                <span class="w-2 h-2 rounded-full bg-[#10b981] animate-pulse"></span>
                <span>Live</span>
            </div>
            <button
                wire:click="loadData"
                class="btn btn-secondary text-sm"
            >
                🔄 Refresh
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="flex flex-wrap gap-4">
        <div class="bg-[#1a1a2e] rounded-lg px-4 py-3 border border-[#2a2a40] w-40 card-glow">
            <div class="text-xl font-bold text-[#e4e4f0]">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-xs text-[#6b6b80]">Total Agents</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg px-4 py-3 border border-[#2a2a40] w-40 card-glow">
            <div class="text-xl font-bold text-[#10b981]">{{ $stats['online'] ?? 0 }}</div>
            <div class="text-xs text-[#6b6b80]">Online</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg px-4 py-3 border border-[#2a2a40] w-40 card-glow">
            <div class="text-xl font-bold text-[#6b6b80]">{{ $stats['offline'] ?? 0 }}</div>
            <div class="text-xs text-[#6b6b80]">Offline</div>
        </div>
        <div class="bg-[#1a1a2e] rounded-lg px-4 py-3 border border-[#2a2a40] w-40 card-glow">
            <div class="text-xl font-bold text-[#ef4444]">{{ $stats['error'] ?? 0 }}</div>
            <div class="text-xs text-[#6b6b80]">Errors</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-4 gap-6">
        <!-- Org Chart Tree -->
        <div class="col-span-3">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] overflow-hidden">
                <!-- Grid Background -->
                <div class="relative p-8 min-h-[500px]" style="background-image: linear-gradient(to right, #1f1f35 1px, transparent 1px), linear-gradient(to bottom, #1f1f35 1px, transparent 1px); background-size: 40px 40px;">
                    
                    @php
                        $statusColors = [
                            'online' => 'bg-[#10b981]',
                            'offline' => 'bg-[#6b6b80]',
                            'error' => 'bg-[#ef4444]',
                            'busy' => 'bg-[#f59e0b]',
                        ];
                        $modelColors = [
                            'GLM-5' => 'bg-[#7c3aed]/20 text-[#a78bfa]',
                            'Dolphin 3.0' => 'bg-[#06b6d4]/20 text-[#22d3ee]',
                            'Claude Haiku' => 'bg-[#f97316]/20 text-[#fb923c]',
                            'GPT-4o Mini' => 'bg-[#10b981]/20 text-[#34d399]',
                        ];
                    @endphp

                    @foreach($tree as $rootAgent)
                        <!-- ROOT LEVEL: CEO -->
                        <div class="flex flex-col items-center">
                            <!-- CEO Card -->
                            <div wire:click="selectAgent({{ $rootAgent['id'] }})" class="cursor-pointer transform transition-transform hover:scale-105">
                                <div class="bg-[#252542] border border-[#2a2a40] rounded-lg p-4 w-56 card-glow">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="relative">
                                            <div class="w-10 h-10 bg-[#7c3aed] rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                {{ strtoupper(substr($rootAgent['name'], 0, 1)) }}
                                            </div>
                                            <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 {{ $statusColors[$rootAgent['status']] ?? 'bg-[#6b6b80]' }} rounded-full border-2 border-[#252542] {{ $rootAgent['status'] === 'online' ? 'animate-pulse' : '' }}"></div>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-[#e4e4f0]">{{ $rootAgent['name'] }}</div>
                                            <div class="text-xs text-[#6b6b80] uppercase">{{ $rootAgent['role'] }}</div>
                                        </div>
                                    </div>
                                    @if($rootAgent['model'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $modelColors[$rootAgent['model']] ?? 'bg-[#252542] text-[#a0a0b8]' }}">{{ $rootAgent['model'] }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-[#252542] text-[#6b6b80]">Human</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Vertical connector -->
                            @if(!empty($rootAgent['children']))
                                <div class="w-0.5 h-10 bg-[#2a2a40]"></div>
                            @endif

                            <!-- LEVEL 1: Coordinator(s) -->
                            @if(!empty($rootAgent['children']))
                                <div class="flex flex-col items-center">
                                    @foreach($rootAgent['children'] as $level1)
                                        <!-- Coordinator Card -->
                                        <div wire:click="selectAgent({{ $level1['id'] }})" class="cursor-pointer transform transition-transform hover:scale-105 mb-6">
                                            <div class="bg-[#252542] border border-[#2a2a40] rounded-lg p-4 w-56 card-glow">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <div class="relative">
                                                        <div class="w-10 h-10 bg-[#7c3aed] rounded-full flex items-center justify-center text-white font-bold text-lg">
                                                            🌙
                                                        </div>
                                                        <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 {{ $statusColors[$level1['status']] ?? 'bg-[#6b6b80]' }} rounded-full border-2 border-[#252542] {{ $level1['status'] === 'online' ? 'animate-pulse' : '' }}"></div>
                                                    </div>
                                                    <div>
                                                        <div class="font-semibold text-[#e4e4f0]">{{ $level1['name'] }}</div>
                                                        <div class="text-xs text-[#6b6b80] uppercase">{{ $level1['role'] }}</div>
                                                    </div>
                                                </div>
                                                @if($level1['model'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $modelColors[$level1['model']] ?? 'bg-[#252542] text-[#a0a0b8]' }}">{{ $level1['model'] }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Vertical connector to Level 2 -->
                                        @if(!empty($level1['children']))
                                            <div class="w-0.5 h-10 bg-[#2a2a40] mb-4"></div>
                                        @endif

                                        <!-- LEVEL 2: Subagents (horizontal row) -->
                                        @if(!empty($level1['children']))
                                            <div class="flex justify-center items-start gap-6 flex-wrap">
                                                @foreach($level1['children'] as $level2)
                                                    <div class="flex flex-col items-center">
                                                        <!-- Vertical connector -->
                                                        <div class="w-0.5 h-6 bg-[#2a2a40]"></div>
                                                        <!-- Subagent Card -->
                                                        <div wire:click="selectAgent({{ $level2['id'] }})" class="cursor-pointer transform transition-transform hover:scale-105">
                                                            <div class="bg-[#252542] border border-[#2a2a40] rounded-lg p-3 w-40 card-glow">
                                                                <div class="flex items-center gap-2 mb-2">
                                                                    <div class="relative">
                                                                        <div class="w-8 h-8 bg-[#7c3aed]/50 rounded-full flex items-center justify-center text-sm">
                                                                            🤖
                                                                        </div>
                                                                        <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 {{ $statusColors[$level2['status']] ?? 'bg-[#6b6b80]' }} rounded-full border-2 border-[#252542]"></div>
                                                                    </div>
                                                                    <div>
                                                                        <div class="font-medium text-[#e4e4f0] text-sm">{{ $level2['name'] }}</div>
                                                                        <div class="text-xs text-[#6b6b80]">{{ $level2['role'] }}</div>
                                                                    </div>
                                                                </div>
                                                                @if($level2['model'])
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $modelColors[$level2['model']] ?? 'bg-[#252542] text-[#a0a0b8]' }}">{{ $level2['model'] }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Model Health Sidebar -->
        <div class="col-span-1">
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4 sticky top-24">
                <h3 class="text-sm font-semibold text-[#e4e4f0] mb-4">Model Health</h3>
                <div class="space-y-3">
                    <!-- GLM-5 -->
                    <div class="bg-[#252542] rounded-lg p-3 border border-[#2a2a40]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-[#a78bfa]">GLM-5</span>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <div class="text-[#6b6b80]">Tokens/s</div>
                                <div class="text-[#e4e4f0] font-medium">~45</div>
                            </div>
                            <div>
                                <div class="text-[#6b6b80]">Latency</div>
                                <div class="text-[#e4e4f0] font-medium">1-2s</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dolphin 3.0 -->
                    <div class="bg-[#252542] rounded-lg p-3 border border-[#2a2a40]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-[#22d3ee]">Dolphin 3.0</span>
                            <span class="badge badge-warning">Standby</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <div class="text-[#6b6b80]">Tokens/s</div>
                                <div class="text-[#e4e4f0] font-medium">~18</div>
                            </div>
                            <div>
                                <div class="text-[#6b6b80]">Latency</div>
                                <div class="text-[#e4e4f0] font-medium">8-12s</div>
                            </div>
                        </div>
                    </div>

                    <!-- Haiku -->
                    <div class="bg-[#252542] rounded-lg p-3 border border-[#2a2a40]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-[#fb923c]">Claude Haiku</span>
                            <span class="badge badge-info">Fallback</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <div class="text-[#6b6b80]">Tokens/s</div>
                                <div class="text-[#e4e4f0] font-medium">~80</div>
                            </div>
                            <div>
                                <div class="text-[#6b6b80]">Latency</div>
                                <div class="text-[#e4e4f0] font-medium">0.5-1s</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Selected Agent Modal -->
    @if($selectedAgent)
        <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50" wire:click="clearSelection">
            <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] shadow-xl max-w-md w-full mx-4 p-5" wire:click.stop>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#7c3aed] rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($selectedAgent->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#e4e4f0]">{{ $selectedAgent->name }}</h3>
                            <p class="text-xs text-[#6b6b80] uppercase">{{ $selectedAgent->role }}</p>
                        </div>
                    </div>
                    <button wire:click="clearSelection" class="p-1 text-[#6b6b80] hover:text-[#e4e4f0]">
                        ✕
                    </button>
                </div>
                
                <div class="space-y-3">
                    @if($selectedAgent->model)
                        <div class="bg-[#252542] rounded-lg p-3">
                            <span class="text-xs text-[#6b6b80] uppercase">Model</span>
                            <div class="text-sm text-[#e4e4f0] mt-1">{{ $selectedAgent->model }}</div>
                        </div>
                    @endif
                    
                    <div class="bg-[#252542] rounded-lg p-3">
                        <span class="text-xs text-[#6b6b80] uppercase">Status</span>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-2 h-2 rounded-full {{ $statusColors[$selectedAgent->status] ?? 'bg-[#6b6b80]' }}"></div>
                            <span class="text-sm text-[#e4e4f0] capitalize">{{ $selectedAgent->status }}</span>
                        </div>
                    </div>
                    
                    @if($selectedAgent->tasks->count() > 0)
                        <div class="border-t border-[#2a2a40] pt-3">
                            <span class="text-xs text-[#6b6b80] uppercase">Recent Tasks</span>
                            <div class="mt-2 space-y-2">
                                @foreach($selectedAgent->tasks->take(3) as $task)
                                    <div class="bg-[#252542] rounded-lg p-2 flex items-center justify-between">
                                        <span class="text-sm text-[#e4e4f0]">{{ $task->name }}</span>
                                        <span class="badge {{ $task->status === 'completed' ? 'badge-success' : ($task->status === 'running' ? 'badge-warning' : 'badge-info') }}">
                                            {{ ucfirst($task->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>