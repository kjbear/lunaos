# LunaOS Feature Specs - Projects & Executive Board

**Created:** Feb 24, 2026
**Status:** Requirements Review
**Dependencies:** Native subagents (Dave, Maya, Chen, Sam, Alex)

---

## Feature 1: Projects Dashboard

### Overview

A comprehensive project management page providing visibility into all active projects, initiation workflows, and requirement management.

### User Stories

| As a | I want to | So that |
|------|-----------|---------|
| Project Owner | See all projects at a glance | I know what's in flight |
| Project Owner | Initiate a new project | I can start work quickly |
| Project Owner | Update requirements | Scope changes are tracked |
| Project Owner | Assign subagents to projects | Work gets distributed |
| Project Owner | See project health metrics | I know what needs attention |

### UI Components

```
┌─────────────────────────────────────────────────────────────────┐
│  Projects                                           [+ New]    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 📊 onewatch.cloud                     Active    85%     │   │
│  │ Status Page Aggregator                                  │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ Tasks: 23 total  •  18 done  •  3 in progress  •  2 todo│   │
│  │ Agents: Dave (3), Maya (2), Sam (1)                     │   │
│  │ Last activity: 2 hours ago                              │   │
│  │ [View Details] [Assign Agents] [Update Requirements]    │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 🏥 IHSSP - In-Home Services SaaS      Planning    0%    │   │
│  │ Multi-tenant platform for agencies                      │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ Tasks: 0 total  •  Requirements pending approval        │   │
│  │ Agents: None assigned                                   │   │
│  │ Last activity: Not started                              │   │
│  │ [View Details] [Assign Agents] [Update Requirements]    │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Data Model

```sql
-- Enhance existing projects table
ALTER TABLE projects ADD COLUMN description TEXT;
ALTER TABLE projects ADD COLUMN repo_url TEXT;
ALTER TABLE projects ADD COLUMN health TEXT DEFAULT 'healthy'; -- healthy, at_risk, blocked
ALTER TABLE projects ADD COLUMN progress INTEGER DEFAULT 0;
ALTER TABLE projects ADD COLUMN owner TEXT DEFAULT 'kyle';
ALTER TABLE projects ADD COLUMN archived_at DATETIME;

-- New table for requirements
CREATE TABLE requirements (
    id TEXT PRIMARY KEY,
    project_id TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    priority TEXT DEFAULT 'medium', -- high, medium, low
    status TEXT DEFAULT 'draft', -- draft, approved, in_progress, done
    created_by TEXT,
    approved_by TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    approved_at DATETIME,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- New table for project assignments
CREATE TABLE project_assignments (
    id INTEGER PRIMARY KEY,
    project_id TEXT NOT NULL,
    agent_id TEXT NOT NULL,
    role TEXT, -- lead, contributor, reviewer
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id)
);
```

### Livewire Components

```php
// app/Livewire/Projects/Index.php
class ProjectsIndex extends Component
{
    public array $projects = [];
    public bool $showNewProjectModal = false;
    public bool $showRequirementsModal = false;
    public ?string $selectedProjectId = null;
    
    // New project form
    public string $newProjectName = '';
    public string $newProjectDescription = '';
    public string $newProjectRepo = '';
    
    public function mount(): void { /* load projects */ }
    public function createProject(): void { /* validate + create */ }
    public function updateRequirements(string $projectId): void { /* open modal */ }
    public function assignAgent(string $projectId, string $agentId): void { /* assign */ }
}

// app/Livewire/Projects/Requirements.php
class ProjectRequirements extends Component
{
    public string $projectId;
    public array $requirements = [];
    public string $newRequirementTitle = '';
    public string $newRequirementDescription = '';
    
