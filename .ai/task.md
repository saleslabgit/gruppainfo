# Task: TASK-2026-08-19-13

Status: planned
Created from: ea2999606c79b861ed6f52b9de99dc6563328613

## Title

Implement Stage 7 group CRUD and moderation

## Goal

Implement the revised `SPEC.md` Stage 7 as the complete internal group-management and moderation workflow, without bank/payment integration and without starting later lifecycle, applications, external API, or email stages.

After this task a psychologist can create a draft group, fill and save its data, submit it for moderation, see moderation feedback/history, revise and resubmit when requested, view every own group, and delete only permitted groups. An administrator can list/search/filter/sort/paginate groups, create/view/edit groups, moderate them through the existing domain state machine, inspect full history, manually delete abandoned draft records, and activate an approved group after manual publication with correct placement dates and optional external catalog ID.

The workflow must remain state-machine driven:

`draft -> moderation -> revision/rejected/approved -> active`

`awaiting_payment` must not be reachable from any Stage 7 UI/action. No Stage 7 action creates `gp_payments`, redirects to a bank, or depends on a payment provider.

## Facts

- Current `main` at task creation is `ea2999606c79b861ed6f52b9de99dc6563328613` (`codex: TASK-2026-08-19-12 implement Stage 6 psychologist cabinet`).
- Stage 1–5 are accepted technically; Stage 6 is implemented and technically accepted, and the user explicitly requested the next stage.
- Current `SPEC.md` Stage 7 requires psychologist group creation/form/view/edit/delete/list, admin CRUD/list, moderation, comments/rejection reason, status history, manual activation, `external_catalog_id`, manual abandoned-draft deletion, policies and IDOR protection.
- Stage 7 acceptance explicitly requires the payment flow to remain absent even for a psychologist whose `gp_users.free=false`.
- `gp_groups` and `gp_group_status_history` already exist from Stage 2 with the required columns, indexes, soft delete, relations, `public_uuid`, moderation fields, publication fields and placement fields. Do not add a replacement group schema.
- `GroupStatus` already contains `awaiting_payment`, `draft`, `moderation`, `revision`, `rejected`, `approved`, `active`, `expired` and the approved transition matrix.
- `GroupStatusTransitionService` already uses `lockForUpdate`, rejects invalid/stale transitions, changes status and writes `gp_group_status_history` atomically. It is the mandatory status-transition boundary and must be reused rather than bypassed.
- `SettingService` and `SettingKey::PlacementDurationDays` already exist. `SettingSeeder` provides `placement_duration_days=30` by default.
- `Group` already generates `public_uuid` on creation and casts status, monetary minor units and publication timestamps.
- Existing dictionaries include definitions for `group_format` and `gender`, but product dictionary item values are intentionally not seeded because they are not approved yet.
- Existing cabinet `/cabinet/groups` is the truthful Stage 6 empty surface that Stage 7 must now replace with the real own-group list.
- Stage 5 provides the real admin shell, Table/Toolbar/Search/Filters/Pagination, Modal, Form controls, Description List and corrected List/Form/Detail compositions.
- `DESIGN_SYSTEM.md` already defines the shared Timeline component (§4.35) and Money Input visual (§4.10), but Timeline is not implemented in the shared Blade namespace and the existing `x-ui.money` is display-only.
- `uikit/index.html` contains approved Timeline, Money, Table/List, form, Modal/Confirmation and responsive reference examples. It remains reference-only.
- `SPEC.md §12` defines the Russian group status labels; raw status codes must not be duplicated through views.
- `SPEC.md §14` allows psychologist editing only in `draft` and `revision`, viewing of every own group, and psychologist deletion only in `draft` and `rejected` when there is no successful non-refunded payment.
- Stage 8 owns automatic expiry and free extension. Stage 10 owns participant applications/counters. Stage 13 owns bank/payment integration.

## Assumptions

