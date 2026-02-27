<div class="task-board">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-[#e4e4f0]">Task Board</h2>
        @if(count($projects) > 0)
        <select wire:model="projectId" wire:change="loadTasks" 
                class="bg-[#1a1a2e] border border-[#2a2a40] rounded-lg px-3 py-2 text-[#e4e4f0] text-sm">
            @foreach($projects as $project)
            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
            @endforeach
        </select>
        @endif
    </div>

    @if(!$projectId)
    <div class="text-center py-12 text-[#6b6b80]">
        <p>No project selected. Create a project first.</p>
    </div>
    @else

    <!-- Kanban Columns -->
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach($columns as $column)
        <div class="flex-shrink-0 w-72" data-status="{{ $column }}">
            <!-- Column Header -->
            <div class="flex items-center justify-between mb-3 px-1">
                <h3 class="font-medium text-[#a0a0b8] uppercase text-xs tracking-wider">
                    {{ ucfirst(str_replace('_', ' ', $column)) }}
                </h3>
                <span class="text-xs text-[#6b6b80] bg-[#1a1a2e] px-2 py-0.5 rounded">
                    {{ count($tasks[$column] ?? []) }}
                </span>
            </div>

            <!-- Column Body -->
            <div class="bg-[#12121f] rounded-lg p-2 min-h-[400px] space-y-2"
                 @if($canEdit) drop-zone="{{ $column }}" @endif>
                
                @forelse(($tasks[$column] ?? []) as $task)
                <div class="task-card bg-[#1a1a2e] rounded-lg p-3 border border-[#2a2a40] cursor-pointer hover:border-[#7c3aed] transition-colors"
                     data-task-id="{{ $task['id'] }}"
                     @if($canEdit) draggable="true" @endif>
                    
                    <!-- Task Header -->
                    <div class="flex items-start justify-between mb-2">
                        <span class="text-xs text-[#6b6b80]">#{{ Str::limit($task['id'], 8, '') }}</span>
                        @if(isset($task['priority']))
                        <span class="text-xs px-1.5 py-0.5 rounded {{ 
                            $task['priority'] === 'high' ? 'bg-red-500/20 text-red-400' : 
                            ($task['priority'] === 'low' ? 'bg-gray-500/20 text-gray-400' : 
                            'bg-yellow-500/20 text-yellow-400') 
                        }}">
                            {{ $task['priority'] }}
                        </span>
                        @endif
                    </div>

                    <!-- Task Title -->
                    <div class="text-sm text-[#e4e4f0] mb-2">{{ $task['title'] ?? 'Untitled' }}</div>

                    <!-- Task Description (truncated) -->
                    @if(isset($task['description']) && $task['description'])
                    <div class="text-xs text-[#6b6b80] mb-2 line-clamp-2">
                        {{ Str::limit($task['description'], 80) }}
                    </div>
                    @endif

                    <!-- Task Footer -->
                    <div class="flex items-center justify-between text-xs">
                        @if(isset($task['assigned_to']))
                        <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-400">
                            {{ $task['assigned_to'] }}
                        </div>
                        @endif
                        
                        @if(isset($task['branch']))
                        <div class="text-[#6b6b80]">
                            🌿 {{ $task['branch'] }}
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-[#3a3a50] text-xs">
                    No tasks
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    @endif
</div>

@once
@push('styles')
<style>
    .task-card[draggable="true"] {
        cursor: grab;
    }
    .task-card[draggable="true"]:active {
        cursor: grabbing;
    }
    .task-card.dragging {
        opacity: 0.5;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
@endonce
