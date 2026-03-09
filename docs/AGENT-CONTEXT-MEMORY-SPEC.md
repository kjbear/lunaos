# Agent Context & Memory System Specification

**Author:** Architect Agent  
**Date:** March 9, 2026  
**Priority:** 🔴 P0 - Core Capability Gap  
**Status:** Draft for Review

---

## Executive Summary

**Problem:** LunaOS chat agents have zero context/memory between sessions. When Kyle asked Steven about "IHSSP", Steven asked "What does IHSSP stand for?" despite extensive IHSSP documentation existing in `/workspace/projects/`.

**Root Cause:** ChatService builds prompts with only:
1. TeamMember persona + instructions
2. Skills loaded from capability mapping
3. Session-only conversation history (sliding window)

**Missing:**
- Project context (IHSSP, SPA, LunaOS docs)
- Cross-session memory (decisions, learnings)
- Workspace awareness (what files exist, what's been done)

**Recommendation:** Hybrid approach with **automatic project context injection** + **on-demand memory retrieval** + **explicit context attachment**.

---

## User Stories

### US-1: "What is IHSSP?" (Kyle's Pain Point) ✅ SOLVED
**As Kyle,** when I ask an agent about a project (IHSSP, SPA, LunaOS),  
**I want the agent to already know** what the project is, its key docs, and recent status,  
**So that** I don't have to re-explain context every conversation.

**Acceptance Criteria:**
- [x] Agent recognizes "IHSSP", "SPA", "LunaOS" as project references
- [x] Agent has access to project summary (from projects/SUMMARY.md)
- [x] Agent can reference key documentation without Kyle pasting links
- [x] Response time remains under 2 seconds

### US-2: "What did Dave decide about the API?"
**As Kyle,** when I ask about a prior decision,  
**I want the agent to recall** relevant conversations and decisions from previous sessions,  
**So that** I don't have to search through chat history myself.

**Acceptance Criteria:**
- [ ] Cross-session memory search returns relevant context
- [ ] Agent cites sources ("In the March 7 chat, Dave recommended...")
- [ ] Memory respects project/session boundaries

### US-3: "Attach the API spec to this chat"
**As Kyle,** when I need deep focus on a specific document,  
**I want to explicitly attach** that document to the current chat,  
**So that** the agent has full context without injection noise.

**Acceptance Criteria:**
- [ ] UI allows attaching files/docs to chat session
- [ ] Attached context is prioritized over auto-injected context
- [ ] Clear visual indicator shows what context is active

### US-4: "Continue from where we left off"
**As Kyle,** when I return to a previous chat session,  
**I want the agent to resume** with full context of what we discussed,  
**So that** the conversation feels continuous.

**Acceptance Criteria:**
- [ ] Session history loads automatically (already works)
- [ ] Project context from previous session is available
- [ ] Agent can summarize prior session on request

### US-5: "What changed since I last chatted with Steven?"
**As Kyle,** when I start a new chat,  
**I want to see** what's new in the workspace since my last session,  
**So that** I'm not rehashing old ground.

**Acceptance Criteria:**
- [ ] Changes summary displayed (new commits, new docs, task updates)
- [ ] Optional: digest view before chat starts
- [ ] Agent can proactively highlight relevant changes

---

## Architecture Decision: Hybrid Context System

### Option Analysis

| Approach | Pros | Cons | Verdict |
|----------|------|------|---------|
| **A: RAG-style retrieval** | Automatic, scales well, semantic | Requires indexing, latency | ✅ Part of solution |
| **B: Explicit attachment** | User control, precise, no noise | Manual effort, not automatic | ✅ Part of solution |
| **C: Agent reads files** | On-demand, flexible | No proactive context, slow | ✅ Part of solution |
| **D: Hybrid** | Best of all worlds | More complex to build | 🎯 **RECOMMENDED** |

### Chosen Architecture: Hybrid (D)

```
┌─────────────────────────────────────────────────────────────────┐
│                       ChatService.buildPrompt()                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │ System Prompt│  │ Agent Skills │  │   Context Layers     │   │
│  │ (TeamMember) │  │ (Capability) │  │                      │   │
│  └──────────────┘  └──────────────┘  │  1. Project Context   │   │
│         │                 │          │     (auto-injected)   │   │
│         │                 │          │                      │   │
│         ▼                 ▼          │  2. Memory Context    │   │
│  ┌──────────────────────────────┐   │     (on-demand RAG)   │   │
│  │        Agent Identity        │   │                      │   │
│  │  "You are Jordan, PM for SPA"│   │  3. Session Context   │   │
│  └──────────────────────────────┘   │     (conversation)    │   │
│                                      │                      │   │
│                                      │  4. Attached Context  │   │
│                                      │     (explicit)        │   │
│                                      └──────────────────────┘   │
│                                                 │                │
│                                                 ▼                │
│                         ┌──────────────────────────────────┐    │
│                         │      Conversation History        │    │
│                         │     (Sliding Window + Tokens)    │    │
│                         └──────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Technical Design

### 1. Project Context Injection (Auto)

**Goal:** Automatically inject project summaries into agent prompts.

**Implementation:**

```php
// app/Services/ContextService.php

class ContextService
{
    protected string $projectsPath;
    protected array $projectCache = [];
    
    public function __construct()
    {
        $this->projectsPath = base_path('../projects');
    }
    
    /**
     * Get project context for a message.
     * Detects project mentions and injects relevant context.
     */
    public function getProjectContext(string $message, array $options = []): string
    {
        $context = [];
        $detectedProjects = $this->detectProjectReferences($message);
        
        foreach ($detectedProjects as $project) {
            $context[] = $this->loadProjectSummary($project);
        }
        
        // If no specific project detected, inject all active projects overview
        if (empty($detectedProjects) && ($options['includeAllActive'] ?? true)) {
            $context[] = $this->loadActiveProjectsOverview();
        }
        
        return implode("\n\n---\n\n", array_filter($context));
    }
    
    /**
     * Detect project references in a message.
     */
    protected function detectProjectReferences(string $message): array
    {
        $projects = [];
        $patterns = [
            '/\bIHSSP\b/i' => 'IHSSP',
            '/\bSPA\b/i' => 'SPA',
            '/\bLunaOS\b/i' => 'LunaOS',
            '/\bluna[- ]?os\b/i' => 'LunaOS',
            '/in[- ]?home service/i' => 'IHSSP',
            '/status[- ]page[- ]aggregator/i' => 'SPA',
            '/dashboard platform/i' => 'LunaOS',
        ];
        
        foreach ($patterns as $pattern => $project) {
            if (preg_match($pattern, $message)) {
                $projects[] = $project;
            }
        }
        
        return array_unique($projects);
    }
    
    /**
     * Load project summary from docs.
     */
    protected function loadProjectSummary(string $projectName): string
    {
        if (isset($this->projectCache[$projectName])) {
            return $this->projectCache[$projectName];
        }
        
        $summaryPath = "{$this->projectsPath}/{$projectName}/SUMMARY.md";
        $readmePath = "{$this->projectsPath}/{$projectName}/README.md";
        
        $content = '';
        
        if (file_exists($summaryPath)) {
            $content = file_get_contents($summaryPath);
        } elseif (file_exists($readmePath)) {
            $content = file_get_contents($readmePath);
        }
        
        // Cache for session
        $this->projectCache[$projectName] = $content;
        
        return $content;
    }
    
    /**
     * Load overview of all active projects.
     */
    protected function loadActiveProjectsOverview(): string
    {
        $overviewPath = "{$this->projectsPath}/README.md";
        
        if (file_exists($overviewPath)) {
            return file_get_contents($overviewPath);
        }
        
        return '';
    }
}
```

**Integration with ChatService:**

```php
// app/Services/ChatService.php

protected function buildPrompt(ChatSession $session, TeamMember $member, string $newMessage): array
{
    $messages = [];

    // 1. System prompt with member identity
    $systemPrompt = $this->buildSystemPrompt($member);
    $messages[] = ['role' => 'system', 'content' => $systemPrompt];

    // 2. Load skills
    $skillsContent = $this->loadSkillsForMember($member);
    if ($skillsContent) {
        $messages[] = ['role' => 'system', 'content' => "## Relevant Skills:\n\n" . $skillsContent];
    }

    // 🆕 3. Inject project context
    $projectContext = $this->contextService->getProjectContext($newMessage);
    if ($projectContext) {
        $messages[] = ['role' => 'system', 'content' => "## Project Context:\n\n" . $projectContext];
    }

    // 4. Conversation history (existing)
    $context = $session->context ?? [];
    foreach ($context as $contextMsg) {
        if (($contextMsg['role'] ?? '') !== 'system') {
            $messages[] = [
                'role' => $contextMsg['role'],
                'content' => $contextMsg['content'],
            ];
        }
    }

    // 5. New message
    $messages[] = ['role' => 'user', 'content' => $newMessage];

    return $messages;
}
```

### 2. Memory Context (On-Demand RAG)

**Goal:** Enable agents to search and retrieve relevant memories.

**Architecture Decision (March 2026):**
- **Phase 1**: NO vector DB needed - file-based context injection works immediately
- **Phase 2+**: If semantic search is needed, use **QMD via HTTP** (not LanceDB)

**Why QMD over LanceDB for PHP/Laravel:**

| Criteria | LanceDB | QMD | Decision |
|----------|---------|-----|----------|
| PHP SDK | ❌ None | ❌ None | Tie |
| HTTP API | ❌ No native | ✅ `--http` mode | **QMD wins** |
| Integration | Python bridge required | Direct HTTP calls | **QMD simpler** |
| Search type | Vector-only | Hybrid (BM25+Vector+Rerank) | **QMD better** |
| Embeddings | Separate API | Bundled local models | **QMD simpler** |

**QMD Integration Pattern:**

```bash
# Run QMD as HTTP server (systemd/supervisor)
qmd mcp --http --port 8181
```

```php
// app/Services/MemoryService.php

class MemoryService
{
    protected string $qmdUrl;
    
    public function __construct()
    {
        $this->qmdUrl = config('services.qmd.url', 'http://localhost:8181');
    }
    
    /**
     * Search memories via QMD HTTP API.
     */
    public function search(string $query, int $limit = 5): array
    {
        $response = Http::post("{$this->qmdUrl}/search", [
            'query' => $query,
            'limit' => $limit,
            'collection' => 'lunaos-memory',
        ]);
        
        return $response->json('results', []);
    }
    
    /**
     * Store a new memory.
     */
    public function store(string $content, array $metadata = []): void
    {
        Http::post("{$this->qmdUrl}/add", [
            'content' => $content,
            'metadata' => $metadata,
            'collection' => 'lunaos-memory',
        ]);
    }
    
    /**
     * Get memories for a specific project.
     */
    public function getProjectMemories(string $projectName): array
    {
        return $this->search("project:{$projectName}", 10);
    }
}
```

**Agent Skill for Memory:**

```markdown
# skills/memory/SKILL.md

---
name: memory
description: Memory search and retrieval capabilities
---

## Memory Skills

### memory_search
Search the memory database for relevant context.

**Usage:**
```
memory_search(query: "IHSSP API decisions", limit: 5)
```

Returns: Array of memory chunks with relevance scores.

### memory_store
Store a new memory for future retrieval.

**Usage:**
```
memory_store(content: "Decided on PostgreSQL RLS for multi-tenancy", metadata: {project: "IHSSP"})
```

### memory_get_project
Get all memories tagged for a specific project.

**Usage:**
```
memory_get_project(project: "SPA")
```
```

### 3. Session Context (Already Exists)

**Goal:** Maintain conversation history within a chat session.

**Current Implementation:** ✅ Already working via `ChatSession.context` JSON column with sliding window.

**Enhancement:** Store summary of key decisions at session end.

```php
// New migration: add session summary

Schema::table('chat_sessions', function (Blueprint $table) {
    $table->json('summary')->nullable()->after('context');
    $table->json('key_decisions')->nullable()->after('summary');
    $table->json('topics_discussed')->nullable()->after('key_decisions');
    $table->foreignUuid('primary_project_id')->nullable()->after('topics_discussed');
});
```

### 4. Attached Context (Explicit)

**Goal:** Allow users to attach specific documents to a chat.

**Database Schema:**

```php
// New migration: chat_context_attachments

Schema::create('chat_context_attachments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('chat_session_id')->constrained('chat_sessions')->cascadeOnDelete();
    $table->enum('type', ['file', 'url', 'project', 'memory']);
    $table->string('source'); // file path, URL, project name, memory ID
    $table->text('content')->nullable(); // cached content
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['chat_session_id', 'type']);
});
```

**API Endpoints:**

```
POST /api/chat/{session}/attach
{
    "type": "file",
    "source": "/workspace/projects/IHSSP/app-requirements.md"
}

