## LunaOS Board Access

**Kanban Board:** http://lunaos.test/kanban

**Direct URL:** Open in browser → http://lunaos.test/kanban

### Test Data Loaded

7 tasks created across the pipeline:

| # | Task | Step | Agent | Priority |
|---|------|------|-------|----------|
| 1 | Hello World Component | QA | Sam | High |
| 2 | Fix login bug | Develop | Dave | Critical |
| 3 | Add user profile | Develop | Dave | Medium |
| 4 | Test checkout flow | QA | Sam | High |
| 5 | Security audit | Security | Security Bot | High |
| 6 | Deploy to staging | Staging | Chen | Medium |
| 7 | Production hotfix | Production | Chen | Critical |

### Features to Try

1. **Filter by Agent:**
   - Click "Dave" → See only dev tasks
   - Click "All" → See everything

2. **Search:**
   - Type "login" → Filter to login-related tasks
   - Type "deploy" → See deployment tasks

3. **Actions:**
   - Hover over task → "✓ Complete" button appears
   - Click complete → Auto-advances to next step/agent

4. **Auto-refresh:**
   - Toggle ON/OFF in top-right
   - Refreshes every 10 seconds when ON

### Next Steps

- DaveWorker will poll for tasks in "develop" step
- SamWorker will poll for "qa" step
- etc...
