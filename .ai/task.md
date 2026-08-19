# Task: TASK-2026-08-19-10

Status: planned
Created from: 8ad8d6f2f5c016ea54324e0de2ee2a718f08323a

## Title

Implement Stage 5 administrative psychologist CRUD

## Goal

Implement the revised `SPEC.md` Stage 5 as the first complete internal CRUD milestone: an administrator can manage psychologist records and their private documents entirely inside the protected Laravel application, without relying on the public website, email onboarding, SMTP, or payment integration.

After this task the administrator must be able to list/search/filter psychologists, create a psychologist manually, inspect all stored questionnaire fields, edit editable profile data, perform approved domain actions for status/tariff/access, soft-delete a psychologist, and upload/view/download/delete private psychologist documents. Significant administrative actions must record the acting administrator, and disable/reject/delete must revoke all existing sessions through the accepted Stage 4 session invalidation boundary.

This task must turn the current minimal `/admin` authentication acceptance surface into the real Stage 5 psychologist-management area while preserving the accepted Stage 4 authentication/security behavior and without starting Stage 6 psychologist cabinet product content.

## Facts

- Current `main` at task creation is `8ad8d6f2f5c016ea54324e0de2ee2a718f08323a` (`codex: TASK-2026-08-19-09 revoke stale sessions for deleted users`).
- Stage 1, Stage 2, Stage 3, and Stage 4 are accepted.
- The approved roadmap is internal-first: Stage 5 is administrative psychologist CRUD; public-site questionnaire intake is Stage 11; email/password onboarding is Stage 12; bank integration is Stage 13.
- The existing `/admin` route is protected by the accepted Stage 4 `stale-session -> auth -> eligible -> role:admin` pipeline.
- `UserSessionInvalidator` already deletes all configured database sessions for one user and must be reused for Stage 5 disable/reject/delete actions.
- `gp_users` already contains all questionnaire/profile fields required by §6, plus `status`, `disabled`, `free`, `admin`, nullable `password`, timestamps, soft delete, and generated `active_email` uniqueness.
- `gp_user_documents` already exists with `user_id`, `type`, private `path`, `original_name`, `mime_type`, `size`, and timestamps.
- The configured local filesystem root is `storage/app/private`; private documents must not be exposed through `public/` or a public storage symlink.
- `UserStatus` currently allows only `pending -> approved`, `pending -> rejected`, and `rejected -> pending`; an approved user is disabled through the independent `disabled` flag rather than `approved -> rejected`.
- `UserStatusTransitionService` is the existing and mandatory domain path for status changes.
- `accept` is a legacy field and is not a source of truth; current internal behavior does not write or depend on it.
- Existing dictionary definitions are seeded, but dictionary item values may legitimately be empty because product values are not yet approved. Stage 5 must not invent dictionary entries.
- Stage 3 already implements reusable Button, Input, Textarea, Select, Checkbox/Radio, Card, Badge, Table, TableToolbar, Search, Pagination, Dropdown, Modal/Confirmation, AppShell, PageHeader, Empty/Error/Loading and related baseline primitives.
- `DESIGN_SYSTEM.md` already specifies deferred components now needed for this stage: Filters/Chip, File Upload, File/Document Item, and Key-Value / Description List.
- `DESIGN_SYSTEM.md` defines only List, Form, and Detail page composition patterns; Stage 5 screens fit those patterns and no new page pattern is required.
- `uikit/index.html` is the approved visual reference and contains the relevant Table Toolbar/Filters, List Item/File Item, Key-Value, File Upload, navigation, forms, modal/confirmation, and responsive intent.

## Assumptions

