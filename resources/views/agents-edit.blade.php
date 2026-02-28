@extends('components.layouts.app')

@section('title', 'Edit Agent - LunaOS')

@section('content')
<livewire:agents.agent-edit :id="$id" />
@endsection
