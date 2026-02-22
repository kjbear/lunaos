<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#e4e4f0]">🎤 Standup Recording</h1>
            <p class="text-sm text-[#6b6b80] mt-1">Record daily standups and track action items</p>
        </div>
        <div class="flex items-center gap-3">
            <button
                wire:click="toggleHistory"
                class="btn btn-secondary text-sm"
            >
                📚 History
            </button>
        </div>
    </div>

    <!-- History Panel -->
    @if($showHistory)
        <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
            <h3 class="text-sm font-semibold text-[#e4e4f0] mb-3">Recent Standups</h3>
            <div class="space-y-2">
                @forelse($recentStandups as $standup)
                    <button
                        wire:click="loadStandup({{ $standup->id }})"
                        class="w-full flex items-center justify-between p-3 bg-[#252542] rounded-lg hover:bg-[#7c3aed]/10 transition-colors text-left"
                    >
                        <div>
                            <div class="text-sm font-medium text-[#e4e4f0]">{{ $standup->date->format('l, F j, Y') }}</div>
                            <div class="text-xs text-[#6b6b80]">{{ $standup->team }} • {{ $standup->deliverables->count() }} deliverables • {{ $standup->actionItems->count() }} action items</div>
                        </div>
                        <span class="text-lg">→</span>
                    </button>
                @empty
                    <div class="text-center text-[#6b6b80] py-4">No standups recorded yet</div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Flash Message -->
    @if(session()->has('message'))
        <div class="bg-[#10b981]/20 border border-[#10b981] text-[#6ee7b7] px-4 py-3 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Main Form -->
    <div class="grid grid-cols-2 gap-6">
        <!-- Left Column: Form -->
        <div class="space-y-4">
            <!-- Date & Team -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-[#a0a0b8] mb-1">Date</label>
                        <input
                            type="date"
                            wire:model="date"
                            class="w-full bg-[#252542] border border-[#2a2a40] rounded-lg px-3 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#a0a0b8] mb-1">Team</label>
                        <input
                            type="text"
                            wire:model="team"
                            class="w-full bg-[#252542] border border-[#2a2a40] rounded-lg px-3 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                        >
                    </div>
                </div>
                <div class="mt-3">
                    <label class="block text-xs font-medium text-[#a0a0b8] mb-1">Facilitator</label>
                    <input
                        type="text"
                        wire:model="facilitator"
                        class="w-full bg-[#252542] border border-[#2a2a40] rounded-lg px-3 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                    >
                </div>
            </div>

            <!-- Recording Interface -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-[#e4e4f0]">Recording</h3>
                    <button
                        wire:click="toggleRecording"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium {{ $isRecording ? 'bg-[#ef4444] text-white' : 'bg-[#7c3aed] text-white' }}"
                    >
                        @if($isRecording)
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            Stop
                        @else
                            <span>⏺</span>
                            Record
                        @endif
                    </button>
                </div>
                <div class="text-center py-8 text-[#6b6b80]">
                    @if($isRecording)
                        <div class="text-4xl font-mono text-[#ef4444]">{{ gmdate('i:s', $recordingTime) }}</div>
                        <div class="text-sm mt-2">Recording in progress...</div>
                    @else
                        <div class="text-4xl">🎤</div>
                        <div class="text-sm mt-2">Click Record to start</div>
                    @endif
                </div>
            </div>

            <!-- Transcript -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <label class="block text-sm font-semibold text-[#e4e4f0] mb-2">Transcript / Notes</label>
                <textarea
                    wire:model="transcript"
                    rows="6"
                    placeholder="Paste transcript or type notes here..."
                    class="w-full bg-[#252542] border border-[#2a2a40] rounded-lg px-3 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed] resize-none"
                ></textarea>
            </div>
        </div>

        <!-- Right Column: Deliverables & Action Items -->
        <div class="space-y-4">
            <!-- Deliverables -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-[#e4e4f0]">Deliverables</h3>
                    <button
                        wire:click="addDeliverable"
                        class="text-xs text-[#7c3aed] hover:text-[#9d5cf5]"
                    >
                        + Add
                    </button>
                </div>
                <div class="space-y-2">
                    @forelse($deliverables as $index => $deliverable)
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                wire:model="deliverables.{{ $index }}.title"
                                placeholder="What was delivered?"
                                class="flex-1 bg-[#252542] border border-[#2a2a40] rounded-lg px-3 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                            >
                            <button
                                wire:click="removeDeliverable({{ $index }})"
                                class="p-2 text-[#6b6b80] hover:text-[#ef4444]"
                            >
                                ✕
                            </button>
                        </div>
                    @empty
                        <div class="text-center text-[#6b6b80] py-4 text-sm">No deliverables added</div>
                    @endforelse
                </div>
            </div>

            <!-- Action Items -->
            <div class="bg-[#1a1a2e] rounded-lg border border-[#2a2a40] p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-[#e4e4f0]">Action Items</h3>
                    <button
                        wire:click="addActionItem"
                        class="text-xs text-[#7c3aed] hover:text-[#9d5cf5]"
                    >
                        + Add
                    </button>
                </div>
                <div class="space-y-2">
                    @forelse($actionItems as $index => $item)
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                wire:model="actionItems.{{ $index }}.title"
                                placeholder="Action item..."
                                class="flex-1 bg-[#252542] border border-[#2a2a40] rounded-lg px-3 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                            >
                            <input
                                type="text"
                                wire:model="actionItems.{{ $index }}.assigned_to"
                                placeholder="Who?"
                                class="w-24 bg-[#252542] border border-[#2a2a40] rounded-lg px-2 py-2 text-sm text-[#e4e4f0] focus:outline-none focus:border-[#7c3aed]"
                            >
                            <button
                                wire:click="removeActionItem({{ $index }})"
                                class="p-2 text-[#6b6b80] hover:text-[#ef4444]"
                            >
                                ✕
                            </button>
                        </div>
                    @empty
                        <div class="text-center text-[#6b6b80] py-4 text-sm">No action items added</div>
                    @endforelse
                </div>
            </div>

            <!-- Save Button -->
            <button
                wire:click="save"
                class="w-full py-3 bg-[#7c3aed] text-white rounded-lg font-medium hover:bg-[#9d5cf5] transition-colors"
            >
                💾 Save Standup
            </button>
        </div>
    </div>
</div>