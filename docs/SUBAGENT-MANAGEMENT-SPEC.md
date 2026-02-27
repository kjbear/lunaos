# LunaOS Subagent Management Module - Specification

**Created:** Feb 24, 2026
**Status:** ✅ COMPLETE
**Target:** LunaOS Phase 1.5 (addition to existing modules)

---

## Overview

A new module providing visibility into subagent workflows, team organization, task tracking, and daily standups. Integrates with OpenClaw's native subagent system and existing `projects.db` SQLite database.

---

## Architecture

### Data Sources

| Source | Type | Purpose |
|--------|------|---------|
| `projects.db` | SQLite (existing) | Projects, tasks, task_history |
| `subagents()` API | OpenClaw tool | Real-time subagent status |
| `lunaos.db` | SQLite (new) | Standups, module config |
| Project folders | Filesystem | Documentation storage |

### Component Map

```
┌─────────────────────────────────────────────────────────┐
│  LunaOS Dashboard                                       │
├─────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────┐   │
│  │  Navigation Bar                                 │   │
│  │  [Home] [Org Chart] [Task Board] [Standups] [⚙️]│   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
│  ┌─────────────┬─────────────────────────────────────┐ │
│  │ Subagent    │                                     │ │
│  │ Monitor     │         Main Content Area           │ │
│  │ (sidebar)   │         (varies by route)           │ │
│  │             │                                     │ │
│  │ ● Dave      │                                     │ │
│  │ ○ Maya      │                                     │ │
│  │ ○ Chen      │                                     │ │
│  │ ○ Sam       │                                     │ │
│  │ ○ Alex      │                                     │ │
│  │ ○ PM        │                                     │ │
│  │             │                                     │ │
│  │ Recent:     │                                     │ │
│  │ • Dave: ... │                                     │ │
│  │ • PM: ...   │                                     │ │
│  └─────────────┴─────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

---

## Module 1: Org Chart

### Purpose
Display team hierarchy with model assignments and health status.

### Data Model

```php
// No database needed - static configuration
// Pulled from openclaw.json config

class TeamMember {
    public string $id;           // agent id (main, pm, dave, etc.)
    public string $name;         // display name
    public string $role;         // Luna, PM, PHP Coder, etc.
    public string $model;        // GLM-5, Dolphin 3.0, etc.
    public string $status;       // online, offline, busy
    public int $depth;           // 0 = main, 1 = PM, 2 = specialists
    public ?string $parentId;    // null for main, "main" for PM, "pm" for specialists
}
```

### Livewire Component

```php
// app/Http/Livewire/OrgChart.php

class OrgChart extends Component
{
    public array $team = [];
    public string $selectedAgent = '';
    
    public function mount(): void
    {
        $this->loadTeam();
    }
    
    public function loadTeam(): void
    {
        // Static config for now - could be API call later
        $this->team = [
            [
                'id' => 'main',
                'name' => 'Luna',
                'role' => 'Main Assistant',
                'model' => 'GLM-5',
                'status' => 'online',
                'depth' => 0,
                'parentId' => null,
            ],
            [
                'id' => 'pm',
                'name' => 'Jordan',
                'role' => 'Project Manager',
                'model' => 'Dolphin 3.0',
                'status' => 'online',
                'depth' => 1,
                'parentId' => 'main',
            ],
            // ... specialists
        ];
    }
    
    public function selectAgent(string $id): void
    {
        $this->selectedAgent = $id;
    }
    
    public function render()
    {
        return view('livewire.org-chart');
    }
}
```

### View Template

```blade
{{-- resources/views/livewire/org-chart.blade.php --}}