    public function add(): void { /* add requirement */ }
    public function approve(string $reqId): void { /* mark approved */ }
    public function prioritize(string $reqId, string $priority): void { /* update priority */ }
}
```

### Views

```blade
{{-- resources/views/livewire/projects/index.blade.php --}}
<div class="projects-index">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Projects</h2>
        <button wire:click="$set('showNewProjectModal', true)" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            + New Project
        </button>
    </div>
    
    <div class="space-y-4">
        @foreach($projects as $project)
        <div class="project-card bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40]">
            {{-- Project card content --}}
        </div>
        @endforeach
    </div>
    
    {{-- New Project Modal --}}
    @if($showNewProjectModal)
    <div class="modal-overlay" wire:click="$set('showNewProjectModal', false)">
        <div class="modal-content" wire:click.stop>
            <h3>Create New Project</h3>
            <form wire:submit="createProject">
                {{-- Form fields --}}
            </form>
        </div>
    </div>
    @endif
</div>
```

### Routes

```php
Route::get('/projects', ProjectsIndex::class)->name('projects');
Route::get('/projects/{id}', ProjectDetail::class)->name('projects.detail');
Route::get('/projects/{id}/requirements', ProjectRequirements::class)->name('projects.requirements');
```

---

## Feature 2: Executive Board

### Overview

A Mix of Experts (MoE) system where AI executive personas debate and provide decisions on strategic questions. Users get both the final decision and a transcript of the debate.

### User Stories

| As a | I want to | So that |
|------|-----------|---------|
| Executive | Ask strategic questions | Get diverse perspectives |
| Executive | See the debate transcript | I understand the reasoning |
| Executive | Customize board members | Different models provide coverage |
| Executive | Save board sessions | I can reference past decisions |
| Executive | Get a final recommendation | I have clear next steps |

### UI Components

```
┌─────────────────────────────────────────────────────────────────┐
│  Executive Board                                    [⚙️ Edit]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Ask the Board                                           │   │
│  │ ┌─────────────────────────────────────────────────────┐ │   │
│  │ │ What technology stack should we use for the new    │ │   │
│  │ │ customer portal? Consider: scalability, cost,     │ │   │
│  │ │ team expertise, and time-to-market.                │ │   │
│  │ └─────────────────────────────────────────────────────┘ │   │
│  │ [Convene Board]                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Board Members                                           │   │
│  │ ┌───────┐ ┌───────┐ ┌───────┐ ┌───────┐ ┌───────┐     │   │
│  │ │ 👔    │ │ 💰    │ │ 💻    │ │ 📢    │ │ 📦    │     │   │
│  │ │ COO   │ │ CFO   │ │ CTO   │ │ CMO   │ │ CPO   │     │   │
│  │ │Haiku  │ │GLM-5  │ │Dolphin│ │Haiku  │ │GLM-5  │     │   │
│  │ └───────┘ └───────┘ └───────┘ └───────┘ └───────┘     │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 📋 Transcript                          [Save Session]   │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ COO (Jordan): From an operations perspective, I'd       │   │
│  │ recommend Laravel with Livewire. The team already      │   │
│  │ has PHP expertise, and Livewire enables rapid          │   │
│  │ development...                                          │   │
│  │                                                         │   │
│  │ CTO (Chen): I agree on Laravel, but I'd add that we    │   │
│  │ should consider adding a Go microservice for heavy     │   │
│  │ processing...                                           │   │
│  │                                                         │   │
│  │ CFO (Maya): What's the cost comparison? If we use      │   │
│  │ Go, we'll need additional developer time...            │   │
│  │                                                         │   │
│  │ [Expand Full Transcript]                                │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ✅ Board Decision                                       │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ RECOMMENDATION: Laravel + Livewire frontend with Go    │   │
│  │ microservices for heavy processing.                    │   │
│  │                                                         │   │
│  │ Key factors:                                           │   │
│  │ • Team expertise in PHP (faster onboarding)            │   │
│  │ • Livewire enables rapid prototyping                   │   │
│  │ • Go microservices for scalability                     │   │
│  │ • Estimated 15% cost reduction vs pure microservices   │   │
│  │                                                         │   │
│  │ [Create Project from Decision]                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Data Model