- Stage 5 manages psychologists only (`admin=false`). The administrator account itself is not listed or editable through psychologist CRUD routes.
- A manually created psychologist starts as `pending`, `disabled=false`, `admin=false`, and has `password=null`. No random password and no administrator password-assignment UI is introduced.
- The creation form may set the initial free/paid tariff because Stage 5 explicitly manages that field; later group behavior still follows the free path until Stage 13 regardless of `gp_users.free`.
- Status, disabled/enabled state, and tariff changes are significant actions and must not be silently folded into a generic profile update. They should be explicit actions with clear authorization, confirmation where destructive, audit, and session-revocation behavior as applicable.
- Profile editing covers the questionnaire/profile fields stored on `gp_users`; system/security fields (`admin`, `password`, `remember_token`, `accept`, generated `active_email`, timestamps, deleted_at) are never editable through the generic profile form.
- Nullable questionnaire booleans may be represented as an approved Select with `not specified / yes / no`; do not invent a new tri-state control.
- For `license_expires_at` and consent date/time, use the approved closed Date/Time Input visual shell. Do not implement a custom calendar/date-picker popover because it remains explicitly unresolved in `DESIGN_SYSTEM.md`; a normalized native browser date/datetime control is acceptable if no custom calendar UI is invented and the closed control conforms to the design system.
- The minimum durable audit required by §4.8/Stage 5 is an immutable user-action history record containing the target psychologist, acting administrator, action, relevant status transition fields where applicable, small structured context for changed significant flags, and timestamp. It need not have a product-facing history UI in Stage 5.
- A dedicated minimal `gp_user_action_history` table is the clearest implementation because existing group status history cannot represent psychologist actions such as tariff/disabled/delete. Keep it narrow rather than creating a generic audit framework.
- Document type should be represented by stable internal codes for the four §6 document categories and rendered with Russian labels centrally; do not store presentation labels as business logic.

## Unknowns

- `SPEC.md` requires a configurable maximum document upload size but does not define a numeric product limit. Do not invent one. Add a project configuration/env hook that can enforce a maximum when configured; when no product value is configured, rely on the PHP/framework request upload ceiling rather than silently choosing a stricter number. Automated tests must set an explicit test value and prove the configurable limit path works. Record the unset product limit factually in documentation/report; this does not permit weakening MIME/type validation or private storage.

## Scope

### 1. Real administrative application structure

Replace the Stage 4 `/admin` acceptance-only surface with the Stage 5 administrative psychologist area while preserving the Stage 4 protected route boundary.

Implement a reusable admin page/layout composition based on the existing `x-ui.app-shell`, not duplicated navigation HTML in every page.

At minimum the real admin navigation for this stage exposes:

- Psychologists as the active product section;
- logout through the existing POST action.

Do not expose fake links to unfinished product sections as if they already work. If `/admin` remains an overview route, it must be a truthful minimal entry surface and link to the psychologist list; alternatively it may redirect to the psychologist list if that is the simplest truthful Stage 5 behavior.

All Stage 5 admin routes remain inside the accepted `stale-session -> auth -> eligible -> role:admin` boundary.

### 2. Psychologist list, search, filters, and pagination

Implement the psychologist list as the approved List page pattern.

Requirements:

- query only active non-deleted `admin=false` users;
- use the shared Table/TableToolbar/Search/Pagination components;
- show the required information from §22: name, email, phone, status, free/paid tariff, enabled/disabled state, and registration date, plus row actions;
- do not create fake bulk-selection controls with no behavior; if the existing generic Table needs a small reusable non-selectable capability to render this list correctly, extend the generic component according to its approved API/design rules rather than adding page-specific CSS;
- search must cover the useful identity fields already present in the table: name parts, email, and phone, using ordinary database queries rather than adding full-text/search infrastructure;
- filters at minimum: user status and free/paid tariff; include enabled/disabled as an additional filter only through the already specified Filters pattern, not an invented control;
- filters/search use GET query parameters, are combinable, and survive pagination;
- result count/range and empty/no-results behavior are truthful;
- pagination uses the existing data-driven Laravel paginator component;
- do not load per-row relationships lazily; add a query-count/N+1 regression test appropriate to what the list actually renders.

Implement the already-specified shared Filters/Chip primitives only to the extent needed by this list. Desktop filter presentation must follow §4.23; mobile filter presentation must use the approved Drawer behavior rather than an invented responsive layout.

### 3. Create psychologist manually

Implement a protected admin create form using the approved Form page pattern and Form Requests.

The form must support the questionnaire/profile fields currently stored in `gp_users`, including where applicable:

