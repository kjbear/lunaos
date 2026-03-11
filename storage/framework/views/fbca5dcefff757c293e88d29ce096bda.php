<?php $__env->startSection('title', 'Edit ' . $member->name . ' - Team - LunaOS'); ?>

<?php $__env->startSection('content'); ?>
<div class="team-edit space-y-6">
    
    <div class="mb-4">
        <a href="<?php echo e(route('team.show', $member->id)); ?>" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to <?php echo e($member->name); ?></span>
        </a>
    </div>
    
    
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center gap-5 p-6">
            <?php
                $gradients = [
                    'workers' => ['from-blue-500/20 to-cyan-500/20', 'border-blue-500/30'],
                    'personas' => ['from-purple-500/20 to-pink-500/20', 'border-purple-500/30'],
                    'board-members' => ['from-amber-500/20 to-orange-500/20', 'border-amber-500/30'],
                ];
                $g = $gradients[$member->type] ?? $gradients['workers'];
            ?>
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?php echo e($g[0]); ?> <?php echo e($g[1]); ?> flex items-center justify-center text-3xl shadow-lg">
                <?php echo e($member->emoji ?? '👤'); ?>

            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Edit <?php echo e($member->name); ?></h1>
                <p class="text-sm text-slate-400 font-medium mt-0.5">Update member details and settings</p>
            </div>
        </div>
    </header>
    
    
    <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
        <form action="<?php echo e(route('team.update', $member->id)); ?>" method="POST" class="space-y-6">
            <?php echo method_field('PUT'); ?>
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Name *</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $member->name)); ?>" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           placeholder="Enter member name">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-400 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Email</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $member->email)); ?>" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           placeholder="member@example.com">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-400 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Title</label>
                    <input type="text" name="title" value="<?php echo e(old('title', $member->title)); ?>" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., Senior Developer">
                </div>
                
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Role *</label>
                    <select name="role" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="worker" <?php echo e(old('role', $member->role) === 'worker' ? 'selected' : ''); ?>>🤖 Worker</option>
                        <option value="persona" <?php echo e(old('role', $member->role) === 'persona' ? 'selected' : ''); ?>>🎭 Persona</option>
                        <option value="board_member" <?php echo e(old('role', $member->role) === 'board_member' ? 'selected' : ''); ?>>👔 Board Member</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-400 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Type *</label>
                    <select name="type" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="workers" <?php echo e(old('type', $member->type) === 'workers' ? 'selected' : ''); ?>>🤖 Workers</option>
                        <option value="personas" <?php echo e(old('type', $member->type) === 'personas' ? 'selected' : ''); ?>>🎭 Personas</option>
                        <option value="board-members" <?php echo e(old('type', $member->type) === 'board-members' ? 'selected' : ''); ?>>👔 Board Members</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-400 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Status</label>
                    <select name="status" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors">
                        <option value="active" <?php echo e(old('status', $member->status) === 'active' ? 'selected' : ''); ?>>🟢 Active</option>
                        <option value="inactive" <?php echo e(old('status', $member->status) === 'inactive' ? 'selected' : ''); ?>>⚫ Inactive</option>
                        <option value="online" <?php echo e(old('status', $member->status) === 'online' ? 'selected' : ''); ?>>🟢 Online</option>
                        <option value="offline" <?php echo e(old('status', $member->status) === 'offline' ? 'selected' : ''); ?>>⚪ Offline</option>
                        <option value="busy" <?php echo e(old('status', $member->status) === 'busy' ? 'selected' : ''); ?>>🟡 Busy</option>
                        <option value="error" <?php echo e(old('status', $member->status) === 'error' ? 'selected' : ''); ?>>🔴 Error</option>
                    </select>
                </div>
                
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">AI Model</label>
                    <input type="text" name="model" value="<?php echo e(old('model', $member->model)); ?>" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., gpt-4, claude-3">
                </div>
                
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Provider</label>
                    <input type="text" name="provider" value="<?php echo e(old('provider', $member->provider)); ?>" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., openai, anthropic">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Avatar Emoji</label>
                    <input type="text" name="emoji" value="<?php echo e(old('emoji', $member->emoji)); ?>" 
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors" 
                           placeholder="e.g., 🤖, 🎭, 👔"
                           maxlength="10">
                    <p class="text-xs text-slate-500 mt-1">A single emoji to represent this member</p>
                </div>
                
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($parents) && $parents->count() > 0): ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-2">Reports To</label>
                    <select name="parent_id" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors">
                        <option value="">— None —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($parent->id); ?>" <?php echo e(old('parent_id', $member->parent_id) === $parent->id ? 'selected' : ''); ?>>
                            <?php echo e($parent->name); ?> (<?php echo e(ucfirst(str_replace('-', ' ', $parent->type))); ?>)
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-2">System Prompt</label>
                <textarea name="system_prompt" rows="4"
                          class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2.5 text-white focus:border-purple-400 focus:outline-none transition-colors font-mono text-sm"
                          placeholder="Enter the system prompt for AI-powered members..."><?php echo e(old('system_prompt', $member->system_prompt)); ?></textarea>
                <p class="text-xs text-slate-500 mt-1">For personas and workers, this defines their behavior and personality</p>
            </div>
            
            
            <div class="flex gap-3 pt-4 border-t border-white/10">
                <a href="<?php echo e(route('team.show', $member->id)); ?>" 
                   class="px-4 py-2.5 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-500 hover:to-pink-500 transition-all font-medium shadow-lg shadow-purple-500/20">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('components.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/team/edit.blade.php ENDPATH**/ ?>