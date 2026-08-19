# Task: TASK-2026-08-19-12

Status: planned
Created from: 10c8d94825859f2d6612888ab50f08e69ecf73d3

## Title

Implement Stage 6 psychologist cabinet

## Goal

Implement the revised `SPEC.md` Stage 6 as the first real psychologist-facing product surface, using only the already accepted internal authentication, profile data, private-document access, and Stage 3/5 design-system primitives.

After this task an approved, enabled psychologist can enter a real responsive cabinet, navigate between **Мои группы** and **Мои данные**, see a truthful pre-Stage-7 empty groups state, and read their own questionnaire/profile data and private documents. The cabinet must be strictly self-scoped: it must not accept another psychologist ID as a way to select profile data, must not leak another user's fields or documents, and must preserve all Stage 4/5 access boundaries.

This task must not start Stage 7 group CRUD/moderation. In particular, it must not add group creation/edit/delete, group status actions, group detail, application counters, moderation history, payments, or fake controls for functionality that does not exist yet.

## Facts

- Current `main` at task creation is `10c8d94825859f2d6612888ab50f08e69ecf73d3` (`codex: TASK-2026-08-19-11 fix Stage 5 UI design-system gaps`).
- Stage 1–4 are accepted. The user requested moving to the next stage after technical acceptance of the Stage 5 correction, so Stage 5 is the implemented baseline for this task.
- Current `SPEC.md` Stage 6 requires a shared psychologist cabinet layout, pages **Мои группы** and **Мои данные**, display of the current psychologist's data/documents, empty states, mobile support, self-only access, and no admin access.
- `SPEC.md §23` states that **Мои данные** shows the psychologist questionnaire data and that self-editing is not required in MVP.
- Stage 7, not Stage 6, owns the real group list content, group create/edit/delete, moderation workflow, group status/history presentation, ownership policies for group CRUD, and group actions.
- The accepted Stage 4 route boundary already provides `stale-session -> auth -> eligible -> role:psychologist` for `/cabinet`.
- The current `/cabinet` is only a Stage 4 acceptance view and has no product content yet.
- `App\Models\User` already exposes `educationType`, `documents`, `groups`, `fullName()`, typed questionnaire booleans, and `personal_data_consent_at`.
- Stage 5 already implemented private documents on `storage/app/private`, centralized `UserDocumentType` labels, `x-ui.document-item`, and owner/admin document authorization. An authenticated psychologist can view/download only their own document through the existing protected `documents.view` / `documents.download` routes; another psychologist receives authorization denial.
- Stage 5 already implemented the corrected shared `Description List`, `Document Item`, `Empty State`, AppShell/Drawer, PageHeader, Card, Date and other primitives needed by this stage. No new visual component is expected to be necessary.
- `DESIGN_SYSTEM.md §4.29` defines the Empty State, §4.37 the File/Document Item, §4.38 the Key-Value/Description List, and §6 the approved page composition patterns.
- The relevant `uikit/index.html` reference contains the approved Empty State, List Item/File Item, Key-Value, Sidebar/Drawer and responsive visual intent. It remains reference-only.
- The development/testing psychologist from Stage 4 already provides known local credentials for manual cabinet verification. Stage 5 can be used to populate that development psychologist's profile and documents if richer manual fixtures are desired; Stage 6 does not need a new password mechanism or extra product seed identity.

## Assumptions

