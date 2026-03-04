# Architecture Decision Record Template

**Template:** Use this for all architectural decisions (Phase 2A, 2B, 3A, etc.)  
**Owner:** Jordan (System Architect)  
**Approval:** Kyle (Product Owner)

---

# ADR-{NNN}: {Short Descriptive Title}

**Status:** {proposed | accepted | rejected | deprecated | superseded}  
**Date:** {YYYY-MM-DD}  
**Author:** Jordan (System Architect)  
**Reviewers:** {Kyle, Luna, others as appropriate}  
**Phase:** {Phase 2A, Phase 2B, etc.}  
**Tags:** {database, api, migration, architecture, etc.}

---

## Context

{
  What is the problem or opportunity we're addressing?
  Why is this decision necessary now?
  What forces are at play (technical, business, timeline)?
  What events led to this decision?
}

---

## Problem Statement

{Clear, concise problem definition in 1-2 sentences.}

---

## Constraints

### Technical
- {limitations, dependencies, compatibility requirements}
- {existing systems that must be supported}
- {technology restrictions}

### Business
- {budget constraints}
- {timeline requirements}
- {strategic goals this must support}

### Operational
- {team capacity}
- {skill sets available/not available}
- {tooling limitations}

---

## Options Considered

### Option 1: {Descriptive Name}

**Description:**
{What is this approach? How does it work? What does implementation look like?}

**Architecture:**
{How does this change system structure? Diagram links if helpful.}

**Pros:**
- ✅ {benefit 1 with explanation}
- ✅ {benefit 2 with explanation}
- ✅ {benefit 3 with explanation}

**Cons:**
- ❌ {drawback 1 with explanation}
- ❌ {drawback 2 with explanation}

**Effort Estimate:** {S/M/L or hours/days/weeks}

**Risks:**
- ⚠️ {risk 1} — Likelihood: {High/Med/Low}, Impact: {High/Med/Low}
- ⚠️ {risk 2} — Likelihood: {High/Med/Low}, Impact: {High/Med/Low}

**Mitigations:**
- {How to reduce risk 1}
- {How to reduce risk 2}

---

### Option 2: {Descriptive Name}

{Same structure as Option 1}

---

### Option 3: {Name} (if applicable)

{Same structure as Option 1}

**Or if only 2 options:**
{Remove this section if only 2 options considered}

---

## Comparison Matrix

| Criteria | Option 1 | Option 2 | Option 3 |
|----------|----------|----------|----------|
| **Effort** | {S/M/L} | {S/M/L} | {S/M/L} |
| **Risk** | {H/M/L} | {H/M/L} | {H/M/L} |
| **Maintainability** | {H/M/L} | {H/M/L} | {H/M/L} |
| **Flexibility** | {H/M/L} | {H/M/L} | {H/M/L} |
| **Timeline** | {Estimate} | {Estimate} | {Estimate} |
| **Team Capacity** | ✅/⚠️/❌ | ✅/⚠️/❌ | ✅/⚠️/❌ |

---

## Decision

**Selected Option:** {Option N} — {Option Name}

**Approval Date:** {YYYY-MM-DD}  
**Approved By:** Kyle Obear (Product Owner)

### Rationale

{
  Why this option over others?
  What trade-offs were accepted?
  What values guided this decision?
  What criteria were most important (cost, speed, quality, maintainability)?
}

### Constraints & Conditions

{
  Any specific conditions or constraints on implementation:
  - Must complete by {date}
  - Must not exceed {budget}
  - Must maintain {compatibility}
  - Must include {specific feature}
}

---

## Implications

### Architecture

{How does this change the system structure? What patterns are introduced? What patterns are deprecated?}

### Data

- **Schema Changes:** {new tables, modified tables, deprecated tables}
- **Migrations:** {what data moves, in what order}
- **Data Integrity:** {how to ensure data safety}
- **Backward Compatibility:** {how long must old schema be supported?}

### API

- **New Endpoints:** {list or reference}
- **Breaking Changes:** {list with migration path}
- **Versioning:** {how are breaking changes versioned?}
- **Documentation:** {what docs need updates?}

### Testing

- **Unit Tests:** {what new tests required?}
- **Integration Tests:** {what flows to test?}
- **Migration Tests:** {how to test data migration?}
- **Browser/E2E Tests:** {what user flows to validate?}
- **Performance Tests:** {what benchmarks to hit?}
- **Coverage Requirements:** {minimum % required}

