@extends('components.layouts.app')

@section('title', 'Executive Board - LunaOS')

@section('content')
<div class="space-y-8">
    {{-- Ask the Board --}}
    <livewire:board.executive-board />

    {{-- Session History --}}
    <livewire:board.board-session-history />
</div>
@endsection