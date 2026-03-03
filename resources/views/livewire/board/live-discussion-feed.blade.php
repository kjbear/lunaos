@php
use Illuminate\Support\Str;
@endphp

<div 
    class="live-discussion-feed-component"
    wire:poll.2s="refreshResponses"
>
    {{-- Status Indicator --}}
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if(!$isComplete)
            <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
            <span class="text-sm text-slate-400 font-semibold">{{ $statusText }}</span>
            @else
            <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
            <span class="text-sm text-emerald-400 font-semibold">{{ $statusText }}</span>
            @endif
        </div>
        <span class="text-xs text-slate-500">{{ count($responses) }} responses</span>
    </div>

    {{-- Responses List --}}
    <div class="space-y-4" id="feed-scroll-{{ $sessionId }}">
        @forelse($responses as $response)
        <div class="p-4 bg-slate-900/60 backdrop-blur-sm rounded-xl border border-white/10 hover:border-purple-500/30 transition-all animate-fade-in">
            <div class="flex items-start gap-3">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xl flex-shrink-0">
                    {{ $response['avatar'] }}
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    {{-- Header --}}
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="text-sm font-bold text-white">{{ $response['member_name'] }}</span>
                        <span class="text-xs text-slate-500">•</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $response['member_role'] }}</span>
                        @if(isset($response['round']))
                        <span class="text-xs px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            Round {{ $response['round'] }}
                        </span>
                        @endif
                        <span class="text-xs text-slate-600 ml-auto">{{ $response['timestamp'] ?? $response['created_at'] }}</span>
                    </div>

                    {{-- Response Body with Markdown --}}
                    <div class="text-sm text-slate-300 leading-relaxed discussion-response mt-2">
                        {!! Str::markdown($response['response']) !!}
                    </div>
                </div>
            </div>
        </div>
        @empty
        {{-- Empty State --}}
        <div class="text-center py-12 px-4 bg-slate-900/40 backdrop-blur-sm rounded-xl border border-white/5">
            <svg class="w-14 h-14 mx-auto mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-slate-400 font-medium mb-1">No responses yet</p>
            <p class="text-slate-600 text-sm">{{ $statusText }}</p>
        </div>
        @endforelse
    </div>

@push('styles')
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
</style>
@endpush

{{-- Auto-scroll JavaScript --}}
@if(!$isComplete && count($responses) > 0)
<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('scroll-to-bottom', () => {
            const scrollContainer = document.getElementById('feed-scroll-{{ $sessionId }}');
            if (scrollContainer) {
                setTimeout(() => {
                    scrollContainer.scrollTop = scrollContainer.scrollHeight;
                    
                    // Smooth scroll for better UX
                    scrollContainer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'end' 
                    });
                }, 200);
            }
        });
        
        // Initial scroll on mount
        @if(count($responses) > 0)
        setTimeout(() => {
            const scrollContainer = document.getElementById('feed-scroll-{{ $sessionId }}');
            if (scrollContainer) {
                scrollContainer.scrollTop = scrollContainer.scrollHeight;
            }
        }, 500);
        @endif
    });
</script>
@endif
</div>