POST /api/chat/{session}/attach
{
    "type": "project",
    "source": "SPA"
}

POST /api/chat/{session}/attach
{
    "type": "memory",
    "source": "mem_abc123"
}
```

**UI Component:**

```html
<!-- Context Panel in Chat UI -->
<div id="context-panel" class="hidden">
    <h3>Attached Context</h3>
    
    <div class="context-items">
        <!-- Dynamically populated -->
    </div>
    
    <div class="attach-controls">
        <button @click="attachFile">📎 Attach File</button>
        <button @click="attachProject">📁 Attach Project</button>
        <button @click="searchMemory">🔍 Search Memory</button>
    </div>
    
    <div class="token-counter">
        Context: {{ contextTokens }} / {{ maxTokens }} tokens
    </div>
</div>
```

---

## Memory Storage Architecture

### Question: Where to store memories?

**Option A: Per-Agent Memory**
- Each agent (Dave, Steven, Jordan) has their own memory
- Pros: Isolated, specialized
- Cons: No shared knowledge, fragmented context

**Option B: Per-Project Memory**
- Memories tagged by project (IHSSP, SPA, LunaOS)
- Pros: Project-focused, easy filtering
- Cons: Cross-project decisions harder to find

**Option C: Global Memory**
- Single memory pool with metadata filtering
- Pros: Cross-project context, unified search
- Cons: Noise, requires good tagging

**Recommendation: Hybrid (B + C)**

```php
// Memory metadata structure
{
    "id": "mem_abc123",
    "content": "Decided on PostgreSQL RLS for IHSSP multi-tenancy",
    "embedding": [...],
    "metadata": {
        "project": "IHSSP",           // Primary project tag
        "projects": ["IHSSP", "SPA"], // Cross-project references
        "type": "decision",           // decision, learning, note, architecture
        "agent": "Dave",              // Who made it (optional)
        "session_id": "sess_xyz",     // Source session (optional)
        "importance": 0.85,           // Relevance weight
        "created_at": "2026-03-09T12:00:00Z"
    }
}
```

**Storage Location:**
- Continue using QMD at `/memory/qmd/` (or LanceDB at `/memory/lancedb/` if already deployed)
- Add metadata indexes for project, type, agent

**Memory Types:**

| Type | Description | Example |
|------|-------------|---------|
| `decision` | Architecture or design choice | "Use RLS for multi-tenancy" |
| `learning` | Lesson learned, gotcha | "Cascade deletes need explicit policy" |
| `note` | General knowledge | "IHSSP = In-Home Services SaaS Platform" |
| `architecture` | System design reference | "SPA uses Go collector + Laravel API" |
| `blocker` | Issue resolved or pending | "Stripe webhooks need retry logic" |

---

## Integration Points

### 1. ChatService Integration

```php
// app/Services/ChatService.php

