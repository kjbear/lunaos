@php
use Illuminate\Support\Str;
@endphp

<div 
    class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden"
    wire:poll.2s="refreshResponses"
>
    <div class="bg-white/[0.02] border-b border-white/10 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-1 h-5 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Live Discussion</h3>
        </div>
        <div class="flex items-center gap-2">
            @if(!$isComplete)
            <span class="flex items-center gap-2 text-xs text-slate-500">
                <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                Live
            </span>
            @endif
            <span class="text-xs text-slate-500">{{ count($responses) }} responses</span>
        </div>
    </div>

    {{-- Question Being Asked --}}
    @if(isset($sessionData['question']))
    <div class="p-4 bg-purple-500/10 border-b border-purple-500/20">
        <div class="text-xs text-purple-300 uppercase font-semibold mb-1">Question</div>
        <p class="text-white text-sm leading-relaxed">{{ $sessionData['question'] }}</p>
        @if(isset($sessionData['context']) && $sessionData['context'])
        <p class="text-slate-400 text-xs mt-2">
            <strong class="text-purple-300">Context:</strong> {{ $sessionData['context'] }}
        </p>
        @endif
    </div>
    @endif

    {{-- Responses Feed --}}
    <div class="p-4 space-y-3 max-h-[500px] overflow-y-auto" id="discussion-feed-{{ $sessionId }}">
        @forelse($responses as $response)
        <div class="p-4 bg-white/[0.02] rounded-xl border border-white/5 animate-fade-in">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-2xl">{{ $response['avatar'] }}</span>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white">{{ $response['member_name'] }}</span>
                        <span class="text-xs text-slate-500">{{ $response['member_role'] }}</span>
                        @if(isset($response['round']))
                        <span class="text-xs px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            Round {{ $response['round'] }}
                        </span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-600 mt-0.5">
                        {{ $response['timestamp'] ?? $response['created_at'] }}
                    </div>
                </div>
            </div>
            <div class="text-sm text-slate-300 leading-relaxed discussion-response" 
                 data-markdown="{{ e($response['response']) }}">
                {!! Str::markdown($response['response']) !!}
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-slate-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-sm">Waiting for board members to respond...</p>
        </div>
        @endforelse
    </div>

    {{-- CEO Recommendation (shown when complete) --}}
    @if($isComplete && $ceoRecommendation)
    <div class="p-4 bg-gradient-to-r from-amber-500/10 to-orange-500/10 border-t border-amber-500/20">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-xl">🎯</div>
            <div>
                <div class="text-sm font-bold text-white">CEO Recommendation</div>
                <div class="text-xs text-amber-300">Final Decision</div>
            </div>
        </div>
        <div class="text-sm text-slate-300 leading-relaxed discussion-response" 
             data-markdown="{{ e($ceoRecommendation) }}">
            {!! Str::markdown($ceoRecommendation) !!}
        </div>
    </div>
    @endif
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

@push('scripts')
<script>
    // Auto-scroll to bottom when new responses arrive
    document.addEventListener('livewire:initialized', () => {
        @this.on('scroll-to-bottom', () => {
            const feed = document.getElementById('discussion-feed-{{ $sessionId }}');
            if (feed) {
                setTimeout(() => {
                    feed.scrollTop = feed.scrollHeight;
                }, 300);
            }
        });
    });
</script>
@endpush