- Use a reusable psychologist cabinet layout, analogous in responsibility to the accepted admin layout, composed from `x-ui.app-shell`. It should expose only real Stage 6 navigation: **Мои группы**, **Мои данные**, and POST logout.
- `/cabinet` should resolve to the primary **Мои группы** section, preferably by redirecting to a named `/cabinet/groups` route rather than keeping a third fake overview page.
- Use `/cabinet/groups` for **Мои группы** and `/cabinet/profile` for **Мои данные** unless an existing repository convention discovered during implementation provides an equally clear stable route. Do not expose a user ID parameter for the own-profile route.
- Because Stage 7 owns the actual group list/CRUD, Stage 6 **Мои группы** is intentionally an empty-content state. The shared Empty State replaces the future list data region; do not fabricate a toolbar, table, pagination, group card schema, or add-group CTA merely to make the page look fuller. This is the pre-Stage-7 empty state of the future list surface, not a new product page pattern.
- The psychologist's **Мои данные** page is read-only. It shows questionnaire/profile information useful to the psychologist and their private documents, but does not expose administrator-only controls or system/security fields such as tariff management, disabled toggle, status transitions, `admin`, password, `accept`, session state, audit records, or internal storage paths.
- The questionnaire fields visible to the psychologist are the same personal/questionnaire facts already stored for that user: identity/contact, education/training/license, group-leading experience, confirmations, webinar readiness, personal-data consent metadata, and their document list. Nullable values use the existing neutral `Не указано` representation.
- Existing owner document routes are the correct delivery boundary. Stage 6 should link to them rather than add duplicate cabinet-specific file-serving routes.
- `SPEC.md §28` requires explicit authorization. For the own-profile cabinet controller, add or reuse a clearly named policy/gate ability for self-view access rather than relying only on Blade assumptions. Do not broaden the existing admin-oriented `UserPolicy::view` semantics in a way that weakens Stage 5 authorization.
- No new design-system component should be added unless implementation proves an actually required shared primitive is missing. If a required visual pattern is absent or unresolved, report the gap instead of inventing it.

## Unknowns

None that block this stage.

## Scope

### 1. Real psychologist cabinet routes and controller boundary

Replace the Stage 4 acceptance-only `/cabinet` surface with real Stage 6 routes/controllers while preserving the existing protected middleware pipeline.

At minimum:

- `/cabinet` resolves to the main **Мои группы** section;
- `GET /cabinet/groups` renders **Мои группы**;
- `GET /cabinet/profile` renders **Мои данные**;
- all three remain inside `stale-session -> auth -> eligible -> role:psychologist`;
- an administrator cannot use these routes as a parallel product interface;
- a guest is redirected by normal auth behavior;
- a psychologist made ineligible remains revoked by the accepted Stage 4 middleware behavior.

Use a thin controller for product reads rather than growing route closures. Keep data selection out of Blade.

For the own-profile action:

- derive the psychologist from the authenticated request/session only;
- do not accept `user_id`, `psychologist_id`, route model binding, query parameter, hidden input, or other client-controlled identity selector for **Мои данные**;
- explicitly authorize the self-view operation through a policy/gate ability appropriate to the existing architecture;
- eager-load only the relationships needed for the page, at minimum `educationType` and `documents`.

Do not add any mutation endpoint to the cabinet in this stage.

### 2. Reusable psychologist cabinet layout/navigation

Add a reusable cabinet layout based on the shared application shell.

Navigation must contain only real current product sections:

- **Мои группы**;
- **Мои данные**;
- POST logout using the existing route/CSRF behavior.

Requirements:

- active navigation state follows the current section;
- desktop uses the existing persistent Sidebar;
- tablet/mobile use the existing AppShell Drawer/Topbar behavior;
- do not add disabled/fake links for Stage 7+ features;
- do not duplicate the complete shell markup per page;
- no page-specific colors, spacing, icons or navigation variant outside the existing design system.

If a small generic navigation helper/partial is needed to avoid unsafe raw HTML duplication, keep it generic and within the existing UI/layout architecture. Do not refactor the accepted admin layout unless the smallest safe reuse genuinely requires it.

### 3. **Мои группы** pre-Stage-7 empty state

Implement the real cabinet landing section with a truthful empty state only.

Requirements:

- PageHeader title: **Мои группы** with concise truthful supporting text;
- use the shared `x-ui.empty-state` with an approved empty-data icon/state;
- communicate that groups will appear in this section once they are added in the product flow;
- no **Добавить группу** button or any other CTA pointing to an unimplemented route;
- no fake group rows/cards, fake counts, fake statuses, fake moderation notices, toolbar, filters, pagination, applications, payments, or dates;
- do not implement Group queries/serialization merely to pre-build Stage 7;
- do not add Group policies/controllers/actions as part of this task.

