@props([
    'id' => '',
    'name' => '',
    'label' => '',
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'value' => 0,
    'showValue' => true,
    'unit' => '',
    'disabled' => false,
])

<div class="slider-input space-y-2" x-data="{ sliderValue: {{ $value }} }">
    @if($label)
    <div class="flex items-center justify-between">
        <label for="{{ $id ?: $name }}" class="block text-sm font-medium text-slate-300">
            {{ $label }}
        </label>
        @if($showValue)
        <span class="text-sm font-mono text-purple-400" x-text="sliderValue.toFixed(step.includes('.') ? step.split('.')[1].length : 0) + '{{ $unit }}'">
            {{ is_numeric($value) ? round($value, $step && strpos((string)$step, '.') !== false ? strlen(substr((string)$step, strpos((string)$step, '.') + 1)) : 0) : $value }}{{ $unit }}
        </span>
        @endif
    </div>
    @endif
    
    <div class="relative">
        <input 
            type="range" 
            id="{{ $id ?: $name }}"
            name="{{ $name }}"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            value="{{ $value }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer slider-thumb-purple disabled:opacity-50 disabled:cursor-not-allowed']) }}
            x-model="sliderValue"
            @input="sliderValue = parseFloat($event.target.value)"
            @change="$wire && $wire.set && $wire.set('{{ $name }}', sliderValue)"
        >
        @if($showValue && !$label)
        <span class="absolute right-0 top-6 text-xs font-mono text-purple-400" x-text="sliderValue + '{{ $unit }}'">
            {{ $value }}{{ $unit }}
        </span>
        @endif
    </div>
</div>

<style>
    /* Custom slider thumb styling */
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
        cursor: pointer;
        border: 2px solid #1e1b4b;
        box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
        transition: all 0.15s ease;
    }
    
    input[type="range"]::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.5);
    }
    
    input[type="range"]::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #a855f7);
        cursor: pointer;
        border: 2px solid #1e1b4b;
        box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
        transition: all 0.15s ease;
    }
    
    input[type="range"]::-moz-range-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.5);
    }
    
    /* Track styling */
    input[type="range"]::-webkit-slider-runnable-track {
        height: 8px;
        border-radius: 4px;
        background: linear-gradient(90deg, #475569 0%, #64748b 100%);
    }
    
    input[type="range"]::-moz-range-track {
        height: 8px;
        border-radius: 4px;
        background: linear-gradient(90deg, #475569 0%, #64748b 100%);
    }
    
    /* Filled track effect */
    input[type="range"] {
        background: linear-gradient(to right, #8b5cf6 0%, #8b5cf6 var(--value-percent, 50%), #334155 var(--value-percent, 50%), #334155 100%);
    }
</style>