```sql
-- Board members (personas)
CREATE TABLE board_members (
    id TEXT PRIMARY KEY,
    role TEXT NOT NULL, -- COO, CFO, CTO, CMO, CPO
    name TEXT NOT NULL,
    avatar TEXT,
    model TEXT NOT NULL, -- Model ID to use
    system_prompt TEXT, -- Persona definition
    is_active BOOLEAN DEFAULT true,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Board sessions
CREATE TABLE board_sessions (
    id TEXT PRIMARY KEY,
    question TEXT NOT NULL,
    context TEXT, -- Additional context provided
    status TEXT DEFAULT 'pending', -- pending, debating, decided
    final_decision TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    decided_at DATETIME
);

-- Board session responses (individual member contributions)
CREATE TABLE board_responses (
    id INTEGER PRIMARY KEY,
    session_id TEXT NOT NULL,
    member_id TEXT NOT NULL,
    response TEXT,
    model_used TEXT,
    tokens_used INTEGER,
    response_order INTEGER, -- Order in the debate
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES board_sessions(id),
    FOREIGN KEY (member_id) REFERENCES board_members(id)
);
```

### Livewire Components

```php
// app/Livewire/ExecutiveBoard.php
class ExecutiveBoard extends Component
{
    public array $boardMembers = [];
    public string $question = '';
    public string $context = '';
    public ?string $currentSessionId = null;
    public array $transcript = [];
    public ?string $finalDecision = null;
    public bool $isDebating = false;
    public bool $showEditModal = false;
    
    public function mount(): void
    {
        $this->loadBoardMembers();
    }
    
    public function loadBoardMembers(): void
    {
        // Load from DB or use defaults
        // Personas modeled after successful executives
        $this->boardMembers = [
            ['id' => 'ceo', 'role' => 'CEO', 'name' => 'Steven', 'model' => 'glm-5', 'avatar' => '🎯', 'inspiration' => 'Steve Jobs - visionary, product-obsessed'],
            ['id' => 'coo', 'role' => 'COO', 'name' => 'Gwynne', 'model' => 'haiku', 'avatar' => '👔', 'inspiration' => 'Gwynne Shotwell - operational excellence'],
            ['id' => 'cto', 'role' => 'CTO', 'name' => 'Werner', 'model' => 'dolphin', 'avatar' => '💻', 'inspiration' => 'Werner Vogels - scalability, architecture'],
            ['id' => 'cfo', 'role' => 'CFO', 'name' => 'Warren', 'model' => 'glm-5', 'avatar' => '💰', 'inspiration' => 'Warren Buffet - value investing, ROI discipline'],
            ['id' => 'cmo', 'role' => 'CMO', 'name' => 'Bozoma', 'model' => 'haiku', 'avatar' => '📢', 'inspiration' => 'Bozoma Saint John - cultural marketing'],
            ['id' => 'cpo', 'role' => 'CPO', 'name' => 'Fidji', 'model' => 'glm-5', 'avatar' => '📦', 'inspiration' => 'Fidji Simo - user-centric product'],
        ];
    }
    
    public function conveneBoard(): void
    {
        // Create session
        $this->currentSessionId = Str::uuid()->toString();
        $this->isDebating = true;
        
        // Delegate to PM subagent to orchestrate the debate
        // PM spawns specialist subagents for each board member
    }
    
    public function updateMember(string $memberId, string $model): void
    {
        // Update member's model
    }
}

// app/Livewire/BoardMemberEdit.php
class BoardMemberEdit extends Component
{
    public string $memberId;
    public string $name = '';
    public string $model = '';
    public string $systemPrompt = '';
    
    public function save(): void { /* save changes */ }
}
```

### Debate Orchestration

