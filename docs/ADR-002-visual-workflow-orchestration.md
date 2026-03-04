# ADR-002: Visual Workflow Orchestration System

**Status:** proposed  
**Date:** 2026-03-03  
**Author:** Jordan (System Architect)  
**Reviewers:** Kyle Obear (Product Owner), Luna (PM)  
**Phase:** Phase 2B (Post-2A)  
**Tags:** workflow, visualization, agent-orchestration, UI/UX

---

## Context

LunaOS currently orchestrates development agents (Dave, Maya, Chen, Sam, Alex) through OpenClaw subagent system. While functional, this orchestration happens outside LunaOS with no visual representation or workflow management capabilities.

**Current State:**
- Agent orchestration managed by Luna (PM agent) in OpenClaw
- Workflow logic embedded in orchestration code
- No visibility into workflow state or progress
- No ability to modify workflows without code changes
- No historical record of workflow executions

**Desired State:**
- Visual representation of agent workflows within LunaOS
- Workflow execution tracked and monitored
- Ability to define/modify workflows via UI (eventually drag-drop)
- Historical workflow executions queryable
- Agent orchestration moved from OpenClaw → LunaOS

**Timing:** This work is scheduled for **Phase 2B** (after Phase 2A completion) to avoid distraction from current Team Module deliverables.

---

## Problem Statement

How do we provide visual workflow orchestration for LunaOS agents that starts with read-only visualization but scales to full drag-drop workflow builder, while maintaining flexibility for non-agent workflows in the future?

---

## Constraints

### Technical
- ✅ **Laravel Workflows package already installed** (`spatie/laravel-workflow`)
- ✅ **Workflow tables exist** in database (workflows, workflow_logs, workflow_signals, workflow_timers, workflow_exceptions, workflow_relationships)
- ✅ **Livewire 3 + HTMX + Tailwind** stack for UI components
- ⚠️ **SQLite for local dev** (must support PostgreSQL for production)
- ⚠️ **No React** — must work with Livewire/Alpine.js or Vue 3

### Business
- ✅ **Phase 2B priority** (not blocking Phase 2A)
- ✅ **Custom build preferred** over third-party integration
- ✅ **Budget:** Development time only (no licensing costs for MIT libraries)

### Operational
- ✅ **Luna** can implement (full-stack Laravel/Livewire expertise)
- ⚠️ **Timeline:** 4-6 weeks total (phased approach)
- ⚠️ **Testing:** Requires workflow execution testing infrastructure

---

## Options Considered

### Option 1: Read-Only Visualization (Phase 1)

**Description:**
Implement visual representation of existing Laravel Workflows using Mermaid.js. Workflows are defined in PHP code (no UI builder). UI shows workflow structure, current state, and execution history.

**Architecture:**
```
PHP Workflow Class → Database → Livewire Component → Mermaid.js → SVG Diagram
```

**Pros:**
- ✅ **Fastest to implement** (2-3 days)
- ✅ **Low risk** (read-only, no code generation)
- ✅ **Immediate value** (visibility into workflows)
- ✅ **Simple tech stack** (Mermaid.js is text-to-diagram)
- ✅ **Proves concept** before investing in full editor

**Cons:**
- ❌ **No workflow creation in UI** (still requires PHP coding)
- ❌ **Manual synchronization** (code changes require manual diagram updates)
- ❌ **Limited flexibility** (diagram reflects code, not source of truth)
- ❌ **Doesn't solve long-term goal** (drag-drop builder still needed)

**Effort Estimate:** 2-3 days (16-24 hours)

**Risks:**
- ⚠️ **Abandonment risk** — Teams may expect editing capability, disappointed by read-only
  - *Mitigation:* Clear communication that Phase 1 is visualization only
- ⚠️ **Mermaid limitations** — Complex workflows may not render well
  - *Mitigation:* Test with real agent workflows before committing

---

### Option 2: Form-Based Workflow Definition + Visualization (Phase 2)

**Description:**
Build UI forms to define workflows (name, description, steps, conditions). Store as JSON in database. Generate PHP workflow classes from JSON. Visualize using X6 or jsPlumb.

