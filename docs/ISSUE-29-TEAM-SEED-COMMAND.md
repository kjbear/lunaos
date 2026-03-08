# Issue #29: Create Artisan Command to Seed Realistic AI Team Data

**Priority:** P2 (HIGH)  
**Assignee:** Dave (Backend)  
**Component:** Team Module / Database Seeding

---

## Problem

Local database has fake/test data. Need realistic AI team member data for development.

---

## Requirements

**Create Artisan command:** `php artisan team:seed-agents`

**Features:**
- Seeds realistic AI team members
- Re-runnable to reset database
- Idempotent (clears or updates existing)
- Includes all team with proper roles

---

## Team Roster

### Board (C-Suite) - 6 members
- Steven - CEO
- Gwynne - COO  
- Werner - CTO
- Warren - CFO
- Bozoma - CMO
- Fidji - CPO

### Project Managers - 2 members
- Jordan - Senior PM
- Alex - PM (API/Integrations)

### Developers - 2 members
- Dave - Senior PHP/Laravel Developer
- Maya - Senior Frontend Developer

### DevOps - 1 member
- Chen - DevOps Engineer

### QA - 1 member  
- Sam - QA Engineer

### Research - 1 member
- Leo - Research & Documentation

**Total:** 13 team members

---

## Command Usage

```bash
# Reset and seed fresh
php artisan team:seed-agents --reset

# Seed additive (no clear)
php artisan team:seed-agents

# Seed specific type
php artisan team:seed-agents --type=board

# Dry run (preview)
php artisan team:seed-agents --dry-run
```

---

## Data Fields

Each member needs:
- name, type (worker/persona/board), role, category
- available (boolean), status (online/offline)
- bio, skills (JSON array), current_capacity (0-100%)

---

## Acceptance Criteria

- [ ] Command exists and works
- [ ] Seeds all 13 team members
- [ ] Proper types/roles assigned
- [ ] --reset clears existing first
- [ ] --dry-run shows preview
- [ ] Idempotent (safe re-run)
- [ ] No duplicates
- [ ] Documentation added

---

## Files to Create

- `app/Console/Commands/SeedTeamAgents.php`
- `database/seeders/TeamMemberSeeder.php`
- `docs/TEAM-SEEDING.md`

---

**Related:** Team cleanup issues #16-28