class ChatService
{
    protected ContextService $contextService;
    protected MemoryService $memoryService;
    
    public function __construct(
        ContextService $contextService,
        MemoryService $memoryService
    ) {
        $this->contextService = $contextService;
        $this->memoryService = $memoryService;
        // ... existing constructor
    }
    
    protected function buildPrompt(ChatSession $session, TeamMember $member, string $newMessage): array
    {
        $messages = [];
        $totalTokens = 0;
        $maxContextTokens = config('chat.max_context_tokens', 8000);

        // 1. System prompt (identity)
        $systemPrompt = $this->buildSystemPrompt($member);
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        $totalTokens += $this->estimateTokens($systemPrompt);

        // 2. Skills
        $skillsContent = $this->loadSkillsForMember($member);
        if ($skillsContent) {
            $skillsSection = "## Relevant Skills:\n\n" . $skillsContent;
            $messages[] = ['role' => 'system', 'content' => $skillsSection];
            $totalTokens += $this->estimateTokens($skillsSection);
        }

        // 🆕 3. Project context (auto-inject)
        $projectContext = $this->contextService->getProjectContext($newMessage);
        if ($projectContext) {
            $projectSection = "## Project Context:\n\n" . $this->truncateForTokens($projectContext, 2000);
            $messages[] = ['role' => 'system', 'content' => $projectSection];
            $totalTokens += $this->estimateTokens($projectSection);
        }

        // 🆕 4. Attached context (explicit)
        $attachedContext = $this->loadAttachedContext($session);
        if ($attachedContext) {
            $attachedSection = "## Attached Context:\n\n" . $attachedContext;
            $messages[] = ['role' => 'system', 'content' => $attachedSection];
            $totalTokens += $this->estimateTokens($attachedSection);
        }

        // 5. Conversation history (sliding window)
        $remainingTokens = $maxContextTokens - $totalTokens - 1000; // Reserve for response
        $context = $session->context ?? [];
        foreach ($context as $contextMsg) {
            if (($contextMsg['role'] ?? '') !== 'system') {
                $msgTokens = $contextMsg['tokens'] ?? $this->estimateTokens($contextMsg['content']);
                if ($totalTokens + $msgTokens <= $remainingTokens) {
                    $messages[] = [
                        'role' => $contextMsg['role'],
                        'content' => $contextMsg['content'],
                    ];
                    $totalTokens += $msgTokens;
                }
            }
        }

        // 6. New message
        $messages[] = ['role' => 'user', 'content' => $newMessage];

        return $messages;
    }
    
