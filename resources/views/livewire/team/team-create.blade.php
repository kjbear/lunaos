@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
    <a href="{{ route('team') }}" class="text-blue-500 hover:underline mb-4 inline-block">&larr; Back to Team</a>
    
    <h1 class="text-2xl font-bold mb-4">Create Team Member</h1>
    
    <form action="{{ route('team.store') }}" method="POST" class="max-w-2xl">
        @csrf
        
        <div class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded-lg p-2 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block font-medium mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg p-2 @error('email') border-red-500 @enderror" required>
                @error('email')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label class="block font-medium mb-1">Role *</label>
                <select name="role" class="w-full border rounded-lg p-2 @error('role') border-red-500 @enderror" required>
                    <option value="worker" {{ old('role') === 'worker' ? 'selected' : '' }}>Worker</option>
                    <option value="persona" {{ old('role') === 'persona' ? 'selected' : '' }}>Persona</option>
                    <option value="board_member" {{ old('role') === 'board_member' ? 'selected' : '' }}>Board Member</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>
        
        <div class="mt-6">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Create Member</button>
        </div>
    </form>
</div>
@endsection
