## Description

[Provide a brief description of the changes in this PR. What problem does it solve? What feature does it add?]

## Related Issue

[Link to related issue or Feature Brief]

- Feature Brief: [Link]
- Related Issue: #[number]

## Type of Change

- [ ] 🐛 Bug fix (non-breaking change which fixes an issue)
- [ ] ✨ New feature (non-breaking change which adds functionality)
- [ ] 💥 Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] 📚 Documentation update
- [ ] 🔧 Configuration change
- [ ] ♻️ Refactoring (no functional changes)

## Checklist

### Documentation
- [ ] Feature Brief exists: [Link to Feature Brief]
- [ ] Technical Design exists (if applicable): [Link to Technical Design]
- [ ] PR template completed

### Code Quality
- [ ] Code follows Laravel conventions
- [ ] No `dd()`, `dump()`, or debug code left in
- [ ] No hardcoded credentials or secrets
- [ ] No commented-out code
- [ ] Self-review completed

### Testing
- [ ] Unit tests exist for new logic
- [ ] Feature tests exist for endpoints
- [ ] All tests pass locally (`php artisan test`)
- [ ] No breaking changes to existing tests

### Database
- [ ] Migration created and tested
- [ ] `down()` method reverses `up()`
- [ ] Seeder created for test data (if needed)
- [ ] No direct database modifications

### Environment
- [ ] `.env.example` updated with new variables (if needed)
- [ ] No new dependencies without approval
- [ ] Works on local (Herd) environment

### Security
- [ ] Input validation in place
- [ ] Authorization checked
- [ ] No sensitive data in logs
- [ ] No SQL injection vulnerabilities

## How to Test

[Step-by-step instructions for testing this PR]

1. Checkout this branch: `git checkout feature/branch-name`
2. Run migrations: `php artisan migrate`
3. Run seeders (if needed): `php artisan db:seed`
4. Start server: `php artisan serve`
5. Navigate to: `http://localhost:8000/...`
6. Test that: [expected behavior]

## Screenshots (if UI changes)

### Before
[Insert screenshot]

### After
[Insert screenshot]

## Breaking Changes

[Describe any breaking changes and migration steps for users]

**If no breaking changes:** No breaking changes.

## Additional Notes

[Any additional information that reviewers should know]

---

## For Reviewers

### Focus Areas
- [ ] Code correctness
- [ ] Test coverage
- [ ] Security implications
- [ ] Performance impact
- [ ] Documentation clarity

### Questions for Review
1. [Question for reviewer]
2. [Question for reviewer]

---

**By submitting this PR, I confirm:**
- [ ] I have completed the pre-submission checklist
- [ ] I have the necessary approvals for this type of change
- [ ] I understand this PR will be tested in a preview environment before merge