**Architecture:**
```
Form Editor → JSON Storage → PHP Generator → Laravel Workflow → Execution
                      ↓
                X6 Visualization
```

**Pros:**
- ✅ **No coding required** to create workflows
- ✅ **Single source of truth** (JSON definition)
- ✅ **Version control friendly** (JSON is diffable)
- ✅ **Moderate complexity** (form validation, not drag-drop)
- ✅ **Extensible** (can add more step types over time)

**Cons:**
- ❌ **Still not drag-drop** (forms are less intuitive than visual editing)
- ❌ **PHP class generation required** (complexity in code generation)
- ❌ **Versioning challenges** (what happens when workflow changes mid-execution?)
- ❌ **Limited visual feedback** (forms don't show flow as clearly as diagrams)

**Effort Estimate:** 1-2 weeks (40-80 hours)

**Risks:**
- ⚠️ **Code generation bugs** — Generated PHP may have syntax errors
  - *Mitigation:* Rigorous testing, linting step before saving
- ⚠️ **Workflow versioning** — Running workflows break when definition changes
  - *Mitigation:* Version workflows, don't modify running instances

---

### Option 3: Full Drag-Drop Workflow Builder (Phase 3)

**Description:**
Complete visual workflow editor using X6 (AntV). Drag nodes from palette, connect with arrows, configure properties. Generate Laravel Workflow classes from visual diagram. Full version control and testing from UI.

**Architecture:**
```
X6 Visual Editor → JSON Schema → PHP Generator → Laravel Workflow → Execution
              ↓
        Live Preview
```

**Pros:**
- ✅ **Best UX** (intuitive visual editing)
- ✅ **True visual workflow** (what you see is what you get)
- ✅ **Maximum flexibility** (any workflow pattern expressible)
- ✅ **Future-proof** (supports non-agent workflows)
- ✅ **Competitive advantage** (distinguishes LunaOS from basic admin panels)

**Cons:**
- ❌ **Highest complexity** (full visual editor + code generation)
- ❌ **Longest timeline** (3-4 weeks)
- ❌ **Most testing required** (visual editor edge cases)
- ❌ **X6 learning curve** (team must learn library APIs)

**Effort Estimate:** 3-4 weeks (120-160 hours)

**Risks:**
- ⚠️ **Scope creep** — "While we're at it, let's add..." syndrome
  - *Mitigation:* Strict phase boundaries, ADR change control
- ⚠️ **X6 bugs/limitations** — Library may not support all needed features
  - *Mitigation:* Proof-of-concept before full commitment
- ⚠️ **Code generation complexity** — Visual → PHP is non-trivial
  - *Mitigation:* Start with simple workflows, iterate complexity

---

### Option 4: Third-Party Integration (Rejected)

**Description:**
Integrate existing workflow orchestration platform (Node-RED, n8n, Camunda) instead of building custom.

**Pros:**
- ✅ **Fastest to deploy** (days, not weeks)
- ✅ **Battle-tested** (production-proven platforms)
- ✅ **Rich features** (years of development)

**Cons:**
- ❌ **Not LunaOS-native** (separate UI, separate auth)
- ❌ **Integration complexity** (API glue, data sync)
- ❌ **Licensing costs** (some platforms are commercial)
- ❌ **Less control** (dependent on third-party roadmap)
- ❌ **Doesn't align with Kyle's vision** (custom build preferred)

**Decision:** **REJECTED** — Kyle explicitly prefers custom build for full control and LunaOS-native experience.

---

## Comparison Matrix

| Criteria | Option 1 | Option 2 | Option 3 | Option 4 |
|----------|----------|----------|----------|----------|
| **Effort** | 2-3 days | 1-2 weeks | 3-4 weeks | 2-3 days |
| **Risk** | Low | Medium | High | Low |
| **Maintainability** | High | Medium | Medium | High |
| **Flexibility** | Low | Medium | High | High |
| **Timeline** | Immediate | Phase 2B | Phase 2B+ | Immediate |
| **Team Capacity** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes |
| **User Experience** | ⚠️ Read-only | ✅ Forms | ✅✅ Drag-drop | ✅✅ Mature |
| **LunaOS-Native** | ✅ Yes | ✅ Yes | ✅ Yes | ❌ No |
| **Long-Term Value** | ⚠️ Low | ✅ Medium | ✅✅ High | ⚠️ Medium |

---

## Decision

**Selected Option:** **Phased Approach (Option 1 → Option 2 → Option 3)**

**Approval Date:** 2026-03-03  
**Approved By:** Kyle Obear (Product Owner)

### Rationale

1. **Risk Mitigation:** Phased approach allows learning at each stage. If Phase 1 reveals fundamental issues, we can pivot before investing heavily.

2. **Early Value:** Phase 1 delivers visual visibility in 2-3 days. Immediate proof-of-value justifies continued investment.

3. **Natural Progression:** Each phase builds on previous:
   - Phase 1: Learn workflow visualization
   - Phase 2: Learn workflow definition + generation
   - Phase 3: Learn visual editor integration

4. **Budget Flexibility:** If time/budget runs short after Phase 2, we still have functional system (form-based). Phase 3 is "nice to have" not "must have".

5. **Kyle's Input:** Explicitly requested phased approach with Phase 1 as quick win, Phase 3 as end goal.

### Constraints & Conditions

- ✅ **Phase 1 after Phase 2A complete** (not blocking current work)
- ✅ **Custom build required** (no third-party integration)
- ✅ **Agent orchestration first** (primary use case)
- ✅ **Design for extensibility** (support non-agent workflows in future)
- ✅ **MIT-licensed libraries only** (no commercial licensing)

---

## Implications

### Architecture

**New Components:**
```
app/Livewire/Workflows/
├── WorkflowIndex.php      (list all workflows)
├── WorkflowShow.php       (detail + visualization)
├── WorkflowCreate.php     (Phase 2: form editor)
├── WorkflowEdit.php       (Phase 2: form editor)
└── WorkflowBuilder.php    (Phase 3: drag-drop X6 editor)

app/Services/
├── WorkflowVisualizationService.php  (generates Mermaid/X6 JSON)
├── WorkflowGeneratorService.php      (Phase 2/3: generates PHP from JSON)
└── WorkflowExecutionService.php      (wraps Spatie workflow execution)

app/Models/
└── WorkflowDefinition.php            (Phase 2: JSON storage model)

resources/views/livewire/workflows/
├── index.blade.php
├── show.blade.php
├── create.blade.php    (Phase 2)
├── edit.blade.php      (Phase 2)
└── builder.blade.php   (Phase 3)
```

**Existing Components Used:**
- `Spatie\Workflow` models (workflows, workflow_logs, etc.)
- Laravel Horizon (for workflow queue monitoring)
- Laravel Activity Log (for workflow audit trail)

### Data

**Phase 1: No New Tables**
- Uses existing `workflows`, `workflow_logs` tables
- Read-only queries

**Phase 2: New Tables Required**
```sql
CREATE TABLE workflow_definitions (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    version INTEGER,
    definition_json JSON,
    status ENUM('draft', 'active', 'deprecated'),
    created_by UUID,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE workflow_definition_versions (
    id UUID PRIMARY KEY,
    workflow_definition_id UUID,
    version INTEGER,
    definition_json JSON,
    change_summary TEXT,
    created_by UUID,
    created_at TIMESTAMP
);
```

**Phase 3: No Schema Changes**
- Phase 2 schema supports Phase 3
- X6 visualization stored as JSON (same as form definition)

### API

**Phase 1 APIs (Read-Only):**
```
GET /workflows                    → List workflows
GET /workflows/{id}               → Workflow detail
GET /workflows/{id}/visualize     → Mermaid diagram
GET /workflows/{id}/executions    → Execution history
GET /workflows/{id}/logs          → Workflow logs
```

**Phase 2 APIs (CRUD):**
```
POST /workflows/definitions       → Create definition
PUT /workflows/definitions/{id}   → Update definition
POST /workflows/definitions/{id}/generate  → Generate PHP class
POST /workflows/definitions/{id}/activate  → Activate version
```

**Phase 3 APIs (Visual Editor):**
```
POST /workflows/builder/save      → Save visual diagram
POST /workflows/builder/export    → Export as JSON/PHP
POST /workflows/builder/test      → Test workflow from editor
```

### Testing

**Unit Tests:**
- WorkflowVisualizationService (Mermaid generation)
- WorkflowGeneratorService (PHP generation, Phase 2)
- WorkflowExecutionService (execution wrapper)

**Integration Tests:**
- Workflow creation → generation → execution flow
- Workflow versioning (activate new version while running)

**Browser Tests:**
- WorkflowIndex loads with visualization
- Workflow builder drag-drop (Phase 3)
- Form validation (Phase 2)

**Coverage Requirements:**
- Minimum: **80%** for all Services
- Critical paths: **100%** (workflow generation, execution)

### Operations

**Deployment:**
- Phase 1: No migration (read-only)
- Phase 2: Migration for workflow_definitions tables
- Phase 3: No migration (uses Phase 2 schema)

**Monitoring:**
- Workflow execution success/failure rates
- Average execution time per workflow type
- Failed workflow alerts (Slack/email)
- Workflow definition changes (audit log)

**Rollback:**
- Phase 1: Trivial (delete new views/components)
- Phase 2: Drop workflow_definitions tables, restore from backup
- Phase 3: Same as Phase 2

### Security

**Access Control:**
- `viewWorkflows` — All authenticated users
- `createWorkflows` — Admin/Architect roles only
- `executeWorkflows` — Admin/Architect roles only
- `deleteWorkflows` — Admin only

**Audit Trail:**
- All workflow definition changes logged
- Execution history retained (90 days minimum)
- Failed executions trigger alerts

### Performance

**Expected Impact:**
- Phase 1: Negligible (read-only queries)
- Phase 2: Minimal (JSON storage/queries)
- Phase 3: X6 rendering may be heavy for large workflows (>50 nodes)

**Benchmarks:**
- Workflow list: <200ms
- Diagram render: <500ms (up to 50 nodes)
- PHP generation: <1s
- Workflow execution: Depends on steps (not UI concern)

**Scaling:**
- Phase 1/2: SQLite fine for local, PostgreSQL for production
- Phase 3: X6 handles large diagrams client-side (no server impact)

### Future Work

**Opens Doors:**
- User-defined workflows (custom automation)
- Workflow templates (pre-built agent orchestrations)
- Workflow marketplace (share workflows between LunaOS instances)
- Workflow analytics (execution patterns, bottlenecks)

**Closes Doors:**
- None (phased approach keeps options open)

**Technical Debt:**
- Phase 1 code will be partially replaced by Phase 2/3
- Acceptable trade-off for early learning + value

---

## Migration Strategy

**Not Applicable** for Phase 1 (no data migration required).

**Phase 2 Migration:**
```bash
# Create workflow_definitions tables
php artisan make:migration create_workflow_definitions_tables

# Migrate existing workflows (if any) to definitions
# (Optional: import existing PHP workflows as JSON definitions)
```

---

## Testing Strategy

### Phase 1 Testing
- **Manual testing:** Load workflow visualization in browser
- **Browser tests:** Mermaid diagram renders correctly
- **Integration tests:** Workflow execution still works after adding visualization

### Phase 2 Testing
- **Unit tests:** WorkflowGeneratorService produces valid PHP
- **Integration tests:** JSON definition → PHP → execution works end-to-end
- **Browser tests:** Form validation, save, activate workflow

### Phase 3 Testing
- **Unit tests:** X6 JSON → PHP generation
- **Browser tests:** Drag-drop editor, node configuration, save
- **Performance tests:** Large workflows (50+ nodes) render in <1s

### Quality Gates
- **Minimum Test Coverage:** 80% for all Services
- **Browser Tests:** All critical paths (create, edit, execute, visualize)
- **Performance:** Diagram render <500ms, PHP generation <1s
- **Security:** RBAC enforced (non-admins can't create/execute workflows)

---

## Compliance

### Architecture Principles
- ✅ **Simplicity over flexibility** (Phase 1 is read-only, not full editor)
- ✅ **Pragmatic optimism** (phased approach balances ambition with reality)
- ✅ **Data safety first** (versioning, rollback, audit trail)
- ✅ **LunaOS-native** (custom build, not third-party)

### Design Patterns
- **Service Layer** (WorkflowVisualizationService, WorkflowGeneratorService)
- **Repository Pattern** (WorkflowDefinitionRepository)
- **Strategy Pattern** (different visualization strategies: Mermaid, X6)
- **Factory Pattern** (PHP class generation from JSON)

### Coding Standards
- PSR-12 (PHP)
- Laravel Conventions (directory structure, naming)
- Livewire Best Practices (component structure)

---

## References

- **Spatie Laravel Workflow:** https://github.com/spatie/laravel-workflow
- **X6 (AntV):** https://x6.antv.vision/
- **Mermaid.js:** https://mermaid.js.org/
- **Livewire 3 Docs:** https://livewire.laravel.com/
- **Related ADR:** ADR-001 (Team Member Consolidation)

---

## Change Log

| Date | Author | Change | Reason | Approvers |
|------|--------|--------|--------|-----------|
| 2026-03-03 | Jordan | Initial ADR created | Phase 2B planning | — |
| 2026-03-03 | Kyle | Approved phased approach | Aligns with vision, manageable timeline | Kyle |

---

## Notes

**Jordan's Architect Commentary:**

This is a high-value feature that distinguishes LunaOS from basic admin panels. The phased approach is critical—too many teams try to build the full drag-drop editor in one sprint and fail.

**Key Insights:**
1. **Phase 1 is cheap insurance** — 2-3 days to learn visualization challenges. If Mermaid doesn't work well, we know before investing in X6.
2. **JSON schema is the linchpin** — Phase 2's JSON definition format must support Phase 3's visual editor. Design schema carefully.
3. **Code generation is tricky** — Generating PHP from JSON sounds simple until you handle edge cases (conditionals, loops, error handling). Consider code templates instead of string concatenation.
4. **Versioning is essential** — Workflows may run for hours/days. Can't break running instances when definition changes. Versioning solves this.

**Risks I'm Watching:**
- **Scope creep in Phase 3** — "Let's add conditional nodes! And parallel execution! And sub-workflows!" — Stay focused on MVP.
- **X6 complexity** — Library is powerful but has learning curve. Budget 2-3 days just for X6 experimentation before building.
- **Agent workflow complexity** — Agent orchestration has unique patterns (parallel execution, human-in-loop, rollbacks). Ensure schema supports these.

**Recommendation for Phase 1 Start:**
Start with **one simple workflow** (e.g., "Create Task → Dave → Maya → Sam → Deploy"). Perfect the visualization on that. Then scale to complex workflows.

---

## Implementation Status

- [ ] ADR approved ✅ (2026-03-03, Kyle)
- [ ] Phase 1: Read-only visualization
- [ ] Phase 2: Form-based workflow definition
- [ ] Phase 3: Drag-drop workflow builder
- [ ] Tests passing
- [ ] Documentation updated
- [ ] Post-implementation review

**Implementation Date:** TBD (after Phase 2A complete)  
**Target Completion:** Phase 2B (April 2026)  
**Implemented By:** Luna (full-stack implementation)

---

## Post-Implementation Review

{To be completed after Phase 3 implementation}

### What Went Well

- {success 1}
- {success 2}

### What Could Be Improved

- {improvement 1}
- {improvement 2}

### Deviations from ADR

{Did implementation deviate from the plan? Why?}

### Lessons Learned

- {lesson 1}
- {lesson 2}

### Follow-up Actions

- [ ] {action 1}
- [ ] {action 2}

---

**ADR Review Date:** TBD  
**Reviewed By:** Jordan, Kyle, Luna
