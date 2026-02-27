# Mission Control - Implementation Specification

**Created:** Feb 24, 2026
**Status:** Ready for Implementation
**Design Reference:** `mission_control.html`

---

## Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Real-time updates | **WebSockets (Laravel Reverb)** | Native Laravel solution, free, local-first |
| Activity storage | **SQLite** | Consistent with existing projects.db, 30-day retention |
| Drag-drop | **Implement immediately** | Native HTML5 drag-drop API + Livewire |
| Charts | **ApexCharts** | More features, better for workload visualization |
| Design system | **Mission Control theme** | Apply across all LunaOS modules |

---

## Phase 1: WebSockets Setup (Laravel Reverb)

### Installation

```bash
composer require laravel/reverb
php artisan reverb:install
npm install --save-dev laravel-echo pusher-js
```

### Configuration

```env
# .env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=lunaos
REVERB_APP_KEY=lunaos-key
REVERB_APP_SECRET=lunaos-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Broadcasting Events

```php
// app/Events/SubagentActivity.php
class SubagentActivity implements ShouldBroadcast
{
    public string $agentId;
    public string $action;
    public string $task;
    public string $status;
    public string $timestamp;

    public function broadcastOn()
    {
        return new Channel('mission-control');
    }
}
```

### Frontend Setup

```javascript
// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    disableStats: true,
});

// Listen for events
window.Echo.channel('mission-control')
    .listen('SubagentActivity', (event) => {
        Livewire.dispatch('activity-received', event);
    });
```

### Server Startup

```bash
# Start Reverb server (separate process)
php artisan reverb:start

# Or via Procfile/Supervisor for production
```

---

## Phase 2: Activity Logging System

### Database Migration

```php
// database/migrations/create_activity_logs_table.php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('agent_id');
    $table->string('agent_name');
    $table->string('action'); // spawned, completed, failed, spawned_child
    $table->string('task')->nullable();
    $table->string('status'); // running, done, failed
    $table->integer('tokens_used')->default(0);
    $table->integer('runtime_ms')->default(0);
    $table->decimal('cost', 8, 6)->default(0);
    $table->json('metadata')->nullable();
    $table->timestamp('created_at');
    
    // Auto-prune after 30 days
    $table->index('created_at');
});

// Add to SQLite connection in config/database.php
'sqlite-activity' => [
    'driver' => 'sqlite',
    'database' => '/Users/kobear/.openclaw/workspace/activity.db',
    'prefix' => '',
],
```

### Model

```php
// app/Models/ActivityLog.php
class ActivityLog extends Model
{
    protected $connection = 'sqlite-activity';
    protected $fillable = [
        'agent_id', 'agent_name', 'action', 'task', 'status',
        'tokens_used', 'runtime_ms', 'cost', 'metadata'
    ];
    
    protected static function booted()
    {
        // Auto-prune old records
        static::created(function () {
            static::where('created_at', '<', now()->subDays(30))->delete();
        });
    }
}
```

### Service

```php
// app/Services/ActivityLogger.php
class ActivityLogger
{
    public static function log(string $agentId, string $action, array $data = []): void
    {
        $log = ActivityLog::create([
            'agent_id' => $agentId,
            'agent_name' => self::getAgentName($agentId),
            'action' => $action,
            'task' => $data['task'] ?? null,
            'status' => $data['status'] ?? 'running',
            'tokens_used' => $data['tokens'] ?? 0,
            'runtime_ms' => $data['runtime'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'metadata' => $data['metadata'] ?? null,
        ]);
        
        // Broadcast to WebSocket
        broadcast(new SubagentActivity(
            $agentId,
            $action,
            $data['task'] ?? '',
            $data['status'] ?? 'running',
            $log->created_at->toIso8601String()
        ));
    }
    
    private static function getAgentName(string $id): string
    {
        return match($id) {
            'main' => 'Luna',
            'pm' => 'Jordan',
            'dave' => 'Dave',
            'maya' => 'Maya',
            'chen' => 'Chen',
            'sam' => 'Sam',
            'alex' => 'Alex',
            default => $id,
        };
    }
}
```

---

## Phase 3: Mission Control Dashboard

### Component Structure

```
app/Livewire/
├── MissionControl.php          # Main dashboard
├── AgentGrid.php               # Real-time agent status
├── ActivityFeed.php            # Scrollable activity log
├── WorkloadChart.php           # ApexCharts workload viz
├── TaskPipeline.php            # Enhanced task board with drag-drop
└── QuickActions.php            # Spawn agents, create tasks

resources/views/livewire/
├── mission-control.blade.php   # Main layout
├── agent-grid.blade.php        # Agent cards
├── activity-feed.blade.php     # Activity list
├── workload-chart.blade.php    # ApexCharts component
├── task-pipeline.blade.php     # Kanban with drag-drop
└── quick-actions.blade.php     # Action buttons
```

### Main Dashboard Component

```php
// app/Livewire/MissionControl.php
class MissionControl extends Component
{
    public array $agents = [];
    public array $activity = [];
    public array $tasks = [];
    public array $workload = [];
    public ?string $currentMission = null;
    
