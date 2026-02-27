@extends('components.layouts.app')

@section('title', 'Org Chart')

@section('content')
<!-- Full-width org chart - no sidebar, match other modules -->
<div class="px-6">
    <livewire:org-chart />
</div>
@endsection