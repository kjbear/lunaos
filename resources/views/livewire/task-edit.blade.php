<div class="task-edit" x-data="{
    deleteReason: '',
    showDeleteConfirm: false
}">
    {{-- Header --}}
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">
                        {{ $isCreate ? '➕' : '✏️' }}
                    </div>
                </div>
                
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">
                        {{ $isCreate ? 'Create New Task' : 'Edit Task' }}
                    </h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">
                        {{ $isCreate ? 'Add a new task to the workflow' : 'Update task details' }}
                    </p>
                </div>
            </div>
            
            <button 
                wire:click="cancel"
                class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all text-sm font-medium"
            >
                ← Back
            </button>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
        <div class="text-red-400 font-semibold mb-2">Please fix the following errors:</div>
        <ul class="list-disc list-inside text-sm text-red-300">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Task Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Basic Info --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Basic Information</h3>
                
                <div class="space-y-4">
                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Title <span class="text-red-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            wire:model="title"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all"
                            placeholder="Enter task title..."
                        >
                    </div>
                    
                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Description
                        </label>
                        <textarea 
                            wire:model="description"
                            rows="4"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all resize-none"
                            placeholder="Describe the task in detail..."
                        ></textarea>
                    </div>
                </div>
            </div>

            {{-- Workflow Settings --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Workflow Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Step --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Step <span class="text-red-400">*</span>
                        </label>
                        <select 
                            wire:model="step"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                        >
                            @foreach($steps as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Status <span class="text-red-400">*</span>
                        </label>
                        <select 
                            wire:model="status"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                        >
                            <option value="pending">⏳ Pending</option>
                            <option value="in_progress">🔄 In Progress</option>
                            <option value="complete">✅ Complete</option>
                            <option value="blocked">🚫 Blocked</option>
                            <option value="failed">❌ Failed</option>
                        </select>
                    </div>
                    
                    {{-- Priority --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Priority <span class="text-red-400">*</span>
                        </label>
                        <select 
                            wire:model="priority"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                        >
                            @foreach($priorities as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Task Type --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Type <span class="text-red-400">*</span>
                        </label>
                        <select 
                            wire:model="task_type"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                        >
                            @foreach($taskTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Assignment --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Assignment</h3>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Assigned To
                    </label>
                    <select 
                        wire:model="assigned_to"
                        class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="">— Unassigned —</option>
                        @foreach($agents as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Additional Info --}}
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Additional Info</h3>
                
                <div class="space-y-4">
                    {{-- Branch Name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Branch Name
                        </label>
                        <input 
                            type="text" 
                            wire:model="branch_name"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all"
                            placeholder="feature/xxx"
                        >
                    </div>
                    
                    {{-- PR URL --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            PR URL
                        </label>
                        <input 
                            type="url" 
                            wire:model="pr_url"
                            class="w-full bg-slate-800/50 border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400/50 focus:ring-1 focus:ring-cyan-400/50 transition-all"
                            placeholder="https://github.com/..."
                        >
                    </div>
                </div>
            </div>

            {{-- Failure Reason (only if failed) --}}
            @if($status === 'failed')
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-red-500/20">
                <h3 class="text-lg font-semibold text-red-400 mb-4">Failure Reason</h3>
                
                <div>
                    <textarea 
                        wire:model="failure_reason"
                        rows="3"
                        class="w-full bg-slate-800/50 border border-red-500/30 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-red-400/50 focus:ring-1 focus:ring-red-400/50 transition-all resize-none"
                        placeholder="Why did this task fail?"
                    ></textarea>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex flex-col gap-3">
                <button 
                    wire:click="save"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-semibold hover:from-cyan-400 hover:to-purple-400 transition-all shadow-lg hover:shadow-xl"
                >
                    {{ $isCreate ? 'Create Task' : 'Save Changes' }}
                </button>
                
                <button 
                    wire:click="cancel"
                    class="w-full py-3 px-4 rounded-xl bg-slate-800/50 text-slate-300 font-medium hover:bg-slate-700/50 transition-all border border-white/10"
                >
                    Cancel
                </button>
                
                @if(!$isCreate && $task)
                <button 
                    x-on:click="showDeleteConfirm = true"
                    class="w-full py-3 px-4 rounded-xl bg-red-500/10 text-red-400 font-medium hover:bg-red-500/20 transition-all border border-red-500/30"
                >
                    Delete Task
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div 
        x-show="showDeleteConfirm"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
    >
        <div class="bg-slate-900 rounded-2xl p-6 max-w-md w-full border border-white/10" x-on:click.outside="showDeleteConfirm = false">
            <h3 class="text-xl font-bold text-white mb-3">Delete Task?</h3>
            <p class="text-slate-400 mb-6">This action cannot be undone. All task history will be lost.</p>
            
            <div class="flex gap-3">
                <button 
                    wire:click="deleteTask({{ $task->id }})"
                    class="flex-1 py-2.5 px-4 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-400 transition-all"
                >
                    Delete
                </button>
                <button 
                    x-on:click="showDeleteConfirm = false"
                    class="flex-1 py-2.5 px-4 rounded-xl bg-slate-800 text-slate-300 font-medium hover:bg-slate-700 transition-all"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
