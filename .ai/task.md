# Task: TASK-2026-08-19-14

Status: planned
Created from: 2cba80312e0118bbf7160f23b2cf241d5b107a32

## Title

Fix Stage 7 acceptance blockers

## Goal

Close the four confirmed Stage 7 acceptance blockers without redesigning or expanding the accepted group CRUD/moderation architecture.

After this correction:

- admin draft cleanup cannot delete a group that still has a successful non-refunded payment, and rejection UI warns about such a payment without introducing refund/bank functionality;
- groups and status history remain readable and correctly attributed after a psychologist is soft-deleted;
- shared Money Input has exactly one composite focus ring on its outer shell;
- a normally seeded local/testing environment can complete the real psychologist `draft -> moderation` flow, and any failed submission shows an explicit actionable validation error instead of appearing to do nothing.

Stage 7 remains unaccepted until all four blockers are closed.

## Facts

- Current `main` at task creation is `2cba80312e0118bbf7160f23b2cf241d5b107a32` (`codex: TASK-2026-08-19-13 implement Stage 7 group CRUD and moderation`).
- The Stage 7 implementation otherwise passed its reported full gate: 86 tests / 957 assertions, Pint and Larastan/PHPStan.
- Existing psychologist delete already blocks a linked payment with `status=succeeded`, but admin `GroupPolicy::cleanup()` currently checks only `draft/awaiting_payment` and therefore allows cleanup despite a successful payment.
- `SPEC.md` requires manual group deletion to be blocked while there is a successful payment without a refund mark. It also requires the rejection interface for a paid group with a successful payment to remind the administrator about manual refund handling and show payment data.
- `gp_payments` already contains `transaction_id`, `amount`, `currency`, `status`, `paid_at`, `refunded_at`, `refund_comment`, timestamps and soft delete. No payment schema change is needed.
- Stage 7 must not create payments or implement refunds/bank integration; Stage 13 owns that functionality.
- `Group::owner()` and `GroupStatusHistory::actor()` currently do not use `withTrashed()`. Stage 5 can soft-delete psychologists while preserving their related data.
- Admin group list/detail accesses group owner identity. Status Timeline currently treats a missing actor relation as `Система`, even if the history row has `actor_type=user`.
- Existing `UserActionHistory` already preserves soft-deleted target/actor relations with `withTrashed()` and is the repository precedent for historical attribution.
- Shared Money Input uses an outer `:focus-within` ring, but its nested native `<input>` also matches the global `input:focus-visible` rule, producing a second focus ring. `DESIGN_SYSTEM.md` requires composite controls to render focus only on the outer shell.
- `SubmitGroupRequest` correctly performs the complete-data validation and existing automated tests prove that a valid group transitions through `GroupStatusTransitionService` from `draft` to `moderation`.
- A normal `DatabaseSeeder` currently creates only the `group_format` and `gender` dictionary definitions; it creates no items for them. Stage 7 browser verification used temporary dictionary items and removed them afterwards.
- Therefore a freshly seeded local environment does not have selectable values for the required `format_id` and `gender_id`, so a real product-owner manual Stage 7 submission cannot be completed from normal seed state.
- Failed submit validation returns to the group detail with `$errors`, but current cabinet layout/detail shows only `session('status')` / `session('error')`; validation errors from the submit action are not surfaced, making failure look like a no-op.
- Production dictionary item values remain unapproved and must not be invented. Stage 8 owns real dictionary/settings administration.

## Assumptions

- A payment blocks destructive cleanup when it represents a successful placement that has not been marked refunded. Use the existing payment status/refund facts; do not invent a second payment lifecycle.
- A `refunded` payment must not keep the cleanup blocked merely because it was successful in the past. Preserve the existing domain status semantics rather than adding ad-hoc flags.
- Rejection itself remains allowed when such a payment exists; the requirement is a visible warning/reminder and payment facts, not a new Stage 7 refund action.
- For historical status attribution, `Система` is valid only when the history row is actually `actor_type=system`. A soft-deleted user actor remains a user actor and must remain identifiable from retained data.
- To make Stage 7 manually testable before Stage 8, it is acceptable to add clearly technical **local/testing-only** dictionary item fixtures for `group_format` and `gender`, following the same environment gate used for development admin/psychologist seeds. These values are test fixtures, not product dictionary decisions.
- Local/testing fixture labels/codes must be obviously non-production/test-only and must never be created by production seeding.
- The submit interaction may stay on group detail; the correction does not need a new wizard or new page pattern. If submit validation fails, the detail page must visibly explain that required group data is incomplete/invalid and expose the concrete validation messages plus a truthful route back to editing where the policy allows it.

