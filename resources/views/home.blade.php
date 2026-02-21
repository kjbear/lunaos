@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Task Manager -->
    <livewire:task-manager />
</div>
@endsection