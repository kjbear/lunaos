<div class="project-requirements space-y-6">
    {{-- Back Link --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('projects') }}" class="text-[#6b6b80] hover:text-[#e4e4f0] transition-colors">
            ← Back to Projects
        </a>
    </div>
    
    {{-- Project Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">{{ $project['name'] ?? 'Requirements' }}</h1>
            <p class="text-sm text-[#6b6b80] mt-1">{{ $project['description'] ?? 'Manage project requirements' }}</p>
        </div>
        <button wire:click="$set('showAddModal', true)" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
            + Add Requirement
        </button>
    </div>
    
    {{-- Requirements List --}}
    <div class="space-y-3">
        @forelse($requirements as $req)
        <div class="bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40]">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $req['priority'] === 'high' ? 'bg-[#ef4444]/20 text-[#ef4444]' : ($req['priority'] === 'medium' ? 'bg-[#f59e0b]/20 text-[#f59e0b]' : 'bg-[#6b6b80]/20 text-[#6b6b80]') }}">
                            {{ ucfirst($req['priority']) }}
                        </span>
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $req['status'] === 'approved' ? 'bg-[#10b981]/20 text-[#10b981]' : 'bg-[#252542] text-[#a0a0b8]' }}">
                            {{ ucfirst($req['status']) }}
                        </span>
                        <h3 class="text-[#e4e4f0] font-medium">{{ $req['title'] }}</h3>
                    </div>
                    @if($req['description'])
                    <p class="text-sm text-[#6b6b80]">{{ $req['description'] }}</p>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 mt-3 pt-3 border-t border-[#2a2a40]">
                @if($req['status'] === 'draft')
                <button wire:click="approve('{{ $req['id'] }}')" 
                        class="px-3 py-1 text-sm bg-[#10b981]/20 text-[#10b981] rounded hover:bg-[#10b981]/30 transition-colors">
                    Approve
                </button>
                @endif
                <div class="flex gap-1">
                    @foreach(['high', 'medium', 'low'] as $p)
                    @if($req['priority'] !== $p)
                    <button wire:click="prioritize('{{ $req['id'] }}', '{{ $p }}')" 
                            class="px-2 py-1 text-xs bg-[#252542] text-[#a0a0b8] rounded hover:bg-[#2f2f52] transition-colors">
                        {{ ucfirst($p) }}
                    </button>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-[#6b6b80]">
            <p>No requirements yet.</p>
            <p class="text-sm mt-2">Add your first requirement to get started.</p>
        </div>
        @endforelse
    </div>
    
    {{-- Add Requirement Modal --}}
    @if($showAddModal)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click="resetAddForm">
        <div class="bg-[#1a1a2e] rounded-xl p-6 w-[90%] max-w-lg border border-[#2a2a40]" wire:click.stop>
            <h3 class="text-lg font-semibold text-[#e4e4f0] mb-4">Add Requirement</h3>
            <form wire:submit="add">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">Title *</label>
                        <input type="text" wire:model="newTitle" 
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none">
                        @error('newTitle')<span class="text-[#ef4444] text-sm">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">Description</label>
                        <textarea wire:model="newDescription" rows="3"
                                  class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm text-[#6b6b80] mb-1">Priority</label>
                        <select wire:model="newPriority" 
                                class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2.5 text-[#e4e4f0] focus:border-purple-500 focus:outline-none">
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" wire:click="resetAddForm" 
                            class="px-4 py-2 bg-[#6b6b80] text-white rounded-lg hover:bg-[#7b7b90] transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Add Requirement
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>