## Unknowns

- The production values for `group_format` and `gender` remain intentionally unknown. Do not seed product-looking production values.
- The numeric minimum length for rejection reason and abandoned-draft age threshold remain unresolved from TASK-13 and are not part of this correction.

## Scope

### 1. Successful-payment safety for admin cleanup

Correct the admin cleanup boundary so a `draft` / existing `awaiting_payment` group cannot be soft-deleted while it has a successful non-refunded payment.

Requirements:

- enforce the invariant server-side, not only by hiding the button;
- keep the invariant true when `GroupWorkflowService::delete()` re-authorizes the freshly locked group;
- use the existing payment relation/status model and existing `PaymentStatus` enum;
- preserve psychologist deletion safety and align it with the same semantic rule where necessary;
- once a payment is actually in the existing refunded state, cleanup may proceed if all other cleanup rules allow it;
- do not add refund mutation endpoints, payment admin CRUD, bank adapters, webhook handling, or payment creation.

Admin group detail must be truthful:

- when cleanup is blocked by a successful non-refunded payment, do not render a destructive cleanup action as available;
- show a clear warning that the payment must be handled/refunded before destructive cleanup;
- expose only safe existing payment facts needed by the administrator: amount/currency through shared money presentation, `transaction_id` when present, and paid date/time when present;
- never expose raw `bank_response`.

Add regression coverage proving:

1. admin cleanup of ordinary `draft` still succeeds;
2. cleanup of `draft` with successful non-refunded payment is denied and the group remains undeleted;
3. the detail UI does not offer cleanup as available in that blocked state and shows the warning/payment facts;
4. a group whose payment is already in the existing refunded state is not blocked by the old successful-payment fact alone;
5. moderation/approved/active cleanup remains denied as before.

### 2. Payment reminder on rejection

When a paid group (`group.free=false`) in `moderation` has a successful non-refunded payment, the admin rejection surface must show the `SPEC.md` reminder before rejection.

Requirements:

- keep the existing `moderation -> rejected` domain transition unchanged;
- rejection remains possible; no automatic refund is attempted;
- the rejection modal/detail visibly warns that a manual refund must be handled separately;
- show the same safe payment facts defined above;
- do not render fake bank actions or a refund button;
- if no such payment exists, do not show fake payment information.

Add a focused rendered-response test for the warning and payment facts.

### 3. Preserve soft-deleted owner and history actor identity

Historical/group reads must survive psychologist soft delete without 500s or false attribution.

Requirements:

- group owner relation used by admin/history surfaces must be able to resolve a soft-deleted psychologist;
- status-history actor relation must be able to resolve a soft-deleted user actor;
- admin group list and group detail must render retained owner identity after owner soft delete;
- Timeline attribution must use `actor_type` truthfully:
  - `actor_type=system` -> `Система`;
  - `actor_type=user` with a resolvable soft-deleted user -> that retained user identity;
  - a user history row must never silently become `Система` only because the related account is no longer active;
- do not restore or re-enable deleted users and do not weaken authentication eligibility;
- do not cascade-delete groups/history.

Add regression tests covering at minimum:

1. create a psychologist/group/history entry with the psychologist as actor, soft-delete the psychologist, then admin list/detail still return 200 and show the retained owner identity;
2. Timeline still attributes the old transition to the user, not `Система`;
3. an actual system history row still renders `Система`;
4. no N+1 regression is introduced in admin list/history reads.

### 4. Fix Money Input composite focus

Bring the shared Money Input into exact focus-system conformance.

Requirements:

- keyboard focus on the native money input produces the approved focus edge/halo only on `.ui-money-input` through `:focus-within`;
- the nested `.ui-money-input__control` must not render its own global `focus-visible` border/ring/shadow;
- retain keyboard accessibility and visible outer focus;
- do not restyle size, padding, typography, currency suffix, validation state or any other accepted Money Input behavior;
- use existing design tokens/rules; do not change `DESIGN_SYSTEM.md` to justify the implementation.

