<div class="task-detail">
    
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">
                        📋
                    </div>
                </div>
                
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">
                        Task #<?php echo e($task->id); ?>

                    </h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">
                        <?php echo e($task->title); ?>

                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a 
                    href="<?php echo e(route('tasks.edit', $task->id)); ?>"
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-semibold hover:from-cyan-400 hover:to-purple-400 transition-all shadow-lg"
                >
                    ✏️ Edit
                </a>
                
                <a 
                    href="<?php echo e(route('tasks')); ?>"
                    class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all text-sm font-medium"
                >
                    ← Back
                </a>
            </div>
        </div>
    </header>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Description</h3>
                <p class="text-slate-300 whitespace-pre-wrap"><?php echo e($task->description ?? 'No description provided.'); ?></p>
            </div>

            
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Task Details</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Status</div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium
                            <?php if($task->status === 'in_progress'): ?> bg-blue-500/20 text-blue-400 border border-blue-500/30
                            <?php elseif($task->status === 'complete'): ?> bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                            <?php elseif($task->status === 'blocked'): ?> bg-red-500/20 text-red-400 border border-red-500/30
                            <?php elseif($task->status === 'failed'): ?> bg-red-500/20 text-red-400 border border-red-500/30
                            <?php else: ?> bg-slate-500/20 text-slate-400 border border-slate-500/30
                            <?php endif; ?>
                        ">
                            <?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?>

                        </span>
                    </div>
                    
                    
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Priority</div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium
                            <?php if($task->priority === 'critical'): ?> bg-red-500/20 text-red-400 border border-red-500/30
                            <?php elseif($task->priority === 'high'): ?> bg-orange-500/20 text-orange-400 border border-orange-500/30
                            <?php elseif($task->priority === 'medium'): ?> bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                            <?php else: ?> bg-slate-500/20 text-slate-400 border border-slate-500/30
                            <?php endif; ?>
                        ">
                            <?php echo e(ucfirst($task->priority)); ?>

                        </span>
                    </div>
                    
                    
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Workflow Step</div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-slate-700/50 text-slate-300 border border-slate-600/50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->step === 'develop'): ?>🔧
                            <?php elseif($task->step === 'qa'): ?>🧪
                            <?php elseif($task->step === 'security'): ?>🔒
                            <?php elseif($task->step === 'staging'): ?>🚀
                            <?php else: ?>✅
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e(ucfirst($task->step)); ?>

                        </span>
                    </div>
                    
                    
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Type</div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-slate-700/50 text-slate-300 border border-slate-600/50">
                            <?php echo e(ucfirst($task->task_type ?? 'feature')); ?>

                        </span>
                    </div>
                    
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->branch_name): ?>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Branch</div>
                        <code class="text-xs px-2 py-1 bg-slate-800 rounded text-cyan-400"><?php echo e($task->branch_name); ?></code>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->pr_url): ?>
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Pull Request</div>
                        <a href="<?php echo e($task->pr_url); ?>" target="_blank" class="text-cyan-400 hover:text-cyan-300 hover:underline text-sm flex items-center gap-1">
                            🔗 View PR
                            <span class="text-xs">↗</span>
                        </a>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Created</div>
                        <div class="text-sm text-white"><?php echo e($task->created_at->format('M j, Y g:i A')); ?></div>
                    </div>
                    
                    
                    <div>
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-2">Updated</div>
                        <div class="text-sm text-white"><?php echo e($task->updated_at->diffForHumans()); ?></div>
                    </div>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->status === 'failed' && $task->failure_reason): ?>
            <div class="bg-red-500/10 backdrop-blur-sm rounded-xl p-6 border border-red-500/30">
                <h3 class="text-lg font-semibold text-red-400 mb-4">Failure Reason</h3>
                <p class="text-red-200 whitespace-pre-wrap"><?php echo e($task->failure_reason); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="space-y-6">
            
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Assigned To</h3>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($agent): ?>
                <div class="flex items-center gap-4">
                    <div class="text-4xl">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($agent->name === 'dave'): ?>👨‍💻
                        <?php elseif($agent->name === 'sam'): ?>🔍
                        <?php elseif($agent->name === 'chen'): ?>⚙️
                        <?php elseif($agent->name === 'security'): ?>🔒
                        <?php else: ?>🤖
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <div class="text-white font-semibold"><?php echo e(ucfirst($agent->name)); ?></div>
                        <div class="text-sm text-white/70"><?php echo e($agent->title ?? 'Agent'); ?></div>
                        <?php if (isset($component)) { $__componentOriginal4f015fb6508e425790bdb8f79792e6ed = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f015fb6508e425790bdb8f79792e6ed = $attributes; } ?>
