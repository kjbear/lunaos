<div class="space-y-6">
    <!-- Page Header with Action -->
    <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 backdrop-blur-xl border border-white/10 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-400 to-purple-500 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 via-purple-500 to-pink-500 flex items-center justify-center text-3xl shadow-xl">🧪</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Test Status</h1>
                    <p class="text-sm text-slate-400 font-medium mt-0.5">Test run history and coverage tracking</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button
                    wire:click="runTests"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-purple-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>▶ Run Tests</span>
                    <span wire:loading>⏳ Running...</span>
                </button>
            </div>
        </div>
    </header>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-5 gap-6">
        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-[#6b6b80]">Total Runs</span>
                <span class="text-2xl">📊</span>
            </div>
            <p class="text-3xl font-bold text-[#e4e4f0]">{{ $stats['total_runs'] }}</p>
            <p class="text-xs text-[#6b6b80] mt-2">Test executions</p>
        </div>
        
        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-[#6b6b80]">Last Run</span>
                <span class="text-2xl">🕐</span>
            </div>
            <p class="text-lg font-bold text-[#e4e4f0]">{{ $stats['last_run'] }}</p>
            <p class="text-xs text-[#6b6b80] mt-2">Most recent</p>
        </div>
        
        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-[#6b6b80]">Avg Pass Rate</span>
                <span class="text-2xl">📈</span>
            </div>
            <p class="text-3xl font-bold {{ $stats['avg_pass_rate'] >= 80 ? 'text-green-400' : 'text-yellow-400' }}">
                {{ number_format($stats['avg_pass_rate'], 1) }}%
            </p>
            <p class="text-xs text-[#6b6b80] mt-2">Across all runs</p>
        </div>
        
        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-[#6b6b80]">Best Coverage</span>
                <span class="text-2xl">🎯</span>
            </div>
            <p class="text-3xl font-bold {{ $stats['best_coverage'] >= 80 ? 'text-green-400' : 'text-f59e0b' }}">
                {{ number_format($stats['best_coverage'], 1) }}%
            </p>
            <p class="text-xs text-[#6b6b80] mt-2">Highest achieved</p>
        </div>
        
        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-[#6b6b80]">Tests Written</span>
                <span class="text-2xl">📝</span>
            </div>
            <p class="text-3xl font-bold text-[#e4e4f0]">{{ $stats['tests_written'] }}</p>
            <p class="text-xs text-[#6b6b80] mt-2">11 unit + 8 feature</p>
        </div>
    </div>

    {{-- Test Run History --}}
    <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#2a2a40] flex items-center justify-between">
            <h3 class="text-lg font-semibold text-[#e4e4f0]">Test Run History</h3>
            <span class="text-sm text-[#6b6b80]">{{ count($recentRuns) }} recent runs</span>
        </div>
        
        @if(count($recentRuns) === 0)
        <div class="p-12 text-center">
            <div class="text-6xl mb-4">🧪</div>
            <h4 class="text-lg font-semibold text-[#e4e4f0] mb-2">No Test Runs Yet</h4>
            <p class="text-[#6b6b80] mb-6">Click "Run Tests" to execute the test suite and see results here</p>
            <button
                wire:click="runTests"
                class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-semibold rounded-xl hover:from-cyan-600 hover:to-purple-600 transition-all"
            >
                Run Your First Test
            </button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#0f0f1a]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Passed</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Failed</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Skipped</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Pass Rate</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#6b6b80] uppercase tracking-wider">Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2a2a40]">
                    @foreach($recentRuns as $run)
                    <tr class="hover:bg-[#1f1f35] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-[#e4e4f0]">{{ $run['date'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-medium rounded-full capitalize
                                {{ $run['status'] === 'passed' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $run['status'] === 'failed' ? 'bg-red-500/20 text-red-400' : '' }}
                                {{ $run['status'] === 'error' ? 'bg-yellow-500/20 text-yellow-400' : '' }}">
                                {{ $run['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-[#e4e4f0]">{{ $run['total'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-green-400 font-semibold">{{ $run['passed'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-red-400 font-semibold">{{ $run['failed'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-gray-400">{{ $run['skipped'] }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-24 bg-[#2a2a40] rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-2 rounded-full" style="width: {{ $run['pass_rate'] }}%"></div>
                                </div>
                                <span class="text-sm font-semibold {{ $run['pass_rate'] >= 80 ? 'text-green-400' : 'text-yellow-400' }}">
                                    {{ $run['pass_rate'] }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-[#6b6b80]">{{ number_format($run['duration'] / 1000, 2) }}s</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Test Files Overview --}}
    <div class="grid grid-cols-2 gap-6">
        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#2a2a40]">
                <h3 class="text-lg font-semibold text-[#e4e4f0]">Unit Tests (Models)</h3>
            </div>
            <div class="divide-y divide-[#2a2a40]">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-medium text-[#e4e4f0]">AgentModelTest.php</p>
                        <p class="text-xs text-[#6b6b80] mt-1">Agent creation, relationships, strategy</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">3 tests</span>
                </div>
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-medium text-[#e4e4f0]">TaskModelTest.php</p>
                        <p class="text-xs text-[#6b6b80] mt-1">Task CRUD, agent FK, status transitions</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">3 tests</span>
                </div>
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-medium text-[#e4e4f0]">ActivityLogModelTest.php</p>
                        <p class="text-xs text-[#6b6b80] mt-1">Activity logging, JSON metadata</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">2 tests</span>
                </div>
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-medium text-[#e4e4f0]">StandupModelTest.php</p>
                        <p class="text-xs text-[#6b6b80] mt-1">Standups, deliverables, action items</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium bg-blue-500/20 text-blue-400 rounded-full">3 tests</span>
                </div>
            </div>
        </div>

        <div class="bg-[#1a1a2e] rounded-xl border border-[#2a2a40] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#2a2a40]">
                <h3 class="text-lg font-semibold text-[#e4e4f0]">Feature Tests (Livewire)</h3>
            </div>
            <div class="divide-y divide-[#2a2a40]">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-medium text-[#e4e4f0]">ModuleTests.php</p>
                        <p class="text-xs text-[#6b6b80] mt-1">All 8 core modules load testing</p>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium bg-purple-500/20 text-purple-400 rounded-full">8 tests</span>
                </div>
            </div>
            <div class="px-6 py-4 bg-[#1f1f35]">
                <p class="text-xs text-[#6b6b80]">
                    <span class="text-yellow-400">⚠️</span> Tests written but blocked by multi-database config (Phase 2 fix)
                </p>
            </div>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="bg-blue-500/10 border-l-4 border-blue-500/50 p-4 rounded-lg">
        <div class="flex gap-3">
            <span class="text-2xl">💡</span>
            <div>
                <h4 class="font-semibold text-blue-400 mb-1">About Test History</h4>
                <p class="text-sm text-blue-200/80">
                    Each test run is recorded in the database. This page tracks your test suite's health over time,
                    showing pass rates, coverage trends, and execution duration. Use this to monitor test stability
                    and identify regressions early.
                </p>
            </div>
        </div>
    </div>
</div>