    protected $listeners = [
        'activity-received' => 'handleActivity',
        'task-updated' => 'refreshTasks',
    ];
    
    public function mount(): void
    {
        $this->loadAgents();
        $this->loadActivity();
        $this->loadTasks();
        $this->loadWorkload();
        $this->loadCurrentMission();
    }
    
    public function handleActivity(array $event): void
    {
        // Real-time update from WebSocket
        array_unshift($this->activity, $event);
        $this->activity = array_slice($this->activity, 0, 50);
        $this->loadAgents(); // Refresh status
    }
    
    // ... other methods
}
```

### Agent Grid with Live Status

```blade
{{-- resources/views/livewire/agent-grid.blade.php --}}
<div class="agent-grid" wire:poll.5s="loadAgents">
    @foreach($agents as $agent)
    <div class="agent-card {{ $agent['status'] }}" 
         data-agent="{{ $agent['id'] }}">
        <div class="agent-avatar">{{ $agent['avatar'] }}</div>
        <div class="agent-info">
            <div class="agent-name">{{ $agent['name'] }}</div>
            <div class="agent-role">{{ $agent['role'] }}</div>
            <div class="agent-status-indicator">
                @if($agent['status'] === 'running')
                    <span class="pulse"></span>
                    <span class="task-preview">{{ Str::limit($agent['task'], 20) }}</span>
                @else
                    <span class="idle">Idle</span>
                @endif
            </div>
        </div>
        <div class="agent-meta">
            <span class="model-badge">{{ $agent['model'] }}</span>
            @if($agent['status'] === 'running')
            <span class="runtime">{{ $agent['runtime'] }}s</span>
            @endif
        </div>
    </div>
    @endforeach
</div>
```

---

## Phase 4: ApexCharts Integration

### Installation

```bash
npm install apexcharts
```

### Blade Component

```php
// app/View/Components/ApexChart.php
class ApexChart extends Component
{
    public string $id;
    public string $type;
    public array $series;
    public array $options;
    
    public function render()
    {
        return view('components.apex-chart');
    }
}
```

### View

```blade
{{-- resources/views/components/apex-chart.blade.php --}}
<div id="{{ $id }}" class="apex-chart"></div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const options = @json($options);
        options.series = @json($series);
        const chart = new ApexCharts(document.getElementById('{{ $id }}'), options);
        chart.render();
        
        // Store reference for Livewire updates
        window['chart_{{ $id }}'] = chart;
    });
</script>
@endpush
```

### Workload Chart Component

```php
// app/Livewire/WorkloadChart.php
class WorkloadChart extends Component
{
    public string $chartId = 'workload-chart';
    public array $series = [];
    public array $options = [];
    
    public function mount(): void
    {
        $this->loadChartData();
    }
    
    public function loadChartData(): void
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/activity.db';
        
        // Get task distribution by agent
        $pdo = new \PDO('sqlite:' . $dbPath);
        $stmt = $pdo->query("
            SELECT agent_name, COUNT(*) as count 
            FROM activity_logs 
            WHERE created_at > datetime('now', '-7 days')
            GROUP BY agent_name
        ");
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->series = [[
            'name' => 'Tasks',
            'data' => array_column($data, 'count'),
        ]];
        
        $this->options = [
            'chart' => [
                'type' => 'bar',
                'height' => 200,
                'toolbar' => ['show' => false],
            ],
            'xaxis' => [
                'categories' => array_column($data, 'agent_name'),
            ],
            'colors' => ['#7c3aed'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => true,
                ],
            ],
        ];
    }
}
```

---

## Phase 5: Drag-Drop Task Pipeline

### Enhanced TaskBoard

```php
// app/Livewire/TaskPipeline.php
class TaskPipeline extends Component
{
    public array $columns = ['todo', 'in_progress', 'blocked', 'review', 'done'];
    public array $tasks = [];
    public bool $canEdit = true; // Enable drag-drop
    
    protected $listeners = ['task-moved' => 'handleTaskMove'];
    
