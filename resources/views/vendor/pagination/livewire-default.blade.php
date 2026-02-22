@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="text-sm text-[#6b6b80]">
            @if ($paginator->firstItem())
                Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
            @else
                Showing 0 results
            @endif
        </div>

        <div class="flex gap-1">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm text-[#6b6b80] bg-[#252542] rounded-lg cursor-not-allowed">
                    ← Prev
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="px-3 py-2 text-sm text-[#e4e4f0] bg-[#252542] rounded-lg hover:bg-[#7c3aed]/20 hover:text-[#7c3aed] transition-colors">
                    ← Prev
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-white bg-[#7c3aed] rounded-lg">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" class="px-3 py-2 text-sm text-[#e4e4f0] bg-[#252542] rounded-lg hover:bg-[#7c3aed]/20 hover:text-[#7c3aed] transition-colors">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @else
                    <span class="px-3 py-2 text-sm text-[#6b6b80]">
                        …
                    </span>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="px-3 py-2 text-sm text-[#e4e4f0] bg-[#252542] rounded-lg hover:bg-[#7c3aed]/20 hover:text-[#7c3aed] transition-colors">
                    Next →
                </button>
            @else
                <span class="px-3 py-2 text-sm text-[#6b6b80] bg-[#252542] rounded-lg cursor-not-allowed">
                    Next →
                </span>
            @endif
        </div>
    </nav>
@endif