    protected function loadAttachedContext(ChatSession $session): ?string
    {
        $attachments = $session->attachments()->get();
        
        if ($attachments->isEmpty()) {
            return null;
        }
        
        $content = [];
        foreach ($attachments as $attachment) {
            $content[] = "### {$attachment->type}: {$attachment->source}\n\n{$attachment->content}";
        }
        
        return implode("\n\n---\n\n", $content);
    }
}
```

### 2. SkillService Integration

```php
// New skill: workspace-context

// skills/workspace-context/SKILL.md

---
name: workspace-context
description: Browse workspace files and project documentation
---

## Workspace Tools

### workspace_list_projects
List all active projects with brief summaries.

### workspace_read_file
Read a file from the workspace.

**Usage:**
```
workspace_read_file(path: "/workspace/projects/IHSSP/app-requirements.md")
```

### workspace_search
Search project documentation.

**Usage:**
```
workspace_search(query: "multi-tenant architecture", project: "IHSSP")
```

### memory_search
Search long-term memory for relevant context.

**Usage:**
```
memory_search(query: "API design decisions", limit: 5)
```

### memory_store
Store a decision or learning for future reference.

**Usage:**
```
memory_store(
    content: "Decided to use Row-Level Security for tenant isolation",
    metadata: {
        project: "IHSSP",
        type: "decision"
    }
)
```
```

### 3. Event Listeners

```php
// app/Listeners/StoreChatDecision.php