### Operations

- **Deployment:** {what changes to deployment process?}
- **Monitoring:** {what new metrics/alerts needed?}
- **Rollback:** {how to undo if deployment fails?}
- **Documentation:** {runbooks, guides to create/update}

### Security

- **New Attack Vectors:** {what new risks introduced?}
- **Mitigations:** {how to address new risks?}
- **Compliance:** {any compliance implications?}

### Performance

- **Expected Impact:** {faster, slower, no change? By how much?}
- **Benchmarks:** {what to measure, what targets to hit}
- **Scaling:** {how does this affect horizontal/vertical scaling?}

### Future Work

{
  What doors does this open?
  What doors does this close?
  What technical debt is incurred?
  What follow-up work is required?
}

---

## Migration Strategy

{For changes involving data moves or breaking changes. Remove if not applicable.}

### Pre-Migration

- [ ] Backup procedure: {command/script}
- [ ] Staging test: {steps to test on staging}
- [ ] Rollback test: {steps to verify rollback works}
- [ ] Communication plan: {who needs to know?}

### Migration Steps

1. {step 1 with command/code}
2. {step 2 with command/code}
3. {step 3 with command/code}
4. {verification step}

### Success Criteria

- {metric 1: e.g., "All migrations complete without errors"}
- {metric 2: e.g., "Data integrity verified (checksums match)"}
- {metric 3: e.g., "All tests passing (100%)"}
- {metric 4: e.g., "Application loads without errors"}

### Rollback Procedure

{
  Step-by-step instructions to undo migration if it fails:
  1. {rollback step 1}
  2. {rollback step 2}
  3. {verification step}
}

**Rollback Decision Criteria:**
{When should rollback be triggered?}
- If {condition 1} → Rollback
- If {condition 2} → Rollback

---

## Testing Strategy

### Unit Tests

{What new unit tests are required? What existing tests need updates?}

### Integration Tests

{What integration flows must be tested?}

### Migration Tests

{How will data migration be validated?}

### Browser/E2E Tests

{What user flows must work after this change?}

### Performance Tests

{What performance benchmarks must be hit?}

### Quality Gates

- **Minimum Test Coverage:** {X%}
- **Performance Benchmarks:** {specific metrics and targets}
- **Security Requirements:** {security checks that must pass}
- **Code Review:** {who must approve?}

---

## Compliance

### Architecture Principles

- ✅ Aligns with {principle 1: e.g., "Simplicity over flexibility"}
- ✅ Aligns with {principle 2: e.g., "Pragmatic optimism"}
- ✅ Aligns with {principle 3: e.g., "Data safety first"}}

### Design Patterns

- {pattern 1: e.g., "Repository pattern for data access"}
- {pattern 2: e.g., "Service layer for business logic"}

### Coding Standards

- {standards that apply: PSR-12, Laravel conventions, etc.}

---

## References

- **Related ADRs:** {ADR-NNN, ADR-MMM}
- **Documentation:** {links to relevant docs}
- **Research:** {papers, articles, blog posts consulted}
- **Discussions:** {meeting notes, Slack threads, etc.}

---

## Change Log

| Date | Author | Change | Reason | Approvers |
|------|--------|--------|--------|-----------|
| YYYY-MM-DD | Jordan | Initial ADR created | Phase 2A kickoff | — |
| YYYY-MM-DD | Kyle | Approved Option 2 | Better long-term maintainability | Kyle |
| YYYY-MM-DD | Jordan | Added migration testing requirement | Post-incident learning | Kyle |

---

## Notes

{
  Additional context that doesn't fit elsewhere:
  - Informal discussions
  - Off-band decisions
  - Lessons learned during implementation
  - Follow-up questions
}

---

## Implementation Status

- [ ] ADR approved
- [ ] Implementation started
- [ ] Implementation complete
- [ ] Tests passing
- [ ] Migration executed
- [ ] Documentation updated
- [ ] ADR review (post-implementation)

**Implementation Date:** {when coding started}  
**Completion Date:** {when deployed to production}  
**Implemented By:** {team members who did the work}

---

## Post-Implementation Review

{To be completed after implementation. What did we learn? What would we do differently?}

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

**ADR Review Date:** {Date of post-implementation review}  
**Reviewed By:** {Jordan, Kyle, Luna}