- Stage 7's temporary “free path for everyone” means **bypassing the payment gate**, not falsifying the historical tariff snapshot. On group creation copy the current psychologist `free` flag into `gp_groups.free` per `SPEC.md §4.4`, but regardless of whether that value is `true` or `false`, create the group directly in `draft` and create no payment. A `free=false` psychologist must therefore still reach the same Stage 7 CRUD/moderation flow.
- Psychologist **Добавить группу** should follow the existing free-group scenario: create a minimal empty `draft` first, then redirect to its edit form. This intentionally makes abandoned drafts possible and matches the nullable Stage 2 schema.
- Draft/revision save may persist incomplete data, but every supplied value must still pass type/range/dictionary validation. Transition to `moderation` is the completeness gate and must validate the full business form before calling the status service.
- The moderation completeness gate should require the group content fields named by `SPEC.md §11`: name, description, schedule, format, meeting duration, participant count, gender and meeting price. System/admin fields (`status`, `disabled`, `accept`, `free`, `public_uuid`, moderation comments, external ID, publication/expiry fields) are never trusted from psychologist form input.
- Meeting duration and participant count must be positive integers. Meeting price is entered for humans in BYN but stored exactly as integer minor units; do not use `float`/binary floating point for parsing.
- Admin creation may select an active non-admin psychologist as owner. Once a group exists, generic edit forms do not silently reassign ownership unless an existing repository rule explicitly requires it.
- Admin may edit group content regardless of group status, as allowed by `SPEC.md §14`; status itself remains changeable only through explicit domain actions.
- Admin soft-delete in this stage is intended for manual cleanup of draft/`awaiting_payment` records, especially abandoned drafts. Do not add a generic destructive action for active/approved/moderation groups merely because “CRUD” appears in the stage description.
- The existing successful-payment deletion guard is a safety invariant, but Stage 7 must not add payment creation/history UI. If a pre-existing/test `succeeded` payment is linked to a group, psychologist deletion must remain denied; no new bank behavior is introduced.
- Revision and rejection comments are persisted both in the existing current-field representation (`moderator_comment` / `rejection_reason`) where applicable and in immutable status history. History is the durable record and previous comments must never be overwritten there.
- Activation is an explicit admin action from `approved` only. It may accept optional `external_catalog_id`; publication timestamps are generated by the application, never user-entered.
- Group status presentation should be centralized on the enum (or one equivalent shared mapper) using the exact Russian labels from `SPEC.md §12` and existing Badge semantic variants only.
- Timeline must be implemented generically in `resources/views/components/ui/` before first product use and demonstrated on `/ui-kit`.
- The first editable meeting-price field requires a generic shared Money Input conforming to `DESIGN_SYSTEM.md §4.10`; do not create a group-specific money control.

## Unknowns

Two numeric product values are not defined anywhere in the current repository/specification and must **not** be invented:

1. `SPEC.md` says rejection reason has a minimum length, but does not define the number. Stage 7 must enforce a required, trimmed, non-empty reason now, but must not invent `min:N`. Record the missing numeric threshold in the report/status documentation.
2. `SPEC.md §22` defines an “abandoned drafts older than N days” quick filter, but no `N` or setting key exists. Implement the fully defined quick filters (`approved` awaiting publication and `expired` requiring removal), normal `draft` status filtering, and manual draft deletion; do not implement a fake abandoned-age threshold. Record this unresolved value for a later product/settings decision.

Dictionary item values for group format/gender are also intentionally unapproved. Do not seed invented product values. Automated/browser verification may create clearly temporary test fixtures and must remove development fixtures afterwards.

These unknown numeric values do not block the core Stage 7 CRUD/moderation acceptance flow.

## Scope

### 1. Group domain/application workflow boundary

Build the smallest group application/domain coordination needed around the existing `GroupStatusTransitionService`.

Requirements:

- never assign `group.status` directly from controllers, requests, Blade or generic mass-assignment endpoints;
- every status transition goes through `GroupStatusTransitionService`;
- multi-step actions that combine transition + side fields must be atomic in one outer transaction;
- use row locking/current DB state for mutation authorization/invariants where stale route-bound models could otherwise allow an invalid operation;
- keep controllers thin;
- do not create a second transition matrix or duplicate history-writing logic.

Explicit Stage 7 actions:

- psychologist submit: `draft -> moderation`;
- psychologist resubmit after corrections: `revision -> moderation`;
- admin request revision: `moderation -> revision`, required comment;
- admin reject: `moderation -> rejected`, required non-empty reason;
- admin approve: `moderation -> approved`;
- admin activate: `approved -> active`.

