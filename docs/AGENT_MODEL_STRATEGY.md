# Agent Model Strategy & Assignments

**Created:** March 1, 2026  
**Last Updated:** March 1, 2026 — 11:46 AM EST (Ollama Cloud assignments)

## Overview

This document defines model assignments for all agents in the Luna team, with rationale, cost estimates, and LunaOS integration requirements.

**Guiding Principles:**
1. **Right tool for the job** — Match model strengths to task requirements
2. **Cost-effective** — Use local models for high-volume, well-defined tasks
3. **Quality where it matters** — Use cloud models for nuanced, high-value outputs
4. **Configurable** — All assignments should be configurable in LunaOS UI (Phase 2)

---

## Model Catalog (Ollama Cloud)

### Available Cloud Models

| Model | Params | Strengths | Best For | Cost/1M tokens |
|-------|--------|-----------|----------|----------------|
| **qwen3.5** | 35B | Thinking mode, multimodal, tools | General purpose, writing, research | ~$0.015 input / $0.045 output |
| **qwen3-coder-next** | — | Agentic coding workflows | Code generation, debugging, tests | ~$0.012 input / $0.036 output |
| **glm-5** | 40B active | Strong reasoning, complex systems | Architecture, deep analysis, strategy | ~$0.015 input / $0.045 output |
| **minimax-m2.5** | — | Real-world productivity | Code + general tasks | ~$0.010 input / $0.030 output |
| **devstral-small-2** | 24B | Tool use, codebase exploration | Code agents, refactoring | ~$0.012 input / $0.036 output |
| **nemotron-3-nano** | 30B | Efficient, agentic | Cost-effective general purpose | ~$0.010 input / $0.030 output |

---

## Agent Model Assignments

### Core Team (Active)

| Agent | Role | Model | Why | Est. Daily Cost |
|-------|------|-------|-----|-----------------|
| **Luna** (You) | PM, coordination | `qwen3.5:397b-cloud` | Current default, proven high quality | Included in base |
| **Sam** | QA Engineer | `qwen3-coder-next:cloud` | Optimized for agentic coding, test writing | ~$0.04/day |
| **Dave** | PHP Developer | `qwen3-coder-next:cloud` | Coding-focused, agentic workflows | ~$0.04/day |
| **Chen** | DevOps Engineer | `nemotron-3-nano:cloud` | Efficient, good for repetitive infra tasks | ~$0.02/day |
| **Maya** | Frontend Dev | `qwen3-coder-next:cloud` | Coding-focused, UI/UX work | ~$0.04/day |
| **Alex** | API Architect | `glm-5:cloud` | Strong reasoning for API design | ~$0.05/day |

### Content & Strategy (Pending)

| Agent | Role | Model | Why | Est. Daily Cost |
|-------|------|-------|-----|-----------------|
| **Casey** | Content Strategist | `qwen3.5:cloud` | Thinking mode, nuanced writing, audience awareness | ~$0.03/day |
| **Ripley** | Market Intelligence | `glm-5:cloud` | Strong reasoning, pattern recognition, synthesis | ~$0.07/day |

### Future Agents (TBD)

| Agent | Role | Recommended Model | Notes |
|-------|------|-------------------|-------|
| **Jordan** | Project Manager | `qwen3.5:cloud` | Coordination, planning, team management |
| **Archimedes** | Security Analyst | `glm-5:cloud` | Deep reasoning for security audits |
| **Echo** | Documentation | `qwen3.5:cloud` | Technical writing, API docs |
| **Scout** | Research | `glm-5:cloud` | Deep research, competitive analysis |

---

## Cost Breakdown

### Current (Code Agents Only)
- **Sam, Dave, Chen, Maya:** ~$0.14/day
- **Monthly:** ~$4.20/month

### With Content & Strategy (Casey + Ripley)
- **All 8 agents:** ~$0.24/day
- **Monthly:** ~$7.20/month