<div class="org-chart-container">
    <!-- Main node at top -->
    <div class="chart-level level-0">
        @foreach($team->where('depth', 0) as $member)
            <div class="node {{ $selectedAgent === $member['id'] ? 'selected' : '' }}"
                 wire:click="selectAgent('{{ $member['id'] }}')">
                <div class="node-avatar">🌙</div>
                <div class="node-name">{{ $member['name'] }}</div>
                <div class="node-role">{{ $member['role'] }}</div>
                <div class="node-model">{{ $member['model'] }}</div>
                <div class="node-status {{ $member['status'] }}">
                    {{ $member['status'] }}
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Connector line -->
    <div class="connector"></div>
    
    <!-- PM level -->
    <div class="chart-level level-1">
        @foreach($team->where('depth', 1) as $member)
            <div class="node {{ $selectedAgent === $member['id'] ? 'selected' : '' }}"
                 wire:click="selectAgent('{{ $member['id'] }}')">
                <div class="node-avatar">📋</div>
                <div class="node-name">{{ $member['name'] }}</div>
                <div class="node-role">{{ $member['role'] }}</div>
                <div class="node-model">{{ $member['model'] }}</div>
                <div class="node-status {{ $member['status'] }}">{{ $member['status'] }}</div>
            </div>
        @endforeach
    </div>
    
    <!-- Connector lines -->
    <div class="connector-fan"></div>
    
    <!-- Specialists level -->
    <div class="chart-level level-2">
        @foreach($team->where('depth', 2) as $member)
            <div class="node {{ $selectedAgent === $member['id'] ? 'selected' : '' }}"
                 wire:click="selectAgent('{{ $member['id'] }}')">
                <div class="node-avatar">{{ $member['avatar'] }}</div>
                <div class="node-name">{{ $member['name'] }}</div>
                <div class="node-role">{{ $member['role'] }}</div>
                <div class="node-model">{{ $member['model'] }}</div>
                <div class="node-status {{ $member['status'] }}">{{ $member['status'] }}</div>
            </div>
        @endforeach
    </div>
    
    <!-- Agent detail panel -->
    @if($selectedAgent)
    <div class="agent-detail-panel">
        {{-- Show recent tasks, activity, etc. --}}
    </div>
    @endif
</div>
```

### Routes

```php
// routes/web.php
Route::get('/org-chart', OrgChart::class)->name('org-chart');
```

### Styling (Tailwind)

```css
/* Custom additions */
.node {
    @apply bg-white rounded-lg shadow-md p-4 cursor-pointer transition-all;
}
.node.selected {
    @apply ring-2 ring-blue-500;
}
.node-status.online {
    @apply text-green-500;
}
.connector {
    @apply w-px h-8 bg-gray-300 mx-auto;
}
```

---

## Module 2: Subagent Monitor

### Purpose
Real-time visibility into which subagents are active, their current tasks, and recent history.

### Data Model

```php
// No local database - uses OpenClaw API

class SubagentStatus 
{
    public string $agentId;
    public string $status;        // running, idle, done, failed
    public ?string $currentTask;
    public int $runtimeSeconds;
    public string $model;
    public int $tokensUsed;
    public Carbon $startedAt;
}
```

### API Endpoint (for polling)

```php
// app/Http/Controllers/Api/SubagentController.php

class SubagentController extends Controller
{
    public function status(): JsonResponse
    {
        // Call OpenClaw gateway to get subagent status
        // This requires a bridge script that calls the OpenClaw API
        
        $status = $this->getOpenClawStatus();
        
        return response()->json([
            'agents' => $status['agents'],
            'active' => $status['active'],
            'recent' => $status['recent'],
        ]);
    }
    
    private function getOpenClawStatus(): array
    {
        // Use a helper script that runs `openclaw agents list`
        // or reads from the gateway API
        $output = Process::run('openclaw agents list --json');
        return json_decode($output, true);
    }
}
```

### Livewire Component

```php
// app/Http/Livewire/SubagentMonitor.php

class SubagentMonitor extends Component
{
    public array $agents = [];
    public array $recentActivity = [];
    public bool $autoRefresh = true;
    public int $refreshInterval = 5000; // 5 seconds
    
    protected $listeners = ['refreshStatus' => 'loadStatus'];
    
    public function mount(): void
    {
        $this->loadStatus();
    }
    
    public function loadStatus(): void
    {
        // Poll OpenClaw for status
        $response = Http::get(config('lunaos.openclaw_api') . '/subagents/status');
        
        if ($response->successful()) {
            $data = $response->json();
            $this->agents = $data['agents'];
            $this->recentActivity = $data['recent'];
        }
        
        // Schedule next refresh if auto-refresh enabled
        if ($this->autoRefresh) {
            $this->dispatch('refreshStatus')->delay($this->refreshInterval);
        }
    }
    
    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
        if ($this->autoRefresh) {
            $this->loadStatus();
        }
    }
    
    public function render()
    {
        return view('livewire.subagent-monitor');
    }
}
```

### View Template

```blade
{{-- resources/views/livewire/subagent-monitor.blade.php --}}

