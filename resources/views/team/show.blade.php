@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <a href="{{ route('team') }}" class="text-blue-500 hover:underline mb-4 inline-block">&larr; Back to Team</a>
    
    <div class="border rounded-lg p-6 max-w-2xl">
        <h1 class="text-2xl font-bold mb-4">{{ $member->name }}</h1>
        
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
        </div>
        
        <div class="flex gap-2 mt-6">
            <a href="/team/{{ $member->getKey() }}/edit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</a>
            <form action="/team/{{ $member->getKey() }}" method="POST" onsubmit="return confirm('Are you sure?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
