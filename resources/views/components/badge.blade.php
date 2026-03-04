@props(['type' => 'info', 'outline' => false])

@php
    $variants = [
        'success' => $outline ? 'badge-success badge-outline' : 'badge-success',
        'warning' => $outline ? 'badge-warning badge-outline' : 'badge-warning',
        'error' => $outline ? 'badge-error badge-outline' : 'badge-error',
        'info' => $outline ? 'badge-info badge-outline' : 'badge-info',
        'neutral' => $outline ? 'badge-ghost badge-outline' : 'badge-ghost',
    ];
@endphp

<span {{ $attributes->merge(['class' => "badge " . ($variants[$type] ?? $variants['info'])]) }}>
    {{ $slot }}
</span>