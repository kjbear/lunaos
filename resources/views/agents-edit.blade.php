@extends('components.layouts.app')

@section('title', 'Edit Agent - LunaOS')

@section('content')
@php
    \Log::info('agents-edit wrapper', ['id' => $id ?? 'NULL']);
@endphp
<livewire:agents.agent-edit :id="(int) $id" />
@endsection
