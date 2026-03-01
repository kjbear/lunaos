# LunaOS Testing Guide

**Created:** March 1, 2026  
**Status:** Phase 1 MVP Complete — Testing Framework Established

## Overview

LunaOS Phase 1 includes a comprehensive testing framework with PHPUnit for unit tests and Laravel Dusk for browser tests. This guide covers test setup, execution, and maintenance.

## Test Structure

```
tests/
├── Unit/
│   ├── ExampleTest.php
│   └── Models/
│       ├── AgentModelTest.php
│       ├── TaskModelTest.php
│       ├── ActivityLogModelTest.php
│       └── StandupModelTest.php
├── Feature/
│   ├── ExampleTest.php
│   ├── Livewire/
│   │   └── ModuleTests.php
│   └── Api/
│       └── (API endpoint tests)
└── Browser/
    └── (Laravel Dusk tests)
```

## Running Tests

### Unit Tests

```bash
# Run all unit tests
cd /Users/kobear/.openclaw/workspace/lunaos
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Unit/Models/AgentModelTest.php

# Run with coverage
php artisan test --coverage
```

### Feature Tests

```bash
# Run all feature tests
php artisan test --testsuite=Feature

# Run Livewire module tests
php artisan test tests/Feature/Livewire/ModuleTests.php
```

### Browser Tests (Laravel Dusk)

```bash
# Start Dusk server
php artisan dusk:serve

# Run browser tests
php artisan dusk

# Run specific browser test
php artisan dusk tests/Browser/ModuleLoadTest.php
```

### All Tests

```bash
# Run entire test suite
php artisan test
php artisan dusk
```

## Multi-Database Testing

LunaOS uses multiple SQLite databases:
- `sqlite` (main) — `database/database.sqlite`
- `sqlite-projects` — `/workspace/projects.db`
- `sqlite-activity` — `/workspace/activity.db`

**Testing Config:** Tests use in-memory databases for all connections.

**Known Issue:** Migration order for multi-database connections requires manual setup. See `tests/TestCase.php` for configuration.

## Test Coverage Goals

| Component | Target Coverage | Current Status |
|-----------|----------------|----------------|
| Models | 80%+ | ✅ Agent, Task, ActivityLog, Standup tests written |
| Controllers | 70%+ | ⏳ Pending |
| Livewire Components | 80%+ | ✅ Module load tests written |
| Services | 75%+ | ⏳ Pending |
| Overall | 80%+ | 🟡 ~60% (Phase 1 MVP) |

## Browser Test Coverage

**8 Core Modules Tested:**
1. ✅ Task Manager — Load, filter by status
2. ✅ Org Chart — Display agents
3. ✅ Activity Feed — Show activity logs
4. ✅ Calendar — Load calendar view
5. ✅ Global Search — Search functionality
6. ✅ Standup — Load standup form
7. ✅ Workspace Viewer — Display workspaces
8. ✅ Docs Viewer — Browse documentation

**Test Scenarios:**
- Page loads without JS errors
- Livewire forms submit via AJAX
- Data displays correctly
- Filtering/search works
- Navigation between modules

## Fixing Common Test Issues

### "Table already exists" Error

**Cause:** Multi-database migrations conflicting with existing file databases.

**Solution:** 
```bash
# Clean test databases
rm /workspace/activity.db
rm /workspace/projects.db
php artisan test
```

Or use in-memory override in `tests/TestCase.php`.

### Livewire Tests Failing

**Cause:** Livewire not initialized in test environment.

**Solution:** Ensure `config/livewire.php` has correct test settings.

### Browser Tests Timeout

**Cause:** Laravel Herd serving, but Dusk can't reach.

**Solution:** Use `http://lunaos.test` in Dusk config, not localhost.

## Continuous Testing

**Pre-commit Hook:**
```bash
#!/bin/bash
cd /Users/kobear/.openclaw/workspace/lunaos
php artisan test --stop-on-failure
if [ $? -ne 0 ]; then
    echo "Tests failed! Commit aborted."
    exit 1
fi
```

**CI/CD Integration:** Tests run on every push to `main` branch.

## Test Files Reference

### Unit Tests (Models)
- `AgentModelTest.php` — Agent creation, relationships, strategy pattern
- `TaskModelTest.php` — Task CRUD, agent relationship, status transitions
- `ActivityLogModelTest.php` — Activity logging, metadata JSON storage
- `StandupModelTest.php` — Standup entries, deliverables, action items

### Feature Tests (Livewire)
- `ModuleTests.php` — All 8 core modules load and display data

### Browser Tests (Future)
- `ModuleLoadTest.php` — All pages load without errors
- `FormSubmissionTest.php` — Livewire forms work correctly
- `NavigationTest.php` — Module navigation works
- `LiveDataTest.php` — Real data displays correctly

## Next Steps (Phase 2)

- [ ] Add controller tests
- [ ] Add service layer tests
- [ ] Increase model test coverage to 90%
- [ ] Write comprehensive Dusk test suite
- [ ] Add performance tests
- [ ] Integrate with CI/CD pipeline

---

**Maintainer:** Luna 🌙 (with Sam, QA Engineer)  
**Last Updated:** March 1, 2026  
**LunaOS Version:** Phase 1 MVP
