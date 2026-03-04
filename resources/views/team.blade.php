@extends('components.layouts.app')

@section('title', 'Team - LunaOS')

@push('head')
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="team-page">
    @if(request()->has('member'))
        {{-- Show individual member details --}}
        <livewire:team.team-details :id="request('member')" />
    @else
        {{-- Show team index with tabs --}}
        <livewire:team.team-index />
    @endif
</div>
@endsection