A forged POST/PATCH attempting any other transition must fail safely and leave status/history/side fields consistent.

### 2. Group creation and free-path override for Stage 7

Psychologist creation:

- expose a real **Добавить группу** action from **Мои группы**;
- create a non-admin-owned `gp_groups` row for the authenticated psychologist only;
- `status=draft`;
- `disabled=false`;
- copy `owner.free` to `group.free`;
- let the model generate `public_uuid`;
- do not write `accept`;
- do not create `gp_payments`;
- do not use `awaiting_payment` even when `owner.free=false`;
- redirect immediately to the draft edit form.

Admin creation:

- provide a protected admin group-create flow using an existing active non-admin psychologist as owner;
- same initial Stage 7 state rules: `draft`, no payment, snapshot owner tariff, generated UUID;
- do not create new psychologist records from the group form.

Test explicitly that a `free=false` psychologist creates a `free=false` **draft** with zero payment rows.

### 3. Shared group form and validation

Implement one reusable group form composition used by psychologist/admin pages where appropriate.

Editable business fields from existing `gp_groups`:

- `name`;
- `description`;
- `schedule`;
- `format_id` from active `group_format` dictionary items;
- `meeting_duration_minutes`;
- `participant_count`;
- `gender_id` from active `gender` dictionary items;
- `price_per_meeting` entered as a human BYN amount and stored as integer minor units.

Validation rules:

- draft/revision save accepts incomplete fields but validates any supplied values;
- moderation submission requires the full business form;
- dictionary IDs must belong to the correct active dictionary, not merely exist globally;
- string DB limits must be respected where the schema defines them;
- positive integer rules for duration/count;
- price conversion must be exact and reject malformed/over-precision input without floats;
- validation errors use shared field-level error presentation and preserve entered values.

Never accept generic form writes to:

- owner ID from psychologist requests;
- `status`;
- `disabled`;
- `accept`;
- `free`;
- `public_uuid`;
- `moderator_comment` / `rejection_reason`;
- `external_catalog_id` from psychologist forms;
- `published_at`, `expires_at`, `expiry_warning_sent_at`, `placement_days`;
- timestamps/deleted state.

Implement the shared Money Input first if needed. Follow `DESIGN_SYSTEM.md §4.10` exactly and update `/ui-kit`; do not modify `DESIGN_SYSTEM.md` to justify implementation choices.

### 4. Psychologist **Мои группы** real list

Replace the Stage 6 empty-only page with the real ownership-scoped group list.

Requirements:

- query only current authenticated psychologist's non-deleted groups;
- never accept an owner/user filter from the client;
- use a bounded/paginated list suitable for growth;
- use the approved List composition and shared Table/Toolbar/Pagination primitives;
- Toolbar may contain the truthful result count and real **Добавить группу** action; do not invent search/filter controls not required for this user list;
- show at minimum group name, centralized status, format, creation date and available view action;
- show publication/expiry values only when present and truthfully as `Не указано`/equivalent when absent;
- do **not** show application counters yet; Stage 10 owns internal application work;
- no payment-history surface;
- empty state now includes the real add-group action;
- list query must not produce N+1 queries.

Available actions must be status-driven and truthful. Do not render an edit/delete/submit control when policy/domain rules do not permit that action.

### 5. Psychologist group detail/edit/delete/submit

Group detail:

- owner can view every own non-deleted group regardless of status;
- another psychologist cannot view it by changing the group ID;
- show full group data, status, dates, optional external catalog ID if present, and current relevant moderation feedback;
- show full immutable status/comment history;
- use the shared Timeline component for history;
- history actor/date/comment must be readable; system actor is represented truthfully when actor is null/system;
- status history relation must not create N+1 actor lookups.

Editing:

- psychologist can edit only `draft` and `revision`;
- `moderation`, `approved`, `active`, `expired` and `rejected` cannot be edited by psychologist;
- owner check and status rule must both be enforced server-side/policy/domain, not only by hidden buttons;
- revision detail makes the latest correction comment prominent using existing Alert/Card patterns, with full history below.

Submitting:

- `draft -> moderation` and `revision -> moderation` only after complete Form Request validation;
- transition writes actor/history through the existing transition service;
- repeated/forged submit from another status is rejected and does not create duplicate/invalid history.

Deletion:

- psychologist may soft-delete only own `draft` or `rejected` groups;
- deny delete when a linked payment is currently `succeeded` (read-only safety check; do not build payment UI);
- use existing destructive Modal/Confirmation pattern;
- deleting a group never force-deletes history/payments/applications;
- another psychologist cannot delete via IDOR.

### 6. Admin group list

Add **Группы** as a real admin navigation section and implement the admin List page.

List requirements:

- all non-deleted groups;
- eager-load only what the rows need, at minimum owner and relevant dictionaries;
- pagination;
- search by internal group ID, group name and psychologist identity (name/email where useful);
- combinable GET filters for status and free/paid snapshot;
- sorting whitelist: creation date, publication date, expiry date; explicit asc/desc; safe default to newest created;
- quick filter for `approved` groups awaiting publication;
- quick filter for `expired` groups requiring manual removal;
- normal `draft` filter remains available;
- do not invent the unresolved “older than N days” abandoned quick-filter threshold;
- no bank/payment-success filter in this internal no-bank stage;
- query parameters survive pagination;
- truthful empty/no-results states;
- no N+1 regression.

Show at minimum the §22 group facts that are available now: ID, name, psychologist, status, free/paid snapshot, creation/publication/expiry dates and view action.

### 7. Admin group create/view/edit

Create:

- admin can create a Stage 7 draft for an existing active psychologist;
- owner selection is validated against active non-admin users;
- no payment is created regardless of selected owner's tariff.

View/detail:

- show psychologist identity, all group fields, status, free/paid snapshot, publication dates, UUID/external ID as appropriate, and full status history;
- do not expose fake bank details when no payment exists;
- use centralized status labels/Badge and shared Timeline;
- show only state-valid moderation/activation/delete actions.

Edit:

- admin may edit group content regardless of status;
- generic edit cannot directly assign status/system fields;
- changing content must not silently create history entries or transition status;
- do not silently change the stored `free` snapshot when psychologist tariff later changes.

### 8. Admin moderation actions

Implement explicit protected actions, each with policy authorization, Form Request validation where input exists, and approved Modal form composition.

**Request revision** (`moderation -> revision`):

- comment required, trimmed, non-empty;
- set/update `moderator_comment` as the current/latest moderator comment;
- write the same comment into immutable status history through the transition;
- never erase previous history comments.

**Reject** (`moderation -> rejected`):

- reason required, trimmed, non-empty;
- do not invent the unspecified numeric `min:N`;
- set `rejection_reason`;
- persist reason in transition history;
- psychologist detail visibly shows the reason.

**Approve** (`moderation -> approved`):

- no arbitrary status select;
- transition/history through the existing service.

All invalid transitions, including forged actions against stale/current incompatible states, must be rejected by the domain transition service and leave data consistent.

### 9. Manual activation after external publication

Implement the admin **Отметить активной** action for `approved` groups only.

In one transaction:

1. obtain current `placement_duration_days` through `SettingService` / `SettingKey::PlacementDurationDays`;
2. require a valid positive configured integer; do not silently hardcode a fallback if configuration is corrupt/missing;
3. transition `approved -> active` through `GroupStatusTransitionService` with the admin actor;
4. set `published_at` to current UTC time;
5. set `placement_days` to the configured value at activation time;
6. set `expires_at = published_at + placement_days`;
7. reset `expiry_warning_sent_at=null`;
8. persist optional trimmed `external_catalog_id` if supplied.

Do not allow browser input to choose publication/expiry timestamps or placement days.

Use time-frozen automated tests to prove exact values and that changing the setting after activation does not rewrite the already stored `placement_days`/dates.

Stage 8, not this task, will automatically transition `active -> expired` and implement free extension.

### 10. Manual admin draft cleanup

Implement a destructive, policy-protected soft-delete action for admin cleanup of `draft` and existing `awaiting_payment` records.

Requirements:

- confirmation required;
- no age threshold is invented;
- do not label every draft as objectively “abandoned” in UI; present manual cleanup truthfully;
- do not expose generic delete of moderation/approved/active/expired groups in this task;
- soft delete only; preserve related history/data;
- test admin can delete a draft and cannot use the cleanup action for an active/moderating group.

### 11. Group policies and IDOR

Add explicit `GroupPolicy` (or equivalent existing Laravel policy pattern) and register it.

At minimum cover:

- psychologist list/create scoped to self;
- view own group only;
- edit/update own only in `draft`/`revision`;
- submit own only in `draft`/`revision`;
- psychologist delete own only in `draft`/`rejected` plus payment safety invariant;
- admin list/create/view/edit;
- admin moderation only on non-deleted psychologist groups and only through valid state actions;
- admin manual cleanup only for permitted draft states.

Route middleware remains defense-in-depth; controller actions must explicitly authorize resources/actions.

Add IDOR tests for view, edit, update, submit and delete with two psychologists.

### 12. Shared Timeline and group status presentation

Implement the already-designed Timeline generically before using it in group detail pages.

Timeline must follow `DESIGN_SYSTEM.md §4.35` exactly:

- 12px marker + 1px connector;
- 16px marker/content gap;
- 24px entry spacing except last;
- approved semantic variants only;
- title, datetime/actor line and optional comment block;
- no group-specific visual primitive.

Add a `/ui-kit` demonstration using the real shared component.

Centralize group status labels using the exact `SPEC.md §12` user-facing wording:

- `awaiting_payment` — `Ожидает оплаты` (not reachable from Stage 7 UI);
- `draft` — `Черновик`;
- `moderation` — `На модерации`;
- `revision` — `На доработке`;
- `rejected` — `Отклонена`;
- `approved` — `Одобрена, ожидает публикации`;
- `active` — `Активная`;
- `expired` — `Закончена`.

Use existing Badge semantic variants; do not add colors.

### 13. Responsive/UI behavior

All new pages must compose approved generic components and the List/Form/Detail page patterns from `DESIGN_SYSTEM.md`.

Verify desktop and mobile (~390px):

- psychologist group list, real add action and empty state;
- create/edit form fields, custom selects and Money Input;
- detail actions remain reachable without hover;
- revision/rejection feedback is obvious;
- Timeline is readable;
- admin list filters/sorting/pagination;
- moderation and activation modals fit viewport and use the accepted body/footer action pattern;
- tables use approved responsive horizontal scrolling;
- no horizontal page overflow outside table scroll containers;
- mobile Drawer navigation includes real **Группы** section where appropriate;
- no runtime CDN/build-tool regression.

If a required visual pattern is not covered by `DESIGN_SYSTEM.md`/uikit, stop and report the gap rather than inventing a fourth page pattern or new component variant.

### 14. Tests

Add focused Stage 7 feature/domain tests. At minimum prove:

1. psychologist `free=true` and `free=false` both create direct `draft` groups; stored `group.free` snapshots their respective tariff and no payment row is created;
2. draft save persists allowed fields and rejects protected-field injection;
3. moderation submission requires complete valid group data and creates `draft -> moderation` history with actor;
4. admin revision requires a non-empty comment, transitions through the domain service, updates current comment and preserves history;
5. psychologist sees revision comment/history, edits, and resubmits `revision -> moderation`;
6. rejection requires a non-empty reason, writes history and psychologist sees it;
7. approval is `moderation -> approved` through the domain service;
8. activation computes UTC `published_at`, frozen `placement_days`, `expires_at`, clears warning timestamp and stores optional external catalog ID atomically;
9. forged invalid transition is rejected and does not corrupt history/fields;
10. psychologist edit authorization is limited to `draft`/`revision`;
11. psychologist delete is limited to own `draft`/`rejected`, is soft delete, and succeeds/denies according to the existing succeeded-payment safety invariant;
12. second psychologist receives denial on view/edit/update/submit/delete of another owner's group;
13. admin can create/view/edit groups and manually soft-delete a draft, but cleanup action does not delete moderation/active records;
14. admin search/status/free filters, defined quick filters, sorting and pagination work and preserve query params;
15. psychologist/admin group lists have bounded query counts/no N+1;
16. status history shows all transitions/actors/comments in order;
17. `awaiting_payment` and payment creation are unreachable from Stage 7 web UI;
18. existing Stage 4–6 auth/session/admin-psychologist/document tests remain green;
19. `/ui-kit` remains production-guarded and demonstrates any newly implemented shared Timeline/Money Input components.

