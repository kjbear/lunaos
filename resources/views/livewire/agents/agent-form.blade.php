<div class="p-6">
    <form wire:submit="save" class="space-y-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" wire:model="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="e.g., dave">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <input type="text" wire:model="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="e.g., PHP Developer">
                @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Type & Emoji -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                <select wire:model="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <option value="worker">Worker</option>
                    <option value="board">Board</option>
                    <option value="executive">Executive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Emoji</label>
                <input type="text" wire:model="emoji" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 text-center text-xl" placeholder="🤖">
            </div>
        </div>

        <!-- Strategy -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Strategy *</label>
            <select wire:model="strategy_class" wire:change="updatedStrategyClass($event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                <option value="">Select a strategy...</option>
                @foreach($strategies as $strategyName => $strategyClass)
                <option value="{{ $strategyName }}">{{ ucfirst($strategyName) }}</option>
                @endforeach
            </select>
            @error('strategy_class') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Step Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Workflow Steps</label>
            <input type="text" wire:model="step_filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="e.g., develop or staging,production">
            <p class="mt-1 text-xs text-gray-500">Steps this agent will poll for</p>
        </div>

        <!-- Skill Doc -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Skill Definition Path</label>
            <input type="text" wire:model="skill_doc_path" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="skills/laravel-specialist/SKILL.md">
            <p class="mt-1 text-xs text-gray-500">Path to markdown skill definition file</p>
        </div>

        <!-- System Prompt -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">System Prompt</label>
            <textarea wire:model="system_prompt" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="You are a senior developer..."></textarea>
            <p class="mt-1 text-xs text-gray-500">Agent will also load skill doc constraints automatically</p>
        </div>

        <!-- Model Config -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                <input type="text" wire:model="model" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500" placeholder="qwen3-coder:latest">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Provider</label>
                <select wire:model="provider" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <option value="ollama">Ollama</option>
                    <option value="openrouter">OpenRouter</option>
                    <option value="anthropic">Anthropic</option>
                    <option value="openai">OpenAI</option>
                </select>
            </div>
        </div>

        <!-- Model Settings -->
        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
            <h3 class="text-sm font-medium text-gray-900">Model Settings</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Temperature</label>
                    <input type="number" wire:model="modelSettings.temperature" step="0.1" min="0" max="1" class="w-full px-2 py-1 border border-gray-300 rounded bg-white text-gray-900 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Max Tokens</label>
                    <input type="number" wire:model="modelSettings.max_tokens" class="w-full px-2 py-1 border border-gray-300 rounded bg-white text-gray-900 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Poll Interval (s)</label>
                    <input type="number" wire:model="modelSettings.poll_interval" min="10" class="w-full px-2 py-1 border border-gray-300 rounded bg-white text-gray-900 text-sm">
                </div>
            </div>
        </div>

        <!-- Runtime -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Runtime Location</label>
                <select wire:model="runtime_location" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <option value="php">PHP/Laravel (Local)</option>
                    <option value="openclaw">OpenClaw Gateway</option>
                </select>
            </div>
            <div class="flex items-center gap-2 pt-6">
                <input type="checkbox" wire:model="is_online" id="is_online" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                <label for="is_online" class="text-sm font-medium text-gray-700">Online (Active)</label>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3 pt-6 border-t border-gray-200">
            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">
                {{ $agent ? 'Update Agent' : 'Create Agent' }}
            </button>
            <button type="button" wire:click="$dispatch('close')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors font-medium">
                Cancel
            </button>
        </div>
    </form>
</div>
