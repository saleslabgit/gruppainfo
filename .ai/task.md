# Task: TASK-2026-08-19-09

Status: planned
Created from: d7950abdae8927ed118d576761dd997acf943b8b

## Title

Revoke stale authenticated sessions for deleted or missing users

## Goal

Close the single Stage 4 security acceptance gap found after `TASK-2026-08-19-08`.

A user who was authenticated and is then soft-deleted currently becomes invisible to Laravel's normal Eloquent auth provider before `EnsureUserIsEligible` runs. The protected request is redirected as a guest by the standard `auth` middleware, but the stale authenticated session itself is not guaranteed to be invalidated. If the same user record is later restored, that old browser session must never become authenticated again without a fresh login.

Fix this narrowly, preserve the existing Stage 4 authentication architecture, and do not start Stage 5.

## Facts

- Current `main` at task creation is `d7950abdae8927ed118d576761dd997acf943b8b` (`codex: TASK-2026-08-19-08 build authentication and access foundation`).
- Stage 1, Stage 2, and Stage 3 are accepted.
- Stage 4 is implemented but not yet accepted because of this stale-session security gap.
- Protected routes currently use middleware in the order `auth`, `eligible`, then role middleware.
- `App\Models\User` uses `SoftDeletes`.
- Laravel's normal Eloquent auth provider does not resolve a soft-deleted `User` through the standard guard lookup.
- `EnsureUserIsEligible` correctly invalidates the current session for users it can inspect as disabled/non-approved/ineligible, but it may never run for a soft-deleted/missing authenticated identity because `auth` stops first.
- Database sessions are the project default and `sessions.user_id` is indexed.
- `UserSessionInvalidator` already exists for deleting all database sessions belonging to a known user.
- No remember-me UX is implemented.

## Assumptions

- The safest correction is to detect a stale authenticated session identity before the normal `auth` middleware can short-circuit the request.
- Use Laravel's existing guard/session APIs; do not hard-code the internal session key string when the guard can provide it.
- Do not globally change the Eloquent user provider to include soft-deleted users. A deleted user must remain impossible to authenticate through normal login/provider resolution.
- Do not duplicate the full `approved` / `disabled` eligibility policy in multiple middleware layers. Existing `EnsureUserIsEligible` should remain the normal current-user eligibility check unless a minimal refactor removes duplication cleanly.

## Scope

### 1. Pre-auth stale-session revocation

Implement the smallest reusable request-level mechanism needed on protected application routes so an existing authenticated session identifier can be inspected even when the normal auth provider can no longer resolve its user.

Required behavior:

- if the request has no authenticated session identity, continue normally so standard `auth` performs the normal guest redirect;
- if the session contains a web-guard user identifier, inspect the corresponding `User` including soft-deleted records;
- if the user record is soft-deleted or no longer exists at all, revoke the current browser authentication/session before redirecting to login;
- invalidation must clear the stale authentication state, invalidate/regenerate the current session as appropriate, and regenerate the CSRF token consistently with the existing logout/revocation behavior;
- show only the existing non-sensitive access-ended message or an equivalent generic message;
- if the user exists and is not deleted, continue to the normal `auth` + `eligible` + role pipeline;
- do not grant protected access from this pre-auth check.

Wire this mechanism only where required for protected internal application routes. Do not change the public home page, `/login`, or `/ui-kit` behavior unnecessarily.

### 2. Preserve existing eligibility behavior

The Stage 4 rules must remain unchanged:

- login accepts only non-deleted, `approved`, enabled users;
- disabled, pending, and rejected authenticated users are revoked by the normal eligibility layer on the next protected request;
- psychologists cannot access `/admin`;
- administrators cannot access psychologist-only `/cabinet`;
- logout remains POST + CSRF and invalidates the current session;
- `UserSessionInvalidator` remains available for Stage 5 administrative disable/reject/delete actions.

Do not weaken or bypass the standard `auth` middleware.

### 3. Regression tests using a real session flow

Add focused regression coverage that proves the bug is actually closed using normal HTTP login/session behavior, not only `actingAs()`.

At minimum cover:

1. create an approved enabled psychologist;
2. authenticate through `POST /login` and confirm `/cabinet` is accessible;
3. soft-delete that user in storage while the client retains the authenticated session;
4. the next request to `/cabinet` redirects to `/login`, ends authentication, and invalidates the stale session state;
5. restore the same user record to an otherwise eligible state;
6. **without logging in again**, the same client/browser session still cannot access `/cabinet` and is redirected to login;
7. after a fresh login, access works again.