class StoreChatDecision
{
    public function handle(AiResponseComplete $event): void
    {
        $session = $event->session;
        $response = $event->response;
        
        // Detect decisions in response
        $decisions = $this->extractDecisions($response);
        
        foreach ($decisions as $decision) {
            // Store in memory
            app(MemoryService::class)->store(
                $decision['content'],
                [
                    'project' => $session->primary_project_id,
                    'type' => 'decision',
                    'session_id' => $session->id,
                    'agent' => $session->teamMember->name,
                ]
            );
        }
    }
    
    protected function extractDecisions(string $response): array
    {
        // Simple pattern matching for now
        // Could be enhanced with AI extraction
        $patterns = [
            '/Decision:?\s*(.+)/i',
            '/We (?:will|should|decided to) (.+)/i',
            '/Going with (.+) approach/i',
        ];
        
        // ... extraction logic
    }
}
```

---

## UI Changes

### 1. Chat Interface Enhancements

**Context Panel (Right Sidebar):**

```
┌─────────────────────────────────────────────────────────────┐
│  [Chat Window]                    │ [Context Panel]         │
│                                   │                         │
│  Jordan (PM - SPA):               │ 📁 Attached Context     │
│  ┌───────────────────────────┐    │ ├─ SPA-ARCHITECTURE.md  │
│  │ Let me check the API...   │    │ ├─ SPA-DECISIONS.md     │
│  └───────────────────────────┘    │ └─ + Add Context        │
│                                   │                         │
│  Kyle:                            │ 🏗️ Project Summary       │
│  ┌───────────────────────────┐    │ SPA - Status Page       │
│  │ What about auth for SPA?  │    │ Aggregator              │
│  └───────────────────────────┘    │ Phase 1: 8 weeks        │
│                                   │ Status: PLANNED         │
│                                   │                         │
│                                   │ 🧠 Memory Hits          │
│                                   │ ├─ "Use OTEL-first..."  │
│                                   │ └─ "PostgreSQL RLS..."  │
│                                   │                         │
│                                   │ 📊 Token Usage          │
│                                   │ Context: 2,450 / 8,000  │
│                                   │ Conversation: 1,200     │
│                                   │ Remaining: 4,350        │
└─────────────────────────────────────────────────────────────┘
```

**Memory Indicator:**

```
┌─────────────────────────────────────┐
│ 🧠 Memory Active                    │
│ 3 memories retrieved for this chat  │
│ [View] [Clear] [Add Memory]         │
└─────────────────────────────────────┘
```

### 2. Project Summary Generation

Each project needs a `SUMMARY.md` file:

```markdown
# SPA - Status Page Aggregator

