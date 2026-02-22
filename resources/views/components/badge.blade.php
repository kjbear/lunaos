@props(['type' => 'info'])

@php
    $types = [
        'success' => 'bg-[#10b981]/20 text-[#10b981]',
        'warning' => 'bg-[#f59e0b]/20 text-[#f59e0b]',
        'error' => 'bg-[#ef4444]/20 text-[#ef4444]',
        'info' => 'bg-[#3b82f6]/20 text-[#3b82f6]',
    ];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {$types[$type]}"]) }}>
    {{ $slot }}
</span>