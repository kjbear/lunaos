<div class="standup-container">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-[#e4e4f0]">Daily Standup</h2>
            <p class="text-sm text-[#6b6b80]">{{ $selectedDate }} • {{ $currentStandup['time'] ?? now()->format('H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="date" 
                   wire:model="selectedDate" 
                   wire:change="refreshStandup"
                   class="bg-[#1a1a2e] border border-[#2a2a40] rounded-lg px-3 py-2 text-[#e4e4f0] text-sm">
            <button wire:click="refreshStandup" 
                    class="flex items-center gap-2 px-4 py-2 bg-[#7c3aed] text-white rounded-lg hover:bg-[#6d28d9] transition-colors text-sm font-medium">
                🔄 Refresh
            </button>
        </div>
    </div>

    @if($currentStandup)
    <!-- Summary Card -->
    <div class="bg-[#1a1a2e] rounded-xl p-6 border border-[#2a2a40] mb-6">
        <h3 class="text-lg font-semibold text-[#e4e4f0] mb-2">Summary</h3>
        <p class="text-[#a0a0b8]">{{ $currentStandup['summary'] }}</p>
        
        <div class="grid grid-cols-4 gap-4 mt-4">
            <div class="text-center p-3 bg-[#12121f] rounded-lg">
                <div class="text-2xl font-bold text-[#e4e4f0]">{{ $currentStandup['total_tasks'] ?? 0 }}</div>
                <div class="text-xs text-[#6b6b80]">Total Tasks</div>
            </div>
            <div class="text-center p-3 bg-[#12121f] rounded-lg">
                <div class="text-2xl font-bold text-green-400">{{ $currentStandup['completed'] ?? 0 }}</div>
                <div class="text-xs text-[#6b6b80]">Completed</div>
            </div>
            <div class="text-center p-3 bg-[#12121f] rounded-lg">
                <div class="text-2xl font-bold text-yellow-400">{{ $currentStandup['in_progress'] ?? 0 }}</div>
                <div class="text-xs text-[#6b6b80]">In Progress</div>
            </div>
            <div class="text-center p-3 bg-[#12121f] rounded-lg">
                <div class="text-2xl font-bold text-red-400">{{ $currentStandup['blocked'] ?? 0 }}</div>
                <div class="text-xs text-[#6b6b80]">Blocked</div>
            </div>
        </div>
    </div>

    <!-- Team Status -->
    <div class="bg-[#1a1a2e] rounded-xl p-6 border border-[#2a2a40] mb-6">
        <h3 class="text-lg font-semibold text-[#e4e4f0] mb-4">Team Status</h3>
        <div class="flex flex-wrap gap-3">
            @foreach($teamStatus as $agent => $status)
            @php
            $avatars = ['Dave' => '💻', 'Maya' => '🎨', 'Chen' => '🔧', 'Sam' => '✅', 'Alex' => '🔌'];
            @endphp
            <div class="flex items-center gap-3 px-4 py-2 bg-[#12121f] rounded-lg">
                <div class="text-2xl">{{ $avatars[$agent] ?? '👤' }}</div>
                <div class="font-medium text-[#e4e4f0]">{{ $agent }}</div>
                <div class="flex gap-2 text-xs ml-2">
                    <span class="px-2 py-0.5 rounded bg-green-500/20 text-green-400">✓ {{ $status['completed'] }}</span>
                    <span class="px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-400">🔄 {{ $status['in_progress'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Next Actions -->
    <div class="bg-[#1a1a2e] rounded-xl p-6 border border-[#2a2a40]">
        <h3 class="text-lg font-semibold text-[#e4e4f0] mb-2">📋 Next Actions</h3>
        <p class="text-[#a0a0b8]">{{ $currentStandup['next_actions'] }}</p>
    </div>

    @if($currentStandup['blocked'] > 0)
    <!-- Blockers -->
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6 mt-6">
        <h3 class="text-lg font-semibold text-red-400 mb-2">🚧 Blockers</h3>
        <p class="text-[#a0a0b8]">{{ $currentStandup['blocked'] }} tasks are currently blocked and need attention.</p>
    </div>
    @endif

    @else
    <div class="text-center py-12 text-[#6b6b80]">
        <p>No standup data available. Click "Refresh" to generate.</p>
    </div>
    @endif
</div>
