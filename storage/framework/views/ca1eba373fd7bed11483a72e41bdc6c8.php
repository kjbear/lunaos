<div class="space-y-6">
    
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-400 to-pink-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 via-pink-500 to-indigo-500 flex items-center justify-center text-3xl shadow-xl">🎯</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Executive Board</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Strategic decision-making with AI executives</p>
                </div>
            </div>
            
            
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-400"><?php echo e($stats['total_sessions'] ?? 0); ?></div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Sessions</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400"><?php echo e($stats['decisions'] ?? 0); ?></div>
                    <div class="text-xs text-slate-400 font-semibold uppercase">Decisions</div>
                </div>
                <div class="h-10 w-px bg-white/10"></div>
                <button 
                    wire:click="resetSession"
                    class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                    title="New Session"
                >
                    🔄
                </button>
            </div>
        </div>
    </header>

    
    <div class="max-w-4xl mx-auto space-y-6">
        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Ask the Board</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$apiConfigured): ?>
                <span class="px-2.5 py-1 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold">
                    ⚠️ Add API Key
                </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-2">Your Strategic Question</label>
                    <textarea 
                        wire:model="question" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-purple-500/50 focus:outline-none resize-none"
                        rows="4"
                        placeholder="e.g., Should we prioritize LunaOS development or the Status Page Aggregator first?"
                    ></textarea>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-2">Additional Context (Optional)</label>
                    <textarea 
                        wire:model="context" 
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-slate-300 focus:border-purple-500/50 focus:outline-none resize-none"
                        rows="2"
                        placeholder="Add any relevant background, constraints, or considerations..."
                    ></textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    
                    <button 
                        wire:click="$dispatch('toast-info', {message: 'Wire click works!'})" 
                        class="px-4 py-3 bg-red-600 text-white font-semibold rounded-xl"
                    >
                        🧪 TEST
                    </button>
                    
                    <button 
                        wire:click="conveneBoard" 
                        wire:loading.attr="disabled"
                        wire:target="conveneBoard"
                        class="relative px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-500 hover:to-pink-500 transition-all shadow-lg shadow-purple-500/25 disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden"
                        <?php echo e($isDebating && !$isLoading ? 'disabled' : ''); ?>

                    >
                        
                        <div wire:loading wire:target="conveneBoard" class="absolute inset-0 bg-purple-900/90 flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="font-semibold">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadingStep === 'convening'): ?>
                                    Convening...
                                <?php elseif($loadingStep === 'joining'): ?>
                                    Board Joining...
                                <?php elseif($loadingStep === 'debating'): ?>
                                    Debate in Progress...
                                <?php elseif($loadingStep === 'consolidating'): ?>
                                    Finalizing...
                                <?php else: ?>
                                    Processing...
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                        
                        
                        <span wire:loading.remove wire:target="conveneBoard">
                            🎙 Convene Board
                        </span>
                    </button>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDebating): ?>
        <div class="bg-gradient-to-r from-purple-900/50 via-pink-900/50 to-purple-900/50 backdrop-blur-sm rounded-2xl border border-purple-500/30 p-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Board Session in Progress</h3>
                </div>
                <span class="text-xs text-purple-300 font-semibold px-3 py-1 bg-purple-500/20 rounded-full border border-purple-500/30">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadingStep === 'convening'): ?>
                        Starting...
                    <?php elseif($loadingStep === 'joining'): ?>
                        Members Joining
                    <?php elseif($loadingStep === 'debating'): ?>
                        Debate Active
                    <?php elseif($loadingStep === 'consolidating'): ?>
                        Finalizing
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            </div>
            
            
            <div class="relative h-2 bg-white/10 rounded-full overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500 via-pink-500 to-purple-500 animate-pulse" 
                     style="width: <?php if($loadingStep === 'convening'): ?> 25% <?php elseif($loadingStep === 'joining'): ?> 50% <?php elseif($loadingStep === 'debating'): ?> 75% <?php elseif($loadingStep === 'consolidating'): ?> 100% <?php else: ?> 0% <?php endif; ?>; transition: width 0.5s ease-in-out;">
                </div>
            </div>
            
            
            <div class="mt-4 flex items-center gap-2 text-sm text-purple-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadingStep === 'convening'): ?>
                    <span class="animate-pulse">🎯 Convening executive board...</span>
                <?php elseif($loadingStep === 'joining'): ?>
                    <span class="animate-pulse">👥 Board members joining the session...</span>
                <?php elseif($loadingStep === 'debating'): ?>
                    <span class="animate-pulse">🎙 Debate in progress - executives deliberating...</span>
                <?php elseif($loadingStep === 'consolidating'): ?>
                    <span class="animate-pulse">✨ Consolidating final decision...</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Board Members</h3>
            </div>

            <div class="relative">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadingStep === 'joining'): ?>
                <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm z-10 rounded-xl flex items-center justify-center">
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-purple-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-purple-300 font-semibold">Board members joining...</p>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 <?php echo e($loadingStep === 'joining' ? 'opacity-30' : ''); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $boardMembers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="group p-4 bg-white/[0.02] rounded-xl border border-white/5 hover:border-purple-500/30 hover:bg-purple-500/10 transition-all">
                        <div class="text-center">
                            <div class="text-4xl mb-3"><?php echo e($member['avatar']); ?></div>
                            <div class="font-bold text-white text-sm"><?php echo e($member['title']); ?></div>
                            <div class="text-xs text-slate-500 mb-2"><?php echo e($member['name']); ?></div>
                            <div class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo e($member['model'] === 'dolphin' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : ($member['model'] === 'haiku' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-purple-500/20 text-purple-300 border border-purple-500/30')); ?>">
                                <?php echo e($member['model']); ?>

                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($transcript ?? []) > 0): ?>
        <div class="bg-slate-900/60 backdrop-blur-sm rounded-2xl border border-white/10 overflow-hidden">
            <div class="bg-white/[0.02] border-b border-white/10 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-5 bg-gradient-to-b from-purple-400 to-pink-500 rounded-full"></div>
                    <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Live Discussion</h3>
                </div>
                <span class="text-xs text-slate-500"><?php echo e(count($transcript)); ?> contributions</span>
            </div>
            <div class="p-4 space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $transcript; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4 bg-white/[0.02] rounded-xl border border-white/5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl"><?php echo e($entry['avatar']); ?></span>
                        <div>
                            <span class="text-sm font-bold text-white"><?php echo e($entry['member_name']); ?></span>
                            <span class="text-xs text-slate-500 ml-2"><?php echo e($entry['member_role']); ?></span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-300 leading-relaxed"><?php echo e($entry['response']); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($finalDecision): ?>
        <div class="bg-gradient-to-br from-emerald-950/80 to-slate-900/80 backdrop-blur-sm rounded-2xl border border-emerald-500/30 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-1 h-6 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
                <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">✅ Board Decision</h3>
            </div>

            <div class="prose prose-invert max-w-none mb-4">
                <p class="text-slate-300 leading-relaxed"><?php echo nl2br(e($finalDecision)); ?></p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($risksBenefits): ?>
            <div class="mt-4 p-4 bg-white/[0.02] rounded-xl border border-white/5">
                <div class="text-xs text-slate-500 uppercase font-semibold mb-2">Risks & Benefits</div>
                <p class="text-sm text-slate-300 leading-relaxed"><?php echo nl2br(e($risksBenefits)); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div><?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/livewire/board/executive-board.blade.php ENDPATH**/ ?>