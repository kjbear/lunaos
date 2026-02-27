<div class="flex flex-col items-center justify-center min-h-screen bg-gray-50 p-4">
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl shadow-lg p-8 max-w-sm w-full text-center">
        <h1 class="text-2xl font-bold mb-4">Hello Counter</h1>
        <p class="text-4xl font-extrabold mb-6">{{ $counter }}</p>
        <div class="flex justify-center space-x-4">
            <button
                wire:click="decrement"
                class="px-4 py-2 bg-white text-purple-700 rounded hover:bg-purple-100 transition"
            >
                -
            </button>
            <button
                wire:click="increment"
                class="px-4 py-2 bg-white text-purple-700 rounded hover:bg-purple-100 transition"
            >
                +
            </button>
        </div>
    </div>
</div>
