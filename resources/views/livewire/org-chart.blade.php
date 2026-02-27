<div class="space-y-6">
    <!-- Polished Page Header -->
    <header style="background: linear-gradient(135deg, rgb(67,56,202) 0%, rgb(124,58,237) 50%, rgb(30,41,59) 100%); backdrop-filter: blur(12px);" class="relative overflow-hidden rounded-2xl border border-white/20 mb-8 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/5 via-purple-500/5 to-pink-500/5"></div>
        <div class="relative flex items-center justify-between p-6">
            <div class="flex items-center gap-5">
                <div class="group relative">
                    <div style="background: linear-gradient(135deg, rgb(168,85,247), rgb(236,72,153));" class="absolute inset-0 rounded-2xl blur-lg opacity-50 group-hover:opacity-75 transition-opacity duration-500"></div>
                    <div style="background: linear-gradient(135deg, rgb(168,85,247), rgb(236,72,153), rgb(99,102,241));" class="relative w-14 h-14 rounded-2xl flex items-center justify-center text-3xl shadow-xl">🌙</div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">LunaOS Organization</h1>
                    <p class="text-sm text-slate-300 font-medium mt-0.5">AI Agent Team Hierarchy</p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm text-slate-300">
                <span class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20">7 Agents</span>
                <span class="px-3 py-1.5 rounded-lg bg-white/10 border border-white/20">4 Levels</span>
            </div>
        </div>
    </header>

    <!-- Stats Cards - Horizontal -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.15)); backdrop-filter: blur(8px);" class="group relative overflow-hidden rounded-2xl p-5 border border-indigo-400/40 hover:border-indigo-400/60 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-1">
                    <div style="background: rgba(99,102,241,0.2);" class="w-11 h-11 rounded-xl border border-indigo-400/40 flex items-center justify-center text-xl">👥</div>
                    <p class="text-xs text-indigo-200 font-semibold uppercase tracking-wider">Total Agents</p>
                </div>
                <p class="text-3xl font-bold text-white ml-14">7</p>
            </div>
        </div>
        <div style="background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(236,72,153,0.15)); backdrop-filter: blur(8px);" class="group relative overflow-hidden rounded-2xl p-5 border border-purple-400/40 hover:border-purple-400/60 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-1">
                    <div style="background: rgba(139,92,246,0.2);" class="w-11 h-11 rounded-xl border border-purple-400/40 flex items-center justify-center text-xl">🌙</div>
                    <p class="text-xs text-purple-200 font-semibold uppercase tracking-wider">Main (GLM-5)</p>
                </div>
                <p class="text-3xl font-bold text-white ml-14">1</p>
            </div>
        </div>
        <div style="background: linear-gradient(135deg, rgba(34,211,238,0.15), rgba(59,130,246,0.15)); backdrop-filter: blur(8px);" class="group relative overflow-hidden rounded-2xl p-5 border border-cyan-400/40 hover:border-cyan-400/60 transition-all duration-300">
            <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-500/10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-1">
                    <div style="background: rgba(34,211,238,0.2);" class="w-11 h-11 rounded-xl border border-cyan-400/40 flex items-center justify-center text-xl">🤖</div>
                    <p class="text-xs text-cyan-200 font-semibold uppercase tracking-wider">Workers (Dolphin)</p>
                </div>
                <p class="text-3xl font-bold text-white ml-14">6</p>
            </div>
        </div>
    </div>

    <!-- Org Chart -->
    <section>
        <div class="flex items-center gap-3 mb-6">
            <div style="background: linear-gradient(to bottom, rgb(168,85,247), rgb(236,72,153));" class="w-1 h-6 rounded-full"></div>
            <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Team Structure</h2>
        </div>
        
        <div style="background: rgba(15,23,42,0.6); backdrop-filter: blur(8px);" class="rounded-2xl border border-white/20 p-8 overflow-x-auto">
            <div class="flex flex-col items-center min-w-[850px]">
                
                <!-- Kyle (CEO) -->
                <div class="group">
                    <div style="background: linear-gradient(135deg, rgba(16,185,129,0.35), rgba(20,184,166,0.35));" class="relative border-2 border-emerald-400 rounded-2xl p-6 w-72 text-center shadow-2xl">
                        <div class="text-6xl mb-3">👤</div>
                        <div class="font-bold text-2xl text-white mb-1">Kyle</div>
                        <div class="text-sm text-slate-200 font-semibold mb-3">CEO</div>
                        <span style="background: rgba(100,116,139,0.2);" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border border-slate-400/40 text-slate-200">Human</span>
                    </div>
                    <!-- Vertical Line Down -->
                    <div style="background: linear-gradient(to bottom, rgb(16,185,129), rgb(139,92,246));" class="absolute left-1/2 -translate-x-1/2 top-full w-0.5 h-16"></div>
                </div>

                <!-- Luna (Main) -->
                <div class="group mt-24">
                    <div style="background: linear-gradient(135deg, rgba(139,92,246,0.35), rgba(236,72,153,0.35));" class="relative border-2 border-purple-400 rounded-2xl p-6 w-72 text-center shadow-2xl">
                        <div class="text-6xl mb-3">🌙</div>
                        <div class="font-bold text-2xl text-white mb-1">Luna</div>
                        <div class="text-sm text-slate-200 font-semibold mb-3">Main Assistant</div>
                        <span style="background: rgba(139,92,246,0.2);" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border border-purple-400 text-purple-200">GLM-5</span>
                    </div>
                    <!-- Vertical Line Down -->
                    <div style="background: linear-gradient(to bottom, rgb(139,92,246), rgb(34,211,238));" class="absolute left-1/2 -translate-x-1/2 top-full w-0.5 h-16"></div>
                </div>

                <!-- Jordan (PM) -->
                <div class="group mt-24">
                    <div style="background: linear-gradient(135deg, rgba(34,211,238,0.35), rgba(59,130,246,0.35));" class="relative border-2 border-cyan-400 rounded-2xl p-5 w-64 text-center shadow-xl">
                        <div class="text-5xl mb-3">📋</div>
                        <div class="font-bold text-xl text-white mb-1">Jordan</div>
                        <div class="text-sm text-slate-200 font-semibold mb-3">Project Manager</div>
                        <span style="background: rgba(34,211,238,0.2);" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold border border-cyan-400 text-cyan-200">Dolphin 3.0</span>
                    </div>
                    <!-- Vertical Line Down -->
                    <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.6));" class="absolute left-1/2 -translate-x-1/2 top-full w-0.5 h-16"></div>
                </div>

                <!-- Horizontal Bar + Specialists -->
                <div class="relative mt-32 w-full">
                    <!-- Vertical connector to horizontal bar -->
                    <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.6));" class="absolute left-1/2 -translate-x-1/2 top-0 w-0.5 h-16"></div>
                    <!-- Horizontal Line -->
                    <div style="background: linear-gradient(to right, transparent, rgb(34,211,238), transparent);" class="absolute top-16 left-1/2 -translate-x-1/2 w-[800px] h-0.5"></div>
                    
                    <!-- Specialists -->
                    <div class="flex justify-center items-start gap-6 pt-24">
                        <!-- Dave -->
                        <div class="flex flex-col items-center">
                            <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.5));" class="w-0.5 h-8 mb-4"></div>
                            @php
                                $dave = \App\Models\Agent::where('name', 'dave')->first();
                                $runtimeClass = $dave?->runtime_location_badge_class ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                                $runtimeLabel = $dave?->runtime_location_label ?? 'Unknown';
                            @endphp
                            <div class="group">
                                <div class="relative bg-[#252542] border-2 border-cyan-400 rounded-xl p-4 w-40 text-center hover:border-cyan-300 hover:shadow-lg hover:shadow-cyan-500/20 transition-all cursor-pointer">
                                    <div class="text-4xl mb-2">💻</div>
                                    <div class="font-semibold text-white mb-1">Dave</div>
                                    <div class="text-xs text-slate-400 mb-2">PHP Coder</div>
                                    <span style="background: rgba(34,211,238,0.2);" class="inline-flex items-center px-2 py-0.5 rounded text-xs border border-cyan-400 text-cyan-200 mb-2">Dolphin 3.0</span>
                                    <br>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs border {{ $runtimeClass }}">
                                        {{ $runtimeLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Maya -->
                        <div class="flex flex-col items-center">
                            <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.5));" class="w-0.5 h-8 mb-4"></div>
                            @php
                                $maya = \App\Models\Agent::where('name', 'maya')->first();
                                $mayaRuntimeClass = $maya?->runtime_location_badge_class ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                                $mayaRuntimeLabel = $maya?->runtime_location_label ?? 'Unknown';
                            @endphp
                            <div class="group">
                                <div class="relative bg-[#252542] border-2 border-pink-400 rounded-xl p-4 w-40 text-center hover:border-pink-300 hover:shadow-lg hover:shadow-pink-500/20 transition-all cursor-pointer">
                                    <div class="text-4xl mb-2">🎨</div>
                                    <div class="font-semibold text-white mb-1">Maya</div>
                                    <div class="text-xs text-slate-400 mb-2">Frontend</div>
                                    <span style="background: rgba(34,211,238,0.2);" class="inline-flex items-center px-2 py-0.5 rounded text-xs border border-cyan-400 text-cyan-200 mb-2">Dolphin 3.0</span>
                                    <br>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs border {{ $mayaRuntimeClass }}">
                                        {{ $mayaRuntimeLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chen -->
                        <div class="flex flex-col items-center">
                            <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.5));" class="w-0.5 h-8 mb-4"></div>
                            <div class="group">
                                <div class="relative bg-[#252542] border-2 border-amber-400 rounded-xl p-4 w-40 text-center hover:border-amber-300 hover:shadow-lg hover:shadow-amber-500/20 transition-all cursor-pointer">
                                    <div class="text-4xl mb-2">🔧</div>
                                    <div class="font-semibold text-white mb-1">Chen</div>
                                    <div class="text-xs text-slate-400 mb-2">DevOps</div>
                                    <span style="background: rgba(34,211,238,0.2);" class="inline-flex items-center px-2 py-0.5 rounded text-xs border border-cyan-400 text-cyan-200">Dolphin 3.0</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sam -->
                        <div class="flex flex-col items-center">
                            <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.5));" class="w-0.5 h-8 mb-4"></div>
                            <div class="group">
                                <div class="relative bg-[#252542] border-2 border-emerald-400 rounded-xl p-4 w-40 text-center hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/20 transition-all cursor-pointer">
                                    <div class="text-4xl mb-2">✅</div>
                                    <div class="font-semibold text-white mb-1">Sam</div>
                                    <div class="text-xs text-slate-400 mb-2">Test Engineer</div>
                                    <span style="background: rgba(34,211,238,0.2);" class="inline-flex items-center px-2 py-0.5 rounded text-xs border border-cyan-400 text-cyan-200">Dolphin 3.0</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alex -->
                        <div class="flex flex-col items-center">
                            <div style="background: linear-gradient(to bottom, rgb(34,211,238), rgba(34,211,238,0.5));" class="w-0.5 h-8 mb-4"></div>
                            <div class="group">
                                <div class="relative bg-[#252542] border-2 border-violet-400 rounded-xl p-4 w-40 text-center hover:border-violet-300 hover:shadow-lg hover:shadow-violet-500/20 transition-all cursor-pointer">
                                    <div class="text-4xl mb-2">🔌</div>
                                    <div class="font-semibold text-white mb-1">Alex</div>
                                    <div class="text-xs text-slate-400 mb-2">API Architect</div>
                                    <span style="background: rgba(34,211,238,0.2);" class="inline-flex items-center px-2 py-0.5 rounded text-xs border border-cyan-400 text-cyan-200">Dolphin 3.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-12 flex flex-wrap justify-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <div style="background: linear-gradient(135deg, rgba(16,185,129,0.35), rgba(20,184,166,0.35));" class="w-4 h-4 rounded border-2 border-emerald-400"></div>
                        <span class="text-slate-300">CEO (Human)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div style="background: linear-gradient(135deg, rgba(139,92,246,0.35), rgba(236,72,153,0.35));" class="w-4 h-4 rounded border-2 border-purple-400"></div>
                        <span class="text-slate-300">Main (GLM-5)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div style="background: linear-gradient(135deg, rgba(34,211,238,0.35), rgba(59,130,246,0.35));" class="w-4 h-4 rounded border-2 border-cyan-400"></div>
                        <span class="text-slate-300">Coordinator (Dolphin)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-[#252542] border-2 border-cyan-400"></div>
                        <span class="text-slate-300">Specialist (Dolphin)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