```php
// app/Services/BoardOrchestrator.php
class BoardOrchestrator
{
    public function orchestrateDebate(string $sessionId, string $question): void
    {
        // 1. PM analyzes the question
        $pmContext = $this->prepareContext($question);
        
        // 2. PM calls each board member in sequence
        foreach ($this->getActiveMembers() as $member) {
            $response = $this->callBoardMember($member, $question, $pmContext);
            $this->saveResponse($sessionId, $member, $response);
            
            // Broadcast each response for real-time updates
            broadcast(new BoardResponse($sessionId, $member, $response));
        }
        
        // 3. PM synthesizes final decision
        $decision = $this->synthesizeDecision($sessionId);
        $this->saveDecision($sessionId, $decision);
        
        // Broadcast final decision
        broadcast(new BoardDecision($sessionId, $decision));
    }
    
    private function callBoardMember(array $member, string $question, array $context): string
    {
        // Spawn subagent with member's persona
        $model = $this->getModelForRole($member['role']);
        
        $response = Http::post(config('services.openclaw.api') . '/spawn', [
            'agentId' => 'pm', // PM orchestrates
            'model' => $model,
            'task' => $this->buildPrompt($member, $question, $context),
        ]);
        
        return $response->json('output');
    }
}
```

### Views

```blade
{{-- resources/views/livewire/executive-board.blade.php --}}
<div class="executive-board" x-data="{ 
    debating: @entangle('isDebating'),
    transcript: @entangle('transcript'),
}">
    <!-- Question Input -->
    <div class="bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40] mb-6">
        <h3 class="text-lg font-semibold text-white mb-3">Ask the Board</h3>
        <textarea wire:model="question" 
                  class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-3 text-white"
                  rows="3"
                  placeholder="Enter your strategic question..."></textarea>
        <div class="mt-3 flex justify-end">
            <button wire:click="conveneBoard" 
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                    :disabled="debating">
                {{ $isDebating ? 'Board in Session...' : 'Convene Board' }}
            </button>
        </div>
    </div>
    
    <!-- Board Members -->
    <div class="bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40] mb-6">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold text-white">Board Members</h3>
            <button wire:click="$set('showEditModal', true)" class="text-gray-400 hover:text-white">
                ⚙️ Edit
            </button>
        </div>
        <div class="flex gap-3">
            @foreach($boardMembers as $member)
            <div class="flex-1 text-center p-3 bg-[#12121f] rounded-lg">
                <div class="text-3xl mb-2">{{ $member['avatar'] }}</div>
                <div class="text-sm font-medium text-white">{{ $member['role'] }}</div>
                <div class="text-xs text-gray-400">{{ $member['name'] }}</div>
                <div class="text-xs mt-1 px-2 py-0.5 rounded {{ $member['model'] === 'dolphin' ? 'bg-cyan-500/20 text-cyan-400' : 'bg-purple-500/20 text-purple-400' }}">
                    {{ $member['model'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Transcript -->
    @if(count($transcript) > 0)
    <div class="bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40] mb-6">
        <h3 class="text-lg font-semibold text-white mb-3">📋 Transcript</h3>
        <div class="space-y-3 max-h-[400px] overflow-y-auto">
            @foreach($transcript as $entry)
            <div class="p-3 bg-[#12121f] rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg">{{ $entry['avatar'] }}</span>
                    <span class="font-medium text-white">{{ $entry['role'] }}</span>
                    <span class="text-xs text-gray-500">({{ $entry['model'] }})</span>
                </div>
                <p class="text-sm text-gray-300">{{ $entry['response'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    <!-- Final Decision -->
    @if($finalDecision)
    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4">
        <h3 class="text-lg font-semibold text-green-400 mb-3">✅ Board Decision</h3>
        <div class="prose prose-invert text-gray-300">
            {!! $finalDecision !!}
        </div>
        <button wire:click="createProjectFromDecision" 
                class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Create Project from Decision
        </button>
    </div>
    @endif
</div>
```

### Routes

```php
Route::get('/board', ExecutiveBoard::class)->name('board');
Route::get('/board/sessions', BoardSessions::class)->name('board.sessions');
Route::get('/board/sessions/{id}', BoardSessionDetail::class)->name('board.session');
```

---

## Implementation Plan Using Subagents

### Team Allocation

| Subagent | Projects Feature | Executive Board Feature |
|----------|------------------|------------------------|
| **Dave (PHP)** | Livewire components, models, migrations | Session management, orchestration backend |
| **Maya (Frontend)** | Project cards, modals, forms | Board member UI, transcript display |
| **Chen (DevOps)** | Database migrations, API routes | WebSocket events, broadcasting setup |
| **Sam (Test)** | Feature tests, integration tests | Board session tests, mock responses |
| **Alex (API)** | REST endpoints for projects | Orchestration API, decision synthesis |

