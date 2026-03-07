@extends('components.layouts.app')
@section('title', $project->name)
@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'overview' }">
    <div class="mb-4"><a href="{{ route('projects') }}" class="btn btn-ghost btn-sm">← Back</a></div>
    <div class="card bg-gradient-to-br from-indigo-950/80 via-purple-950/80 to-slate-900/80 border border-white/10 shadow-2xl">
        <div class="card-body p-6">
            <div class="flex justify-between">
                <div><h1 class="text-3xl font-bold text-white mb-2">{{ $project->name }}</h1>@if($project->description)<p class="text-slate-300">{{ $project->description }}</p>@endif</div>
                <div class="text-right"><div class="text-4xl font-bold text-white">{{ number_format($project->percent_complete, 1) }}%</div><div class="text-xs text-slate-400 uppercase">Complete</div></div>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <div class="badge {{ $project->status === 'active' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($project->status) }}</div>
                <div class="badge {{ $project->health === 'healthy' ? 'badge-success' : 'badge-error' }}">{{ ucfirst($project->health) }}</div>
                @if($project->architecture_type)<div class="badge badge-accent">{{ ucfirst($project->architecture_type) }}</div>@endif
                @if($project->projectManager)<div class="badge badge-purple">👤 {{ $project->projectManager->name }}</div>@endif
                @if($artifactsByType['board_discussion']->count() > 0 && $artifactsByType['board_discussion']->first()->source_id)
                    <a href="{{ route('tasks.executive.result', $artifactsByType['board_discussion']->first()->source_id) }}" class="btn btn-xs btn-outline btn-info">💬 Board Discussion</a>
                @endif
            </div>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success">✅ {{ session('success') }}</div>@endif
    <div class="card bg-base-200/50 border border-white/10">
        <div class="card-body p-6">
            <div class="flex justify-between mb-2"><span class="font-semibold">Progress</span><span class="font-bold">{{ number_format($project->percent_complete, 1) }}%</span></div>
            <progress class="progress progress-{{ $project->health === 'healthy' ? 'success' : 'error' }} w-full" value="{{ $project->percent_complete }}" max="100"></progress>
        </div>
    </div>
    <div role="tablist" class="tabs tabs-bordered tabs-lg">
        <a role="tab" class="tab" @click.prevent="activeTab='overview'" :class="activeTab==='overview'?'tab-active':''">📊 Overview</a>
        <a role="tab" class="tab" @click.prevent="activeTab='requirements'" :class="activeTab==='requirements'?'tab-active':''">📋 Req ({{ $artifactsByType['requirement']->count() }})</a>
        <a role="tab" class="tab" @click.prevent="activeTab='notes'" :class="activeTab==='notes'?'tab-active':''">📝 Notes ({{ $artifactsByType['note']->count() }})</a>
        <a role="tab" class="tab" @click.prevent="activeTab='docs'" :class="activeTab==='docs'?'tab-active':''">📄 Docs ({{ $artifactsByType['doc']->count() }})</a>
        <a role="tab" class="tab" @click.prevent="activeTab='tasks'" :class="activeTab==='tasks'?'tab-active':''">✅ Tasks ({{ $project->tasks->count()??0 }})</a>
        <a role="tab" class="tab" @click.prevent="activeTab='team'" :class="activeTab==='team'?'tab-active':''">👥 Team ({{ $project->teamMembers->count()??0 }})</a>
        @if($project->issues && $project->issues->count() > 0)<a role="tab" class="tab" @click.prevent="activeTab='issues'" :class="activeTab==='issues'?'tab-active':''">⚠️ Issues</a>@endif
    </div>
    <div class="mt-6 space-y-6">
        <div x-show="activeTab==='overview'" x-transition class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                @if($project->technologies && count($project->technologies) > 0)
                <div class="card bg-base-200/50 border border-white/10"><div class="card-body"><h3 class="card-title">🛠️ Technologies</h3><div class="flex gap-2 flex-wrap">@foreach($project->technologies as $tech)<div class="badge badge-outline">{{ $tech }}</div>@endforeach</div></div></div>
                @endif
            </div>
            <div class="space-y-6">
                <div class="card bg-base-200/50 border border-white/10"><div class="card-body"><h3 class="card-title">📊 Stats</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-base-content/60">Requirements</span><span>{{ $artifactsByType['requirement']->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Notes</span><span>{{ $artifactsByType['note']->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Docs</span><span>{{ $artifactsByType['doc']->count() }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Tasks</span><span>{{ $project->tasks->count()??0 }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Team</span><span>{{ $project->teamMembers->count()??0 }}</span></div>
                    </div>
                </div></div>
            </div>
        </div>
        <div x-show="activeTab==='requirements'" x-cloak>
            <div class="card bg-base-200/50 border border-white/10">
                <div class="card-body">
                    <div class="flex justify-between mb-4"><h2 class="text-2xl font-bold">📋 Requirements</h2><button onclick="document.getElementById('addReqModal').showModal()" class="btn btn-primary btn-sm">+ Add</button></div>
                    @if($artifactsByType['requirement']->count() > 0)
                    <table class="table table-zebra"><thead><tr><th>#</th><th>Title</th><th>Created</th></tr></thead><tbody>
                        @foreach($artifactsByType['requirement'] as $i=>$artifact)<tr><td>{{ $i+1 }}</td><td>{{ $artifact->title }}</td><td>{{ $artifact->created_at->format('M j') }}</td></tr>@endforeach
                    </tbody></table>
                    @else <p class="text-center py-8 text-base-content/60">No requirements</p> @endif
                </div>
            </div>
        </div>
        <div x-show="activeTab==='notes'" x-cloak>
            <div class="card bg-base-200/50 border border-white/10">
                <div class="card-body">
                    <div class="flex justify-between mb-4"><h2 class="text-2xl font-bold">📝 Notes</h2><button onclick="document.getElementById('addNoteModal').showModal()" class="btn btn-secondary btn-sm">+ Add</button></div>
                    @if($artifactsByType['note']->count() > 0)
                    <table class="table table-zebra"><tbody>@foreach($artifactsByType['note'] as $artifact)<tr><td>{{ $artifact->title }}</td><td>{{ $artifact->created_at->format('M j') }}</td></tr>@endforeach</tbody></table>
                    @else <p class="text-center py-8 text-base-content/60">No notes</p> @endif
                </div>
            </div>
        </div>
        <div x-show="activeTab==='docs'" x-cloak>
            <div class="card bg-base-200/50 border border-white/10">
                <div class="card-body">
                    <div class="flex justify-between mb-4"><h2 class="text-2xl font-bold">📄 Docs</h2><button onclick="document.getElementById('addDocModal').showModal()" class="btn btn-accent btn-sm">+ Add</button></div>
                    @if($artifactsByType['doc']->count() > 0)
                    <table class="table table-zebra"><tbody>@foreach($artifactsByType['doc'] as $artifact)<tr><td>{{ $artifact->title }}</td><td>{{ $artifact->created_at->format('M j') }}</td></tr>@endforeach</tbody></table>
                    @else <p class="text-center py-8 text-base-content/60">No docs</p> @endif
                </div>
            </div>
        </div>
        <div x-show="activeTab==='tasks'" x-cloak>
            <div class="card bg-base-200/50 border border-white/10">
                <div class="card-body">
                    <div class="flex justify-between mb-4"><h2 class="text-2xl font-bold">✅ Tasks</h2><a href="{{ route('tasks.create', ['project_id'=>$project->id]) }}" class="btn btn-primary btn-sm">+ Add</a></div>
                    @if($project->tasks && $project->tasks->count() > 0)
                    <table class="table table-zebra"><tbody>@foreach($project->tasks as $task)<tr><td>{{ $task->title }}</td><td>{{ $task->status }}</td></tr>@endforeach</tbody></table>
                    @else <p class="text-center py-8 text-base-content/60">No tasks</p> @endif
                </div>
            </div>
        </div>
        <div x-show="activeTab==='team'" x-cloak>
            <div class="card bg-base-200/50 border border-white/10">
                <div class="card-body">
                    <div class="flex justify-between mb-4"><h2 class="text-2xl font-bold">👥 Team</h2><button onclick="document.getElementById('assignAgentModal').showModal()" class="btn btn-primary btn-sm">+ Assign</button></div>
                    @if($project->teamMembers && $project->teamMembers->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">@foreach($project->teamMembers as $a)<div class="card bg-base-300"><div class="card-body"><div class="font-bold">{{ $a->agent->name??'Unknown' }}</div><div class="text-sm">{{ $a->role }}</div><form action="{{ route('projects.assignments.destroy', [$project->id,$a->id]) }}" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-ghost btn-error btn-sm">Remove</button></form></div></div>@endforeach</div>
                    @else <p class="text-center py-8 text-base-content/60">No team members</p> @endif
                </div>
            </div>
        </div>
        @if($project->issues && $project->issues->count() > 0)
        <div x-show="activeTab==='issues'" x-cloak>
            <div class="card bg-base-200/50 border border-white/10"><div class="card-body"><h2 class="text-2xl font-bold mb-4">⚠️ Issues</h2>
                <div class="space-y-4">@foreach($project->issues as $issue)<div class="card bg-base-300"><div class="card-body"><div class="font-bold">{{ $issue->title }}</div><div class="badge badge-{{ $issue->severity==='critical'?'error':'warning' }}">{{ $issue->severity }}</div></div></div>@endforeach</div>
            </div></div>
        </div>
        @endif
    </div>

    {{-- Modals --}}
    <dialog id="addReqModal" class="modal"><div class="modal-box"><h3 class="font-bold mb-4">Add Requirement</h3>
        <form action="{{ route('projects.artifacts.store', [$project->id, 'requirement']) }}" method="POST">@csrf
            <div class="form-control mb-4"><label class="label">Title</label><input type="text" name="title" required class="input input-bordered"></div>
            <div class="form-control mb-4"><label class="label">Content</label><textarea name="content" required class="textarea textarea-bordered h-32"></textarea></div>
            <div class="modal-action"><button type="button" onclick="document.getElementById('addReqModal').close()" class="btn">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
        </form>
    </div><form method="dialog" class="modal-backdrop"><button>close</button></form></dialog>

    <dialog id="addNoteModal" class="modal"><div class="modal-box"><h3 class="font-bold mb-4">Add Note</h3>
        <form action="{{ route('projects.artifacts.store', [$project->id, 'note']) }}" method="POST">@csrf
            <div class="form-control mb-4"><label class="label">Title</label><input type="text" name="title" required class="input input-bordered"></div>
            <div class="form-control mb-4"><label class="label">Content</label><textarea name="content" required class="textarea textarea-bordered h-32"></textarea></div>
            <div class="modal-action"><button type="button" onclick="document.getElementById('addNoteModal').close()" class="btn">Cancel</button><button type="submit" class="btn btn-secondary">Add</button></div>
        </form>
    </div><form method="dialog" class="modal-backdrop"><button>close</button></form></dialog>

    <dialog id="addDocModal" class="modal"><div class="modal-box"><h3 class="font-bold mb-4">Add Doc</h3>
        <form action="{{ route('projects.artifacts.store', [$project->id, 'doc']) }}" method="POST">@csrf
            <div class="form-control mb-4"><label class="label">Title</label><input type="text" name="title" required class="input input-bordered"></div>
            <div class="form-control mb-4"><label class="label">Content</label><textarea name="content" required class="textarea textarea-bordered h-32"></textarea></div>
            <div class="modal-action"><button type="button" onclick="document.getElementById('addDocModal').close()" class="btn">Cancel</button><button type="submit" class="btn btn-accent">Add</button></div>
        </form>
    </div><form method="dialog" class="modal-backdrop"><button>close</button></form></dialog>

    <dialog id="assignAgentModal" class="modal"><div class="modal-box"><h3 class="font-bold mb-4">Assign Agent</h3>
        <form action="{{ route('projects.assignments.store', $project->id) }}" method="POST">@csrf
            <div class="form-control mb-4"><label class="label">Agent</label><select name="agent_id" required class="select select-bordered">@foreach(\App\Models\Agent::all() as $agent)<option value="{{ $agent->id }}">{{ $agent->name }}</option>@endforeach</select></div>
            <div class="form-control mb-4"><label class="label">Role</label><select name="role" required class="select select-bordered"><option value="project_manager">PM</option><option value="developer">Developer</option><option value="qa">QA</option></select></div>
            <div class="modal-action"><button type="button" onclick="document.getElementById('assignAgentModal').close()" class="btn">Cancel</button><button type="submit" class="btn btn-primary">Assign</button></div>
        </form>
    </div><form method="dialog" class="modal-backdrop"><button>close</button></form></dialog>
</div>
@endsection
