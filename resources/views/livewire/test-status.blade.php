<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">LunaOS Test Status</h1>
        <p class="text-gray-600 mt-1">PHPUnit test suite overview and coverage</p>
    </div>

    {{-- Last Run Status --}}
    <div class="mb-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Last Test Run</h2>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">Date</span>
                <p class="font-mono text-sm">{{ $lastRun['date'] }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Result</span>
                <p class="{{ $lastRun['result'] === 'Pass' ? 'text-green-600' : 'text-yellow-600' }} font-semibold">
                    {{ $lastRun['result'] }}
                </p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Note</span>
                <p class="text-sm text-gray-700">{{ $lastRun['note'] }}</p>
            </div>
        </div>
    </div>

    {{-- Coverage Summary --}}
    <div class="mb-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Test Coverage</h2>
        <div class="grid grid-cols-5 gap-4">
            @foreach($coverage as $key => $data)
            <div class="text-center">
                <div class="relative w-20 h-20 mx-auto mb-2">
                    <svg class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="36" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                        <circle 
                            cx="40" 
                            cy="40" 
                            r="36" 
                            stroke="{{ $data['status'] === 'good' ? '#10b981' : ($data['status'] === 'partial' ? '#f59e0b' : '#6b7280') }}" 
                            stroke-width="8" 
                            fill="none"
                            stroke-dasharray="{{ 2 * pi() * 36 }}"
                            stroke-dashoffset="{{ 2 * pi() * 36 * (1 - $data['current'] / 100) }}"
                        />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-lg font-bold">{{ $data['current'] }}%</span>
                    </div>
                </div>
                <p class="text-sm font-medium text-gray-700 capitalize">{{ $key }}</p>
                <p class="text-xs text-gray-500">Target: {{ $data['target'] }}%</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Test Files --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Test Files</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($testSummary as $suite)
            <div class="px-6 py-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-semibold text-gray-800">
                        {{ $suite['suite'] }} - {{ $suite['category'] }}
                    </h3>
                    <div class="text-sm">
                        <span class="text-gray-600">{{ $suite['total'] }} tests</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-green-600">{{ $suite['passing'] }} passing</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-yellow-600">{{ $suite['pending'] }} pending</span>
                    </div>
                </div>
                <div class="grid gap-3">
                    @foreach($suite['files'] as $file)
                    <div class="flex items-start justify-between p-3 bg-gray-50 rounded-md">
                        <div>
                            <p class="font-mono text-sm font-medium text-gray-900">{{ $file['name'] }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $file['coverage'] }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($file['status'] === 'written')
                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">Written</span>
                            @elseif($file['status'] === 'passing')
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded">Passing</span>
                            @else
                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded">Pending</span>
                            @endif
                            <span class="text-xs text-gray-500">{{ $file['tests'] }} tests</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Commands --}}
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Run Tests</h2>
        <div class="space-y-3">
            <div>
                <p class="text-sm text-gray-600 mb-1">Run all unit tests:</p>
                <code class="block bg-gray-900 text-green-400 p-3 rounded-md text-sm font-mono">
                    cd /workspace/lunaos && php artisan test --testsuite=Unit
                </code>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Run all feature tests:</p>
                <code class="block bg-gray-900 text-green-400 p-3 rounded-md text-sm font-mono">
                    cd /workspace/lunaos && php artisan test --testsuite=Feature
                </code>
            </div>
            <div>
                <p class="text-sm text-gray-600 mb-1">Run with coverage:</p>
                <code class="block bg-gray-900 text-green-400 p-3 rounded-md text-sm font-mono">
                    cd /workspace/lunaos && php artisan test --coverage
                </code>
            </div>
        </div>
    </div>

    {{-- Known Issues --}}
    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Known Issue: Multi-Database Testing</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>
                        Tests are written but cannot run due to multi-database SQLite configuration.
                        The `sqlite-activity` and `sqlite-projects` connections use hardcoded file paths
                        that aren't properly overridden by PHPUnit's in-memory config.
                    </p>
                    <p class="mt-2">
                        <strong>Phase 2 Fix:</strong> Create separate test database configuration or use
                        SQLite file-based tests with temporary databases.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Documentation Links --}}
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Documentation</h2>
        <ul class="space-y-2">
            <li>
                <a href="/docs/TESTING_GUIDE.md" class="text-blue-600 hover:text-blue-800 hover:underline">
                    📖 Testing Guide
                </a>
                <span class="text-gray-500 text-sm ml-2">— How to run and maintain tests</span>
            </li>
            <li>
                <a href="/docs/PHASE1_COMPLETION_REPORT.md" class="text-blue-600 hover:text-blue-800 hover:underline">
                    📊 Phase 1 Completion Report
                </a>
                <span class="text-gray-500 text-sm ml-2">— Full status and metrics</span>
            </li>
            <li>
                <a href="/docs/AGENT_WORKFLOW.md" class="text-blue-600 hover:text-blue-800 hover:underline">
                    🤖 Agent Workflow Guide
                </a>
                <span class="text-gray-500 text-sm ml-2">— How agents work in LunaOS</span>
            </li>
        </ul>
    </div>
</div>
