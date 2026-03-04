@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <a href="/team/{{ $member->getKey() }}" class="text-blue-500 hover:underline mb-4 inline-block">&larr; Back to Member</a>
    
    <h1 class="text-2xl font-bold mb-4">Edit Team Member</h1>
    
    <form action="/team/{{ $member->getKey() }}" method="POST" class="max-w-2xl">
        @method('PUT')
        @csrf
        @method('PUT')
        
        <div class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $member->name) }}" class="w-full border rounded-lg p-2 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $member->email) }}" class="w-full border rounded-lg p-2 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block font-medium mb-1">Role *</label>
                <select name="role" class="w-full border rounded-lg p-2 @error('role') border-red-500 @enderror" required>
                    <option value="worker" {{ old('role', $member->role) === 'worker' ? 'selected' : '' }}>Worker</option>
                    <option value="persona" {{ old('role', $member->role) === 'persona' ? 'selected' : '' }}>Persona</option>
                    <option value="board_member" {{ old('role', $member->role) === 'board_member' ? 'selected' : '' }}>Board Member</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block font-medium mb-1">Status</label>
                <select name="status" class="w-full border rounded-lg p-2">
                    <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $member->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="online" {{ old('status', $member->status) === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ old('status', $member->status) === 'offline' ? 'selected' : '' }}>Offline</option>
                </select>
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Update Member</button>
        </div>
    </form>
</div>
@endsection
