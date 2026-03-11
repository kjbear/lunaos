<div class="member-ai-config space-y-6">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$member): ?>
        <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-4 text-red-400">
            <p>⚠️ Unable to load member data. Please try refreshing the page.</p>
        </div>
    <?php else: ?>
    
    <div class="mb-4">
        <a href="<?php echo e(route('team.show', $member->id)); ?>" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors">
            <span>←</span>
            <span>Back to Member</span>
        </a>
    </div>

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">AI Configuration</h1>
            <p class="text-slate-400 mt-1">Configure AI behavior for <?php echo e($member->name); ?></p>
        </div>
        <button type="button" wire:click="resetToDefaults" 
                class="px-4 py-2 text-sm bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
            ↻ Reset to Defaults
        </button>
    </div>
    <form wire:submit="save" class="space-y-8">
        
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>🧠</span> Model Settings
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Model</label>
                    <select wire:model="model" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableModels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['model'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Response Style</label>
                    <select wire:model="responseStyle" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $responseStyles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['responseStyle'];
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
        </div>

        
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>⚙️</span> Generation Parameters
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Temperature
                        <span class="text-slate-500 text-xs ml-2">(<?php echo e($temperature); ?>)</span>
                    </label>
                    <input type="range" wire:model.live="temperature" min="0" max="2" step="0.1"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    <div class="flex justify-between text-xs text-slate-500 mt-1">
                        <span>Precise</span>
                        <span>Creative</span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['temperature'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Max Tokens
                    </label>
                    <input type="number" wire:model="maxTokens" min="1" max="128000"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maxTokens'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Top P
                        <span class="text-slate-500 text-xs ml-2">(<?php echo e($topP); ?>)</span>
                    </label>
                    <input type="range" wire:model.live="topP" min="0" max="1" step="0.05"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['topP'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Frequency Penalty
                        <span class="text-slate-500 text-xs ml-2">(<?php echo e($frequencyPenalty); ?>)</span>
                    </label>
                    <input type="range" wire:model.live="frequencyPenalty" min="-2" max="2" step="0.1"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['frequencyPenalty'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        Presence Penalty
                        <span class="text-slate-500 text-xs ml-2">(<?php echo e($presencePenalty); ?>)</span>
                    </label>
                    <input type="range" wire:model.live="presencePenalty" min="-2" max="2" step="0.1"
                           class="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-purple-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['presencePenalty'];
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
        </div>

        
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>📝</span> Prompts & Instructions
            </h2>
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">System Prompt</label>
                    <textarea wire:model="systemPrompt" rows="4"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors font-mono text-sm"
                              placeholder="Enter the base system prompt for this AI team member..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['systemPrompt'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Persona Description</label>
                    <textarea wire:model="personaDescription" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors text-sm"
                              placeholder="Describe the persona, character traits, and communication style..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['personaDescription'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Special Instructions</label>
                    <textarea wire:model="specialInstructions" rows="3"
                              class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors text-sm"
                              placeholder="Any specific instructions or edge cases..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['specialInstructions'];
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
        </div>

        
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>🛠️</span> Capabilities
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableCapabilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" wire:click="toggleCapability('<?php echo e($key); ?>')"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'px-4 py-2 rounded-lg text-sm font-medium transition-all border',
                                in_array($key, $capabilities) 
                                    ? 'bg-purple-500/20 text-purple-300 border-purple-500/30' 
                                    : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10'
                            ]); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($key, $capabilities)): ?> ✓ <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php echo e($label); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['capabilities'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-400 text-sm mt-2"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="bg-gradient-to-br from-slate-900/60 to-slate-950/60 backdrop-blur-xl rounded-2xl border border-white/10 p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <span>📋</span> Task Settings
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Max Concurrent Tasks</label>
                    <input type="number" wire:model="maxConcurrentTasks" min="1" max="10"
                           class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maxConcurrentTasks'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Priority Level</label>
                    <select wire:model="priorityLevel" 
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $priorityLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['priorityLevel'];
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
                    <label class="block text-sm font-medium text-slate-300 mb-2">Auto-Assign</label>
                    <div class="flex items-center gap-3 mt-3">
                        <button type="button" wire:click="$toggle('autoAssign')"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                    $autoAssign ? 'bg-purple-500' : 'bg-white/20'
                                ]); ?>">
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                $autoAssign ? 'translate-x-6' : 'translate-x-1'
                            ]); ?>"></span>
                        </button>
                        <span class="text-slate-400 text-sm">
                            <?php echo e($autoAssign ? 'Enabled' : 'Disabled'); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex items-center justify-end gap-4">
            <a href="<?php echo e(route('team.show', $member->id)); ?>" 
               class="px-6 py-2 bg-white/5 text-slate-300 rounded-lg hover:bg-white/10 transition-all border border-white/10 font-medium">
                Cancel
            </a>
            <button type="submit" wire:loading.attr="disabled"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'px-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-all font-medium',
                        'opacity-50 cursor-not-allowed' => false
                    ]); ?>">
                <span wire:loading.remove>💾 Save Configuration</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/livewire/team/member-ai-config.blade.php ENDPATH**/ ?>