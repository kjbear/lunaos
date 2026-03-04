<div>
    <div class="container mx-auto py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Team Members</h1>
            <a href="{{ route('team.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Member
            </a>
        </div>
        
        {{-- Tab Filters --}}
        <div class="flex gap-2 mb-4">
            <a href="?tab=workers" 
               class="px-4 py-2 rounded {{ $activeTab === 'workers' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Team
            </a>
            <a href="?tab=personas" 
               class="px-4 py-2 rounded {{ $activeTab === 'personas' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Personas
            </a>
            <a href="?tab=board-members" 
               class="px-4 py-2 rounded {{ $activeTab === 'board-members' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Board
            </a>
        </div>
        
        {{-- Members List --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($members as $member)
                <div class="border rounded-lg p-4">
                    <h3 class="font-semibold">{{ $member->name }}</h3>
                    @if($member->title)
                        <p class="text-sm text-gray-700">{{ $member->title }}</p>
                    @endif
                    @if($member->email)
                        <p class="text-sm text-gray-600">{{ $member->email }}</p>
                    @endif
                    <p class="text-sm text-gray-500">{{ $member->type }}</p>
                    <a href="{{ route('team.show', $member->id) }}" class="text-blue-500 hover:underline">View Details</a>
                </div>
            @empty
                <p class="text-gray-500">No team members</p>
            @endforelse
        </div>
        
        {{-- Pagination --}}
        @if($members->hasPages())
        <div class="mt-6">
            <div class="text-sm text-gray-700 mb-2">Pagination</div>
            {{ $members->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
</div>
