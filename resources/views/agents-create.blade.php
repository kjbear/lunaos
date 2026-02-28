@extends('components.layouts.app')

@section('title', 'Create Agent - LunaOS')

@section('content')
@if(session('success'))
<div class="mb-6 p-4 bg-green-900/30 border border-green-500/30 rounded-xl text-green-300">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 bg-red-900/30 border border-red-500/30 rounded-xl text-red-300">
    {{ session('error') }}
</div>
@endif

<livewire:agents.agent-create />
@endsection
