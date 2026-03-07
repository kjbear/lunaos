# LunaOS Git Workflow Documentation

> Official workflow guide for parallel agent development. PR-only CI with agent-named branches.

---

## Table of Contents

1. [Branch Naming Convention](#branch-naming-convention)
2. [Full Workflow Diagram](#full-workflow-diagram)
3. [Parallel Development Guidelines](#parallel-development-guidelines)
4. [CI Integration](#ci-integration)
5. [Kyle's Review Process](#kyles-review-process)
6. [Subagent Checklist](#subagent-checklist)
7. [Local Deployment](#local-deployment)

---

## Branch Naming Convention

### Format

```
<agent>/<type>-<description>
```

### Valid Agents

| Agent | Role |
|-------|------|
| `dave` | Backend developer |
| `maya` | Frontend/UI developer |
| `sam` | Systems architect |
| `chen` | DevOps engineer |
| `alex` | Full-stack developer |
| `jordan` | QA/testing specialist |
| `leo` | Documentation writer |

### Valid Types

| Type | Purpose |
|------|---------|
| `feature` | New functionality |
| `fix` | Bug fixes |
| `refactor` | Code improvements without behavior changes |
| `test` | Test additions/improvements |
| `docs` | Documentation updates |

### Examples

```bash
# Backend work
dave/feature-projects-api
dave/fix-auth-validation

# Frontend work
maya/feature-task-board
maya/fix-mobile-nav

# Architecture
sam/refactor-dependency-injection

# DevOps/Infrastructure
chen/fix-ci-pipeline
chen/feature-docker-compose

# Full-stack
alex/feature-user-settings

# Testing
jordan/test-auth-flow
jordan/fix-e2e-flaky-tests

# Documentation
leo/docs-api-reference
leo/docs-deployment-guide
```

---

## Full Workflow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                     LUNAOS GIT WORKFLOW                         │
└─────────────────────────────────────────────────────────────────┘

  ┌──────────────┐
  │ 1. AGENT     │   Agent receives task assignment
  │    CREATES   │   ────────────────────────────────
  │    BRANCH    │   $ git checkout main
  └──────┬───────┘   $ git pull origin main
         │           $ git checkout -b <agent>/<type>-<description>
         ▼
  ┌──────────────┐
  │ 2. AGENT     │   Write code, make changes
  │    WRITES    │   ────────────────────────
  │    CODE      │   $ git add .
  └──────┬───────┘   $ git commit -m "feat: add projects API"
         │
         ▼
  ┌──────────────┐
  │ 3. AGENT     │   Push to remote, create PR
  │    PUSHES    │   ─────────────────────────────────────
  │    & PR      │   $ git push -u origin <branch-name>
  └──────┬───────┘   $ gh pr create --title "..." --body "..."
         │
         ▼
  ┌──────────────┐
  │ 4. CI RUNS   │   GitHub Actions triggers on PR
  │    AUTOMATIC │   ────────────────────────────
  │              │   ✓ Pint (Laravel code style)
  └──────┬───────┘   ✓ PHPStan (static analysis)
         │           ✓ PHPUnit (unit/integration tests)
         ▼
  ┌──────────────┐
  │ 5. KYLE      │   Manual code review
  │    REVIEWS   │   ──────────────────────────
  │              │   • Code quality & patterns
  └──────┬───────┘   • Architecture alignment
         │           • Test coverage
         ▼
  ┌──────────────┐
  │ 6. KYLE      │   Squash & merge to main
  │    MERGES    │   ────────────────────────
  │              │   $ gh pr merge --squash
  └──────┬───────┘
         │
         ▼
  ┌──────────────┐
  │ 7. LOCAL     │   Pull latest to local
  │    DEPLOYMENT│   ───────────────────────────────
  │              │   $ git checkout main
  └──────────────┘   $ git pull origin main
                     → lunaos.test auto-updates (Laravel Valet)
```

---

## Parallel Development Guidelines

### Core Principle

Each agent works on their own branch in isolation. No conflicts until merge time.

### Branch Isolation Rules

```
AGENT WORKFLOW - NO CONFLICTS UNTIL MERGE
─────────────────────────────────────────

main ─────────────────────────────────────●────────────●──────>
                                              \          \
dave/feature-projects-api ──────────────────────●─────────┘
                                                   \
maya/ui-dashboard ─────────────────────────────────●───────>
                                                       \
sam/refactor-config ───────────────────────────────────●────►

● = Merge point (potential conflicts occur here only)
```

### When Conflicts Occur

Conflicts only happen at merge time when two agents modified the same files.

#### Conflict Resolution Process

```bash
# 1. Update your branch with latest main
$ git checkout main
$ git pull origin main
$ git checkout <your-branch>
$ git merge main

# 2. If conflicts occur, Git will mark them:
#    <<<<<<< HEAD
#    your changes
#    =======
#    main changes
#    >>>>>>> main

# 3. Resolve conflicts manually in your editor
#    - Remove conflict markers (<<<<<<, ======, >>>>>>)
#    - Keep correct code (usually combination of both)

# 4. Mark conflicts as resolved
$ git add <resolved-files>
$ git commit -m "resolve merge conflicts with main"

# 5. Push resolved branch
$ git push origin <your-branch>
```

#### Best Practices to Avoid Conflicts

1. **Keep branches small** - Smaller changes = smaller conflict surface
2. **Communicate with other agents** - Know what others are working on
3. **Pull frequently** - Merge main into your branch daily to catch conflicts early
4. **Avoid touching shared files** - Stay in your component/feature area

---

## CI Integration

### Trigger Policy

| Event | CI Runs? | Notes |
|-------|----------|-------|
| Push to branch | ❌ No | Prevents noise during development |
| PR created/updated | ✅ Yes | Full CI suite |
| Push to main | ❌ No | Main is protected, only merges allowed |

### What CI Runs

```yaml
# .github/workflows/ci.yml (conceptual)

on:
  pull_request:
    branches: [main]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install
      - run: ./vendor/bin/pint --test
      # Fails if code style violations found

  static-analysis:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install
      - run: ./vendor/bin/phpstan analyse
      # Fails on type errors, unused code, etc.

  tests:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: lunaos_test
      redis:
        image: redis:alpine
    steps:
      - uses: actions/checkout@v4
      - run: composer install
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: php artisan migrate --force
      - run: ./vendor/bin/phpunit

  branch-name-validation:
    runs-on: ubuntu-latest
    steps:
      - name: Validate branch name
        run: |
          BRANCH="${{ github.head_ref }}"
          if [[ ! "$BRANCH" =~ ^(dave|maya|sam|chen|alex|jordan|leo)/(feature|fix|refactor|test|docs)-[a-z0-9-]+$ ]]; then
            echo "Invalid branch name: $BRANCH"
            echo "Expected format: <agent>/<type>-<description>"
            echo "Agents: dave, maya, sam, chen, alex, jordan, leo"
            echo "Types: feature, fix, refactor, test, docs"
            exit 1
          fi
```

### CI Status Checks

All three checks must pass before merge:
- ✅ `lint` — Pint passes
- ✅ `static-analysis` — PHPStan passes
- ✅ `tests` — PHPUnit passes
- ✅ `branch-name-validation` — Correct naming

---

## Kyle's Review Process

### What Kyle Checks

#### Code Quality

- [ ] Follows Laravel conventions (controllers, models, services)
- [ ] Proper dependency injection (no facades in constructors)
- [ ] Single responsibility principle
- [ ] DRY code (no duplicated logic)

#### Architecture

- [ ] Controllers stay thin (business logic in services)
- [ ] Models contain Eloquent relationships and scopes
- [ ] Services handle complex business logic
- [ ] Proper use of Laravel features (policies, events, jobs)

#### Testing

- [ ] New features include tests
- [ ] Bug fixes include regression tests
- [ ] Tests are meaningful, not just for coverage

#### Security

- [ ] No SQL injection (use Eloquent/query builder)
- [ ] Proper authorization (policies/gates)
- [ ] Input validation (form requests)
- [ ] No sensitive data in logs

### How to Request Changes

When Kyle finds issues, he'll comment on the PR:

```markdown
## Review Comments

**File:** `app/Services/ProjectService.php:45`

> This method is doing too much. Consider extracting the notification
> logic into a separate service or event.
>
> ❌ Requested change

---

**File:** `tests/Feature/ProjectTest.php:23`

> Good test coverage! ✅
```

After addressing feedback:

```bash
# Make changes
$ git add .
$ git commit -m "refactor: extract notification logic to event"
$ git push origin <your-branch>

# PR automatically updates
# CI runs again automatically
```

### Merge Criteria

| Requirement | Must Pass |
|-------------|-----------|
| CI Checks | All green ✅ |
| Branch Name | Valid format ✅ |
| Code Review | Approved by Kyle ✅ |
| No Conflicts | Mergeable with main ✅ |

---

## Subagent Checklist

### Step-by-Step for Every Task

```bash
# ═══════════════════════════════════════════════════════════════
# AGENT WORKFLOW CHECKLIST
# ═══════════════════════════════════════════════════════════════

# ───────────────────────────────────────────────────────────────
# STEP 1: SYNC WITH MAIN
# ───────────────────────────────────────────────────────────────
$ git checkout main
$ git pull origin main

# ───────────────────────────────────────────────────────────────
# STEP 2: CREATE YOUR BRANCH
# ───────────────────────────────────────────────────────────────
# Replace <agent> with: dave, maya, sam, chen, alex, jordan, or leo
# Replace <type> with: feature, fix, refactor, test, or docs
# Replace <description> with: lowercase-hyphenated-description
$ git checkout -b <agent>/<type>-<description>

# Example:
# $ git checkout -b chen/fix-ci-pipeline

# ───────────────────────────────────────────────────────────────
# STEP 3: DO THE WORK
# ───────────────────────────────────────────────────────────────
# Write code, tests, docs, etc.
# Run local checks:
$ ./vendor/bin/pint --test      # Code style
$ ./vendor/bin/phpstan analyse  # Static analysis
$ ./vendor/bin/phpunit          # Tests

# ───────────────────────────────────────────────────────────────
# STEP 4: COMMIT YOUR CHANGES
# ───────────────────────────────────────────────────────────────
$ git add .
$ git commit -m "<type>: <description>"

# Commit type prefixes:
# feat:     New feature
# fix:      Bug fix
# refactor: Code refactor
# test:     Adding/updating tests
# docs:     Documentation changes
# chore:    Maintenance tasks

# ───────────────────────────────────────────────────────────────
# STEP 5: PUSH AND CREATE PR
# ───────────────────────────────────────────────────────────────
$ git push -u origin <agent>/<type>-<description>

# Create PR with description
$ gh pr create --title "<type>: <description>" --body "
## What
Brief description of changes

## Why
Reason for this change

## How
Technical approach taken

## Testing
- [ ] Tests pass locally
- [ ] New tests added (if applicable)
- [ ] Manual testing performed
"

# ───────────────────────────────────────────────────────────────
# STEP 6: WAIT FOR CI & REVIEW
# ───────────────────────────────────────────────────────────────
# CI runs automatically
# Wait for Kyle's review
# Address any feedback

# ───────────────────────────────────────────────────────────────
# STEP 7: AFTER MERGE
# ───────────────────────────────────────────────────────────────
# Kyle handles the merge
# You're done! Move to next task or wait for assignment.
```

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

#### Examples

```bash
# Feature
$ git commit -m "feat(projects): add bulk delete endpoint"

# Bug fix
$ git commit -m "fix(auth): handle expired tokens gracefully"

# Refactor
$ git commit -m "refactor(services): extract common validation logic"

# Tests
$ git commit -m "test(projects): add edge case tests for API"

# Documentation
$ git commit -m "docs(api): update endpoint descriptions"
```

---

## Local Deployment

### Overview

LunaOS is **local-first**. After merge, you pull to your local machine and the app is ready.

```
MERGE TO MAIN ───► git pull ───► lunaos.test updated
                                              │
                                         Laravel Valet
                                         auto-reloads
```

### Post-Merge Steps

```bash
# ═══════════════════════════════════════════════════════════════
# LOCAL UPDATE PROCESS
# ═══════════════════════════════════════════════════════════════

# Switch to main
$ git checkout main

# Pull latest (includes merged PR)
$ git pull origin main

# Run migrations if any
$ php artisan migrate --force

# Clear caches
$ php artisan cache:clear
$ php artisan config:clear
$ php artisan view:clear

# Optionally restart queue workers
$ php artisan queue:restart

# Done! lunaos.test reflects the changes
```

### No Remote Deployment

| Environment | Status | Notes |
|-------------|--------|-------|
| Local (lunaos.test) | ✅ Active | Laravel Valet, live reload |
| Staging | ❌ Not configured | Local-first tool |
| Production | ❌ Not configured | Personal tool, stays local |

### Troubleshooting

```bash
# If something's broken after pull
$ php artisan optimize:clear    # Clear all caches
$ composer install              # Reinstall dependencies
$ php artisan storage:link     # Re-link storage
$ php artisan migrate --force  # Run migrations

# Check Valet status
$ valet status

# Restart Valet if needed
$ valet restart
```

---

## Quick Reference

### Branch Names

```bash
# Valid ✓
dave/feature-projects-api
maya/fix-task-board-mobile
sam/refactor-dependency-injection
chen/docs-ci-workflow
alex/test-auth-flow
jordan/fix-flaky-tests
leo/docs-api-endpoints

# Invalid ✗
feature/projects-api           # Missing agent prefix
dave/projects-api              # Missing type
dave/Feature-Projects-API     # Uppercase (use lowercase)
dave/feature_projects_api      # Underscores (use hyphens)
```

### Essential Commands

```bash
# Start new work
git checkout main && git pull origin main
git checkout -b <agent>/<type>-<description>

# Check your changes
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
./vendor/bin/phpunit

# Submit work
git add . && git commit -m "feat: ..."
git push -u origin <branch-name>
gh pr create

# Stay in sync
git checkout main && git pull
git checkout <branch> && git merge main
```

---

## Version History

| Date | Author | Changes |
|------|--------|---------|
| 2026-03-06 | Chen | Initial documentation |

---

*This document maintained for LunaOS parallel development workflow.*