<div class="subagent-monitor" wire:poll.5s="loadStatus">
    <div class="monitor-header">
        <h3>Subagent Status</h3>
        <div class="controls">
            <button wire:click="loadStatus" class="btn btn-sm">
                🔄 Refresh
            </button>
            <label class="toggle">
                <input type="checkbox" wire:model="autoRefresh">
                Auto-refresh
            </label>
        </div>
    </div>
    
    <div class="agents-grid">
        @foreach($agents as $agent)
        <div class="agent-card {{ $agent['status'] }}">
            <div class="agent-avatar">{{ $agent['avatar'] }}</div>
            <div class="agent-info">
                <div class="agent-name">{{ $agent['name'] }}</div>
                <div class="agent-status">
                    @if($agent['status'] === 'running')
                        <span class="pulse"></span>
                        Running: {{ Str::limit($agent['task'], 30) }}
                        <span class="runtime">{{ $agent['runtime'] }}s</span>
                    @else
                        {{ ucfirst($agent['status']) }}
                    @endif
                </div>
                @if($agent['status'] === 'running')
                <div class="progress-bar">
                    <div class="progress" style="width: {{ $agent['progress'] ?? 50 }}%"></div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="recent-activity">
        <h4>Recent Activity</h4>
        <ul class="activity-list">
            @foreach($recentActivity as $activity)
            <li class="activity-item {{ $activity['status'] }}">
                <span class="time">{{ $activity['time'] }}</span>
                <span class="agent">{{ $activity['agent'] }}</span>
                <span class="action">{{ $activity['action'] }}</span>
                <span class="task">{{ Str::limit($activity['task'], 40) }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>

@script
<script>
    // Auto-scroll recent activity to bottom
    document.querySelector('.activity-list')?.scrollTo(0, 999999);
</script>
@endscript
```

---

## Module 3: Task Board (Kanban)

### Purpose
Visual task tracking with drag-drop capability (read-only initially, interactive later).

### Data Model

Uses existing `projects.db`:

```sql
-- Already exists:
-- projects (id, name, repo_path, status, created_at)
-- tasks (id, project_id, title, description, assigned_to, status, branch, created_at, completed_at)
-- task_history (id, task_id, agent, action, notes, timestamp)

-- New columns needed:
ALTER TABLE tasks ADD COLUMN priority TEXT DEFAULT 'medium';
ALTER TABLE tasks ADD COLUMN labels TEXT; -- JSON array
ALTER TABLE tasks ADD COLUMN position INTEGER DEFAULT 0; -- For ordering within columns
```

### Livewire Component

```php
// app/Http/Livewire/TaskBoard.php

class TaskBoard extends Component
{
    public string $projectId;
    public array $columns = ['todo', 'in_progress', 'blocked', 'done'];
    public array $tasks = [];
    public bool $canEdit = false; // Read-only for now
    
    protected $listeners = ['taskMoved' => 'handleTaskMove'];
    
    public function mount(string $projectId = null): void
    {
        $this->projectId = $projectId ?? $this->getDefaultProject();
        $this->loadTasks();
        $this->canEdit = false; // Architecture allows this to become true
    }
    
    public function loadTasks(): void
    {
        $dbPath = config('lunaos.projects_db');
        
        $this->tasks = DB::connection('sqlite')
            ->table('tasks')
            ->where('project_id', $this->projectId)
            ->orderBy('position')
            ->get()
            ->groupBy('status')
            ->toArray();
    }
    
    // Architecture for future interactivity
    public function handleTaskMove(string $taskId, string $newStatus): void
    {
        if (!$this->canEdit) {
            return;
        }
        
        DB::connection('sqlite')
            ->table('tasks')
            ->where('id', $taskId)
            ->update([
                'status' => $newStatus,
                'completed_at' => $newStatus === 'done' ? now() : null,
            ]);
        
        $this->loadTasks();
    }
    
    public function render()
    {
        return view('livewire.task-board');
    }
}
```

### View Template

```blade
{{-- resources/views/livewire/task-board.blade.php --}}

<div class="task-board" 
     {{-- Architecture for future drag-drop --}}
     x-data="{ dragged: null }"
     @task-moved.window="handleTaskMove">
    
    <div class="board-header">
        <h2>Task Board</h2>
        <select wire:model="projectId" wire:change="loadTasks" class="project-select">
            @foreach($projects as $project)
            <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="board-columns">
        @foreach($columns as $column)
        <div class="board-column" 
             data-status="{{ $column }}"
             {{-- Drop zone for future interactivity --}}
             @drop="if($wire.canEdit) { $wire.handleTaskMove(dragged, '{{ $column }}') }">
            
            <div class="column-header">
                <h3>{{ ucfirst(str_replace('_', ' ', $column)) }}</h3>
                <span class="task-count">{{ count($tasks[$column] ?? []) }}</span>
            </div>
            
            <div class="column-body">
                @foreach(($tasks[$column] ?? []) as $task)
                <div class="task-card"
                     data-task-id="{{ $task->id }}"
                     {{-- Draggable for future interactivity --}}
                     draggable="{{ $canEdit ? 'true' : 'false' }}"
                     @dragstart="dragged = '{{ $task->id }}'">
                    
                    <div class="task-header">
                        <span class="task-id">#{{ $task->id }}</span>
                        <span class="task-priority {{ $task->priority ?? 'medium' }}">
                            {{ $task->priority ?? 'medium' }}
                        </span>
                    </div>
                    
                    <div class="task-title">{{ $task->title }}</div>
                    
                    @if($task->description)
                    <div class="task-description">
                        {{ Str::limit($task->description, 80) }}
                    </div>
                    @endif
                    
                    <div class="task-footer">
                        @if($task->assigned_to)
                        <div class="task-assignee {{ strtolower($task->assigned_to) }}">
                            {{ $task->assigned_to }}
                        </div>
                        @endif
                        
                        @if($task->branch)
                        <div class="task-branch">
                            🌿 {{ $task->branch }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
```

### Styling

```css
.board-columns {
    @apply flex gap-4 overflow-x-auto pb-4;
}

.board-column {
    @apply flex-shrink-0 w-72 bg-gray-100 rounded-lg;
}

.task-card {
    @apply bg-white rounded-md p-3 mb-2 shadow-sm cursor-pointer;
}

.task-card[draggable="true"] {
    @apply cursor-grab;
}

.task-card[draggable="true"]:active {
    @apply cursor-grabbing;
}
```

---

## Module 4: Daily Standups

### Purpose
Automated daily standups with PM + team status, delivered to dashboard and email.

### Data Model

```sql
-- New table in lunaos.db
CREATE TABLE standups (
    id INTEGER PRIMARY KEY,
    date DATE NOT NULL,
    time TIME NOT NULL,
    project_id TEXT,
    summary TEXT,
    team_status TEXT, -- JSON
    blockers TEXT,
    next_actions TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Also need in projects.db:
CREATE TABLE standup_notes (
    id INTEGER PRIMARY KEY,
    standup_id INTEGER,
    agent_id TEXT,
    notes TEXT,
    tasks_completed INTEGER,
    tasks_in_progress INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Livewire Component

```php
// app/Http/Livewire/Standup.php

class Standup extends Component
{
    public ?string $selectedDate = null;
    public array $standups = [];
    public array $currentStandup = [];
    public bool $autoGenerate = true;
    
    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadStandups();
    }
    
    public function loadStandups(): void
    {
        $this->standups = Standup::orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
        
        $today = Standup::where('date', $this->selectedDate)->first();
        
        if (!$today && $this->autoGenerate && $this->selectedDate === now()->format('Y-m-d')) {
            $this->generateStandup();
        } else {
            $this->currentStandup = $today?->toArray() ?? [];
        }
    }
    
    public function generateStandup(): void
    {
        // Spawn PM subagent to generate standup
        $this->dispatch('generate-standup');
    }
    
    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->loadStandups();
    }
    
    public function render()
    {
        return view('livewire.standup');
    }
}
```

### Standup Generation (via PM)

```php
// app/Services/StandupGenerator.php

class StandupGenerator
{
    public function generate(string $date): Standup
    {
        // Get task status from projects.db
        $tasks = $this->getTaskSummary();
        
        // Get subagent activity
        $activity = $this->getSubagentActivity();
        
        // Create standup record
        $standup = Standup::create([
            'date' => $date,
            'time' => now()->format('H:i'),
            'summary' => $this->generateSummary($tasks, $activity),
            'team_status' => json_encode($this->getTeamStatus()),
            'blockers' => $this->getBlockers($tasks),
            'next_actions' => $this->getNextActions($tasks),
        ]);
        
        // Generate project documentation
        $this->generateProjectDocs($standup);
        
        return $standup;
    }
    
    private function generateSummary(array $tasks, array $activity): string
    {
        $completed = count($tasks['done'] ?? []);
        $inProgress = count($tasks['in_progress'] ?? []);
        
        return "Team completed {$completed} tasks today. {$inProgress} tasks in progress. No blockers reported.";
    }
}
```

### View Template

```blade
{{-- resources/views/livewire/standup.blade.php --}}

<div class="standup-container">
    <div class="standup-header">
        <h2>Daily Standup</h2>
        <div class="date-picker">
            <input type="date" wire:model="selectedDate" wire:change="loadStandups">
        </div>
        <button wire:click="generateStandup" class="btn btn-primary">
            🔄 Generate Standup
        </button>
    </div>
    
    @if($currentStandup)
    <div class="standup-content">
        <div class="standup-meta">
            <span class="date">{{ $currentStandup['date'] }}</span>
            <span class="time">{{ $currentStandup['time'] }}</span>
        </div>
        
        <div class="standup-summary">
            <h3>Summary</h3>
            <p>{{ $currentStandup['summary'] }}</p>
        </div>
        
        <div class="team-status">
            <h3>Team Status</h3>
            <div class="status-grid">
                @foreach(json_decode($currentStandup['team_status'], true) as $agent => $status)
                <div class="agent-status-card">
                    <div class="agent-name">{{ $agent }}</div>
                    <div class="agent-stats">
                        <span class="completed">{{ $status['completed'] }} ✓</span>
                        <span class="in-progress">{{ $status['in_progress'] }} 🔄</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        @if($currentStandup['blockers'])
        <div class="blockers">
            <h3>🚧 Blockers</h3>
            <p>{{ $currentStandup['blockers'] }}</p>
        </div>
        @endif
        
        <div class="next-actions">
            <h3>📋 Next Actions</h3>
            <p>{{ $currentStandup['next_actions'] }}</p>
        </div>
    </div>
    @else
    <div class="no-standup">
        <p>No standup for {{ $selectedDate }}. Click "Generate Standup" to create one.</p>
    </div>
    @endif
    
    <div class="standup-history">
        <h3>Recent Standups</h3>
        <ul class="history-list">
            @foreach($standups as $standup)
            <li wire:click="selectDate('{{ $standup['date'] }}')" 
                class="{{ $standup['date'] === $selectedDate ? 'selected' : '' }}">
                {{ $standup['date'] }} - {{ Str::limit($standup['summary'], 50) }}
            </li>
            @endforeach
        </ul>
    </div>
</div>
```

### Cron Job (for automated standups)

```php
// app/Console/Commands/GenerateDailyStandup.php

class GenerateDailyStandup extends Command
{
    protected $signature = 'standup:generate {--time=09:00}';
    
    public function handle(): void
    {
        $generator = app(StandupGenerator::class);
        $standup = $generator->generate(now()->format('Y-m-d'));
        
        // Send email notification
        Mail::to(config('lunaos.standup_email'))
            ->send(new StandupEmail($standup));
        
        $this->info("Standup generated for {$standup->date}");
    }
}

// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('standup:generate')
        ->weekdays()
        ->at('09:00');
}
```

---

## Module 5: Project Documentation

### Purpose
Auto-generate project documentation with task history and standups.

### File Structure

```
projects/
├── test-project/
│   ├── README.md              # Auto-generated project overview
│   ├── tasks/
│   │   ├── task-001.md        # Individual task details
│   │   ├── task-002.md
│   │   └── ...
│   └── standups/
│       ├── 2026-02-24.md
│       └── ...
└── onewatch-cloud/
    ├── README.md
    ├── tasks/
    └── standups/
```

### Task Document Format

```markdown
# Task: Create Customer Model

**Task ID:** task-001
**Project:** test-project
**Assigned To:** Dave
**Status:** Done
**Created:** 2026-02-24 13:49
**Completed:** 2026-02-24 13:52

## Description
Create a Customer model with standard fields.

## Work Log

| Time | Agent | Action |
|------|-------|--------|
| 13:49 | PM | Created task |
| 13:50 | PM | Spawned Dave |
| 13:51 | Dave | Created migration |
| 13:51 | Dave | Created model |
| 13:52 | PM | Marked complete |

## Output
- `/app/Models/Customer.php`
- `/database/migrations/2026_02_24_create_customers_table.php`

## Branch
`feature/customer-model`
```

### README Format

```markdown
# test-project

**Status:** Active
**Created:** 2026-02-24
**Repository:** (if configured)

## Overview

(Auto-generated from project description)

## Statistics

- Total Tasks: 5
- Completed: 3
- In Progress: 1
- Blocked: 0
- Todo: 1

## Team

| Agent | Tasks Completed |
|-------|-----------------|
| Dave | 2 |
| Maya | 1 |

## Recent Activity

- 2026-02-24: Dave completed Customer model
- 2026-02-24: PM created project

## Links

- [Task Board](/tasks?project=test-project)
- [Standups](/standups?project=test-project)
```

---

## Configuration

```php
// config/lunaos.php

return [
    'openclaw_api' => env('OPENCLAW_API', 'http://127.0.0.1:18789'),
    'projects_db' => env('PROJECTS_DB', database_path('projects.db')),
    'standup_email' => env('STANDUP_EMAIL', 'kjobear@gmail.com'),
    'auto_standup' => env('AUTO_STANDUP', true),
    'standup_time' => env('STANDUP_TIME', '09:00'),
];
```

---

## Database Migrations

```php
// database/migrations/2026_02_24_create_lunaos_tables.php

Schema::create('standups', function (Blueprint $table) {
    $table->id();
    $table->date('date');
    $table->time('time');
    $table->string('project_id')->nullable();
    $table->text('summary');
    $table->text('team_status')->nullable(); // JSON
    $table->text('blockers')->nullable();
    $table->text('next_actions')->nullable();
    $table->timestamps();
});

// database/migrations/2026_02_24_update_projects_db.php
// (Adds columns to existing projects.db)
```

---

## Routes

```php
// routes/web.php

Route::get('/', Home::class)->name('home');
Route::get('/org-chart', OrgChart::class)->name('org-chart');
Route::get('/tasks', TaskBoard::class)->name('tasks');
Route::get('/tasks/{projectId}', TaskBoard::class)->name('tasks.project');
Route::get('/standups', Standup::class)->name('standups');
Route::get('/standups/{date}', Standup::class)->name('standups.date');

// API routes for real-time updates
Route::prefix('api')->group(function () {
    Route::get('/subagents/status', [SubagentController::class, 'status']);
    Route::get('/tasks/{projectId}', [TaskController::class, 'index']);
    Route::post('/tasks/{taskId}/move', [TaskController::class, 'move']);
});
```

---

## Implementation Order

1. **Database setup** - Migrations for lunaos.db, update projects.db
2. **Models** - Standup model, Task model (connects to projects.db)
3. **Org Chart** - Static component, quick win
4. **Subagent Monitor** - Requires API bridge to OpenClaw
5. **Task Board** - Read data from projects.db
6. **Standups** - Generation logic + views
7. **Project Docs** - Auto-generation service

---

## Future Enhancements (Post-MVP)

- Interactive drag-drop on Task Board
- Real-time updates via WebSockets
- Task creation from LunaOS
- Time tracking per task
- Sprint planning views
- Burndown charts
- Agent performance metrics

---

## Testing

```php
// tests/Feature/TaskBoardTest.php

test('task board loads tasks from projects db', function () {
    // Create test data in projects.db
    DB::connection('projects')->table('tasks')->insert([
        'id' => 'test-001',
        'project_id' => 'test-project',
        'title' => 'Test Task',
        'status' => 'todo',
        'assigned_to' => 'Dave',
    ]);
    
    Livewire::test(TaskBoard::class, ['projectId' => 'test-project'])
        ->assertSee('Test Task')
        ->assertSee('Dave');
});

test('task board is read-only by default', function () {
    $component = Livewire::test(TaskBoard::class);
    
    expect($component->canEdit)->toBeFalse();
});
```

---

## Open Questions (Resolved)

| Question | Answer |
|----------|--------|
| Real-time or manual refresh? | **Real-time** (5s polling) |
| Standup trigger? | **Both** (cron 9AM + manual button) |
| Kanban interaction? | **Read-only initially**, architecture allows future drag-drop |
| Docs location? | **`projects/` folder in workspace** |

---

## Estimated Effort

| Component | Hours |
|-----------|-------|
| Database setup | 2 |
| Org Chart | 3 |
| Subagent Monitor | 4 |
| Task Board | 5 |
| Standups | 4 |
| Project Docs | 2 |
| Testing | 3 |
| **Total** | **23 hours** |

With subagents working in parallel on different modules: ~8-10 hours calendar time.
