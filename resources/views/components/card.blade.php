@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-[#1a1a2e] border-[#2a2a40]',
        'elevated' => 'bg-[#252542] border-[#2a2a40]',
        'transparent' => 'bg-transparent border-transparent',
    ];
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border p-4 {$variants[$variant]} card-glow"]) }}>
    {{ $slot }}
</div>