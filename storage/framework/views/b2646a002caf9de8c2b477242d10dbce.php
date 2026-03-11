<div class="task-board-unified" x-data="{
    handleDragStart(e) { 
        e.dataTransfer.setData('taskId', e.target.dataset.taskId); 
        e.target.style.opacity = '0.4'; 
    },
    handleDrop(e, newStep) { 
        e.preventDefault(); 
        const taskId = e.dataTransfer.getData('taskId'); 
        $wire.$call('moveTask', taskId, newStep);
    }
}">
    
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-5">
                    <div class="group relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                        <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-amber-500 flex items-center justify-center text-3xl shadow-xl">
                            📊
                        </div>
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">Task Board</h1>
                        <p class="text-sm text-slate-400 font-medium mt-0.5">Kanban-style workflow visualization</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($autoRefresh): ?>
                    <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-semibold text-emerald-400">Auto-refresh ON</span>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <button 
                        wire:click="$refresh"
                        class="group relative p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all duration-200"
                    >
                        <span class="group-hover:rotate-180 transition-transform duration-500 block">↻</span>
                    </button>
                </div>
            </div>
            
            
            <div class="flex flex-wrap gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['total' => 'Total', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed_today' => 'Today']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="group relative flex-1 min-w-[150px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-blue-400/30 transition-all">
                        <div class="text-sm text-slate-400 font-medium mb-1"><?php echo e($label); ?></div>
                        <div class="text-2xl font-bold text-white"><?php echo e($stats[$key] ?? 0); ?></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </header>

    
    <section class="mb-6">
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <div class="flex items-center gap-6 overflow-x-auto">
                <span class="text-sm font-medium text-slate-400">Filter by Agent:</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['all', 'dave', 'sam', 'chen', 'security']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button 
                    wire:click="$set('selectedAgent', '<?php echo e($agent); ?>')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap
                        <?php echo e($selectedAgent === $agent 
                            ? 'bg-gradient-to-r from-cyan-500/20 to-purple-500/20 text-white border border-cyan-400/50' 
                            : 'bg-slate-800/50 text-slate-400 border border-white/10 hover:border-white/20 hover:text-white'); ?>"
                >
                    <?php echo e($agent === 'all' ? 'All' : ucfirst($agent)); ?>

                    <span class="ml-2 px-2 py-0.5 rounded-full bg-white/10 text-xs">
                        <?php echo e($agentCounts[$agent] ?? 0); ?>

                    </span>
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="overflow-x-auto">
        <div class="flex gap-4 min-w-[1200px]">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groupedTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step => $tasks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex-1 min-w-[280px]">
                
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xl"><?php echo e($stepIcons[$step] ?? '📋'); ?></span>
                    <h3 class="font-semibold text-white uppercase tracking-wide"><?php echo e($columns[$step]); ?></h3>
                    <span class="px-2.5 py-0.5 rounded-full bg-white/10 text-xs font-mono text-slate-400">
                        <?php echo e(count($tasks)); ?>

                    </span>
                </div>
                
                
                <div class="bg-slate-900/40 backdrop-blur-sm rounded-xl p-3 border border-white/5 min-h-[400px] max-h-[700px] overflow-y-auto">
                    <div class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div 
                            class="group relative bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-white/10 hover:border-cyan-400/30 transition-all duration-200 cursor-move hover:shadow-lg hover:-translate-y-0.5"
                            draggable="true"
                            x-on:dragstart="handleDragStart"
                            x-data="{ taskId: '<?php echo e($task->id); ?>' }"
                            :data-task-id="taskId"
                        >
                            
                            <div class="absolute left-0 top-3 bottom-3 w-1 rounded-r
                                <?php if($task->priority === 'critical'): ?> bg-red-500
                                <?php elseif($task->priority === 'high'): ?> bg-orange-500
                                <?php elseif($task->priority === 'medium'): ?> bg-yellow-500
                                <?php else: ?> bg-slate-500
                                <?php endif; ?>
                            "></div>
                            
                            
                            <div class="ml-2">
                                <div class="flex items-start justify-between mb-2">
                                    <span class="text-xs font-mono text-slate-500">#<?php echo e($task->id); ?></span>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->assigned_to): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full
                                        <?php if($task->assigned_to === 'dave'): ?> bg-blue-500/20 text-blue-400
                                        <?php elseif($task->assigned_to === 'sam'): ?> bg-emerald-500/20 text-emerald-400
                                        <?php elseif($task->assigned_to === 'chen'): ?> bg-purple-500/20 text-purple-400
                                        <?php elseif($task->assigned_to === 'security'): ?> bg-orange-500/20 text-orange-400
                                        <?php else: ?> bg-slate-500/20 text-slate-400
                                        <?php endif; ?>
                                    ">
                                        <?php echo e(ucfirst($task->assigned_to)); ?>

                                    </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <h4 class="text-sm font-semibold text-white mb-2 group-hover:text-cyan-400 transition-colors">
                                    <?php echo e($task->title); ?>

                                </h4>
                                
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        <?php if($task->priority === 'critical'): ?> bg-red-500/20 text-red-400
                                        <?php elseif($task->priority === 'high'): ?> bg-orange-500/20 text-orange-400
                                        <?php elseif($task->priority === 'medium'): ?> bg-yellow-500/20 text-yellow-400
                                        <?php else: ?> bg-slate-500/20 text-slate-400
                                        <?php endif; ?>
                                    ">
                                        <?php echo e(ucfirst($task->priority)); ?>

                                    </span>
                                    
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        <?php if($task->status === 'in_progress'): ?> bg-blue-500/20 text-blue-400
                                        <?php elseif($task->status === 'pending'): ?> bg-slate-500/20 text-slate-400
                                        <?php else: ?> bg-slate-500/20 text-slate-400
                                        <?php endif; ?>
                                    ">
                                        <?php echo e(ucfirst($task->status)); ?>

                                    </span>
                                </div>
                                
                                <div class="text-xs text-slate-500 flex items-center gap-2">
                                    <span>🕐 <?php echo e($task->created_at->diffForHumans(short: true)); ?></span>
                                </div>
                            </div>
                            
                            
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-1">
                                <button 
                                    wire:click="viewTask(<?php echo e($task->id); ?>)"
                                    class="p-1.5 rounded-lg bg-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-600 transition-all"
                                    title="View Details"
                                >
                                    👁
                                </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->getNextStep()): ?>
                                <button 
                                    wire:click="completeTask(<?php echo e($task->id); ?>)"
                                    class="p-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 transition-all"
                                    title="Advance to Next Step"
                                >
                                    →
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-8 text-slate-500 text-sm">
                            No tasks in <?php echo e(strtolower($columns[$step])); ?>

                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
</div><?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/livewire/task-board-unified.blade.php ENDPATH**/ ?>