The page must remain visually valid on desktop and mobile using only existing shared components/tokens.

### 4. **Мои данные** read-only profile page

Implement the current psychologist's questionnaire/profile as the approved Detail-page composition using shared Card + Description List primitives.

Display, where present:

- surname, first name, middle name;
- email and phone;
- education type;
- other education;
- modality/program;
- training center;
- graduation year;
- training hours;
- license number;
- license expiry date;
- group-leading experience;
- groups-held count;
- documents-truth confirmation;
- education-compliance confirmation;
- webinar/live readiness;
- personal-data-consent date/time;
- personal-data-consent version.

Presentation requirements:

- nullable/empty values render truthfully as `Не указано`;
- questionnaire booleans render the same centralized yes/no/not-specified wording used by the accepted Stage 5 presentation, without raw `0/1`;
- dates/date-times use existing date/time presentation conventions; display timestamps in configured `Europe/Minsk` presentation timezone;
- do not show raw enum/database codes where a human-readable label already exists;
- do not show tariff (`free`), admin flag, disabled flag, password/remember token, legacy `accept`, generated `active_email`, internal audit metadata, storage paths, or session information as profile data;
- do not render Edit, Save, Upload, Delete, status-action, tariff-action, access-action, or password controls;
- do not add a profile update endpoint.

Reuse the corrected shared Description List. Do not create a cabinet-only Key-Value variant or page-specific CSS fork.

### 5. Own private documents in **Мои данные**

Render only the current psychologist's documents below the profile information.

Requirements:

- use the existing `x-ui.document-item` and centralized `UserDocumentType` label;
- show original filename and safe metadata such as document category and size;
- provide **Открыть** / **Скачать** through the existing authorized `documents.view` / `documents.download` routes;
- do not expose `path`, filesystem disk name, private storage directory, or a `/storage/...` URL;
- no upload control in the psychologist cabinet;
- no delete control in the psychologist cabinet;
- if there are no documents, render a truthful shared empty state rather than a broken/blank area;
- no document mutation behavior is introduced.

Preserve the existing document policy: owner can receive their bytes, another psychologist cannot. Do not weaken admin document access.

### 6. Self-scope and IDOR protection

Stage 6 must make cross-user access structurally difficult and test it explicitly.

At minimum:

- **Мои данные** has no route parameter selecting a user;
- when two psychologists exist, the first psychologist's profile page renders the first user's fields/documents and does not render a unique marker belonging only to the second user;
- a psychologist can open/download their own document;
- the same psychologist receives 403/not-found equivalent for another psychologist's document and never receives the bytes;
- an administrator cannot access psychologist-only cabinet routes;
- a psychologist still receives denial for Stage 5 admin routes;
- changing query parameters must not select another psychologist's profile because profile identity comes only from the authenticated user.

Keep existing Stage 4 stale-session/access revocation and Stage 5 document-policy tests green.

### 7. UI/design-system and responsive verification

Use `DESIGN_SYSTEM.md` and `uikit/index.html` exactly as governance requires.

Expected existing shared components are sufficient:

- AppShell / Drawer navigation;
- PageHeader;
- Card;
- Description List / Description Item;
- Document Item;
- Empty State;
- Date;
- normal text/link primitives.

Do not add a new visual variant simply for the cabinet.

Verify at least normal desktop and mobile (~390px):

- navigation active states;
- mobile Drawer access to both sections and logout;
- **Мои группы** empty state fits without overflow;
- **Мои данные** Description Lists collapse/read correctly;
- document items/actions remain usable on mobile;
- no horizontal page overflow;
- no runtime CDN/build regression;
- keyboard focus remains the accepted shared behavior.

No UI-kit change is required unless a generic shared component actually changes. If no shared component changes, keep `/ui-kit` untouched and just preserve its tests.