### Phase Breakdown

#### Phase 1: Projects Dashboard (Backend)
**Assigned:** Dave, Chen, Alex

```
Tasks:
1. [Dave] Create migrations for requirements, project_assignments
2. [Dave] Create Eloquent models with relationships
3. [Alex] Create API endpoints for CRUD operations
4. [Chen] Add WebSocket events for real-time updates
```

#### Phase 2: Projects Dashboard (Frontend)
**Assigned:** Maya, Sam

```
Tasks:
1. [Maya] Create ProjectsIndex Livewire component
2. [Maya] Design project cards with health indicators
3. [Maya] Build new project modal
4. [Maya] Create requirements management modal
5. [Sam] Write Livewire component tests
```

#### Phase 3: Executive Board (Backend)
**Assigned:** Dave, Chen, Alex

```
Tasks:
1. [Dave] Create migrations for board_members, board_sessions, board_responses
2. [Dave] Create BoardMember, BoardSession, BoardResponse models
3. [Alex] Create BoardOrchestrator service
4. [Alex] Implement debate orchestration logic
5. [Chen] Set up broadcasting for real-time transcript updates
```

#### Phase 4: Executive Board (Frontend)
**Assigned:** Maya, Sam

```
Tasks:
1. [Maya] Create ExecutiveBoard Livewire component
2. [Maya] Build question input with context field
3. [Maya] Create board member display/edit UI
4. [Maya] Build real-time transcript display
5. [Maya] Create final decision panel
6. [Sam] Write component tests for board interactions
```

#### Phase 5: Integration & Testing
**Assigned:** Sam, Chen

```
Tasks:
1. [Sam] Integration tests for project workflows
2. [Sam] Integration tests for board sessions
3. [Sam] Performance testing for concurrent debates
4. [Chen] WebSocket load testing
5. [Chen] Database optimization
```

---

## Timeline Estimate

| Phase | Duration | Agents |
|-------|----------|--------|
| Phase 1: Projects Backend | 3 hours | Dave, Chen, Alex |
| Phase 2: Projects Frontend | 3 hours | Maya, Sam |
| Phase 3: Board Backend | 4 hours | Dave, Chen, Alex |
| Phase 4: Board Frontend | 4 hours | Maya, Sam |
| Phase 5: Integration | 2 hours | Sam, Chen |
| **Total** | **16 hours** | All |

With parallel execution (multiple subagents working simultaneously): **~6-8 hours calendar time**

---

## Database Schema Summary

```sql
-- Projects feature
projects (enhanced)
├── description TEXT
├── repo_url TEXT
├── health TEXT
├── progress INTEGER
└── archived_at DATETIME

requirements
├── project_id → projects
├── title, description
├── priority, status
└── approved_at

project_assignments
├── project_id → projects
├── agent_id
└── role

-- Executive Board feature
board_members
├── role, name, avatar
├── model
└── system_prompt

board_sessions
├── question, context
├── status
└── final_decision

board_responses
├── session_id → board_sessions
├── member_id → board_members
├── response
└── response_order
```

---

## Questions for Review

**ANSWERS CONFIRMED:**

1. **Projects:** ✅ Yes — support project templates for quick starts
2. **Projects:** ❌ Not initially — skip Gantt/timeline view (can add later)
3. **Board:** ✅ Yes — full bi-directional conversation with goal of CEO recommendation
4. **Board:** ❌ No anonymous mode — treat as live roundtable where all members see each other's responses
5. **Board:** ❌ No weighted voting — discussion with recommendations including risks and benefits analysis

---

## Confirmed Design Decisions

### Executive Board - Roundtable Format

The board operates as a **live roundtable discussion**:

1. **CEO asks question** → Question is presented to all board members
2. **Initial responses** → Each member provides their perspective
3. **Discussion phase** → Members can respond to each other, ask follow-ups, challenge assumptions
4. **Synthesis** → PM synthesizes the discussion into a recommendation
5. **CEO receives:** Final recommendation + full transcript + risks/benefits analysis