    public function handleTaskMove(string $taskId, string $newStatus): void
    {
        $dbPath = '/Users/kobear/.openclaw/workspace/projects.db';
        $pdo = new \PDO('sqlite:' . $dbPath);
        
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $taskId]);
        
        // Log activity
        ActivityLogger::log('system', 'task_moved', [
            'task' => "Task {$taskId} moved to {$newStatus}",
            'status' => 'done',
        ]);
        
        $this->loadTasks();
    }
}
```

### View with Drag-Drop

```blade
<div class="task-pipeline">
    @foreach($columns as $column)
    <div class="pipeline-column" 
         data-status="{{ $column }}"
         ondragover="event.preventDefault()"
         ondrop="handleDrop(event, '{{ $column }}')">
        
        <div class="column-header">
            <h3>{{ ucfirst(str_replace('_', ' ', $column)) }}</h3>
            <span class="count">{{ count($tasks[$column] ?? []) }}</span>
        </div>
        
        <div class="column-body">
            @foreach(($tasks[$column] ?? []) as $task)
            <div class="task-card"
                 draggable="{{ $canEdit ? 'true' : 'false' }}"
                 data-task-id="{{ $task['id'] }}"
                 ondragstart="handleDragStart(event)">
                {{-- Task content --}}
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
function handleDragStart(e) {
    e.dataTransfer.setData('taskId', e.target.dataset.taskId);
}

function handleDrop(e, newStatus) {
    e.preventDefault();
    const taskId = e.dataTransfer.getData('taskId');
    
    @this.call('handleTaskMove', taskId, newStatus);
}
</script>
@endpush
```

---

## Phase 6: Design System Unification

### Shared Styles

```css
/* resources/css/mission-control.css */

/* Color Palette */
:root {
    --bg-primary: #0d0d1a;
    --bg-secondary: #1a1a2e;
    --bg-tertiary: #252542;
    --text-primary: #e4e4f0;
    --text-secondary: #a0a0b8;
    --text-muted: #6b6b80;
    --accent-purple: #7c3aed;
    --accent-cyan: #06b6d4;
    --accent-green: #10b981;
    --accent-yellow: #f59e0b;
    --accent-red: #ef4444;
}

/* Agent Cards */
.agent-card {
    @apply bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40];
}
.agent-card.running {
    @apply border-green-500/50;
}
.agent-card.running .pulse {
    @apply w-2 h-2 rounded-full bg-green-500 animate-pulse;
}

/* Task Cards */
.task-card {
    @apply bg-[#1a1a2e] rounded-lg p-3 border border-[#2a2a40] cursor-pointer;
}
.task-card:hover {
    @apply border-[#7c3aed];
}
.task-card[draggable="true"] {
    @apply cursor-grab;
}
.task-card[draggable="true"]:active {
    @apply cursor-grabbing opacity-50;
}

/* Activity Feed */
.activity-item {
    @apply flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-[#252542];
}
.activity-item.running {
    @apply border-l-2 border-yellow-500;
}
.activity-item.done {
    @apply border-l-2 border-green-500;
}
.activity-item.failed {
    @apply border-l-2 border-red-500;
}
```

### Layout Updates

Apply Mission Control design to existing modules:

1. **Org Chart** → Same dark theme, consistent cards
2. **Task Board** → Rename to TaskPipeline, add drag-drop
3. **Standup** → Compact inline layout
4. **Home** → Redirect to Mission Control

---

## Routes

```php
// routes/web.php
Route::get('/', MissionControl::class)->name('home');
Route::get('/org-chart', OrgChart::class)->name('org-chart');
Route::get('/tasks', TaskPipeline::class)->name('tasks');
Route::get('/standup', Standup::class)->name('standup');
Route::get('/activity', ActivityFeed::class)->name('activity');

// API for Quick Actions
Route::post('/api/spawn-agent', [AgentController::class, 'spawn']);
Route::post('/api/create-task', [TaskController::class, 'create']);
```

---

## Implementation Order

| Phase | Tasks | Effort |
|-------|-------|--------|
| **1** | Install Reverb, configure WebSockets | 1 hour |
| **2** | Create activity logs DB + service | 1 hour |
| **3** | MissionControl component + Agent Grid | 2 hours |
| **4** | ApexCharts + Workload visualization | 1.5 hours |
| **5** | TaskPipeline with drag-drop | 2 hours |
| **6** | Design unification across modules | 1.5 hours |
| **7** | Quick Actions + forms | 1 hour |
| **Total** | | **10 hours** |

---

## Testing Checklist

- [ ] WebSocket connection established
- [ ] Real-time activity updates appear without refresh
- [ ] Activity logs stored in SQLite
- [ ] 30-day auto-prune works
- [ ] ApexCharts renders workload data
- [ ] Drag-drop moves tasks between columns
- [ ] Task status persists to database
- [ ] All modules use Mission Control theme
- [ ] Quick Actions spawn agents correctly
- [ ] Activity feed scrolls smoothly

---

## Future Enhancements

- Keyboard shortcuts for common actions
- Agent performance metrics over time
- Project switching in Mission Control header
- Notification sounds for completions
- Export activity logs to CSV
- Custom agent personas from UI
