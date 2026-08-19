# Task: TASK-2026-08-19-08

Status: planned
Created from: 54dd02157cce63ee4aec0bbaa6d802b12d4b18f9

## Title

Build Stage 4 authentication and access foundation

## Goal

Implement the revised Stage 4 from `SPEC.md`: a complete internal session-authentication and access-control foundation that can be tested entirely inside the Laravel application before any external form, email-onboarding, SMTP, or bank integration is introduced.

After this task, the development administrator and a development psychologist must be able to log in through the real application UI, reach only their permitted protected area, log out safely, and lose access immediately when the account becomes ineligible. The result must provide reusable authentication/session boundaries for the upcoming internal CRUD stages without implementing Stage 5 administrator CRUD or Stage 6 cabinet functionality.

## Facts

- Current `main` at task creation is `54dd02157cce63ee4aec0bbaa6d802b12d4b18f9`.
- Stage 1, Stage 2, and Stage 3 are completed and accepted.
- `SPEC.md` has been reordered to an internal-first roadmap: Stage 4–10 build and test the internal product before public-site forms, email, and bank integration.
- Current Stage 4 is **Authentication & Access Foundation** only. External questionnaire intake is now Stage 11; email/password setup is Stage 12; bank integration starts at Stage 13.
- `App\Models\User` already extends Laravel `Authenticatable`, uses `gp_users`, casts `status` to `UserStatus`, and hashes `password` through the model cast.
- Laravel's configured `web` guard already uses the session driver and the Eloquent `User` provider.
- Sessions already use the `database` driver by default.
- The existing `sessions` table has nullable indexed `user_id`, so per-user session invalidation can be implemented without a new session schema.
- Development/testing seeding currently creates an approved, enabled administrator only.
- The current UI baseline includes reusable password input, buttons, alerts/cards, application shell primitives, responsive behavior, local Montserrat/Lucide, and no frontend build step.
- The design system and UI Kit are mandatory for all new interface work.
- No product login/logout, protected admin/cabinet routes, role middleware, or application-level access-revocation middleware currently exists.

## Assumptions

- Use Laravel's existing session authentication directly. Do not add Breeze, Jetstream, Fortify, Sanctum, starter kits, authentication packages, or another guard/provider unless an actual repository constraint proves the default `web` guard insufficient.
- Use one login form for both roles. After successful authentication, redirect an administrator to the minimal protected admin acceptance surface and a psychologist to the minimal protected cabinet acceptance surface.
- The normal product rule for protected access is: active non-deleted `User`, `status=approved`, `disabled=false`. The same eligibility rule applies to administrator accounts unless a later approved requirement changes it.
- `admin` remains the role discriminator for Stage 4. Do not introduce a role/permission package or a new roles table.
- A small reusable session-invalidating service/helper is justified because Stage 5 must invoke the same behavior when an administrator disables/deletes an account. Use the existing database-session structure rather than a generic security framework.
- Development/testing psychologist credentials may be seeded idempotently like the existing administrator. They must never be created in production environments.
- Minimal `/admin` and `/cabinet` pages exist only as authentication/authorization acceptance surfaces. Do not build the Stage 5 admin CRUD or Stage 6 psychologist dashboard here.

## Unknowns

None that block this task.

## Scope

### 1. Session login

Implement a normal Laravel web login flow using the existing `web` guard.

At minimum:

- `GET /login` for guests;
- `POST /login` with CSRF and server-side validation;
- email + password authentication through Laravel's authentication facilities;
- a generic authentication error that does not reveal whether an email exists;
- session ID regeneration after successful login;
- login throttling/rate limiting keyed safely by credentials/IP using normal Laravel facilities;
- already-authenticated users should not use the guest login flow as an alternate navigation path.

Do not add registration, public questionnaire intake, forgot-password/setup-email behavior, or external API auth in this task.

### 2. Logout

Implement logout as a protected POST action:

- call Laravel logout through the active guard;
- invalidate the current session;
- regenerate the CSRF token;
- redirect to the login/public surface with a clear result;
- do not expose state-changing logout through GET.

### 3. Access eligibility middleware

Add reusable middleware for every protected application request after normal authentication.

For the authenticated user, protected access is allowed only when:

- the user still exists and is not soft-deleted;
- `status === UserStatus::Approved`;
- `disabled === false`.

If an authenticated session becomes ineligible:

- terminate authentication immediately on the next protected request;
- invalidate the current session and regenerate the CSRF token;
- redirect to login with a clear but non-sensitive access message;
- never leave an ineligible user authenticated in a protected response.

Do not write `status` directly from this middleware. It only enforces current state.

### 4. Role boundaries

Create explicit protected route boundaries for the two current roles.