- last/first/middle name;
- phone and email;
- education type from existing active dictionary items, with a valid empty/not-specified option when the dictionary has no items;
- other education;
- modality/program;
- training center;
- graduation year;
- training hours;
- license number and expiry date;
- group-leading experience;
- groups-held count;
- document truth confirmation;
- education compliance confirmation;
- webinar/live willingness;
- personal-data-consent timestamp and version;
- initial free/paid tariff.

Creation rules:

- force `admin=false`;
- force `status=pending` through the approved initial-state rule; do not expose arbitrary status selection;
- force `disabled=false`;
- leave `password=null`;
- never write `accept` directly;
- never accept hidden/request fields for `admin`, password, status, disabled, generated columns, or other protected system attributes;
- active email uniqueness must respect the existing generated-column semantics: an active duplicate is rejected, while a soft-deleted row with that email does not incorrectly block manual creation;
- server-side validation is mandatory and failed validation returns field-level shared errors while preserving entered values.

Record the acting administrator for the creation action in the user action history.

### 4. View psychologist details

Implement a protected Detail page that exposes all stored psychologist questionnaire/profile fields in a readable structure, plus current status/tariff/access state, timestamps, and documents.

Use the shared Key-Value / Description List component defined in `DESIGN_SYSTEM.md` and the approved Detail page composition. Implement that component generically in `resources/views/components/ui/` before using it.

Requirements:

- nullable values display a truthful neutral "not specified" representation rather than empty/misleading data;
- status uses a centralized human-readable label and existing semantic Badge mapping, not raw database strings duplicated across views;
- dates use existing date/time presentation helpers/components and `Europe/Minsk` display conventions;
- show explicit available actions based on current state rather than displaying impossible/disabled fake controls;
- the administrator can navigate to edit and the explicit significant actions described below.

Do not add an audit Timeline UI unless it is genuinely required to satisfy an already-specified Stage 5 user scenario; audit persistence and automated verification are sufficient in this stage.

### 5. Edit psychologist profile data

Implement profile editing through a separate Form page and Form Request.

Editable fields are the questionnaire/profile data from the create form. Do not allow the generic update endpoint to mutate:

- `status`;
- `disabled`;
- `free`;
- `admin`;
- `password` / `remember_token`;
- `accept`;
- generated/system columns.

Email uniqueness on update must ignore the current active record while still respecting other active records and allowing addresses that exist only on soft-deleted records.

Editing ordinary profile fields does not send email or modify sessions/status.

### 6. Explicit status actions: approve and reject

Expose only domain-valid Stage 5 status actions.

- `pending -> approved`: administrator action through `UserStatusTransitionService`;
- `pending -> rejected`: administrator action through `UserStatusTransitionService`;
- do not expose `approved -> rejected`, because the current approved status matrix forbids it and approved users are disabled through `disabled`;
- do not expose an arbitrary status select/edit endpoint;
- do not expose `rejected -> pending` as a manual Stage 5 action unless an existing project file explicitly requires it; that transition is reserved for the later repeat-questionnaire flow.

Both approve and reject must record the acting administrator in the user action history. Reject must invoke `UserSessionInvalidator` even if the normal login eligibility rules mean such sessions should not usually exist.

Use neutral/destructive confirmation according to the existing Confirmation/Modal design pattern; do not invent a new confirmation UI.

### 7. Explicit tariff action

Implement changing `free` as an explicit authorized action, not a side effect hidden in generic profile update after creation.

Requirements:

- administrator can change free <-> paid;
- record actor plus before/after value in user action history;
- do not touch existing groups or payments;
- do not activate payment behavior; until Stage 13 group creation still follows the free internal path regardless of user tariff.

Use existing design-system actions and a clear confirmation if the implementation requires confirmation to avoid accidental changes.

### 8. Explicit disable / enable actions

Implement administrator actions for the independent `disabled` flag.

Disable:

- set `disabled=true` through a small application/domain operation rather than an unguarded mass-assignment endpoint;
- record actor and before/after state;
- invoke `UserSessionInvalidator` for the target psychologist so every database session is removed immediately;
- the Stage 4 current-request eligibility/revocation behavior remains intact as defense in depth.

Enable:

- set `disabled=false`;
- record actor and before/after state;
- do not create a login session, password, or email flow.