### 8. Tests

Add focused feature/integration coverage, preferably in a dedicated Stage 6 cabinet test file.

Cover at minimum:

1. guest `/cabinet`, `/cabinet/groups`, and `/cabinet/profile` requests follow normal login redirect behavior;
2. administrator receives the existing explicit role denial on psychologist cabinet product routes;
3. approved enabled psychologist reaches `/cabinet`, is directed to **Мои группы**, and sees the Stage 6 empty state;
4. **Мои группы** contains no fake Stage 7 create/edit/group action route or button;
5. psychologist opens **Мои данные** and sees their own questionnaire values including representative nullable, boolean and date/time fields;
6. profile page does not show another psychologist's unique data marker;
7. profile page does not expose admin/security fields or mutation controls;
8. own documents are listed with view/download links and no raw private path;
9. profile with zero documents renders the approved empty document state;
10. own document view/download remains authorized and another psychologist's document remains denied;
11. existing `/admin` denial for psychologists remains green;
12. existing disabled/rejected/soft-delete/stale-session behavior remains green;
13. Stage 5 admin CRUD/document/audit behavior remains green;
14. `/ui-kit` remains local/testing-only and unchanged unless a justified shared component modification occurs.

Where useful, assert query behavior remains bounded and no per-document relationship N+1 is introduced by the profile page.

### 9. Documentation and report

Update only documentation made factual by the implementation:

- `docs/architecture.md` — psychologist cabinet self-scope/read boundary and reuse of the existing private-document policy/controller;
- `docs/development.md` — local Stage 6 URLs and concise manual verification using the development psychologist;
- `docs/project-status.md` — Stage 6 implemented and Stage 7 next;
- `.ai/report.md` — exact implementation, changed files, tests, browser checks and remaining gaps.

Do not change `SPEC.md`; Stage 6 and the roadmap are already approved.

## Out Of Scope

- Stage 7 group CRUD, group forms, group detail, group moderation, group policies, group status actions, or group history UI.
- **Добавить группу** or any fake/stub Stage 7 action.
- Group application counters or application lists.
- Group expiry/lifecycle/extension behavior.
- Admin group management.
- Editing psychologist profile data from the psychologist cabinet.
- Uploading or deleting psychologist documents from the psychologist cabinet.
- Password setup/reset/change UI.
- Registration or public questionnaire intake.
- Email/SMTP/queued notifications.
- `/api/v1` external integrations.
- Payments/bank integration.
- New roles/permissions framework.
- Schema migrations or domain-model changes unless an unexpected concrete blocker proves one strictly necessary and is reported instead of guessed.
- New frontend dependencies, Node/npm/Vite, runtime CDN, Vue/React/Livewire/Inertia.
- Redesign of accepted Stage 3/5 shared components.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, current `SPEC.md`, `DESIGN_SYSTEM.md`, and `uikit/index.html`.
- Work only from current `main` and the current task; do not change `.ai/task.md` during implementation.
- Preserve the accepted Stage 4 auth/access pipeline and Stage 5 CRUD/document-security behavior.
- Current-user identity for **Мои данные** comes only from the authenticated request; never trust client-provided user IDs for self-profile selection.
- Keep controllers thin and use explicit policy/gate authorization for self-profile access.
- Do not duplicate private document delivery; reuse the existing authorized controller/routes.
- Use only shared design-system components/tokens; no cabinet-specific visual primitive or arbitrary CSS value.
- Do not invent future group UX while Stage 7 remains separate.
- No new dependencies unless an actual blocker is proven and reported first.
- No `uikit/` runtime dependency or modification.
- No secrets, real personal data, uploaded local fixtures, screenshots, browser artifacts, logs or caches in the commit.

## Acceptance Criteria

