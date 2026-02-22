@props(['value' => 0, 'label' => '', 'color' => 'default'])

@php
    $colors = [
        'default' => 'text-[#e4e4f0]',
        'primary' => 'text-[#7c3aed]',
        'success' => 'text-[#10b981]',
        'warning' => 'text-[#f59e0b]',
        'error' => 'text-[#ef4444]',
        'info' => 'text-[#3b82f6]',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-[#1a1a2e] rounded-lg p-4 border border-[#2a2a40] card-glow']) }}>
    <div class="text-2xl font-bold {{ $colors[$color] }}">{{ $value }}</div>
    <div class="text-xs text-[#6b6b80]">{{ $label }}</div>
</div>