Use simple middleware/authorization appropriate to the current two-role model:

- `/admin` route group: authenticated + access-eligible + `admin=true`;
- `/cabinet` route group: authenticated + access-eligible + psychologist role (`admin=false`);
- guest access redirects to login;
- a psychologist attempting admin routes receives 403 (or the project's equivalent explicit denial) rather than data leakage;
- do not create a permissions framework.

Create only minimal role-specific acceptance pages using the shared design system. They should make the current signed-in identity/role and logout action testable, but must not implement Stage 5/6 product content.

### 5. Reusable per-user session invalidation

Implement the smallest shared mechanism needed to invalidate all active database sessions for one `User`.

Requirements:

- operate against the configured database session table/connection rather than hard-coding unrelated infrastructure assumptions;
- use the existing indexed `sessions.user_id` relationship;
- be callable independently of the current request so Stage 5 can invoke it when disabling/deleting users;
- safely handle a user with zero or multiple sessions;
- do not add Redis/session packages or a generic session-management subsystem;
- do not add remember-me UI in this task. If the implementation also rotates an existing remember token for defense in depth, keep it simple and test the actual behavior claimed.

Add direct automated coverage proving multiple sessions belonging to the target user are removed without removing another user's sessions.

### 6. Development/testing psychologist seed

Add an idempotent development/testing-only psychologist seed analogous to `DevelopmentAdminSeeder`.

Requirements:

- create one approved, enabled, non-admin psychologist with a known local-only password;
- expose the values through the existing `config/seed.php` pattern, with sensible `.test`/local-only defaults and optional env overrides;
- call it only in `local`/`testing`, never production;
- repeat seeding must update/create safely without duplicates;
- do not create a product-facing manual password feature to support testing.

Keep the existing development administrator seed behavior intact.

### 7. Login and acceptance UI

Use the actual Stage 3 design system and reusable Blade components.

At minimum:

- login page uses the approved form/card/button/alert primitives and password reveal behavior already implemented;
- validation/authentication errors are presented through shared components;
- minimal admin/cabinet acceptance pages use the existing responsive shell/page structure where appropriate;
- logout is available through a proper POST form/action;
- desktop and mobile behavior must conform to `DESIGN_SYSTEM.md` and `uikit/index.html`.

If a genuinely necessary generic UI component is missing, add it to the shared UI namespace/design system before using it. Do not create page-specific visual substitutes.

### 8. Authentication and authorization tests

Add focused feature/integration tests on the existing MySQL test environment.

Cover at minimum:

1. guest can render login;
2. approved enabled development-equivalent psychologist credentials authenticate and redirect to cabinet;
3. approved enabled administrator credentials authenticate and redirect to admin;
4. wrong password fails with a generic error and no authenticated session;
5. pending user cannot authenticate into protected product access;
6. rejected user cannot authenticate into protected product access;
7. disabled approved user cannot authenticate into protected product access;
8. psychologist cannot access admin routes;
9. administrator cannot accidentally use psychologist-only cabinet route if the role boundary is intentionally exclusive;
10. guest protected-route request is redirected to login;
11. POST logout invalidates the session and protected routes become inaccessible;
12. an already-authenticated user who becomes disabled is logged out on the next protected request;
13. an already-authenticated user whose current state is otherwise no longer eligible is denied/revoked on the next protected request;
14. per-user session invalidation deletes all target sessions and preserves unrelated sessions;
15. login rate limiting activates after the configured threshold and resets/behaves correctly after successful authentication as appropriate to the chosen standard Laravel pattern;
16. production seeding does not create the development psychologist.

Where practical, assert session regeneration/security behavior rather than merely response status.

### 9. Documentation and report

Update only documentation made factual by this implementation:

- `docs/architecture.md` — authentication/access middleware and session invalidation boundary;
- `docs/development.md` — local login URLs and development admin/psychologist seed credentials/config variables without adding real secrets;
- `docs/project-status.md` — Stage 4 implemented state and Stage 5 as next stage;
- `.env.example` / `config/seed.php` only as required for local seed configuration;
- `.ai/report.md` — factual Stage 4 report, changed files, tests, browser verification, and remaining gaps.

Do not change the newly approved Stage 4–15 roadmap in `SPEC.md` unless implementation exposes a direct factual contradiction that cannot be resolved otherwise.

## Out Of Scope

- Public psychologist questionnaire API or any `/api/v1` integration route.
- HMAC, `X-Timestamp`, `X-Request-Id`, integration secret, public-site documentation.
- Incoming participant application API.
- SMTP, mail sending, queued email jobs, password setup/reset emails or tokens.
- Product registration UI.
- Stage 5 psychologist CRUD or document management screens.
- Stage 6 real psychologist cabinet pages/data presentation.
- Group CRUD/moderation.
- Dictionaries/settings CRUD.
- Scheduler/lifecycle/application workflows.
- Bank/payment integration.
- New role/permission framework or authentication package.
- Remember-me UX unless already required by an existing explicit project requirement (none currently is).
- Product-facing ability for an administrator to assign/set a psychologist password manually.
- Node/npm/Vite/runtime CDN/frontend framework changes.
- Unrelated domain/schema refactoring.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, current `SPEC.md`, `DESIGN_SYSTEM.md`, and `uikit/index.html`.
- Use current Laravel 12 APIs and the existing `web` session guard/provider.
- Keep authentication/session logic out of Blade templates.
- Keep controllers thin; access rules belong in middleware/services/policies as appropriate.
- Preserve the existing `UserStatus` enum and approved transition matrix; Stage 4 enforces status but does not redefine it.
- Preserve database session storage.
- Do not write `accept` directly or introduce it into authentication logic.
- Do not expose whether a login email exists through error text.
- Do not weaken CSRF/session security for convenience.
- Do not create local-only users in production.
- Do not add new dependencies unless an actual blocker is proven and reported first.
- Keep the diff limited to authentication/access foundation, local test identity support, relevant shared UI use, tests, report, and factual docs.

## Acceptance Criteria

1. A real shared login page authenticates approved/enabled admin and psychologist users through Laravel's existing session guard.
2. Successful login regenerates the session and redirects by role to the correct protected acceptance surface.
3. Failed login uses a generic error and does not authenticate the user.
4. Login is rate-limited using standard Laravel facilities.
5. Logout is POST+CSRF, invalidates the current session, regenerates the token, and removes protected access.
6. Every protected request requires a non-deleted, approved, enabled current user.
7. A user who becomes ineligible while logged in loses access on the next protected request.
8. `/admin` is inaccessible to psychologists; `/cabinet` is restricted to the psychologist role according to the defined Stage 4 boundary.
9. A reusable service/helper can invalidate every database session belonging to one user without deleting other users' sessions.
10. Development/testing seeding creates an approved psychologist with configurable local credentials and remains idempotent; production does not seed that account.
11. Login/admin/cabinet acceptance UI uses shared design-system components with no page-specific design fork.
12. No public integration route, email/password-setup flow, Stage 5 CRUD, or Stage 6 product page is implemented.
13. Existing Stage 1–3 behavior remains green, including `/ui-kit` environment protection and design-system checks.
14. Focused auth/access tests and the full MySQL test suite pass.
15. Pint, Larastan/PHPStan, platform requirements, and applicable runtime/browser checks pass.
16. `docs/project-status.md` accurately records Stage 4 as implemented only after the implementation is actually complete.
17. `.ai/report.md` accurately records what was implemented and actually verified.
18. Final diff contains only files required by Stage 4.

## Checks

Run and report at minimum:

- focused authentication/access feature tests;
- focused per-user database session invalidation test;
- seed/idempotency/environment tests for the development psychologist;
- full `php artisan test` on the isolated MySQL test database;
- `composer check` including Pint and Larastan/PHPStan;
- `composer check-platform-reqs`;
- `git diff --check`;
- local HTTP/browser check for `/login`, successful admin login, successful psychologist login, role denial, logout, and access revocation;
- browser desktop and mobile verification of login and minimal protected acceptance pages if existing browser tooling is available;
- browser console/network inspection to ensure no runtime CDN/build-tool regression;
- final `git status --short`, full diff, and staged-file inspection.

The user remains the product tester for final visual acceptance of the new login/access surfaces.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, current `SPEC.md` Stage 4 and relevant §7/§29 rules, `DESIGN_SYSTEM.md`, `docs/architecture.md`, `docs/development.md`, and `docs/project-status.md`;
- inspect `User`, `UserStatus`, current transition services, `config/auth.php`, `config/session.php`, framework/session migrations, current seeders/config, routes, middleware bootstrap, existing Stage 3 UI components, and relevant tests;
- inspect `uikit/index.html` for the login/form/shell visual intent as required by governance;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-08` is the current planned task created from `54dd02157cce63ee4aec0bbaa6d802b12d4b18f9` and has not already been completed;
- do not touch unknown local changes.

Before commit:

- complete all applicable automated/runtime/browser checks;
- update `.ai/report.md` with actual results only;
- inspect final Git status, full diff, and staged files;
- stage only files related to this task;
- ensure no secrets, browser artifacts, screenshots, logs, caches, external integration test data, or unrelated files are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-08 build authentication and access foundation`

If safe completion is impossible, report `partial`, `blocked`, or `failed` instead of claiming success.
