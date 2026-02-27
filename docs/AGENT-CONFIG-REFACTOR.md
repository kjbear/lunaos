# Agent Configuration System - Refactoring Plan

**Created:** February 26, 2026 — 8:13 PM EST  
**Status:** 📋 Ready for Implementation  
**Priority:** High  
**Estimated Time:** 45-60 minutes  
**Author:** Luna 🌙

---

## 📋 Overview

Make agent workers fully configurable via database/HR module instead of hardcoded PHP classes.

**Problem:**
- Agent settings (name, model, prompt) are hardcoded in PHP classes
- Tasks can't specify which repo/project to work on
- No way to configure agents via HR module
- One agent = one project limitation

**Solution:**
- Extend `Agent` model with LLM configuration fields
- Extend `Task` model with `repository_id` field
- Create `Repository` model for multi-project support
- Refactor `AgentWorker` to read config from database
- Add HR UI to manage agent configurations

---

## 🗄️ Database Schema Changes

### 1. Add LLM Config to `agents` table

**Migration:** `2026_02_26_200500_add_llm_config_to_agents_table.php`

```php
Schema::table('agents', function (Blueprint $table) {
    $table->string('provider')->default('ollama'); // ollama, openrouter, anthropic
    $table->string('model')->default('gpt-oss:120b-cloud');
    $table->text('system_prompt')->nullable();
    $table->json('model_settings')->nullable(); // temperature, max_tokens, etc.
    $table->string('avatar')->default('🤖');
});
```

---

### 2. Add Repository Field to `tasks` table

**Migration:** `2026_02_26_200501_add_repository_to_tasks_table.php`

```php
Schema::table('tasks', function (Blueprint $table) {
    $table->uuid('repository_id')->nullable()->after('assigned_to');
    $table->foreign('repository_id')->references('id')->on('repositories');
});
```

---

### 3. Create `repositories` table

**Migration:** `2026_02_26_200502_create_repositories_table.php`

```php
Schema::create('repositories', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name'); // "LunaOS"
    $table->string('path'); // "/workspace/lunaos"
    $table->string('git_url'); // "https://github.com/kjbear/lunaos"
    $table->string('default_branch')->default('main');
    $table->boolean('is_active')->default(true);
    $table->json('settings')->nullable(); // branch_prefix, pr_template, etc.
    $table->timestamps();
});
```

---

## 📦 Model Changes

### Agent.php (Enhanced)

```php
class Agent extends Model
{
    protected $fillable = [
        'name',
        'role',
        'model',
        'provider',
        'system_prompt',
        'model_settings',
        'avatar',
        'status',
        'parent_id',
        'emoji',
    ];

    protected $casts = [
        'model_settings' => 'array',
        'is_active' => 'boolean',
    ];

    // Helper methods
    public function getFullModel(): string
    {
        return "{$this->provider}/{$this->model}";
    }

    public function getModelSettings(): array
    {
        return array_merge([
            'temperature' => 0.7,
            'max_tokens' => 4096,
        ], $this->model_settings ?? []);
    }

    public function scopeWorkers($query)
    {
        return $query->where('role', 'worker');
    }
}
```

---

### Repository.php (New)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Repository extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'path',
        'git_url',
        'default_branch',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function getBranchPrefixAttribute(): string
    {
        return $this->settings['branch_prefix'] ?? 'feature';
    }

    public function getPrTemplateAttribute(): ?string
    {
        return $this->settings['pr_template'] ?? null;
    }
}
```

---

### Task.php (Enhanced)

```php
// Add relationship
public function repository(): BelongsTo
{
    return $this->belongsTo(Repository::class);
}

// Update completeTask to use repository config
public function completeTask(?string $nextStep = null, ?string $nextAgent = null, array $extraData = []): void
{
    // Use repository from task, or default
    $repository = $this->repository ?? Repository::where('is_active', true)->first();
    
    // ... rest of logic
}
```

---

## 🔧 AgentWorker Base Class Refactoring

### Before (Hardcoded):
```php
class DaveAgentWorker extends AgentWorker
{
    protected string $model = 'qwen3-coder:latest';
    
    protected function pollForWork(): ?Task
    {
        return Task::where('assigned_to', 'dave')->first();
    }
}
```

### After (Configurable):
```php
class DaveAgentWorker extends AgentWorker
{
    protected function getAgentConfig(): Agent
    {
        return Agent::where('name', 'dave')->firstOrFail();
    }

    protected function getModel(): string
    {
        return $this->agent->model;
    }

    protected function getProvider(): string
    {
        return $this->agent->provider;
    }

    protected function getSystemPrompt(): string
    {
        return $this->agent->system_prompt ?? $this->getDefaultPrompt();
    }

    protected function pollForWork(): ?Task
    {
        return Task::where('assigned_to', $this->agent->name)
            ->where('step', $this->getCurrentStep())
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();
    }

