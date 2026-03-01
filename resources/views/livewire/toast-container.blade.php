<div class="fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"
     x-data
     @toast.window="$wire.addToast($event.detail.message, $event.detail.type ?? 'info', $event.detail.duration ?? 4000)"
     @toast-success.window="$wire.success($event.detail.message, $event.detail.duration ?? 4000)"
     @toast-error.window="$wire.error($event.detail.message, $event.detail.duration ?? 6000)"
     @toast-info.window="$wire.info($event.detail.message, $event.detail.duration ?? 4000)"
     @toast-warning.window="$wire.warning($event.detail.message, $event.detail.duration ?? 5000)">
    @foreach($toasts as $toast)
    <div class="toast-item pointer-events-auto"
         style="animation: slide-in 0.3s ease-out"
         x-data="{ show: true }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-8"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-8"
         wire:key="{{ $toast['id'] }}">
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg min-w-[300px] max-w-[400px]
                    @if($toast['type'] === 'success') bg-[#10b981] text-white @endif
                    @if($toast['type'] === 'error') bg-[#ef4444] text-white @endif
                    @if($toast['type'] === 'warning') bg-[#f59e0b] text-white @endif
                    @if($toast['type'] === 'info') bg-[#3b82f6] text-white @endif">
            <span class="text-lg flex-shrink-0">
                @if($toast['type'] === 'success') ✅ @endif
                @if($toast['type'] === 'error') ❌ @endif
                @if($toast['type'] === 'warning') ⚠️ @endif
                @if($toast['type'] === 'info') ℹ️ @endif
            </span>
            <p class="flex-1 text-sm font-medium">{{ $toast['message'] }}</p>
            <button wire:click="removeToast('{{ $toast['id'] }}')"
                    x-on:click="show = false"
                    class="flex-shrink-0 hover:opacity-75 transition-opacity">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>
    @endforeach

    <style>
        @keyframes slide-in {
            from { opacity: 0; transform: translateX(2rem); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</div>