### Projects - Template Support

Quick-start templates for common project types:
- **Laravel API Project** — Models, migrations, controllers scaffold
- **Livewire Dashboard** — Auth, navigation, base components
- **Documentation Site** — Markdown structure, search, navigation

Templates stored in `templates/` directory and cloneable on project creation.

---

## Feature 3: HR Module (Persona Management)

### Overview

A unified interface to view, create, edit, and manage all AI personas in the system — including subagents, executive board members, and future custom personas.

### User Stories

| As a | I want to | So that |
|------|-----------|---------|
| Admin | See all personas in one place | I know who's on the team |
| Admin | Create new personas | I can expand the team |
| Admin | Edit persona configuration | I can fine-tune behavior |
| Admin | Deactivate personas | I can remove unused ones |
| Admin | View persona workspace files | I can see their instructions |
| Admin | See persona performance | I know who's productive |

### UI Components

```
┌─────────────────────────────────────────────────────────────────┐
│  HR — Personas                                        [+ New]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Filters: [All ▼] [Active] [Subagents] [Board] [Custom]        │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 🟢 Dave                                    PHP Backend  │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ Model: Dolphin 3.0  •  Role: Subagent  •  Status: Active│   │
│  │ Projects: 3  •  Tasks: 47  •  Success: 94%              │   │
│  │ [View Workspace] [Edit Config] [View History]           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 🟢 Steven                                      CEO      │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ Model: GLM-5  •  Role: Board Member  •  Status: Active  │   │
│  │ Inspiration: Steve Jobs - visionary, product-obsessed   │   │
│  │ Sessions: 12  •  Decisions: 8                           │   │
│  │ [View Workspace] [Edit Config] [View History]           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ⚪ TestAgent                                   Custom   │   │
│  │ ─────────────────────────────────────────────────────── │   │
│  │ Model: Haiku  •  Role: Custom  •  Status: Inactive     │   │
│  │ Projects: 0  •  Tasks: 0                               │   │
│  │ [View Workspace] [Edit Config] [Activate] [Delete]     │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Data Model

```sql
-- Unified personas table
CREATE TABLE personas (
    id TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    role TEXT NOT NULL, -- 'subagent', 'board_member', 'custom'
    model TEXT NOT NULL, -- 'dolphin', 'haiku', 'glm-5'
    avatar TEXT,
    status TEXT DEFAULT 'active', -- 'active', 'inactive', 'archived'
    inspiration TEXT, -- For board members: who they're modeled after
    system_prompt TEXT, -- Custom instructions
    workspace_path TEXT, -- Path to their AGENTS.md/TOOLS.md
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    deactivated_at DATETIME
);

-- Persona metrics (aggregated from activity logs)
CREATE TABLE persona_metrics (
    id INTEGER PRIMARY KEY,
    persona_id TEXT NOT NULL,
    projects_count INTEGER DEFAULT 0,
    tasks_completed INTEGER DEFAULT 0,
    tasks_failed INTEGER DEFAULT 0,
    tokens_used INTEGER DEFAULT 0,
    sessions_count INTEGER DEFAULT 0,
    decisions_count INTEGER DEFAULT 0, -- For board members
    success_rate REAL DEFAULT 0,
    last_active_at DATETIME,
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);

-- Persona workspace files (cached content)
CREATE TABLE persona_workspaces (
    id INTEGER PRIMARY KEY,
    persona_id TEXT NOT NULL,
    file_name TEXT NOT NULL, -- 'AGENTS.md', 'TOOLS.md', etc.
    content TEXT,
    last_synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (persona_id) REFERENCES personas(id)
);
```

### Livewire Components

```php
// app/Livewire/HR/PersonasIndex.php
class PersonasIndex extends Component
{
    public array $personas = [];
    public string $filter = 'all'; // all, active, subagents, board, custom
    public bool $showEditModal = false;
    public bool $showCreateModal = false;
    public ?string $selectedPersonaId = null;
    
