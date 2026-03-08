@props([
    'id' => '',
    'name' => '',
    'label' => '',
    'placeholder' => 'Type and press Enter to add...',
    'tags' => [],
    'disabled' => false,
    'error' => null,
])

<div 
    class="tag-input space-y-2"
    x-data="{
        tags: {{ json_encode($tags) }},
        newTag: '',
        addTag() {
            const trimmed = this.newTag.trim();
            if (trimmed && !this.tags.includes(trimmed)) {
                this.tags.push(trimmed);
                this.newTag = '';
                this.syncToLivewire();
            }
        },
        removeTag(index) {
            this.tags.splice(index, 1);
            this.syncToLivewire();
        },
        syncToLivewire() {
            // Update hidden input for form submission
            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = JSON.stringify(this.tags);
            }
            // Sync with Livewire if available
            if (this.$wire && this.$wire.set) {
                this.$wire.set('{{ $name }}', this.tags);
            }
        }
    }"
    x-init="
        // Watch for external changes to tags
        $watch('tags', (value) => {
            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = JSON.stringify(value);
            }
        });
    "
>
    @if($label)
    <label for="{{ $id ?: $name }}" class="block text-sm font-medium text-slate-300">
        {{ $label }}
    </label>
    @endif
    
    <div class="relative">
        {{-- Hidden input for form submission --}}
        <input 
            type="hidden" 
            name="{{ $name }}" 
            x-ref="hiddenInput"
            value="{{ json_encode($tags) }}"
        >
        
        {{-- Tag display area --}}
        <div class="flex flex-wrap gap-2 p-3 min-h-[48px] bg-slate-800/50 border {{ $error ? 'border-red-500/50' : 'border-white/10' }} rounded-lg focus-within:border-purple-500/50 transition-colors" {{ $disabled ? 'style="opacity: 0.5; pointer-events: none;"' : '' }}>
            <template x-for="(tag, index) in tags" :key="index">
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-500/20 text-purple-300 border border-purple-500/30 rounded-full text-sm">
                    <span x-text="tag"></span>
                    <button 
                        type="button"
                        @click="removeTag(index)"
                        class="ml-1 hover:text-red-400 transition-colors focus:outline-none"
                        aria-label="Remove tag"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            </template>
            
            {{-- Input for new tags --}}
            <input 
                type="text"
                id="{{ $id ?: $name }}"
                x-model="newTag"
                @keydown.enter.prevent="addTag()"
                @keydown.tab.prevent.stop="addTag()"
                placeholder="{{ $placeholder }}"
                class="flex-1 min-w-[150px] bg-transparent border-none focus:ring-0 text-white placeholder-slate-500 text-sm outline-none"
                {{ $disabled ? 'disabled' : '' }}
            >
        </div>
    </div>
    
    @if($error)
    <p class="text-xs text-red-400">{{ $error }}</p>
    @endif
    
    <p class="text-xs text-slate-500">
        Press <kbd class="px-1.5 py-0.5 bg-slate-700 rounded text-slate-300 font-mono text-xs">Enter</kbd> or <kbd class="px-1.5 py-0.5 bg-slate-700 rounded text-slate-300 font-mono text-xs">Tab</kbd> to add tags
    </p>
</div>