Use confirmation for disable because it immediately removes access. Do not disable/enable administrator accounts through these psychologist routes.

### 9. Soft delete psychologist

Implement explicit administrator soft delete for `admin=false` psychologists.

Requirements:

- confirmation uses the existing destructive confirmation pattern;
- delete through Eloquent soft delete; do not force-delete related historical/domain data;
- invoke `UserSessionInvalidator` before/with the administrative operation so existing database sessions are removed;
- record actor/action in user action history in a way that remains queryable after the target row is soft-deleted;
- do not delete the psychologist's documents merely because the user record is soft-deleted; retained private records/data remain intact according to the project deletion rules;
- do not implement restore/trash UI in this stage;
- after soft delete the user disappears from the normal psychologist list and cannot use protected access;
- a subsequently created active psychologist may reuse the soft-deleted email according to the existing database uniqueness rule.

### 10. Minimal durable user action audit

Add the smallest schema/model/service support required to persist significant Stage 5 administrator actions.

Use a dedicated immutable history table, e.g. `gp_user_action_history`, rather than a generic auditing package/framework.

At minimum each record must preserve:

- target psychologist ID;
- acting administrator ID (nullable only for future/system compatibility, but Stage 5 admin actions must always populate it);
- actor type (`user` / `system`) if needed to keep future non-user transitions representable;
- stable action code;
- `from_status` / `to_status` for status transitions where applicable;
- small structured metadata for significant flag changes where needed (for example free/disabled before/after);
- immutable creation timestamp.

At minimum audit these Stage 5 actions:

- manual psychologist creation;
- approve;
- reject;
- tariff change;
- disable;
- enable;
- soft delete.

Do not store passwords, session identifiers, uploaded file contents, or unnecessary full questionnaire snapshots in audit metadata.

Status transition and its audit record must be atomic. Other multi-record administrative changes must use transactions where consistency requires it. Keep controllers thin and coordinate audit/session side effects in the smallest suitable service boundary.

### 11. Private psychologist documents

Implement the Stage 5 admin document workflow using `gp_user_documents` and `storage/app/private`.

Document categories are the four §6 categories:

- diploma;
- certificate;
- license / membership;
- state registration certificate.

Use stable internal codes and a centralized label mapping/enum; do not persist translated labels as the technical identifier.

Upload requirements:

- admin-only upload action for an active psychologist record;
- Form Request/server validation;
- only PDF, JPEG, and PNG based on actual MIME/content validation, not extension alone;
- random/hash storage filename; preserve original filename only in the database;
- persist actual MIME type and byte size from the uploaded file;
- store only on the private local disk/path; never under `public/` and never expose the raw storage path to the browser;
- support the configurable size-limit hook described under Unknowns; test it with an explicit test value;
- handle storage/database failure deliberately and avoid silently leaving a successful DB record for a file that was not stored.

Viewing/downloading:

- serve documents only through a Laravel controller after authorization;
- administrator may view/download any psychologist document;
- an authenticated psychologist may access only their own document if the general protected document route is made owner-capable per §6; another psychologist must receive 403/not-found equivalent and must never receive the bytes;
- do not use direct public URLs or expose private disk paths;
- preserve safe content type and original filename for view/download behavior.

Delete document:

- admin-only explicit action with destructive confirmation;
- delete the DB record and private file deliberately; test that the file is removed from storage;
- deleting a document does not delete the psychologist.

Implement the shared File Upload and File/Document Item components from `DESIGN_SYSTEM.md` before using them on product pages. No page-specific upload/document styling.

### 12. Policies and authorization

Use Laravel policies/authorization in addition to the existing route role middleware.

At minimum:

- psychologist CRUD actions are authorized for administrators only;
- admin psychologist routes cannot target `admin=true` users as if they were psychologists;
- document upload/delete is admin-only;
- document view/download follows owner-or-admin §6 rules if exposed outside the admin route group;
- route/ID tampering cannot expose another psychologist's private document to a psychologist.

Controller actions must call/benefit from explicit policy authorization; do not rely solely on hidden UI buttons.

### 13. Shared design-system expansion required by Stage 5

