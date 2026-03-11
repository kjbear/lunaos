<?php $__env->startSection('title', $member->name . ' - Team - LunaOS'); ?>

<?php $__env->startSection('content'); ?>
<div class="team-show space-y-6" x-data="{ activeTab: 'overview' }">
    
    <div class="mb-4">
        <a href="<?php echo e(route('team')); ?>" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to Team</span>
        </a>
    </div>
    
    
    <div class="group relative overflow-hidden bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 hover:border-purple-500/30 transition-all duration-300">
        <?php
            $gradients = [
                'workers' => ['from-blue-500/20 to-cyan-500/20', 'from-blue-500/10 to-cyan-500/10', 'border-blue-500/30', 'from-blue-600 to-cyan-600', 'shadow-blue-500/20'],
                'personas' => ['from-purple-500/20 to-pink-500/20', 'from-purple-500/10 to-pink-500/10', 'border-purple-500/30', 'from-purple-600 to-pink-600', 'shadow-purple-500/20'],
                'board-members' => ['from-amber-500/20 to-orange-500/20', 'from-amber-500/10 to-orange-500/10', 'border-amber-500/30', 'from-amber-600 to-orange-600', 'shadow-amber-500/20'],
            ];
            $g = $gradients[$member->type] ?? $gradients['workers'];
        ?>
        
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br <?php echo e($g[1]); ?> rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        
        
        <div class="relative flex items-start gap-6 p-6 border-b border-white/10">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br <?php echo e($g[0]); ?> <?php echo e($g[2]); ?> flex items-center justify-center text-4xl shadow-lg flex-shrink-0">
                <?php echo e($member->emoji ?? ($member->type === 'workers' ? '🤖' : ($member->type === 'personas' ? '🎭' : '👔'))); ?>

            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-white"><?php echo e($member->name); ?></h1>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">
                        <?php echo e(ucfirst(str_replace('-', ' ', $member->type))); ?>

                    </span>
                    <span class="px-3 py-1 rounded-lg text-xs font-semibold <?php echo e($member->status === 'active' || $member->status === 'online' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border border-slate-500/30'); ?>">
                        <?php echo e(ucfirst($member->status)); ?>

                    </span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->title): ?>
                    <p class="text-slate-400 text-lg"><?php echo e($member->title); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->email): ?>
                    <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                        <span>📧</span>
                        <span><?php echo e($member->email); ?></span>
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            
            <div class="flex gap-2">
                <a href="<?php echo e(route('team.edit', $member->id)); ?>" 
                   class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
                    ✏️ Edit
                </a>
                <form action="<?php echo e(route('team.destroy', $member->id)); ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this member?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" 
                            class="px-4 py-2 text-sm bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-all border border-red-500/30 font-medium">
                        🗑️ Delete
                    </button>
                </form>
            </div>
        </div>
        
        
        <div class="relative border-b border-white/10">
            <nav class="flex gap-1 px-6" role="tablist">
                <button 
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'bg-white/10 text-white border-b-2 border-purple-500' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                    class="px-4 py-3 text-sm font-medium transition-colors rounded-t-lg"
                    role="tab"
                    :aria-selected="activeTab === 'overview'"
                >
                    📋 Overview
                </button>
                <button 
                    @click="activeTab = 'ai-config'"
                    :class="activeTab === 'ai-config' ? 'bg-white/10 text-white border-b-2 border-purple-500' : 'text-slate-400 hover:text-white hover:bg-white/5'"
                    class="px-4 py-3 text-sm font-medium transition-colors rounded-t-lg"
                    role="tab"
                    :aria-selected="activeTab === 'ai-config'"
                >
                    🤖 AI Config
                </button>
            </nav>
        </div>
    </div>
    
    
    <div class="tabs-body">
        
        <div x-show="activeTab === 'overview'" x-cloak>
            <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                    
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Information</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Role</span>
                                <span class="text-white font-medium"><?php echo e(ucfirst($member->role)); ?></span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->model): ?>
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Model</span>
                                <span class="text-white font-medium"><?php echo e($member->model); ?></span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->provider): ?>
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Provider</span>
                                <span class="text-white font-medium"><?php echo e($member->provider); ?></span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="flex justify-between items-center py-2 border-b border-white/5">
                                <span class="text-slate-500">Member Since</span>
                                <span class="text-white font-medium"><?php echo e($member->created_at?->diffForHumans() ?? 'Recently'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="space-y-4">
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Stats</h2>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Tasks</p>
                                <p class="text-2xl font-bold text-white mt-1"><?php echo e($member->tasks->count()); ?></p>
                            </div>
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Reports</p>
                                <p class="text-2xl font-bold text-white mt-1"><?php echo e($member->children->count()); ?></p>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->parent): ?>
                        <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                            <p class="text-xs text-slate-400 uppercase tracking-wider">Reports To</p>
                            <a href="<?php echo e(route('team.show', $member->parent->id)); ?>" class="text-purple-400 hover:text-purple-300 font-medium mt-1 block">
                                <?php echo e($member->parent->name); ?>

                            </a>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->children->count() > 0): ?>
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Direct Reports</h2>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $member->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('team.show', $child->id)); ?>" 
                               class="flex items-center gap-3 p-3 bg-white/5 rounded-lg border border-white/10 hover:bg-white/10 transition-colors">
                                <span class="text-lg"><?php echo e($child->emoji ?? '👤'); ?></span>
                                <span class="text-white font-medium"><?php echo e($child->name); ?></span>
                                <span class="ml-auto text-xs text-slate-500"><?php echo e(ucfirst($child->type)); ?></span>
                            </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php else: ?>
                        <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Tasks Assigned</h2>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->tasks->count() > 0): ?>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $member->tasks->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10">
                                <span class="text-white text-sm truncate flex-1"><?php echo e($task->title); ?></span>
                                <span class="text-xs px-2 py-1 rounded ml-2 <?php echo e($task->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'); ?>">
                                    <?php echo e(ucfirst($task->status)); ?>

                                </span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->tasks->count() > 5): ?>
                            <p class="text-xs text-slate-500 text-center">+<?php echo e($member->tasks->count() - 5); ?> more</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-6 text-slate-500">
                            <p>No tasks assigned</p>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div x-show="activeTab === 'ai-config'" x-cloak>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('team.member-ai-config', ['memberId' => $member->id]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1757904698-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    </div>
</div>


<style>
    [x-cloak] { display: none !important; }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('components.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/team/show.blade.php ENDPATH**/ ?>