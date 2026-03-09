@props([
    'agent',
    'show' => false,
])

@if($show && $agent)
<div class="fixed inset-0 overflow-y-auto px-4 py-6 z-50" wire:click.self="$wire.closeModal()">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$wire.closeModal()"></div>

    <!-- Modal Panel -->
    <div class="relative mb-6 bg-slate-900 rounded-2xl overflow-hidden shadow-2xl max-w-2xl mx-auto border border-white/10">
        <!-- Header -->
        <div class="relative px-6 py-5 border-b border-white/10 bg-gradient-to-r from-slate-800 to-slate-900">
            <h3 class="text-xl font-bold text-white">
                {{ $agent['emoji'] ?? '👤' }} {{ $agent['name'] ?? 'Unknown' }}
            </h3>
            <p class="text-sm text-slate-400 mt-1 font-medium">
                {{ $agent['title'] ?? '' }}
            </p>
            <button
                type="button"
                class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors"
                wire:click="closeModal"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-6 space-y-6">
            <!-- Role Badge -->
            <div class="flex items-center gap-3">
                @php
                    $role = $agent['role'] ?? 'worker';
                    $roleColors = [
                        'executive' => ['bg-purple-500/20', 'text-purple-400', 'border-purple-500/30'],
                        'board_member' => ['bg-blue-500/20', 'text-blue-400', 'border-blue-500/30'],
                        'worker' => ['bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30'],
                    ];
                    [$bg, $text, $border] = $roleColors[$role] ?? $roleColors['worker'];
                    $roleLabel = ucfirst(str_replace('_', ' ', $role));
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $bg }} {{ $text }} {{ $border }}">
                    {{ $roleLabel }}
                </span>
                
                @if ($agent['type'] ?? false)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-700/50 text-slate-300 border border-white/10">
                        {{ ucfirst($agent['type']) }}
                    </span>
                @endif
            </div>

            <!-- Model Info -->
            @if ($agent['model'] ?? false)
                <div class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-white/5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Model</p>
                            <p class="text-sm font-medium text-white">{{ $agent['model'] }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Skills -->
            @if (!empty($agent['skills'] ?? []))
                <div class="space-y-2">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Skills</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($agent['skills'] as $skill)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-900 border-t border-white/10 flex justify-end gap-3">
            <button
                type="button"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 transition-all"
                wire:click="closeModal"
            >
                Close
            </button>
            @if ($agent['id'] ?? false)
            <a
                href="{{ route('team.show', $agent['id']) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-gradient-to-r from-cyan-500 to-blue-500 text-white hover:from-cyan-600 hover:to-blue-600 transition-all"
            >
                View Full Profile
            </a>
            @endif
        </div>
    </div>
</div>
@endif