    // Create/Edit form
    public string $personaName = '';
    public string $personaRole = 'custom';
    public string $personaModel = 'haiku';
    public string $personaAvatar = '🤖';
    public string $personaInspiration = '';
    public string $personaSystemPrompt = '';
    
    public function mount(): void { /* load personas */ }
    public function filterBy(string $filter): void { /* update filter */ }
    public function create(): void { /* show create modal */ }
    public function edit(string $id): void { /* show edit modal */ }
    public function save(): void { /* create or update */ }
    public function deactivate(string $id): void { /* soft delete */ }
    public function viewWorkspace(string $id): void { /* show workspace files */ }
}

// app/Livewire/HR/PersonaWorkspace.php
class PersonaWorkspace extends Component
{
    public string $personaId;
    public array $files = [];
    
    public function mount(string $personaId): void { /* load files */ }
    public function sync(): void { /* refresh from filesystem */ }
}
```

### Views

```blade
{{-- resources/views/livewire/hr/personas-index.blade.php --}}
<div class="personas-index">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">HR — Personas</h2>
        <button wire:click="create" 
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            + New Persona
        </button>
    </div>
    
    {{-- Filter tabs --}}
    <div class="flex gap-2 mb-4">
        @foreach(['all', 'active', 'subagents', 'board', 'custom'] as $f)
        <button wire:click="filterBy('{{ $f }}')"
                class="px-3 py-1 rounded {{ $filter === $f ? 'bg-purple-600 text-white' : 'bg-[#2a2a40] text-gray-400' }}">
            {{ ucfirst($f) }}
        </button>
        @endforeach
    </div>
    
    {{-- Persona cards --}}
    <div class="space-y-4">
        @foreach($personas as $persona)
        <div class="persona-card bg-[#1a1a2e] rounded-xl p-4 border border-[#2a2a40]">
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ $persona['avatar'] }}</span>
                        <span class="text-lg font-semibold text-white">{{ $persona['name'] }}</span>
                        <span class="px-2 py-0.5 rounded text-xs {{ $persona['status'] === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                            {{ $persona['status'] }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-400 mt-1">
                        {{ $persona['role'] }} • {{ $persona['model'] }}
                    </div>
                    @if($persona['inspiration'])
                    <div class="text-xs text-gray-500 mt-1">
                        Inspired by: {{ $persona['inspiration'] }}
                    </div>
                    @endif
                </div>
                <div class="text-right text-sm text-gray-400">
                    @if($persona['role'] === 'subagent')
                    <div>Projects: {{ $persona['metrics']['projects_count'] }}</div>
                    <div>Tasks: {{ $persona['metrics']['tasks_completed'] }}</div>
                    <div>Success: {{ $persona['metrics']['success_rate'] }}%</div>
                    @elseif($persona['role'] === 'board_member')
                    <div>Sessions: {{ $persona['metrics']['sessions_count'] }}</div>
                    <div>Decisions: {{ $persona['metrics']['decisions_count'] }}</div>
                    @endif
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button wire:click="viewWorkspace('{{ $persona['id'] }}')" 
                        class="px-3 py-1 text-sm bg-[#2a2a40] text-gray-300 rounded hover:bg-[#3a3a50]">
                    View Workspace
                </button>
                <button wire:click="edit('{{ $persona['id'] }}')" 
                        class="px-3 py-1 text-sm bg-[#2a2a40] text-gray-300 rounded hover:bg-[#3a3a50]">
                    Edit Config
                </button>
            </div>
        </div>
        @endforeach
    </div>
    
    {{-- Create/Edit Modal --}}
    @if($showCreateModal || $showEditModal)
    <div class="modal-overlay" wire:click="$set('showCreateModal', false)">
        <div class="modal-content" wire:click.stop>
            <h3 class="text-lg font-semibold text-white mb-4">
                {{ $showCreateModal ? 'Create New Persona' : 'Edit Persona' }}
            </h3>
            <form wire:submit="save">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Name</label>
                        <input type="text" wire:model="personaName" 
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Role</label>
                        <select wire:model="personaRole" 
                                class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2 text-white">
                            <option value="subagent">Subagent</option>
                            <option value="board_member">Board Member</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Model</label>
                        <select wire:model="personaModel" 
                                class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2 text-white">
                            <option value="dolphin">Dolphin 3.0 (Local)</option>
                            <option value="haiku">Claude Haiku (OpenRouter)</option>
                            <option value="glm-5">GLM-5 (OpenRouter)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Avatar Emoji</label>
                        <input type="text" wire:model="personaAvatar" 
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2 text-white">
                    </div>
                    @if($personaRole === 'board_member')
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Inspiration</label>
                        <input type="text" wire:model="personaInspiration" 
                               placeholder="e.g., Steve Jobs - visionary, product-obsessed"
                               class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2 text-white">
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">System Prompt</label>
                        <textarea wire:model="personaSystemPrompt" rows="4"
                                  class="w-full bg-[#12121f] border border-[#2a2a40] rounded-lg p-2 text-white"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" wire:click="$set('showCreateModal', false)" 
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        {{ $showCreateModal ? 'Create' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
```

### Routes

```php
Route::get('/hr', PersonasIndex::class)->name('hr');
Route::get('/hr/{id}/workspace', PersonaWorkspace::class)->name('hr.workspace');
Route::get('/hr/{id}/history', PersonaHistory::class)->name('hr.history');
```

---

## Implementation Plan for HR Module

### Phase 6: HR Module (Backend + Frontend)
**Assigned:** Dave, Maya, Alex

```
Tasks:
1. [Dave] Create migrations for personas, persona_metrics, persona_workspaces
2. [Dave] Create Eloquent models with relationships
3. [Alex] Create API endpoints for CRUD operations
4. [Maya] Create PersonasIndex Livewire component
5. [Maya] Create PersonaWorkspace component
6. [Maya] Build persona cards with metrics
7. [Maya] Build create/edit modal form
8. [Alex] Create PersonaMetricsService (aggregate from activity logs)
9. [Dave] Create PersonaSyncCommand (sync workspaces from filesystem)
```

### Timeline

| Phase | Duration | Agents |
|-------|----------|--------|
| Phase 6: HR Module | 4 hours | Dave, Maya, Alex |

**Total with HR:** ~20 hours work, ~8-10 hours calendar time

---

## Updated Database Schema Summary

```sql
-- Projects feature
projects (enhanced)
├── description TEXT
├── repo_url TEXT
├── health TEXT
├── progress INTEGER
└── archived_at DATETIME

requirements
├── project_id → projects
├── title, description
├── priority, status
└── approved_at

project_assignments
├── project_id → projects
├── agent_id
└── role

-- Executive Board feature
board_members
├── role, name, avatar
├── model
└── system_prompt

board_sessions
├── question, context
├── status
└── final_decision

board_responses
├── session_id → board_sessions
├── member_id → board_members
├── response
└── response_order

-- HR Module (NEW)
personas
├── name, role, model, avatar
├── status, inspiration
├── system_prompt
└── workspace_path

persona_metrics
├── persona_id → personas
├── projects_count, tasks_completed
├── tokens_used, success_rate
└── last_active_at

persona_workspaces
├── persona_id → personas
├── file_name, content
└── last_synced_at
```

---

## Executive Board Personas (Confirmed)

| Role | Inspiration | Persona Name | Model |
|------|-------------|--------------|-------|
| **CEO** | Steve Jobs | **Steven** | GLM-5 |
| **COO** | Gwynne Shotwell (SpaceX) | **Gwynne** | Haiku |
| **CTO** | Werner Vogels (Amazon) | **Werner** | Dolphin |
| **CFO** | Warren Buffet | **Warren** | GLM-5 |
| **CMO** | Bozoma Saint John (Netflix) | **Bozoma** | Haiku |
| **CPO** | Fidji Simo (Instacart) | **Fidji** | GLM-5 |

---

**Ready for implementation.** All three features (Projects Dashboard, Executive Board, HR Module) are specced and ready for subagent assignment.