1. `/cabinet` is a real Stage 6 psychologist product entry and resolves to **Мои группы** rather than the old auth acceptance page.
2. The cabinet has a reusable responsive layout with real **Мои группы**, **Мои данные**, and POST logout navigation only.
3. **Мои группы** shows a truthful shared empty state and contains no fake Stage 7 group CRUD/action UI.
4. **Мои данные** displays only the authenticated psychologist's questionnaire/profile data in a read-only Detail composition.
5. Nullable, boolean and date/time profile values are rendered human-readably through existing presentation conventions.
6. The psychologist cabinet exposes no profile-edit, tariff, disable/status, password, audit, upload, or document-delete controls.
7. The current psychologist's private documents are listed through shared Document Item and existing authorized view/download routes; private paths are not exposed.
8. A psychologist cannot obtain another psychologist's profile data by URL/query manipulation and cannot obtain another psychologist's document bytes.
9. Guest/admin/psychologist route boundaries remain correct; ineligible/stale users continue to lose access according to Stage 4.
10. Stage 5 admin CRUD, status/audit/session revocation and private-document security remain green.
11. Desktop and mobile cabinet navigation, empty state, profile data and document list conform to the accepted design system with no horizontal overflow.
12. No Stage 7 group CRUD/moderation, public API, email/onboarding or payment behavior is introduced.
13. Focused Stage 6 tests and the full isolated-MySQL suite pass.
14. Pint, Larastan/PHPStan, platform requirements, applicable JS syntax check if JS changes, and `git diff --check` pass.
15. `docs/project-status.md` records Stage 6 as implemented only after implementation and verification are actually complete, with Stage 7 as next.
16. `.ai/report.md` accurately records actual verification and final diff contains only Stage 6-related files.

## Checks

Run and report at minimum:

- focused Stage 6 cabinet feature tests;
- existing `AuthenticationAccessTest` and `StaleAuthenticatedSessionTest`;
- existing Stage 5 admin CRUD and psychologist document tests;
- existing `UiKitPageTest`;
- full `php artisan test` on the isolated MySQL test database;
- `composer check` including Pint and Larastan/PHPStan;
- `composer check-platform-reqs`;
- `node --check public/app.js` only if JS changes;
- local route inspection for `/cabinet`, `/cabinet/groups`, `/cabinet/profile` and existing document routes;
- local HTTP/browser login as development psychologist;
- desktop browser verification of **Мои группы** and **Мои данные**;
- mobile browser verification around 390px, including Drawer navigation and document actions;
- browser self-scope check with two psychologists/unique markers if safely possible;
- browser console/network inspection: no errors/warnings caused by the task and no runtime CDN/build-tool requests;
- `git diff --check`;
- final `git status --short`, full diff and staged-file inspection.

Final manual visual/product acceptance remains with the product owner.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, current `SPEC.md` Stage 6 and relevant §23/§28/§29 rules, `DESIGN_SYSTEM.md`, `docs/architecture.md`, `docs/development.md`, and `docs/project-status.md`;
- inspect `routes/web.php`, current Stage 4 `/cabinet` view, `User`, `UserDocument`, `UserDocumentPolicy`, existing document controller/routes, Stage 5 admin profile/detail presentation, the existing UI components named in this task, and relevant auth/document tests;
- inspect the approved `uikit/index.html` sections for navigation, Empty State, List/File Item, Key-Value/Description List and responsive behavior;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-12` is the current planned task created from `10c8d94825859f2d6612888ab50f08e69ecf73d3` and Stage 7 has not already started;
- do not touch unknown local changes.

Before commit:

- complete all applicable focused/full/browser checks;
- update `.ai/report.md` with actual results only;
- inspect final Git status, full diff and staged files;
- stage only Stage 6-related files;
- ensure no secrets, real personal data, test uploads, screenshots, browser artifacts, logs, caches or unrelated files are committed.

If the gate passes, commit with:

`codex: TASK-2026-08-19-12 implement Stage 6 psychologist cabinet`

If a required product/design/security behavior cannot be safely implemented within this scope, report `partial`, `blocked`, or `failed` instead of starting Stage 7 or weakening existing boundaries.
