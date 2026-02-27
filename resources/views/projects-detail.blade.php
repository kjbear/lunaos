@extends('components.layouts.app')

@section('title', 'Project Details - LunaOS')

@section('content')
<div>
    <livewire:projects.project-detail :id="$id" />
</div>
@endsection
