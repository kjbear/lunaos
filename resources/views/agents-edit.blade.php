@extends('components.layouts.app')

@section('title', 'Edit Agent - LunaOS')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Livewire Component -->
    <livewire:agents.agent-edit :id="$id" />
</div>
@endsection
