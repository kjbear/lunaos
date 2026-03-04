@props(['value' => 0, 'label' => '', 'color' => 'default'])

@php
    $variants = [
        'default' => 'text-base-content',
        'primary' => 'text-primary',
        'success' => 'text-success',
        'warning' => 'text-warning',
        'error' => 'text-error',
        'info' => 'text-info',
    ];
@endphp

<div class="stats shadow-lg bg-base-200 border border-base-300">
    <div class="stat">
        <div class="stat-title text-base-content/60">{{ $label }}</div>
        <div class="stat-value {{ $variants[$color] ?? $variants['default'] }}">{{ $value }}</div>
    </div>
</div>