Before product-page use, implement only the currently specified deferred shared components actually required by this task:

- Chip / active filter presentation;
- Filters / Filter Panel behavior needed by the list;
- Key-Value / Description List;
- File Upload;
- File / Document Item.

Reuse the existing Drawer for mobile Filters where possible; if the current Drawer exists only inside AppShell and a generic Drawer primitive is required, extract/implement it according to §4.28 without changing its visual specification.

Do not implement unrelated deferred catalogue components such as Timeline, Stepper, Metric, Progress, Toast, Tabs, Switch, Avatar, Choice Card, or Popover merely for completeness.

Update `/ui-kit` with inspectable examples/states for the newly implemented shared components so they remain verifiable independently of the Stage 5 product pages. Do not change numeric design rules in `DESIGN_SYSTEM.md` unless a genuine contradiction is found; no new visual values are approved by this task.

### 14. UX states and responsive behavior

All Stage 5 product screens must use the approved List/Form/Detail patterns and existing responsive rules.

Cover applicable states:

- normal populated list;
- empty psychologist list;
- no search/filter results;
- server-side validation errors on create/edit/upload;
- success/status flash feedback after mutations;
- explicit 403/404 behavior for unauthorized/missing resources;
- confirmations for reject, disable, delete psychologist, and delete document;
- desktop/tablet/mobile layouts with no horizontal page overflow; table horizontal scrolling is allowed only according to the approved Table behavior;
- mobile filter panel uses Drawer; mobile form/modal actions follow the already accepted full-width/ordering rules.

Do not add async SPA-style loading behavior just to manufacture a loading state; these pages are server-rendered Blade flows.

### 15. Tests

Add focused MySQL-backed feature/integration tests covering at minimum:

1. admin can open the psychologist list; psychologist and guest cannot access admin CRUD;
2. list contains only `admin=false`, non-deleted psychologists;
3. search by name/email/phone and combinable filters return correct results and query parameters survive pagination;
4. list query count remains bounded/no N+1 as result rows increase;
5. admin can create a psychologist; protected/system fields cannot be injected; created user is pending, enabled, non-admin, password null;
6. active duplicate email is rejected; email belonging only to a soft-deleted row is allowed;
7. admin can view all persisted questionnaire/profile fields;
8. admin can edit allowed profile fields but cannot alter status/disabled/free/admin/password/accept through generic update payload tampering;
9. pending -> approved and pending -> rejected use the domain transition service and invalid transitions are rejected;
10. approve/reject audit records contain the acting admin and correct status transition;
11. tariff change records actor/before/after and does not alter existing group `free` values;
12. disable removes all target database sessions, preserves other users' sessions, records audit, and blocks access;
13. reject and soft delete also invoke session invalidation behavior;
14. enable records audit and does not create/authenticate a session;
15. soft delete hides the psychologist, preserves related documents, and allows later active-email reuse;
16. private document upload stores bytes on private storage with random path and correct metadata; unsupported/spoofed MIME is rejected;
17. configured document size limit is enforced when a test value is provided;
18. admin can view/download a private document through authorized controller response; raw storage path/public URL is not exposed;
19. psychologist cannot access another psychologist's document by changing document/user IDs; owner access is correct if the route supports owner viewing;
20. document delete removes the private file and DB row and cannot target another resource through tampered IDs;
21. user-action audit does not contain password/session/file bytes and records the required significant actions;
22. existing Stage 4 authentication/access/stale-session/session-invalidator tests remain green;
23. `/ui-kit` still obeys its production guard and renders the newly implemented shared components in local/testing.

Use `Storage::fake()` where appropriate for deterministic file tests, while keeping the production code on the configured private local disk.

### 16. Documentation and report

Update factual documentation made true by Stage 5:

- `docs/architecture.md` — admin CRUD boundaries, policies, user action audit, private document controller/storage boundary;
- `docs/development.md` — manual Stage 5 verification, document upload config hook, local test workflow;
- `docs/project-status.md` — Stage 5 implemented state and Stage 6 as next stage;
- `.env.example` / an appropriate config file only if needed for the optional document maximum-size hook; do not put an invented numeric product limit into the repository;
- `.ai/report.md` — exact implementation, migrations, UI components added, checks, browser verification, known unresolved document-size product value, and any gaps.

