<div class="space-y-6">
    <!-- Page Header -->
    <header class="relative overflow-hidden rounded-2xl border border-white/20 mb-8 shadow-2xl" style="background: linear-gradient(135deg, #475569 0%, #334155 100%); backdrop-filter: blur(12px);">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div class="absolute inset-0 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500" style="background: linear-gradient(135deg, rgb(168,85,247), rgb(236,72,153));"></div>
                    <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center text-3xl shadow-xl" style="background: linear-gradient(135deg, rgb(168,85,247), rgb(236,72,153), rgb(99,102,241));">🌙</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">LunaOS Organization</h1>
                    <p class="text-sm text-slate-300 font-medium mt-0.5">Team Structure Visualization</p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm text-slate-300">
                <span class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20">{{ $teamCount }} Agents</span>
                <span class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20">Levels: 3</span>
            </div>
        </div>
    </header>

    <!-- Visualization Container -->
    <section>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1 h-6 rounded-full" style="background: linear-gradient(to bottom, rgb(168,85,247), rgb(236,72,153));"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Interactive Org Chart</h2>
        </div>

        <!-- Org Chart Container -->
        <div class="rounded-2xl border border-white/20 overflow-hidden shadow-2xl" style="background: rgba(15,23,42,0.8); backdrop-filter: blur(8px); height: 800px;">
            <div id="org-chart" class="w-full h-full"></div>
        </div>

        <!-- Legend -->
        <div class="mt-6 flex flex-wrap justify-center gap-6 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2" style="background: #8b5cf6; border-color: #a78bfa;"></div>
                <span class="text-slate-300">Executive (Purple)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2" style="background: #3b82f6; border-color: #60a5fa;"></div>
                <span class="text-slate-300">Board Member (Blue)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2" style="background: #10b981; border-color: #34d399;"></div>
                <span class="text-slate-300">Worker (Green)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border-2" style="background: #64748b; border-color: #94a3b8;"></div>
                <span class="text-slate-300">Other (Slate)</span>
            </div>
        </div>
    </section>
</div>

<!-- Agent Detail Modal -->
@if ($showModal && $selectedAgent)
<div class="fixed inset-0 overflow-y-auto px-4 py-6 z-50" wire:click.self="closeModal">
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="closeModal"></div>
    <div class="relative mb-6 bg-slate-900 rounded-2xl overflow-hidden shadow-2xl max-w-2xl mx-auto border border-white/10">
        <div class="relative px-6 py-5 border-b border-white/10 bg-gradient-to-r from-slate-800 to-slate-900">
            <h3 class="text-xl font-bold text-white">
                {{ $selectedAgent['emoji'] ?? '👤' }} {{ $selectedAgent['label'] ?? $selectedAgent['name'] ?? 'Unknown' }}
            </h3>
            <p class="text-sm text-slate-400 mt-1 font-medium">{{ $selectedAgent['title'] ?? '' }}</p>
            <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-white transition-colors" wire:click="closeModal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="px-6 py-6 space-y-6">
            @php
                $role = $selectedAgent['group'] ?? 'worker';
                $roleColors = [
                    'executive' => ['bg-purple-500/20', 'text-purple-400', 'border-purple-500/30'],
                    'board_member' => ['bg-blue-500/20', 'text-blue-400', 'border-blue-500/30'],
                    'worker' => ['bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30'],
                ];
                [$bg, $text, $border] = $roleColors[$role] ?? $roleColors['worker'];
            @endphp
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border {{ $bg }} {{ $text }} {{ $border }}">
                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                </span>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-900 border-t border-white/10 flex justify-end gap-3">
            <button type="button" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 transition-all" wire:click="closeModal">
                Close
            </button>
        </div>
    </div>
</div>
@endif

@push('head')
<script src="https://unpkg.com/vis-network@9.1.6/dist/vis-network.min.js"></script>
@endpush

@push('scripts')
<script>
    (function() {
        const graphData = @json($graphData);
        
        function initOrgChart() {
            const container = document.getElementById('org-chart');
            if (!container || typeof vis === 'undefined') {
                console.log('Waiting for vis.js...');
                setTimeout(initOrgChart, 100);
                return;
            }

            const data = {
                nodes: new vis.DataSet(graphData.nodes),
                edges: new vis.DataSet(graphData.edges)
            };

            const options = {
                layout: {
                    hierarchical: {
                        enabled: true,
                        direction: 'UD',
                        sortMethod: 'directed',
                        nodeSpacing: 150,
                        levelSeparation: 150,
                        treeSpacing: 100
                    }
                },
                nodes: {
                    shape: 'box',
                    font: { color: '#ffffff', size: 14, face: 'Inter, sans-serif' },
                    borderWidth: 2,
                    shadow: true,
                    margin: { top: 10, right: 10, bottom: 10, left: 10 }
                },
                edges: {
                    width: 2,
                    color: { color: '#64748b', highlight: '#3b82f6' },
                    smooth: { type: 'continuous' },
                    arrows: { to: { enabled: true } }
                },
                groups: {
                    executive: { color: { background: '#8b5cf6', border: '#a78bfa' } },
                    board_member: { color: { background: '#3b82f6', border: '#60a5fa' } },
                    worker: { color: { background: '#10b981', border: '#34d399' } }
                },
                interaction: {
                    hover: true,
                    navigationButtons: true,
                    zoomView: true
                }
            };

            const network = new vis.Network(container, data, options);

            network.on('click', function(params) {
                if (params.nodes.length > 0) {
                    const nodeId = params.nodes[0];
                    @this.call('selectAgent', nodeId.toString());
                }
            });

            network.stabilize();
        }

        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initOrgChart);
        } else {
            initOrgChart();
        }
    })();
</script>
@endpush