Also cover a genuinely missing user record (for example a force-deleted test user) so a session referencing a non-existent identity is cleared safely.

Where practical, assert the old database session row/auth session identifier is removed or replaced, not merely that the response status is a redirect.

Keep the existing disabled/pending/rejected/rate-limit/role/session tests green.

### 4. Report and factual documentation

Update `.ai/report.md` with the exact fix and actual verification results.

Update `docs/architecture.md` only if the middleware/request pipeline boundary materially changes and needs to be documented. Update `docs/project-status.md` only if necessary to keep the Stage 4 security statement factual. Avoid unrelated documentation churn.

Do not change `SPEC.md`; the approved roadmap and Stage 4 product requirements are already correct.

## Out Of Scope

- Stage 5 administrative psychologist CRUD.
- Adding disable/reject/delete admin actions.
- External questionnaire/API integration.
- Email/password setup/reset flows.
- Group/cabinet product functionality.
- New auth guards/providers or authentication packages.
- Remember-me functionality.
- Changing the user status matrix.
- Schema changes unless an unexpected blocker proves they are strictly required.
- UI redesign or new design-system components.
- Node/npm/Vite/CDN changes.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, current `SPEC.md`, and existing Stage 4 architecture.
- Keep the fix surgical and security-focused.
- Use Laravel's current session/guard APIs instead of depending on an undocumented hard-coded session key.
- Do not make soft-deleted users visible to normal login authentication.
- Do not turn a stale deleted-user session into a valid guest session that can later silently become authenticated again after restore.
- Preserve database sessions and current CSRF/logout semantics.
- Do not introduce new dependencies.

## Acceptance Criteria

1. A browser session authenticated before soft delete is fully revoked on the next protected request.
2. Restoring the same user does not reactivate that old session; a fresh login is required.
3. A stale session referencing a user record that no longer exists is safely revoked.
4. Normal guest requests still follow the standard login redirect behavior without being misclassified as revoked authenticated sessions.
5. Disabled, pending, rejected, role-boundary, logout, login-rate-limit, and session-regeneration behavior from TASK-08 remains unchanged and green.
6. Normal login still cannot authenticate a soft-deleted user.
7. The fix does not globally modify the auth provider to return trashed users.
8. No Stage 5/product CRUD, external integration, email, or unrelated functionality is introduced.
9. Focused regression tests and the full MySQL test suite pass.
10. Pint, Larastan/PHPStan, platform requirements, and `git diff --check` pass.
11. `.ai/report.md` accurately records the correction and verification.
12. Final diff contains only files necessary for this Stage 4 security correction.

## Checks

Run and report at minimum:

- focused authentication regression tests for soft-delete → protected request → restore → still logged out → fresh login;
- focused regression test for an authenticated session whose user record is subsequently missing;
- existing `AuthenticationAccessTest` and `UserSessionInvalidatorTest` coverage;
- full `php artisan test` on the isolated MySQL test database;
- `composer check` including Pint and Larastan/PHPStan;
- `composer check-platform-reqs`;
- `git diff --check`;
- if practical, a real browser/session check of login → delete/restore fixture → protected-route denial; do not claim browser verification if the tooling cannot safely perform the data mutation;
- final `git status --short`, full diff, and staged-file inspection.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, current Stage 4 `SPEC.md`, and relevant auth/session documentation;
- inspect `routes/web.php`, `bootstrap/app.php`, `EnsureUserIsEligible`, `UserSessionInvalidator`, `User`, Laravel auth/session configuration, and current Stage 4 tests;
- run `git log --oneline -5` and `git status --short`;
- confirm this is `TASK-2026-08-19-09` created from `d7950abdae8927ed118d576761dd997acf943b8b` and TASK-08 is implemented but not accepted;
- do not touch unknown local changes.

Before commit:

- complete the required tests/checks;
- update `.ai/report.md` with actual results only;
- inspect final status, full diff, and staged files;
- stage only task-related files;
- ensure no secrets, local data, browser artifacts, logs, caches, or unrelated files are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-09 revoke stale sessions for deleted users`

If the stale-session behavior cannot be made safe without a broader architecture change, report `blocked` instead of weakening authentication semantics.