Do not change the approved Stage 4–15 roadmap in `SPEC.md` unless a direct contradiction is discovered and cannot be resolved within the existing specification.

## Out Of Scope

- Public questionnaire intake or any `/api/v1` integration route.
- HMAC, timestamp/signature verification, `X-Request-Id`, public-site integration documentation.
- Email/password-setup links, SMTP, mail jobs, password reset/setup UI, or resend-email action.
- Administrator assignment of a psychologist's product password.
- Stage 6 psychologist cabinet product pages (`My data`, `My groups`) beyond authorization needed for private document access.
- Group CRUD/moderation/lifecycle behavior.
- Dictionary/settings CRUD or inventing dictionary item values.
- Participant application workflows.
- Payment creation, `awaiting_payment` UI flow, payment list behavior, bank adapter/webhook/refund behavior.
- Restore/trash management UI for soft-deleted psychologists.
- Bulk psychologist actions unless explicitly already implemented and required (none are).
- Generic audit framework/package or broad event-sourcing architecture.
- Unrelated deferred design-system components.
- New frontend framework, Node/npm/Vite, runtime CDN, or frontend build tooling.
- Unrelated refactoring of Stage 1–4 domain/authentication code.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, current `SPEC.md`, `DESIGN_SYSTEM.md`, and `uikit/index.html`.
- Preserve the accepted Stage 4 authentication pipeline and security semantics.
- Every Stage 5 route that mutates data uses CSRF-protected web forms and server-side validation.
- Use Form Requests for create/edit/document input validation.
- Use policies/authorization for resource access; hiding a UI action is not authorization.
- Status transitions only through `UserStatusTransitionService`; never direct status assignment from controllers/forms.
- Significant state changes must be auditable with the acting administrator.
- Disable/reject/delete must use `UserSessionInvalidator`; do not duplicate raw session-table deletion in controllers.
- `admin`, password, `accept`, generated columns, and other protected system fields must not be mass assignable from request input.
- Do not invent dictionary values, payment behavior, email behavior, or a numeric document upload limit.
- Keep documents private and never expose raw storage paths.
- Use the existing centralized date/time formatting conventions for display.
- All UI must compose shared generic design-system components; no psychologist-page-specific visual primitive or page-specific CSS fork.
- If an actually required design value/pattern is absent or unresolved, stop/report the design gap rather than inventing it.
- Keep `uikit/` reference-only; do not load it at runtime or modify it merely to match implementation.
- Do not add dependencies unless an actual blocker is proven first.

## Acceptance Criteria

1. An authenticated administrator has a real responsive psychologist-management section using the approved List/Form/Detail design patterns.
2. The psychologist list supports working search, status/tariff filters, truthful empty/no-result states, pagination, and no N+1 regression.
3. Administrator can manually create a psychologist with all applicable questionnaire fields; new records are non-admin, pending, enabled, have no product password, and do not trigger email.
4. Active email uniqueness behaves consistently with the database generated-column rule, including reuse after soft delete.
5. Administrator can view all stored psychologist fields and edit only approved profile fields.
6. Status is not generic form data: pending users can be approved/rejected only through `UserStatusTransitionService`, invalid transitions are blocked, and no approved->rejected shortcut is introduced.
7. Tariff and disabled/enabled are explicit audited actions; tariff changes do not mutate existing groups.
8. Disable, reject, and soft delete invalidate every target database session through `UserSessionInvalidator` without deleting unrelated sessions.
9. Soft delete removes the psychologist from normal admin lists/access while preserving related domain/document data; no restore UI is introduced.
10. Required significant actions persist immutable actor-aware audit records without sensitive payloads.
11. Admin can upload, view/download, and delete private PDF/JPEG/PNG psychologist documents; files use random private paths and correct metadata, and no direct public URL/path is exposed.
12. Authorization prevents psychologists from opening admin CRUD and prevents IDOR access to another psychologist's documents.
13. Newly needed shared Chip/Filters, Key-Value, File Upload, and File/Document Item components are implemented centrally according to `DESIGN_SYSTEM.md`/`uikit/index.html` and demonstrated on `/ui-kit`.
14. No external questionnaire route, email/password onboarding, Stage 6 product cabinet, group flow, dictionary CRUD, or bank/payment behavior is introduced.
15. Existing Stage 1–4 behavior and security tests remain green.
16. Focused Stage 5 tests and the full isolated-MySQL suite pass.
17. Pint, Larastan/PHPStan, platform requirements, and applicable JS/runtime checks pass.
18. Browser verification covers admin list/create/detail/edit/significant actions/document flow at desktop and mobile widths and confirms no runtime CDN/build regression.
19. `docs/project-status.md` truthfully records Stage 5 as implemented only after completion; `.ai/report.md` accurately records actual checks and the still-unset numeric document-size product limit.
20. Final diff contains only files required for Stage 5 and the minimal shared design-system expansion it actually consumes.