Use temporary dictionary items/factories in automated tests. Do not add invented dictionary values to production seed data.

### 15. Documentation and report

Update only facts made true by Stage 7:

- `docs/architecture.md` — group ownership/policies, domain workflow coordination, activation boundary and no-bank Stage 7 rule;
- `docs/development.md` — Stage 7 URLs and concise manual verification for psychologist/admin, including temporary dictionary fixture guidance if needed;
- `docs/project-status.md` — Stage 7 implemented, Stage 8 next, plus the two unresolved numeric thresholds;
- `.ai/report.md` — exact implementation, changed files, checks/browser flows, facts/unknowns/remaining risks.

Do not change `SPEC.md`; the roadmap/stage is already approved.

## Out Of Scope

- Bank/acquirer adapter, redirect, return or webhook.
- Creating `gp_payments` from any Stage 7 UI/action.
- Making `awaiting_payment` reachable through Stage 7 product flows.
- Payment history UI or bank/payment-success filters.
- Automatic `active -> expired` scheduler behavior.
- Expiry warning email/job/queue behavior.
- Free or paid extension.
- Participant application list/counters/status handling.
- Public application intake or psychologist questionnaire intake API.
- `/api/v1`, HMAC, idempotency or external-site integration.
- Email/password onboarding or SMTP.
- Dictionary/settings CRUD.
- Inventing group-format/gender product dictionary values.
- Inventing the rejection-reason minimum length.
- Inventing the abandoned-draft age threshold.
- New schema for groups/history unless a concrete incompatibility with the already-approved Stage 2 schema is proven and reported first.
- Vue/React/Livewire/Inertia/Tailwind/Vite/npm/Node or runtime CDN dependencies.
- Unrelated redesign/refactoring of accepted Stage 3–6 UI.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, current `SPEC.md`, `DESIGN_SYSTEM.md`, and `uikit/index.html`.
- Work only from current `main` and this task; do not modify `.ai/task.md` during execution.
- Preserve Stage 4 auth/session/stale-session security and Stage 5/6 access/document behavior.
- Reuse the existing `GroupStatus`, `GroupStatusTransitionService`, `SettingService`, models, schema and Laravel policy architecture.
- No controller/form may directly write group status.
- `accept` remains legacy/non-authoritative and must not become a workflow source of truth.
- Money remains integer minor units; no floats.
- Store timestamps in UTC and render through existing Europe/Minsk conventions.
- Use Form Requests for all mutations/input validation.
- Use explicit authorization in controller actions in addition to route role middleware.
- Use only shared design-system components/tokens and approved page compositions.
- Add shared Timeline/Money Input before product use; do not create page-specific copies.
- Do not invent missing numeric/product/design values.
- Do not seed unapproved dictionary items.
- No new dependency unless a concrete blocker is proven and reported.

## Acceptance Criteria

