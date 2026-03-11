<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
<div class="flex items-center gap-3">
    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-400 to-purple-500 flex items-center justify-center text-sm shadow-lg flex-shrink-0">
        👤
    </div>
    <div x-show="!collapsed" class="flex-1 min-w-0 overflow-hidden">
        <p class="text-sm font-medium text-[#e4e4f0] truncate"><?php echo e(auth()->user()->name); ?></p>
        <p class="text-xs text-[#6b6b80] truncate"><?php echo e(auth()->user()->email); ?></p>
    </div>
    <a href="<?php echo e(route('logout')); ?>" 
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="text-[#6b6b80] hover:text-red-400 transition-colors"
       title="Logout">
        🚪
    </a>
    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden">
        <?php echo csrf_field(); ?>
    </form>
</div>
<?php else: ?>
<div class="flex items-center justify-center">
    <a href="<?php echo e(route('login')); ?>" class="text-sm text-[#a0a0b8] hover:text-[#e4e4f0] transition-colors">
        🔐 Login to LunaOS
    </a>
</div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/kobear/.openclaw/workspace/lunaos/resources/views/layouts/partials/sidebar-footer.blade.php ENDPATH**/ ?>