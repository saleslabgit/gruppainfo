# Report: TASK-2026-08-19-09

Status: done

## Summary

Closed the Stage 4 stale-session gap with a protected-route pre-auth middleware. It reads the current web guard's authentication identifier through `SessionGuard::getName()`, inspects the corresponding `User` including soft-deleted records, and fully logs out/invalidates the current session before redirecting when the record is deleted or missing.

Registered the middleware ahead of Laravel's standard authentication middleware in both the protected route declaration and the HTTP kernel priority list. This explicit priority is required because Laravel otherwise sorts middleware implementing `AuthenticatesRequests` ahead of unlisted custom route middleware.

Added real HTTP login/session regressions proving soft-delete revocation survives restore, a missing user is revoked safely, a normal guest remains a normal guest, the session identifier is rotated, the guard authentication key is removed, and the resolved kernel priority remains pre-auth first.

## Changed Files

- `app/Http/Middleware/RevokeStaleAuthenticatedSession.php` — pre-auth stale identity inspection and secure current-session revocation.
- `bootstrap/app.php` — middleware alias and explicit priority before `AuthenticatesRequests`.
- `routes/web.php` — stale-session middleware applied only to protected internal routes before `auth` and `eligible`.
- `tests/Feature/StaleAuthenticatedSessionTest.php` — soft-delete/restore, missing-user, guest, session rotation/key removal, fresh-login, and kernel-priority regressions.
- `docs/architecture.md` — factual protected request pipeline and provider boundary.
- `.ai/report.md` — this report.

## Checks

- Focused `StaleAuthenticatedSessionTest`, `AuthenticationAccessTest`, and `UserSessionInvalidatorTest` — passed with 24 tests / 136 assertions after the priority correction.
- `docker compose exec app composer check` — passed after the final implementation: Pint checked 71 files, Larastan/PHPStan reported no errors, and the full isolated-MySQL suite passed with 56 tests / 603 assertions.
- `docker compose exec app composer check-platform-reqs` — passed for PHP 8.2.32 and all required extensions.
- `git diff --check` — passed.
- Real Chromium session flow — fresh psychologist login reached `/cabinet`; after a temporary soft delete, the next protected request redirected to `/login` with the generic access-ended message; after restoring the same row, the unchanged browser session still redirected to login; a fresh login restored access.
- Browser console during the regression flow — zero errors and warnings.
- Local seed psychologist was restored to `deleted_at=null` immediately after the browser mutation.

## Facts

- The middleware does not call the normal user provider to authenticate a deleted user and does not change provider configuration.
- A request without the web guard's session identifier continues to standard `auth`, preserving the ordinary guest redirect without an access-ended message.
- Existing `EnsureUserIsEligible` remains the single normal policy check for approved/disabled state; the new layer checks only missing/soft-deleted identities that standard auth cannot resolve.
- Logout, CSRF regeneration, role boundaries, login eligibility/rate limiting, and `UserSessionInvalidator` behavior are unchanged.
- The HTTP regression uses normal POST login and retained client cookies rather than `actingAs()`; it asserts removal of the guard session key and response-cookie session ID rotation.
- Direct database-row deletion was not claimed in the HTTP regression: `phpunit.xml` intentionally forces the array session driver, and the current local `.env` uses the file driver. The existing focused `UserSessionInvalidatorTest` continues to cover database-session deletion directly.
- No Stage 5 UI/CRUD, schema, dependency, integration, email, provider, or design change was added.

## Assumptions

- The configured `web` guard remains Laravel's session guard, as established by Stage 4 configuration. The middleware fails explicitly if that architecture changes.

## Unknowns

- None that block this correction.

## Risks / Next Step

- No known Stage 4 stale-session security gap remains.
- Stage 4 can return to product/security acceptance; Stage 5 has not been started.
