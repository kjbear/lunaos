# Issue #29: AI Persona/Prompt Editor for Team Members

**Priority:** P2 (HIGH)  
**Assignee:** Dave (Backend) + Maya (Frontend)  
**Component:** Team Module (PHP AI System)

---

## Problem

Team member detail page at `/team/{uuid}` shows basic info but no way to edit AI-specific metadata for the PHP-driven AI system.

---

## Requirements

Add new "AI Configuration" section to team member detail page with:

### Section 1: Model Settings
- Model selection (glm-5, dolphin, haiku, etc.)
- Temperature (slider 0.0-2.0)
- Max tokens (input)
- Top P, frequency/presence penalties

### Section 2: Persona & Prompt
- Persona name
- System prompt (textarea, markdown)
- Response style (formal/casual/technical/etc.)
- Special instructions

### Section 3: Capabilities
- Skills (tags)
- Capabilities (checkboxes)
- Specializations

### Section 4: Operations
- Available (online/offline)
- Capacity (0-100%)
- Max concurrent tasks
- Auto-assign enabled
- Priority level

### Section 5: Custom Metadata (JSON)
- Advanced config
- Integration settings

---

## Technical Approach

**Dave (Backend):**
1. Create migration: Add AI config columns to team_members table
2. Update TeamMember model: fillable, casts, validation
3. Create Livewire component for editing
4. Add API endpoints if needed

**Maya (Frontend):**
1. Add AI Config tab to team detail page
2. Form with sliders, selects, tag inputs, markdown textarea
3. Livewire form handling
4. Validation + toast notifications
5. Responsive design

---

## Acceptance Criteria

- [ ] Migration with all AI config fields
- [ ] Model updated (fillable, casts)
- [ ] AI Config tab on detail page
- [ ] All fields editable
- [ ] Form validation
- [ ] Save/Cancel works
- [ ] Responsive design
- [ ] Tests written

---

**Note:** This is for PHP AI system, NOT OpenClaw agents.
