@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Team Members</h1>
    
    <div class="flex gap-2 mb-4">
        @foreach(['workers', 'personas', 'board-members'] as $tabName)
            <a href="?tab={{ $tabName }}" 
               class="px-4 py-2 rounded {{ $activeTab === $tabName ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                {{ ucfirst(str_replace('-', ' ', $tabName)) }}
            </a>
        @endforeach
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($members as $member)
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold">{{ $member->name }}</h3>
                @if($member->email)
                    <p class="text-sm text-gray-600">{{ $member->email }}</p>
                @endif
                <p class="text-sm text-gray-500">{{ $member->type }}</p>
                <a href="/team/{{ $member->getKey() }}" class="text-blue-500 hover:underline">View Details</a>
            </div>
        @empty
            <p class="text-gray-500">No team members found.</p>
        @endforelse
    </div>
</div>
@endsection