1. Psychologist can create a draft, save group data, submit to moderation, view all own groups and view group detail.
2. A `free=false` psychologist follows the same Stage 7 direct-draft path, while the group's `free` snapshot remains `false`; no payment is created and `awaiting_payment` is not used.
3. Psychologist can edit only own `draft`/`revision` groups and delete only own permitted `draft`/`rejected` groups.
4. Another psychologist cannot view/edit/update/submit/delete a group by changing IDs.
5. Admin has real group list/create/view/edit surfaces with search, status/free filters, defined quick filters, sorting and pagination without N+1.
6. `draft -> moderation`, `revision -> moderation`, `moderation -> revision/rejected/approved`, and `approved -> active` happen only through `GroupStatusTransitionService`.
7. Revision comment is required, preserved in history and visibly shown to psychologist.
8. Rejection reason is required/non-empty, preserved in history and visibly shown to psychologist; no arbitrary numeric minimum is invented.
9. History contains every Stage 7 transition with actor/date/comment and is visible to psychologist/admin through the shared Timeline.
10. Admin activation uses configured placement duration and atomically sets `published_at`, `placement_days`, `expires_at`, clears warning timestamp and optionally stores `external_catalog_id`.
11. Admin can manually soft-delete draft cleanup records but cannot use that action as a generic active/moderation delete.
12. Existing group `public_uuid`, tariff snapshot, history and related rows remain preserved according to soft-delete/domain rules.
13. Group forms use server-side validation and exact minor-unit money conversion; protected/system fields cannot be mass-assigned from requests.
14. No Stage 7 web action creates a payment, redirects to a bank or exposes `awaiting_payment` as a selectable/reachable state.
15. New UI uses approved List/Form/Detail patterns and shared components; Timeline and Money Input are generic and represented on `/ui-kit` before product use.
16. Desktop/mobile flows are usable, moderation feedback is obvious, modals are compliant and tables do not cause page-level overflow.
17. Existing Stage 4–6 security/product regressions remain green.
18. Full MySQL test suite, Pint, Larastan/PHPStan, platform requirements, applicable JS syntax check and `git diff --check` pass.
19. Final diff contains only Stage 7 group workflow/UI/tests/docs and justified shared UI additions.
20. The two undefined numeric thresholds remain explicitly documented as unresolved rather than guessed.

## Checks

Run and report at minimum:

- focused group status/domain workflow tests, including stale/invalid transition cases;
- focused psychologist group CRUD/policy/IDOR tests;
- focused admin group CRUD/list/filter/sort/moderation/activation tests;
- query-count/N+1 tests for psychologist and admin group lists;
- payment-absence regression for both free and paid-tariff psychologists;
- existing `AuthenticationAccessTest`, `StaleAuthenticatedSessionTest`, `UserSessionInvalidatorTest`, Stage 5 admin/document tests and Stage 6 cabinet tests;
- existing and expanded `UiKitPageTest` for Timeline/Money Input if added;
- full isolated-MySQL `php artisan test` / project `composer check` gate;
- Pint and Larastan/PHPStan through the existing project command;
- `composer check-platform-reqs`;
- `node --check public/app.js` only if JS changes and Node is available externally without adding it as a dependency;
- route inspection for group routes and absence of `/api/v1`/payment routes added by this task;
- real browser flow at desktop and ~390px using temporary test dictionary fixtures if needed:
  - psychologist create -> edit/save -> submit;
  - admin request revision -> psychologist sees feedback -> edit/resubmit;
  - separate reject fixture with visible reason;
  - separate approve -> manual activation fixture with dates/external ID;
  - ownership/available-action checks;
  - admin search/filter/sort/pagination;
  - mobile navigation/forms/modals/timeline;
- browser console/network inspection with no runtime CDN/external requests;
- cleanup of every temporary browser fixture/artifact;
- `git diff --check`;
- final `git status --short`, full diff and staged-file inspection.

Final manual visual/product acceptance remains with the product owner.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, current Stage 7 sections of `SPEC.md`, `DESIGN_SYSTEM.md`, current project-status/architecture/development docs;
- inspect `Group`, `GroupStatus`, `GroupStatusTransitionService`, `GroupStatusHistory`, payment status relation used only for deletion safety, settings service/key/seeder, dictionary definitions, current admin/cabinet layouts/routes and relevant shared UI components;
- inspect the matching `uikit/index.html` Timeline, Money, Table/List, form and Modal examples;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-13` is the current planned task created from `ea2999606c79b861ed6f52b9de99dc6563328613`;
- do not touch unknown local changes.

Before commit:

- complete all applicable focused/full/browser checks;
- update `.ai/report.md` with actual results only, including the unresolved numeric thresholds;
- update only factual Stage 7 documentation;
- inspect final Git status, full diff and staged files;
- stage only task-related files;
- ensure no screenshots, Playwright artifacts, temporary dictionary/group/payment fixtures, uploaded files, logs, caches, secrets or sensitive data are committed.

If the gate passes, commit with:

`codex: TASK-2026-08-19-13 implement Stage 7 group CRUD and moderation`

If safe completion is impossible because of a real unresolved product/design/security requirement, report `partial`, `blocked`, or `failed` instead of inventing the missing value or weakening existing boundaries.
