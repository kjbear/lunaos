<div class="persona-workspace">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('hr') }}" class="text-gray-400 hover:text-white transition-colors">
            ← Back to Personas
        </a>
    </div>
    
    <div class="bg-[#1a1a2e] rounded-xl p-6 border border-[#2a2a40]">
        {{-- Persona Info --}}
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-[#2a2a40]">
            <span class="text-4xl">{{ $persona['avatar'] }}</span>
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $persona['name'] }}</h2>
                <div class="flex items-center gap-3 text-sm text-gray-400 mt-1">
                    <span class="px-2 py-1 rounded {{ $persona['model'] === 'dolphin' ? 'bg-cyan-500/20 text-cyan-400' : ($persona['model'] === 'haiku' ? 'bg-orange-500/20 text-orange-400' : 'bg-purple-500/20 text-purple-400') }}">
                        {{ $persona['model'] }}
                    </span>
                    <span>{{ ucfirst($persona['role']) }}</span>
                </div>
            </div>
            <div class="ml-auto">
                <button wire:click="sync" 
                        class="px-4 py-2 bg-[#2a2a40] text-gray-300 rounded-lg hover:bg-[#3a3a50] transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="sync">🔄</span>
                    <span wire:loading wire:target="sync">⟳</span>
                    Sync from Filesystem
                </button>
            </div>
        </div>
        
        {{-- File Tabs --}}
        <div class="flex gap-2 mb-4">
            @foreach($files as $file)
            <button wire:click="selectFile('{{ $file }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $selectedFile === $file ? 'bg-purple-600 text-white' : 'bg-[#2a2a40] text-gray-400 hover:bg-[#3a3a50]' }}">
                {{ $file }}
            </button>
            @endforeach
        </div>
        
        {{-- Content --}}
        <div class="bg-[#12121f] rounded-lg p-6 border border-[#2a2a40]">
            @if($content && $content !== '# No content yet')
            <div class="prose prose-invert max-w-none">
                {!! \Illuminate\Support\Str::markdown($content) !!}
            </div>
            @else
            <div class="text-center py-12 text-gray-500">
                <p class="mb-2">No content found for {{ $selectedFile }}</p>
                <p class="text-sm">Click "Sync from Filesystem" to load from the persona's workspace.</p>
            </div>
            @endif
        </div>
        
        {{-- Last Synced --}}
        <div class="mt-4 text-xs text-gray-500">
            Selected: {{ $selectedFile }}
        </div>
    </div>
</div>