Update `UiKitPageTest` (or equivalent shared-component coverage) so it proves both the outer focus treatment and suppression of the inner focus ring. Browser verification must include keyboard focus on Money Input at desktop and mobile widths.

### 5. Make normal local/testing Stage 7 submit actually executable

A developer/product owner must be able to start from the repository's normal local/testing seed and complete `draft -> moderation` without manually inserting dictionary rows.

Implement the smallest development-only fixture support:

- add idempotent local/testing-only dictionary item fixtures for the existing `group_format` and `gender` dictionaries;
- follow the existing `DatabaseSeeder` environment boundary used for development admin/psychologist seeds;
- fixture codes/labels must be clearly technical/test-only, not plausible production product decisions;
- production seeding must continue to create no invented format/gender items;
- do not build Stage 8 dictionary CRUD in this correction;
- do not change the approved `DictionarySeeder` definitions into product values.

Add seed regression coverage proving:

1. local/testing normal seed creates at least one usable active item in each required dictionary;
2. repeated seed is idempotent;
3. production path does not create the development-only items.

### 6. Make submit failure visible and actionable

Correct the UX for `POST /cabinet/groups/{group}/submit` validation failure.

Requirements:

- when submission fails complete-data validation, the returned product surface visibly tells the psychologist that the group was not sent to moderation;
- show the actual validation messages in a shared approved feedback pattern; do not silently discard `$errors`;
- preserve field-specific errors for the edit form and existing entered/stored data;
- provide a truthful Edit action/link when the group is still editable;
- do not claim success and do not change status/history on validation failure;
- no new custom page pattern or one-off visual component.

Add tests for an incomplete draft submitted from the detail page:

- response returns to a visible error state;
- status remains `draft`;
- no status-history row is created;
- error text is rendered to the user;
- edit route remains available.

### 7. Real end-to-end moderation regression

Add/adjust a realistic session-based Stage 7 flow that does not depend on ad-hoc temporary DB setup beyond the normal local/testing seed:

1. seed normally;
2. login as the development psychologist through the real login endpoint/session;
3. create a group;
4. open edit form and select the local/testing group format/gender fixtures;
5. fill every required field and save if the existing UX requires it;
6. submit from the actual UI/action;
7. assert redirect/success feedback;
8. assert database status is `moderation`;
9. assert exactly one `draft -> moderation` history row with the psychologist actor;
10. reload group detail and verify `На модерации` is visible.

Browser verification must reproduce this flow from a normal seeded development database, not by inserting temporary dictionary values immediately before the test.

### 8. Documentation and report

Update only factual documentation made necessary by the correction:

- document the local/testing-only group dictionary fixtures and that they are not production product values;
- keep the unresolved production dictionary values explicit;
- update `.ai/report.md` with the exact fixes, tests and browser flow actually run;
- do not change `SPEC.md`.

## Out Of Scope

- Stage 8 dictionary/settings CRUD.
- Production `group_format` / `gender` values.
- Payment creation, payment list/admin CRUD, refund mutation, bank adapter, redirect, webhook or transaction reconciliation.
- Stage 8 expiry/extension lifecycle.
- Stage 10 participant applications.
- Stage 11 external API.
- Stage 12 email/onboarding.
- New schema/migrations unless a concrete blocker proves they are strictly necessary; current schema already supports these corrections.
- Changes to group transition matrix.
- New roles/permissions framework.
- Redesign of Stage 7 pages or shared components outside the Money Input focus correction and necessary feedback/warning composition.
- New frontend dependencies, npm/Node/Vite/CDN runtime assets.
- Resolving the previously unknown rejection minimum length or abandoned-draft age threshold.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, current `SPEC.md`, `DESIGN_SYSTEM.md` and `uikit/index.html`.
- Work from current `main` and this task only; do not modify `.ai/task.md` during implementation.
- Preserve the accepted Stage 4 auth/session boundary and Stage 5 psychologist/document security.
- Preserve the Stage 7 `GroupStatusTransitionService` as the only status-transition/history-writing boundary.
- Do not weaken policies to make tests pass.
- Payment safety must be server-side and survive stale-model/re-authorization paths.
- Soft-deleted historical identities are for read/history truth only; do not make deleted psychologists eligible to authenticate.
- Development dictionary fixtures must stay strictly local/testing-only.
- Use existing shared Alert/Modal/Description/Money/Timeline/UI primitives; do not invent new visual variants.
- No secrets, real personal data, persistent browser fixtures or unrelated cleanup.

