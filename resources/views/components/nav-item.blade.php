@props(['emoji' => '📋', 'label' => '', 'href' => '#', 'active' => false])

<a href="{{ $href }}" {{ $attributes->merge(['class' => "sidebar-item flex items-center gap-3 px-4 py-3 rounded-lg text-[#a0a0b8] hover:text-[#e4e4f0] " . ($active ? 'active' : '')]) }}>
    <span class="text-lg">{{ $emoji }}</span>
    <span class="font-medium">{{ $label }}</span>
</a>