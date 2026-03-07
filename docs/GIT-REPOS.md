# Git Repository Configuration

**Purpose:** Central reference for all repositories associated with LunaOS ecosystem projects.

**Last Updated:** March 7, 2026

---

## 🎯 Primary Active Repositories

### **LunaOS Dashboard Platform**
- **Repo:** `https://github.com/kjbear/lunaos.git`
- **Purpose:** Main LunaOS dashboard for AI agent team visibility
- **Branches:** `main` (production), feature branches (`<agent>/<type>-<description>`)
- **CI/CD:** GitHub Actions (Pint, Tests, Branch validation)
- **Status:** ✅ ACTIVE - Primary development target
- **Workflow:** `lunaos/docs/GIT-WORKFLOW.md`

### **SPA (Status Page Aggregator)**
- **Repo:** `https://github.com/kjbear/spa.git` (TODO: create)
- **Domain:** onewatch.cloud
- **Purpose:** Multi-tenant SaaS status page aggregator + VRM
- **Stack:** Laravel + Go collector + PostgreSQL + HTMX
- **Status:** 📋 PLANNED - Phase 1 MVP (~100 pts, 8 weeks)
- **Workflow:** TBD (will follow LunaOS pattern)

### **IHSSP (In-Home Services SaaS Platform)**
- **Repo:** `https://github.com/kjbear/ihssp.git` (TODO: create)
- **Purpose:** Multi-tenant platform for agencies managing PT/Speech/Special Ed services
- **Stack:** Laravel + Flutter + Stripe billing
- **Status:** ⏳ BACKLOG - Requirements complete, awaiting architect approval
- **Workflow:** TBD (will follow LunaOS pattern)

---

## 🔧 Supporting Repositories

### **Executive Board**
- **Repo:** `https://github.com/kjbear/executive-board.git`
- **Purpose:** Testing ground for executive board AI agent system (6 C-Suite members)
- **Status:** 🧪 EXPERIMENTAL - Used for prototyping
- **Note:** NOT for LunaOS production code

### **QuickClose**
- **Repo:** `https://github.com/kjbear/quickclose.git`
- **Purpose:** TODO - Define scope
- **Status:** 📁 EXISTING - Needs documentation

### **Awesome Status Pages**
- **Repo:** `https://github.com/kjbear/awesome-status-pages.git`
- **Purpose:** Curated list of status page examples/inspiration
- **Status:** 📚 REFERENCE - Research/examples

### **LunaOS Staging**
- **Repo:** `https://github.com/kjbear/lunaos-staging.git`
- **Purpose:** Staging environment for LunaOS (pre-production testing)
- **Status:** 🔄 SYNCED - Mirror of lunaos main for testing

---

## 📋 Repository Creation Checklist

When creating a NEW repository, follow this checklist:

### **Pre-Creation**
- [ ] Define project name and purpose
- [ ] Choose tech stack
- [ ] Document in `FUTURE_PROJECTS.md`
- [ ] Get Kyle's approval

### **Repository Setup**
```bash
# Create repo on GitHub
gh repo create kjbear/<project-name> --public --source=. --push

# Initialize with LunaOS template
cp -r ../lunaos/.github .
cp -r ../lunaos/docs/GIT-WORKFLOW.md docs/
cp ../lunaos/.editorconfig .
cp ../lunaos/.gitignore .
```

### **Configuration**
- [ ] Add `docs/GIT-WORKFLOW.md` (from lunaos)
- [ ] Create `AGENTS.md` for project-specific agents
- [ ] Create `TOOLS.md` for environment specifics
- [ ] Add this `GIT-REPOS.md` reference
- [ ] Configure CI/CD (copy from lunaos if applicable)
- [ ] Add `.github/workflows/ci.yml` (validate, lint, test)
- [ ] Set branch protection on `main`

### **Documentation**
- [ ] Update `FUTURE_PROJECTS.md` with repo URL
- [ ] Create `README.md` with project overview
- [ ] Add to `workspace/PROJECTS.md` index
- [ ] Create initial sprint plan in `docs/`

### **Team Onboarding**
- [ ] Add agent configurations (Dave, Maya, Sam, etc.)
- [ ] Document workflow in project's `AGENTS.md`
- [ ] Test PR workflow with sample branch
- [ ] Verify CI runs on PR creation

---

## 🔴 CRITICAL: Repository Verification

**BEFORE pushing to ANY repo:**
```bash
cd /path/to/project
git remote -v
```

**Expected output:**
```
origin  https://github.com/kjbear/<correct-repo>.git (fetch)
origin  https://github.com/kjbear/<correct-repo>.git (push)
```

**If WRONG:**
```bash
# Fix the remote URL
git remote set-url origin https://github.com/kjbear/<correct-repo>.git

# Verify fix
git remote -v
```

**Lesson learned (Mar 7, 2026):** Dave's GitHub Sync PR initially went to `executive-board` instead of `lunaos` because the subagent didn't verify the repository. This caused confusion and rework. **Always verify before pushing!**

---

## 📊 Repository Status Dashboard

| Repo | Status | Branch Protection | CI/CD | Last Activity |
|------|--------|-------------------|-------|---------------|
| lunaos | ✅ Active | Yes | GitHub Actions | Today |
| executive-board | 🧪 Experimental | No | GitHub Actions | Today |
| lunaos-staging | 🔄 Staging | No | Manual sync | Feb 2026 |
| spa | 📋 Planned | TBD | TBD | Not started |
| ihssp | ⏳ Backlog | TBD | TBD | Not started |
| quickclose | 📁 Exists | TBD | TBD | Unknown |
| awesome-status-pages | 📚 Reference | No | None | Feb 2026 |

---

## 🎓 Best Practices

### **Always**
- ✅ Verify `git remote -v` before pushing
- ✅ Use branch naming convention: `<agent>/<type>-<description>`
- ✅ Create PR for all changes (no direct main pushes)
- ✅ Wait for CI to pass before merging
- ✅ Document new repos in this file

### **Never**
- ❌ Push directly to `main`
- ❌ Merge your own PRs without bypass (if you must, document why)
- ❌ Skip CI checks
- ❌ Mix code between repositories
- ❌ Assume you're in the right repo - ALWAYS verify

---

## 🔗 Related Files

- `lunaos/docs/GIT-WORKFLOW.md` - Detailed workflow guide
- `AGENTS.md` - Agent configurations and roles
- `FUTURE_PROJECTS.md` - Planned projects and timelines
- `workspace/PROJECTS.md` - Project index

---

**Maintained by:** Luna 🌙  
**Review Cycle:** Update whenever new repo created or project status changes
