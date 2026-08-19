# Report: TASK-2026-08-19-04

Status: done

## Summary

Hardened user and payment status transitions against stale Eloquent instances. Both services now reload and lock the current database row inside a transaction, validate the requested transition against that locked state, update only the locked model, and refresh the caller-visible model after a successful commit.

Added focused MySQL regression coverage proving that stale user and payment instances cannot overwrite newer statuses. Existing status matrices, group transition behavior, schema, and project documentation remain unchanged.

## Changed Files

- `app/Domain/User/UserStatusTransitionService.php` — validates and writes user status against a freshly row-locked record in a database transaction, then refreshes the supplied model.
- `app/Domain/Payment/PaymentStatusTransitionService.php` — applies the same transaction and row-lock protection to payments.
- `tests/Feature/StatusTransitionServiceTest.php` — adds stale user/payment regression tests and verifies successful transitions return the committed status.
- `.ai/report.md` — replaces the prior task report with this correction report.

## Checks

- Focused `php artisan test --filter='stale_(user|payment)'` on the isolated MySQL test database — passed: 2 tests, 4 assertions.
- `docker compose exec -T app composer check` — passed:
  - Pint check passed for 60 files;
  - Larastan/PHPStan level 5 passed with no errors;
  - full isolated-MySQL suite passed: 28 tests, 431 assertions.
- `git diff --check` — passed.
- Docker service inspection — `app` and `db` healthy; `web` running.
- Final diff and staged-file inspection — completed before commit; only the two transition services, their focused test file, and this report are included.

## Facts

- User and payment services now follow the existing group-service concurrency pattern: transaction, `lockForUpdate()`, validation against the locked row, and update of that row only.
- A stale `pending` user can no longer overwrite an `approved` database status with `rejected`.
- A stale `pending` payment can no longer overwrite a `succeeded` database status with `failed`.
- Rejected stale transitions leave the committed database state unchanged.
- Successful transitions refresh and return the caller-provided model with the committed target status.
- No enum values, transition matrices, group transition code, database schema, dependencies, or project documentation changed.

## Assumptions

- The existing group transition implementation is the approved concurrency pattern, as stated by the task.

## Unknowns

- None that affect this correction.

## Risks / Next Step

- No known acceptance gap remains for this task.
- Browser/runtime acceptance was not run because the task changes domain concurrency logic only and explicitly does not require a browser pass.
