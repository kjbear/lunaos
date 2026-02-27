<div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">🤖 Agent Management</h1>
            <p class="text-slate-400 mt-1">Manage AI worker agents and configurations</p>
        </div>
        <a 
            href="{{ route('agents.create') }}"
            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white rounded-lg transition-all flex items-center gap-2 font-medium shadow-lg"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Agent
        </a>
    </div>

    <!-- Agent Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($agents as $agent)
        <div class="bg-gradient-to-br from-slate-800/80 to-slate-900/80 rounded-xl shadow-lg overflow-hidden border border-white/10 hover:shadow-xl transition-all hover:border-purple-500/30">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">{{ $agent->emoji ?? '🤖' }}</span>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ ucfirst($agent->name) }}</h3>
                            <p class="text-purple-100 text-sm">{{ $agent->role ?? 'No role' }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium text-white capitalize">
                        {{ $agent->type ?? 'worker' }}
                    </span>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 space-y-4">
                <!-- Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $agent->is_online ? 'bg-green-500' : 'bg-slate-500' }}"></div>
                        <span class="text-sm font-medium text-slate-300">
                            {{ $agent->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <span class="px-2 py-1 bg-slate-700/50 rounded text-xs text-slate-400">
                        {{ $agent->model ?? 'Unknown' }}
                    </span>
                </div>

                <!-- Strategy -->
                @if($agent->strategy_class)
                <div>
                    <p class="text-xs text-slate-500 mb-1">Strategy</p>
                    <span class="inline-block px-3 py-1 bg-purple-900/30 text-purple-300 rounded-full text-sm font-medium border border-purple-500/20">
                        {{ $agent->strategy_class }}
                    </span>
                </div>
                @endif

                <!-- Skill Doc -->
                @if($agent->skill_doc_path)
                <div>
                    <p class="text-xs text-slate-500 mb-1">Skill Definition</p>
                    <span class="text-sm text-slate-300">{{ basename(dirname($agent->skill_doc_path)) }}</span>
                </div>
                @endif

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/10">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-purple-400">{{ $agent->tasks->count() }}</p>
                        <p class="text-xs text-slate-500">Total Tasks</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-400">{{ $agent->tasks->where('status', 'complete')->count() }}</p>
                        <p class="text-xs text-slate-500">Completed</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-4 border-t border-white/10">
                    <a 
                        href="{{ route('agents.edit', $agent->id) }}"
                        class="flex-1 px-3 py-2 bg-purple-900/30 text-purple-300 rounded-lg hover:bg-purple-900/50 transition-colors text-sm font-medium border border-purple-500/20 text-center"
                    >
                        Edit
                    </a>
                    @if(!in_array($agent->name, ['dave', 'sam', 'chen']))
                    <button 
                        wire:click="confirmDelete({{ $agent->id }})"
                        class="px-3 py-2 bg-red-900/30 text-red-300 rounded-lg hover:bg-red-900/50 transition-colors text-sm font-medium border border-red-500/20"
                    >
                        Delete
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if($agents->isEmpty())
    <div class="text-center py-16 bg-slate-800/50 rounded-2xl border border-white/10">
        <svg class="mx-auto h-12 w-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-white">No agents yet</h3>
        <p class="mt-2 text-slate-400">Get started by creating your first AI agent.</p>
        <a href="{{ route('agents.create') }}" class="inline-block mt-6 px-6 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-colors font-medium">
            Add Agent
        </a>
    </div>
    @endif
</div>