### Full Team (12 Agents)
- **All agents:** ~$0.45-0.60/day (estimated)
- **Monthly:** ~$13.50-18.00/month

**Comparison:**
- All-API approach (Haiku/GPT-4): ~$800-1,000/month for same output
- **Savings:** 98%+ vs all-API
- **Tradeoff:** Slightly slower on some tasks, but quality is 85-95% (acceptable)

---

## LunaOS Integration Requirements (Phase 2)

### Agent Configuration UI

**Location:** `lunaos.test/agents/settings`

**Features:**
1. **Agent List** — All configured agents with current status
2. **Model Selector** — Dropdown of available Ollama Cloud models
3. **Task Frequency** — How often agent runs tasks (hourly, daily, on-demand)
4. **Output Format** — Templates for reports, summaries, etc.
5. **Collaboration Rules** — Define agent-to-agent workflows
6. **Cost Tracking** — Real-time token usage, daily/monthly spend per agent

**Config Storage:**
- `lunaos/config/agents/{agent_name}.php` — Agent-specific settings
- `lunaos/config/team.php` — Team-wide defaults
- `lunaos/database/agent_usage.sqlite` — Per-agent token tracking

### Workflow Engine

**Purpose:** Define how agents collaborate

**Example: Casey + Ripley Workflow**
```php
[
  'workflow' => 'content_strategy',
  'trigger' => 'daily',
  'steps' => [
    [
      'agent' => 'ripley',
      'task' => 'market_research',
      'output' => 'daily_report.md',
      'recipients' => ['kyle', 'casey'],
    ],
    [
      'agent' => 'casey',
      'task' => 'article_pitches',
      'input' => 'ripley_daily_report.md',
      'output' => 'article_pitches.md',
      'recipients' => ['kyle'],
    ],
  ],
]
```

### Model Fallback Strategy

**If cloud model unavailable:**
1. Try alternative cloud model (e.g., glm-5 → qwen3.5)
2. Fall back to local sidecar (Dolphin 3.0 on Mac Mini)
3. Alert Luna → Luna alerts Kyle if all fallbacks fail

**Configuration:**
```php
'lunaos.model_fallback' => [
  'primary' => 'ollama-local/glm-5:cloud',
  'fallback_1' => 'ollama-local/qwen3.5:cloud',
  'fallback_2' => 'dolphin/dphn_Dolphin3.0:latest', // local
  'alert_threshold' => 3, // failures before alerting
],
```

---

## Decision Log

### March 1, 2026 — Ollama Cloud Selection
**Decision:** Use Ollama Cloud models for all agents
**Rationale:**
- Consistent API interface
- Good balance of cost vs quality
- Wide model selection (coding, writing, reasoning)
- No need to manage separate API keys per provider

**Alternatives Considered:**
- OpenRouter (wider model selection, but more complex routing)
- Direct API calls (more control, but more maintenance)
- Local-only (Dolphin 3.0) — rejected for Casey/Ripley (quality matters)

### March 1, 2026 — Model Assignments
**Decision:** Assign models based on task type
**Rationale:**
- Coding agents → qwen3-coder-next (purpose-built)
- Writing/strategy → qwen3.5, glm-5 (strong reasoning, nuance)
- DevOps → nemotron-3-nano (cost-effective, efficient)

**Rejected:**
- One model for all agents — too much optimization loss
- All-local for cost savings — quality gap too large for strategy tasks

---

## Future Considerations

1. **Model Evolution** — New models released regularly; review quarterly
2. **Cost Optimization** — Track actual vs estimated costs; adjust assignments
3. **Performance Metrics** — Measure output quality per model; refine assignments
4. **Local vs Cloud** — If local hardware improves, reconsider cloud dependencies
5. **Multi-Model Tasks** — Some tasks might benefit from sequential model use (e.g., draft on local, polish on cloud)

---

**Owner:** Kyle Obear  
**PM:** Luna 🌙  
**Next Review:** April 1, 2026 (after Phase 2 LunaOS deployment)
