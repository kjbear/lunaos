@props(['href', 'active' => false, 'icon', 'label'])

@php
$baseClasses = "sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] hover:bg-[#1f1f35] transition-colors";
$activeClasses = $active ? 'bg-[#1f1f35] text-[#e4e4f0]' : '';
@endphp

<a href="{{ $href }}" 
   class="{{ $baseClasses }} {{ $activeClasses }}"
   title="{{ $label }}">
    <span class="text-lg flex-shrink-0">{{ $icon }}</span>
    <span x-show="!collapsed" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-200" class="font-medium">{{ $label }}</span>
</a>
