<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Test Status Dashboard</h1>
    
    <div class="bg-white rounded shadow p-4 mb-4">
        <h2 class="text-lg font-semibold mb-2">Quick Summary</h2>
        <p class="text-gray-700">Total Tests: <strong>19</strong></p>
        <p class="text-gray-700">Unit Tests: <strong>11</strong></p>
        <p class="text-gray-700">Feature Tests: <strong>8</strong></p>
        <p class="text-gray-700">Status: <span class="text-yellow-600">Written (config issue)</span></p>
    </div>
    
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
        <p class="text-blue-700">
            <strong>Note:</strong> Tests are written but can't run due to multi-database SQLite configuration.
            This is a Phase 2 fix.
        </p>
    </div>
    
    <div class="mt-6">
        <h3 class="text-lg font-semibold mb-2">Test Files</h3>
        <ul class="space-y-2">
            <li class="bg-gray-50 p-2 rounded">
                <code class="text-sm">AgentModelTest.php</code> - 3 tests
            </li>
            <li class="bg-gray-50 p-2 rounded">
                <code class="text-sm">TaskModelTest.php</code> - 3 tests
            </li>
            <li class="bg-gray-50 p-2 rounded">
                <code class="text-sm">ActivityLogModelTest.php</code> - 2 tests
            </li>
            <li class="bg-gray-50 p-2 rounded">
                <code class="text-sm">StandupModelTest.php</code> - 3 tests
            </li>
            <li class="bg-gray-50 p-2 rounded">
                <code class="text-sm">ModuleTests.php</code> - 8 tests (Livewire)
            </li>
        </ul>
    </div>
</div>
