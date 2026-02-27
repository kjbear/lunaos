<div class="subagent-monitor" wire:poll.5s="loadStatus">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-[#e4e4f0]">Subagent Status</h3>
        <div class="flex items-center gap-2">
            <button wire:click="loadStatus" class="p-1.5 text-[#6b6b80] hover:text-[#e4e4f0] transition-colors rounded hover:bg-[#252542]">
                🔄
            </button>
            <label class="flex items-center gap-2 text-xs text-[#6b6b80]">
                <input type="checkbox" wire:model="autoRefresh" class="rounded border-[#3a3a50] bg-[#1a1a2e]">
                Auto
            </label>
        </div>
    </div>

    <!-- Agents Grid -->
    <div class="grid grid-cols-3 gap-2 mb-4">
        @foreach($agents as $agent)
        <div 
            wire:click="selectAgent('{{ $agent['id'] }}')"
            class="agent-card {{ $agent['status'] }} {{ $selectedAgent === $agent['id'] ? 'ring-2 ring-cyan-500' : '' }} bg-[#1a1a2e] rounded-lg p-2 cursor-pointer hover:bg-[#252542] transition-all border border-[#2a2a40]"
        >
            <div class="flex items-center gap-2">
                <div class="text-xl">{{ $agent['avatar'] }}</div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm text-[#e4e4f0] truncate">{{ $agent['name'] }}</div>
                    <div class="flex items-center gap-1">
                        @if($agent['status'] === 'running')
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="text-xs text-green-400">Running</span>
                        @elseif($agent['status'] === 'online')
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs text-blue-400">Online</span>
                        @else
                            <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                            <span class="text-xs text-[#6b6b80]">Idle</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Activity -->
    <div class="border-t border-[#2a2a40] pt-3">
        <h4 class="text-xs font-semibold text-[#6b6b80] uppercase mb-2">Recent Activity</h4>
        <div class="space-y-1 max-h-40 overflow-y-auto">
            @forelse($recentActivity as $activity)
            <div class="flex items-center gap-2 text-xs py-1">
                <span class="text-[#6b6b80] w-10">{{ $activity['time'] }}</span>
                <span class="font-medium text-[#a0a0b8] w-12">{{ $activity['agent'] }}</span>
                <span class="{{ $activity['status'] === 'done' ? 'text-green-400' : 'text-yellow-400' }}">●</span>
                <span class="text-[#e4e4f0] truncate">{{ $activity['task'] }}</span>
            </div>
            @empty
            <div class="text-xs text-[#6b6b80]">No recent activity</div>
            @endforelse
        </div>
    </div>

    <!-- Selected Agent Detail -->
    @if($selectedAgent)
    @php($selected = collect($agents)->firstWhere('id', $selectedAgent))
    <div class="mt-3 p-3 bg-[#252542] rounded-lg border border-[#3a3a50]">
        <div class="flex items-center gap-3">
            <div class="text-3xl">{{ $selected['avatar'] }}</div>
            <div>
                <div class="font-semibold text-[#e4e4f0]">{{ $selected['name'] }}</div>
                <div class="text-xs text-[#6b6b80]">{{ $selected['role'] }}</div>
            </div>
        </div>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-xs px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-400">{{ $selected['model'] }}</span>
            <span class="text-xs text-[#6b6b80]">ID: {{ $selected['id'] }}</span>
        </div>
    </div>
    @endif
</div>
