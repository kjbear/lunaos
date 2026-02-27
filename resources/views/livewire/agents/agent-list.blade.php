<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Agent Management</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Manage AI worker agents and configurations</p>
        </div>
        <button 
            wire:click="openCreateModal"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Agent
        </button>
    </div>

    <!-- Agent Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($agents as $agent)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">{{ $agent->emoji ?? '🤖' }}</span>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ ucfirst($agent->name) }}</h3>
                            <p class="text-blue-100 text-sm">{{ $agent->role }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 bg-white/20 backdrop-blur-sm rounded-full text-xs font-medium text-white">
                        {{ $agent->type }}
                    </span>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                <!-- Status -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $agent->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $agent->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-400">
                        {{ $agent->model }}
                    </span>
                </div>

                <!-- Strategy -->
                <div class="mb-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Strategy</p>
                    <span class="inline-block px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium">
                        {{ $agent->strategy_class ?? 'Not configured' }}
                    </span>
                </div>

                <!-- Skill Doc -->
                @if($agent->skill_doc_path)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Skill Definition</p>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ basename(dirname($agent->skill_doc_path)) }}</span>
                    </div>
                </div>
                @endif

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $agent->tasks()->count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tasks</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $agent->tasks()->where('status', 'complete')->count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        wire:click="openEditModal({{ $agent->id }})"
                        class="flex-1 px-3 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-sm font-medium"
                    >
                        Edit
                    </button>
                    @if($agent->name !== 'dave' && $agent->name !== 'sam' && $agent->name !== 'chen')
                    <button 
                        wire:click="deleteAgent({{ $agent->id }})"
                        wire:confirm="Are you sure you want to delete this agent?"
                        class="px-3 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors text-sm font-medium"
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
    <div class="text-center py-16">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No agents</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new agent.</p>
        <div class="mt-6">
            <button 
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Agent
            </button>
        </div>
    </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $editingAgent ? 'Edit Agent' : 'Create New Agent' }}
                </h2>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            @livewire('agents.agent-form', ['agent' => $editingAgent])
        </div>
    </div>
    @endif
</div>
