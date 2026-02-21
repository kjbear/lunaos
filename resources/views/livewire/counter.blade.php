<div class="flex items-center space-x-4">
    <button 
        wire:click="decrement"
        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium transition-colors"
    >
        −
    </button>
    
    <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 min-w-[4rem] text-center">
        {{ $count }}
    </span>
    
    <button 
        wire:click="increment"
        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors"
    >
        +
    </button>
</div>