<?php $component = Mary\View\Components\Badge::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Mary\View\Components\Badge::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'neutral','class' => 'mt-1']); ?><?php echo e($agent->model ?? 'Unknown'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f015fb6508e425790bdb8f79792e6ed)): ?>
<?php $attributes = $__attributesOriginal4f015fb6508e425790bdb8f79792e6ed; ?>
<?php unset($__attributesOriginal4f015fb6508e425790bdb8f79792e6ed); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f015fb6508e425790bdb8f79792e6ed)): ?>
<?php $component = $__componentOriginal4f015fb6508e425790bdb8f79792e6ed; ?>
<?php unset($__componentOriginal4f015fb6508e425790bdb8f79792e6ed); ?>
<?php endif; ?>
                    </div>
                </div>
                <?php elseif($task->assigned_to): ?>
                <div class="text-slate-300 font-medium"><?php echo e(ucfirst($task->assigned_to)); ?></div>
                <div class="text-sm text-slate-400 mt-1">Agent details not available</div>
                <?php else: ?>
                <div class="text-slate-400 italic">Unassigned</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
                <h3 class="text-lg font-semibold text-white mb-4">Activity</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400">Events</span>
                        <span class="text-lg font-bold text-white"><?php echo e($activities->count()); ?></span>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->completed_at): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400">Completed</span>
                        <span class="text-sm text-emerald-400"><?php echo e($task->completed_at->diffForHumans()); ?></span>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-400">Age</span>
                        <span class="text-sm text-slate-300"><?php echo e($task->created_at->diffForHumans()); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <section class="mt-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-purple-500 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Activity History</h2>
        </div>
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-xl p-6 border border-white/10">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="relative pl-6 pb-6 last:pb-0 <?php echo e(!$loop->last ? 'border-l border-white/10' : ''); ?>">
                
                <div class="absolute left-[-5px] top-1 w-2.5 h-2.5 rounded-full bg-gradient-to-br from-cyan-400 to-purple-500 border-2 border-slate-900"></div>
                
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->agent_name === 'dave'): ?>👨‍💻
                            <?php elseif($activity->agent_name === 'sam'): ?>🔍
                            <?php elseif($activity->agent_name === 'chen'): ?>⚙️
                            <?php elseif($activity->agent_name === 'security'): ?>🔒
                            <?php elseif($activity->agent_name === 'manual'): ?>👤
                            <?php else: ?>🤖
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                        <div>
                            <div class="text-white font-semibold"><?php echo e(ucfirst($activity->agent_name)); ?></div>
                            <div class="text-xs text-slate-400"><?php echo e(str_replace('_', ' ', ucfirst($activity->action))); ?></div>
                        </div>
                    </div>
                    <div class="text-sm text-slate-400"><?php echo e($activity->created_at->format('M j, g:i A')); ?></div>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity->metadata_json && collect($activity->metadata_json)->filter()->isNotEmpty()): ?>
                <div class="mt-3 bg-slate-800/50 rounded-lg p-3 text-sm border border-white/5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activity->metadata_json['reason'])): ?>
                    <div class="text-slate-300 mb-2">
                        <span class="text-xs text-slate-500 uppercase">Reason:</span>
                        <?php echo e($activity->metadata_json['reason']); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activity->metadata_json['from_step']) && isset($activity->metadata_json['to_step'])): ?>
                    <div class="text-slate-300">
                        <span class="text-xs text-slate-500 uppercase">Step Change:</span>
                        <?php echo e($activity->metadata_json['from_step']); ?> → <?php echo e($activity->metadata_json['to_step']); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activity->metadata_json['from_status']) && isset($activity->metadata_json['to_status'])): ?>
                    <div class="text-slate-300">
                        <span class="text-xs text-slate-500 uppercase">Status Change:</span>
                        <?php echo e($activity->metadata_json['from_status']); ?> → <?php echo e($activity->metadata_json['to_status']); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activity->metadata_json['assignee'])): ?>
                    <div class="text-slate-300">
                        <span class="text-xs text-slate-500 uppercase">Assigned:</span>
                        <?php echo e($activity->metadata_json['assignee']); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activity->metadata_json['priority'])): ?>
                        <span class="text-slate-500"> (Priority: <?php echo e(ucfirst($activity->metadata_json['priority'])); ?>)</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activity->metadata_json['title'])): ?>
                    <div class="text-slate-300">
                        <span class="text-xs text-slate-500 uppercase">Title:</span>
                        <?php echo e($activity->metadata_json['title']); ?>

                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <details class="mt-2">
                        <summary class="text-xs text-slate-500 cursor-pointer hover:text-slate-400 transition-colors">View Full JSON</summary>
                        <pre class="mt-2 text-xs text-slate-400 overflow-x-auto bg-slate-900/80 rounded p-2"><?php echo e(json_encode($activity->metadata_json, JSON_PRETTY_PRINT)); ?></pre>
                    </details>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-slate-400 text-center py-8">No activity recorded yet</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
</div><?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/livewire/task-detail.blade.php ENDPATH**/ ?>