    protected function generateCodeWithAI(Task $task): array
    {
        $agentClass = $this->getAgentAiClass(); // e.g., DaveCoder
        $ai = new $agentClass;
        
        $response = $ai->prompt(
            $this->buildPrompt($task),
            provider: Lab::from($this->agent->provider),
            model: $this->agent->model,
            timeout: 300,
        );
        
        return $this->parseResponse($response);
    }
}
```

---

## 🎨 New HR UI

### AgentConfig Livewire Component

**Route:** `/hr/agents`

**Features:**
- List all agents (Dave, Sam, Chen, Security, etc.)
- Edit model/provider/prompt per agent
- Test agent with sample task
- View agent activity logs

**Fields:**
- Name (read-only)
- Provider dropdown (Ollama, OpenRouter, Anthropic)
- Model (text input with autocomplete)
- System Prompt (textarea)
- Model Settings (JSON editor for temp, max_tokens, etc.)
- Status toggle (active/inactive)

---

### Repository Management

**Route:** `/hr/repositories`

**Features:**
- Add/edit repositories
- Set default branch
- Configure branch naming prefix
- Set PR template
- Link to multiple agent tasks

---

## 🔧 GitService Enhancement

### Before:
```php
protected function createFeatureBranch(Task $task): string
{
    $branchName = "feature/{$task->id}-" . Str::slug($task->title);
}
```

### After:
```php
protected function createFeatureBranch(Task $task): string
{
    $repo = $task->repository ?? Repository::where('is_active')->first();
    $prefix = $repo->settings['branch_prefix'] ?? 'feature';
    $branchName = "{$prefix}/{$task->id}-" . Str::slug($task->title);
    
    // Checkout repo path
    $this->setRepoPath($repo->path);
    
    return $branchName;
}
```

---

## 📦 Migration Path

### Phase 1: Database + Models (15 min)
- [ ] Create migrations
- [ ] Update Agent, Task models
- [ ] Create Repository model
- [ ] Seed default data (Dave, Sam, Chen agents + LunaOS repo)

### Phase 2: Refactor AgentWorker (20 min)
- [ ] Update base AgentWorker class
- [ ] Update DaveAgentWorker
- [ ] Test with existing workflow

### Phase 3: HR UI (30 min)
- [ ] Create AgentConfig Livewire component
- [ ] Create Repository management UI
- [ ] Add to HR navigation

### Phase 4: Test End-to-End (15 min)
- [ ] Create test task with specific repo
- [ ] Run through Dave → Sam workflow
- [ ] Verify branch created in correct repo
- [ ] Verify agent used configured model

---

## 🎯 Example Usage

### HR Admin Flow:
1. Go to `/hr/agents`
2. Click "Dave"
3. Change model from `gpt-oss:120b-cloud` → `claude-sonnet-4-5-20250929`
4. Update system prompt
5. Save
6. Next task Dave processes uses new config automatically

### Task Creation Flow:
1. Create task
2. Select repository: "LunaOS" or "IHSSP"
3. Assign to agent: "dave"
4. Task queued
5. Dave checks out correct repo, creates branch, commits to that repo

---

## ✅ Benefits

- **No code changes to swap models** - Change in HR UI
- **Multi-project support** - Agents work on any repo
- **A/B testing** - Run Dave with different models
- **Per-repo git config** - Branch prefixes, PR templates
- **Better observability** - Track which models perform best
- **Future-proof** - Add new agents via database only

---

## 📝 Files to Create/Modify

**Migrations:**
- `database/migrations/2026_02_26_200500_add_llm_config_to_agents_table.php`
- `database/migrations/2026_02_26_200501_add_repository_to_tasks_table.php`
- `database/migrations/2026_02_26_200502_create_repositories_table.php`

**Models:**
- `app/Models/Agent.php` (enhanced)
- `app/Models/Task.php` (enhanced)
- `app/Models/Repository.php` (new)

**Agents:**
- `app/Agents/AgentWorker.php` (refactored base class)
- `app/Agents/DaveAgentWorker.php` (updated)

**Services:**
- `app/Services/GitService.php` (enhanced)

**Livewire:**
- `app/Livewire/HR/AgentConfig.php` (new)
- `app/Livewire/HR/Repositories.php` (new)

**Views:**
- `resources/views/livewire/hr/agent-config.blade.php`
- `resources/views/livewire/hr/repositories.blade.php`

**Routes:**
- `routes/web.php` (add HR agent/repos routes)

---

## 🚀 Next Steps

**Ready to implement?** I'll start with:
1. Database migrations
2. Model updates
3. Seed default data
4. Refactor AgentWorker base class

**Estimated time:** 45-60 minutes total

---

_This document is also available in the LunaOS Docs section for easy access._
