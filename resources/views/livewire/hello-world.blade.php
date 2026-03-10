<div class="flex flex-col items-center justify-center p-8">
    <h1 class="text-4xl font-bold mb-6 text-gray-900 dark:text-gray-100">
        Hello, {{ $name }}!
    </h1>

    <div class="w-full max-w-md">
        <input
            type="text"
            wire:model="name"
            wire:keydown.enter="updateName($event.target.value)"
            placeholder="Enter your name"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-gray-100"
        >
    </div>

    @if($name === 'Dave')
        <p class="mt-4 text-green-600 dark:text-green-400 font-semibold">
            Welcome back, Dave!
        </p>
    @endif
</div>