## Checks

Run and report at minimum:

- focused Stage 5 psychologist CRUD/list/filter/authorization tests;
- focused status action/audit/session-invalidation tests;
- focused document upload/MIME/private-access/IDOR/delete tests using storage fakes where appropriate;
- existing Stage 4 `AuthenticationAccessTest`, `StaleAuthenticatedSessionTest`, and `UserSessionInvalidatorTest`;
- existing UI-kit feature tests plus coverage for newly implemented shared components where practical;
- full `php artisan test` on the isolated MySQL test database;
- `composer check` including Pint and Larastan/PHPStan;
- `composer check-platform-reqs`;
- JS syntax check if `public/app.js` changes for generic upload/filter behavior;
- `git diff --check`;
- route inspection confirming all Stage 5 mutations are protected web routes and no `/api/v1` route was added;
- local browser flow as development admin: list -> search/filter -> create -> detail -> edit -> approve/reject on appropriate fixtures -> tariff change -> disable/enable -> delete;
- real browser document flow: upload allowed file -> authorized view/download -> delete, with direct/public path unavailable;
- access-revocation browser check where practical: psychologist session exists, admin disables target, next psychologist protected request loses access;
- desktop and mobile browser verification of list/form/detail/filter/confirmation/document UI using the approved design system;
- browser console/network inspection for errors, external assets, CDN requests, or build-tool regressions;
- final `git status --short`, complete diff, and staged-file inspection before commit.

The product owner remains the final manual tester for Stage 5 UX/visual acceptance.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, current `SPEC.md` Stage 5 plus relevant §4.8, §5–7, §20–25, §28–29 rules;
- read `DESIGN_SYSTEM.md` and inspect current `uikit/index.html` sections relevant to List/Form/Detail, Table Toolbar/Filters, File Upload, File/Document Item, Key-Value, navigation, confirmations, and responsive behavior;
- inspect current `User`, `UserDocument`, user/document migrations, `UserStatus`, `UserStatusTransitionService`, `UserSessionInvalidator`, Stage 4 middleware/routes/tests, existing UI components, filesystem config, seed/dictionary state, and current admin acceptance view;
- inspect recent Git history and `git status --short`;
- confirm `TASK-2026-08-19-10` is the current planned task created from `8ad8d6f2f5c016ea54324e0de2ee2a718f08323a`, Stage 4 is accepted, and Stage 5 has not already been implemented;
- identify which shared UI components already exist and implement only the missing Stage 5-required components listed in this task;
- do not touch unknown local changes.

Before commit:

- complete all applicable automated/runtime/browser checks;
- update factual docs and `.ai/report.md` with actual results only;
- inspect final Git status, full diff, generated migrations, storage/config changes, and staged files;
- stage only Stage 5-related files;
- ensure no uploaded test documents, local private files, screenshots, browser artifacts, logs, caches, real personal data, credentials, secrets, or unrelated files are committed;
- ensure `uikit/index.html` / `uikit/support.js` remain reference-only and unchanged unless a separate necessary reference correction is explicitly justified in the report.

If the gate passes, commit with:

`codex: TASK-2026-08-19-10 implement administrative psychologist CRUD`

If a required product/design/security decision is genuinely unresolved and prevents safe completion, report `partial` or `blocked` rather than inventing behavior.