## Acceptance Criteria

1. Admin cannot cleanup a `draft/awaiting_payment` group with a successful non-refunded payment; the group remains intact.
2. Admin detail truthfully warns about the blocking payment and shows safe payment facts; after existing refunded state, payment no longer blocks cleanup.
3. Rejection UI for a paid moderation group with successful non-refunded payment shows the required manual-refund reminder/payment facts, but no refund/bank action is added.
4. Admin group list/detail remains functional after owner psychologist soft delete and still shows retained owner identity.
5. Soft-deleted user actors remain correctly attributed in group Timeline; only actual `actor_type=system` entries render as `Система`.
6. Money Input shows exactly one outer composite focus ring and no nested input ring.
7. A normal local/testing seed contains clearly test-only selectable `group_format` and `gender` items; production seed does not.
8. From normal local/testing seed, the real psychologist UI can create, complete and submit a group to `moderation` without manual DB fixture insertion.
9. Successful submit creates exactly the valid `draft -> moderation` history with the psychologist actor and visible success/status feedback.
10. Incomplete/invalid submit visibly explains failure, preserves `draft`, writes no history and gives the psychologist an actionable way back to editing.
11. Existing Stage 7 CRUD, IDOR, edit/delete/status restrictions, activation dates and no-bank behavior remain green.
12. Existing Stage 4–6 regressions remain green.
13. No Stage 8+ functionality or new dependency is introduced.

## Checks

Run and report at minimum:

- focused admin cleanup/payment-guard tests, including successful and refunded cases;
- focused rejection-payment reminder rendered-response test;
- focused soft-deleted group owner/history actor tests;
- focused submit validation and real `draft -> moderation` tests;
- local/testing seed idempotency + production-exclusion tests for development dictionary fixtures;
- `UiKitPageTest` / shared Money Input focus regression;
- existing `PsychologistGroupCrudTest` and `AdminGroupWorkflowTest` in full;
- Stage 4 auth/stale-session tests;
- Stage 5 admin psychologist/document tests;
- Stage 6 psychologist cabinet tests;
- full isolated-MySQL `composer check` gate;
- `composer check-platform-reqs`;
- real Chromium desktop and ~390px mobile checks for:
  - Money Input keyboard focus;
  - normal-seed psychologist login -> group edit -> submit -> visible `На модерации`;
  - incomplete submit -> visible validation feedback;
  - admin blocked-cleanup/payment warning and rejection reminder where a clearly temporary test payment fixture is used;
  - soft-deleted owner/history rendering without console/runtime errors;
- no external/CDN requests;
- clean up every temporary payment/group/history/browser artifact introduced for manual verification;
- `git diff --check`;
- final `git status --short`, full diff and staged-file inspection.

## Hard Workflow Gate

Before implementation:

- confirm `TASK-2026-08-19-14` is the current planned task created from `2cba80312e0118bbf7160f23b2cf241d5b107a32`;
- read `WORKFLOW.md`, `AGENTS.md`, this task, relevant current group/payment models/policies/requests/views/tests, `DESIGN_SYSTEM.md` and relevant `uikit/index.html` sections;
- do not touch unknown local changes.

Before commit:

- complete all applicable focused/full/browser checks;
- update `.ai/report.md` with actual results only;
- update only factual documentation required by this correction;
- inspect final Git status, full diff and staged files;
- stage only task-related files;
- ensure no screenshots, Playwright artifacts, temporary DB fixtures, logs, caches, secrets or sensitive data are committed;
- commit only after the gate passes with message:

`codex: TASK-2026-08-19-14 fix Stage 7 acceptance blockers`

If any confirmed blocker cannot be safely closed without a new product decision, report `partial`, `blocked` or `failed` rather than silently weakening the requirement or starting Stage 8.