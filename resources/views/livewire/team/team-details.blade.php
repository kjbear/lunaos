<div>
    <div class="container mx-auto py-8">
        <a href="{{ route('team') }}" class="text-blue-500 hover:underline mb-4 inline-block">&larr; Back to Team</a>
        
        @if($member)
        <div class="border rounded-lg p-6 max-w-2xl">
            <div class="flex items-center gap-4 mb-4">
                @if($member->avatar)
                    <span class="text-4xl">{{ $member->avatar }}</span>
                @endif
                <h1 class="text-2xl font-bold">{{ $member->name }}</h1>
            </div>
            
            <div class="space-y-2">
                @if($member->email)
                    <p><strong>Email:</strong> {{ $member->email }}</p>
                @endif
                
                @if($member->title)
                    <p><strong>Title:</strong> {{ $member->title }}</p>
                @endif
                
                <p><strong>Type:</strong> {{ $member->type }}</p>
                <p><strong>Role:</strong> {{ $member->role }}</p>
                <p><strong>Status:</strong> {{ $member->status }}</p>
                
                @if($member->model)
                    <p><strong>Model:</strong> {{ $member->model }}</p>
                @endif
                
                @if($member->provider)
                    <p><strong>Provider:</strong> {{ $member->provider }}</p>
                @endif
                
                @if($member->parent)
                    <p><strong>Reports To:</strong> {{ $member->parent->name }}</p>
                @endif
                
                @if($member->children->count() > 0)
                    <div class="mt-4">
                        <p><strong>Direct Reports:</strong></p>
                        <ul class="ml-4 list-disc">
                            @foreach($member->children as $child)
                                <li>{{ $child->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <p><strong>Member Since:</strong> {{ $member->created_at?->diffForHumans() ?? 'Recently' }}</p>
            </div>
            
            <div class="flex gap-2 mt-6">
                <a href="{{ route('team.edit', $member->id) }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</a>
                <button type="button" wire:click="toggleStatus" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Toggle Status
                </button>
                <button type="button" wire:click="confirmDelete" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
            </div>
            
            {{-- Metadata Section --}}
            @if(count($metadata) > 0)
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-3">Metadata</h2>
                <div class="p-3 bg-gray-50 rounded">
                    @foreach($metadata as $key => $value)
                        <p><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Settings Section --}}
            @if(count($settings) > 0)
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-3">Settings</h2>
                <div class="p-3 bg-gray-50 rounded">
                    @foreach($settings as $key => $value)
                        <p><strong>{{ $key }}:</strong> {{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}</p>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Activity History Section --}}
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-3">Activity History</h2>
                @if(count($activityHistory) > 0)
                <div class="space-y-2">
                    @foreach($activityHistory as $activity)
                        <div class="p-3 border rounded bg-gray-50">
                            <span class="text-sm">{{ $activity['timestamp'] }}</span>
                            <p>{{ ucfirst($activity['action']) }}: {{ $activity['item'] }}</p>
                        </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No recent activity</p>
                @endif
            </div>
            
            {{-- Tasks Section --}}
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-3">Assigned Tasks</h2>
                @if($member->tasks->count() > 0)
                <div class="space-y-2">
                    @foreach($member->tasks as $task)
                        <div class="p-3 border rounded {{ $task->status === 'completed' ? 'bg-green-50' : 'bg-gray-50' }}">
                            <span class="font-medium">{{ $task->title }}</span>
                            <span class="ml-2 text-sm text-gray-600">({{ $task->status }})</span>
                        </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No tasks assigned</p>
                @endif
            </div>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <p>Team member not found.</p>
            <a href="{{ route('team') }}" class="text-blue-500 hover:underline mt-2 inline-block">Return to Team</a>
        </div>
        @endif
    </div>
</div>