## Quick Facts
- **Status:** 📋 PLANNED
- **Phase:** Phase 1 MVP (8 weeks, ~100 pts)
- **Repo:** https://github.com/kjbear/spa.git
- **Domain:** onewatch.cloud

## Tech Stack
- Laravel 12 + Go collector
- PostgreSQL with RLS
- HTMX + Tailwind CSS
- OpenTelemetry-first

## Current Focus
- Creating GitHub repo
- Setting up CI/CD
- Sprint 1 planning (infrastructure)

## Key Decisions
1. SQS-based dynamic vendor management
2. OTEL-first architecture
3. HTMX over React for simplicity

## Related Docs
- `SPA-ARCHITECTURE-REVISED.md` - Full architecture
- `SPA-DECISIONS.md` - 9 major decisions
- `SPA-USER-STORIES.md` - User stories
```

---

## Phased Approach

### Phase 1: MVP (Week 1) - 🟢 Start Here

**Goal:** Solve Kyle's immediate pain point - agents don't know projects.

**Deliverables:**
- ✅ `ContextService` with project detection
- ✅ Auto-inject project summaries into prompts
- ✅ Generate `SUMMARY.md` for each project (IHSSP, SPA, LunaOS)
- ✅ Integration with `ChatService.buildPrompt()`

**Effort:** S (2-3 days)

**Who:** Dave (backend) + Luna (spec generation)

**Dependencies:** None

---

### Phase 2: Memory Integration (Week 2)

**Goal:** Enable cross-session memory search.

**Deliverables:**
- ✅ `MemoryService` with QMD HTTP integration
- ✅ `memory_search` skill for agents
- ✅ `memory_store` skill for agents
- ✅ Project metadata tagging
- ✅ QMD running as HTTP service

**Effort:** M (3-4 days)

**Who:** Dave (backend) + ops for QMD setup

**Dependencies:** Phase 1, QMD HTTP service deployed

**Note:** LanceDB was considered but rejected because:
1. No PHP SDK (would require Python/Node bridge)
2. QMD provides hybrid search (BM25 + Vector) vs LanceDB's vector-only
3. QMD has built-in HTTP API for direct PHP integration
4. QMD bundles local embedding models (no separate API)

---

### Phase 3: Explicit Attachments (Week 3)

**Goal:** Allow manual context attachment.

**Deliverables:**
- ✅ `chat_context_attachments` table + migration
- ✅ API endpoints (attach/detach/list)
- ✅ UI context panel (right sidebar)
- ✅ Token counter for context budget

**Effort:** M (3-4 days)

**Who:** Dave (backend) + Maya (frontend)

**Dependencies:** Phase 1, API routes

---

### Phase 4: Smart Summaries (Week 4)

**Goal:** Auto-summarize sessions for memory storage.

**Deliverables:**
- ✅ Session summary generation (AI-powered)
- ✅ Key decision extraction
- ✅ Topics discussed tracking
- ✅ Session-to-memory pipeline

**Effort:** M (3-4 days)

**Who:** Dave (backend)

**Dependencies:** Phase 2

---

### Phase 5: Proactive Context (Future)

**Goal:** Proactively suggest context changes.

**Deliverables:**
- 🔮 "What's changed since last chat" digest
- 🔮 Context recommendations ("You might want to attach...")
- 🔮 Auto-context for recurring topics

**Effort:** L (1-2 weeks)

**Dependencies:** Phases 1-4

---

## Estimated Effort Summary

| Phase | Effort | Owner | Timeline | Priority |
|-------|--------|-------|----------|----------|
| Phase 1: MVP | S (2-3 days) | Dave + Luna | Week 1 | 🔴 P0 |
| Phase 2: Memory | M (3-4 days) | Dave + Chen | Week 2 | 🟠 P1 |
| Phase 3: Attachments | M (3-4 days) | Dave + Maya | Week 3 | 🟠 P1 |
| Phase 4: Summaries | M (3-4 days) | Dave | Week 4 | 🟡 P2 |
| Phase 5: Proactive | L (1-2 weeks) | Team | Future | 🔵 P3 |

**Total MVP (Phases 1-3):** ~2 weeks

---

## Database Changes

### New Tables

```sql
-- chat_context_attachments
CREATE TABLE chat_context_attachments (
    id UUID PRIMARY KEY,
    chat_session_id UUID REFERENCES chat_sessions(id) ON DELETE CASCADE,
    type VARCHAR(20), -- 'file', 'url', 'project', 'memory'
    source VARCHAR(500),
    content TEXT,
    metadata JSONB,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_attachments_session ON chat_context_attachments(chat_session_id);
CREATE INDEX idx_attachments_type ON chat_context_attachments(type);
```

### Modified Tables

```sql
-- chat_sessions (add columns)
ALTER TABLE chat_sessions ADD COLUMN summary TEXT;
ALTER TABLE chat_sessions ADD COLUMN key_decisions JSONB;
ALTER TABLE chat_sessions ADD COLUMN topics_discussed JSONB;
ALTER TABLE chat_sessions ADD COLUMN primary_project_id UUID REFERENCES projects(id);

-- memories (if creating new table, not using QMD vector store)
CREATE TABLE memories (
    id UUID PRIMARY KEY,
    content TEXT NOT NULL,
    embedding VECTOR(1536), -- OpenAI embedding size
    metadata JSONB,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE INDEX idx_memories_project ON memories((metadata->>'project'));
CREATE INDEX idx_memories_type ON memories((metadata->>'type'));
```

---

## API Endpoints

```
# Context Attachment API
POST   /api/chat/{session}/attach          # Attach context
DELETE /api/chat/{session}/attach/{id}     # Detach context
GET    /api/chat/{session}/attachments     # List attachments

# Memory API
POST   /api/memory/search                  # Search memories
POST   /api/memory/store                   # Store memory
GET    /api/memory/project/{name}          # Get project memories
DELETE /api/memory/{id}                    # Delete memory

# Project Context API
GET    /api/projects                       # List all projects
GET    /api/projects/{name}/summary        # Get project summary
POST   /api/projects/{name}/summary        # Update/refresh summary
```

---

## Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Context window overflow | Models get confused, lose coherence | Token counting + truncation + priority scoring |
| Stale project summaries | Agents reference outdated info | Auto-refresh summaries on git push + manual trigger |
| Memory search noise | Irrelevant memories retrieved | Better embedding model + metadata filtering |
| Attachment abuse | Users attach too much, break context | Token budget UI + warnings |
| Memory search performance | Slow searches on large sets | QMD index optimization + caching |

---

## Success Metrics

### Phase 1 Success
- [ ] "What is IHSSP?" returns accurate summary without Kyle pasting links
- [ ] Agent references project docs in responses
- [ ] No increase in response latency > 500ms

### Phase 2 Success
- [ ] Memory search returns relevant results for "What did we decide about API?"
- [ ] Agents store decisions with `memory_store` skill
- [ ] Memory hits displayed in context panel

### Phase 3 Success
- [ ] Users can attach documents via UI
- [ ] Token counter shows context budget
- [ ] Attached context prioritized over auto-injected

### Overall Success
- [ ] Zero "What is IHSSP?" moments
- [ ] Session continuity feels natural
- [ ] Context is discoverable and manageable

---

## Implementation Checklist

### Phase 1: MVP ✅ COMPLETE
- [x] Create `ContextService` class
- [x] Implement `detectProjectReferences()` 
- [x] Implement `loadProjectSummary()`
- [x] Create `SUMMARY.md` for IHSSP
- [x] Create `SUMMARY.md` for SPA
- [x] Create `SUMMARY.md` for LunaOS
- [x] Update `ChatService.buildPrompt()` to inject project context
- [x] Add config for max project context tokens
- [x] Test with "What is IHSSP?" prompt
- [x] Test with "Tell me about SPA" prompt

**Implementation Notes:** `docs/PHASE1-IMPLEMENTATION-NOTES.md`  
**Commit:** 872c3b6 (March 9, 2026)  
**Status:** Ready for Phase 2

### Phase 2: Memory
- [ ] Create `MemoryService` class
- [ ] Integrate with QMD HTTP service (or create `memories` table as fallback)
- [ ] Create `memory` skill with search/store actions
- [ ] Add `memory_search` to agent capabilities
- [ ] Test memory search with sample queries
- [ ] Test memory storage with sample decisions

### Phase 3: Attachments
- [ ] Create `chat_context_attachments` migration
- [ ] Create `ChatContextAttachment` model
- [ ] Add `attachments()` relationship to `ChatSession`
- [ ] Create API endpoints (attach/detach/list)
- [ ] Create UI context panel component
- [ ] Add token counter to UI
- [ ] Test attachment flow end-to-end

---

## Appendix A: Project Summary Template

```markdown
# [PROJECT_NAME] - [PROJECT_TITLE]

## Quick Facts
- **Status:** [STATUS EMOJI] [STATUS TEXT]
- **Phase:** [PHASE INFO]
- **Repo:** [REPO URL]
- **Domain:** [DOMAIN IF APPLICABLE]

## Tech Stack
- [TECH 1]
- [TECH 2]
- [TECH 3]

## Current Focus
- [FOCUS AREA 1]
- [FOCUS AREA 2]
- [FOCUS AREA 3]

## Key Decisions
1. [DECISION 1]
2. [DECISION 2]
3. [DECISION 3]

## Related Docs
- `[DOC_1.md]` - [DESCRIPTION]
- `[DOC_2.md]` - [DESCRIPTION]
```

---

## Appendix B: Memory Entry Template

```json
{
  "id": "mem_abc123",
  "content": "[What to remember]",
  "embedding": [...],
  "metadata": {
    "project": "[PROJECT_NAME]",
    "projects": ["[PROJECT_1]", "[PROJECT_2]"],
    "type": "decision|learning|note|architecture|blocker",
    "agent": "[AGENT_NAME]",
    "session_id": "[SESSION_ID]",
    "importance": 0.0 - 1.0,
    "created_at": "[ISO_DATE]"
  }
}
```

---

**Document Version:** 1.0  
**Last Updated:** March 9, 2026  
**Next Review:** After Phase 1 completion