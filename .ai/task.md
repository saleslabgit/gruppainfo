# Task: TASK-2026-08-19-04

Status: planned
Created from: bf6d4386b844eabdfc74ff3c1ef47eef2a6f0f6d

## Title

Harden user and payment status transitions against stale models

## Goal

Fix the Stage 2 review blocker: `UserStatusTransitionService` and `PaymentStatusTransitionService` currently validate transitions against the status stored in the caller's Eloquent instance rather than the current locked database row.

After this task, stale model instances must not be able to bypass the approved transition matrices. User and payment transitions must validate and write against the current database state under a row lock, matching the concurrency-safety principle already used by `GroupStatusTransitionService`.

## Facts

- `TASK-2026-08-19-03` was implemented in commit `bf6d4386b844eabdfc74ff3c1ef47eef2a6f0f6d`.
- Stage 2 review found one blocker in user/payment transition concurrency handling.
- `UserStatusTransitionService` currently reads `$user->status` from the passed model and then saves that model.
- `PaymentStatusTransitionService` currently reads `$payment->status` from the passed model and then saves that model.
- A stale model can therefore validate a transition from an obsolete status and overwrite a newer database status.
- Example user failure: two instances are loaded as `pending`; one performs `pending → approved`; the stale second instance can currently perform `pending → rejected`, producing the forbidden effective transition `approved → rejected`.
- Example payment failure: two instances are loaded as `pending`; one performs `pending → succeeded`; the stale second instance can currently perform `pending → failed`, producing the forbidden effective transition `succeeded → failed`.
- `GroupStatusTransitionService` already reloads the row with `lockForUpdate()` inside a transaction and validates the actual current status.
- No other Stage 2 blocker was identified in the review.

## Assumptions

- Use the same simple transaction + row-lock approach already established for group transitions; do not introduce optimistic-lock columns, generic concurrency frameworks, or new dependencies.

## Unknowns

None that block this correction.

## Scope

### 1. User status transitions

Update `UserStatusTransitionService` so that each transition:

- executes inside a database transaction;
- reloads the target user from the database by primary key using `lockForUpdate()`;
- evaluates `canTransitionTo()` against the freshly locked row's current status;
- throws `InvalidStatusTransition` if the transition is not valid from the actual current status;
- writes the target status only to the locked/current row;
- returns/refeshes the caller-visible model so the returned result reflects the committed database state.

Do not change the approved `UserStatus` transition matrix.

### 2. Payment status transitions

Update `PaymentStatusTransitionService` with the same guarantees:

- database transaction;
- reload by primary key with `lockForUpdate()`;
- validate against the actual current database status;
- reject invalid transitions with `InvalidStatusTransition`;
- update only the locked/current row;
- return a model reflecting the committed state.

Do not change the approved `PaymentStatus` transition matrix.

### 3. Regression tests

Add focused MySQL tests proving stale models cannot bypass either transition matrix.

At minimum test:

1. **User stale instance**
   - create a user in `pending`;
   - load two independent Eloquent instances of that row;
   - transition the first `pending → approved`;
   - attempt `pending → rejected` using the stale second instance;
   - assert `InvalidStatusTransition` is thrown;
   - assert the database status remains `approved`.

2. **Payment stale instance**
   - create a payment in `pending`;
   - load two independent Eloquent instances;
   - transition the first `pending → succeeded`;
   - attempt `pending → failed` using the stale second instance;
   - assert `InvalidStatusTransition` is thrown;
   - assert the database status remains `succeeded`.

Keep the existing exhaustive status-matrix tests green.

If useful, add one focused assertion that successful transitions still return a model with the new committed status.

### 4. Report and documentation

- Replace `.ai/report.md` with the factual report for this correction.
- Do not change project documentation unless the implementation reveals that an existing Stage 2 document contains a now-inaccurate statement about transition behavior. Avoid documentation churn for unchanged facts.

## Out Of Scope

- Any changes to status enum values or transition matrices.
- Changes to `GroupStatusTransitionService` unless required only to keep shared tests consistent; do not refactor it gratuitously.
- New audit/history behavior for users or payments.
- Stage 3 or later functionality.
- Authentication, controllers, routes, UI, APIs, scheduler, payment-provider integration, or queue jobs.
- Database schema changes.
- New dependencies or generic locking/state-machine abstractions.
- Unrelated refactoring or cleanup.

## Constraints

- Follow `WORKFLOW.md` and `AGENTS.md`.
- Keep the diff surgical.
- Use the existing MySQL test environment, not SQLite.
- Do not change `SPEC.md`, `WORKFLOW.md`, `AGENTS.md`, or `.ai/task.md`.
- Do not alter approved status matrices.
- Do not commit secrets, local data, logs, caches, or unrelated artifacts.

## Acceptance Criteria

1. User transitions validate against a freshly loaded, row-locked database record inside a transaction.
2. Payment transitions validate against a freshly loaded, row-locked database record inside a transaction.
3. A stale `pending` user instance cannot overwrite a newer `approved` state with `rejected`.
4. A stale `pending` payment instance cannot overwrite a newer `succeeded` state with `failed` or another transition valid only from the stale state.
5. Rejected stale transitions leave the actual database state unchanged.
6. Existing allowed and forbidden transition behavior remains unchanged for fresh models.
7. Existing group transition behavior remains unchanged.
8. All automated tests pass on the isolated MySQL test database.
9. Pint and Larastan pass.
10. `.ai/report.md` accurately describes the correction and checks.
11. Final diff contains only files required for this correction.

## Checks

Run and report at minimum:

- focused stale-model regression tests;
- full `php artisan test` on the isolated MySQL test database;
- Pint in check mode;
- Larastan/PHPStan using the committed configuration;
- `composer check` if it remains the project aggregate quality command;
- `git diff --check`;
- final `git status`, diff, and staged-file inspection.

A browser/runtime acceptance pass is not required because this correction changes domain concurrency logic only.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, and `.ai/task.md`;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-04` is the latest relevant planner task and has not already been completed;
- do not touch unknown local changes.

Before commit:

- complete the required checks;
- update `.ai/report.md` with actual results;
- inspect `git status --short`, full diff, and staged files;
- stage only files related to this task;
- ensure no secrets or unrelated artifacts are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-04 harden status transitions against stale models`

If safe completion is impossible, report `partial`, `blocked`, or `failed` instead of claiming success.
