# Task: TASK-2026-08-19-11

Status: planned
Created from: e06dd53b99938ee8280495747ef297f6947248c7

## Title

Fix Stage 5 UI and design-system conformance gaps

## Goal

Close the four UI/design-system blockers found during acceptance of `TASK-2026-08-19-10` without changing the accepted Stage 5 CRUD, authorization, audit, document-security, session-revocation, or domain behavior.

The correction must make the shared File Upload interaction truthful and functional, align the shared two-column Description List with the exact canonical design values, compose confirmation modals through the approved Modal body/footer pattern, and restore the psychologist List page to the approved `PageHeader -> TableToolbar -> 16px gap -> Table -> Pagination` composition.

Stage 5 remains unaccepted until these corrections are complete.

## Facts

- Current `main` at task creation is `e06dd53b99938ee8280495747ef297f6947248c7` (`codex: TASK-2026-08-19-10 implement administrative psychologist CRUD`).
- Stage 1–4 are accepted.
- Stage 5 backend/security implementation passed review; the remaining blockers are UI/design-system conformance only.
- The current Stage 5 implementation already has working psychologist CRUD, policies, status transitions, audit, private document storage/delivery, MIME validation, IDOR protection, session invalidation, search/filters/pagination, and automated tests.
- `DESIGN_SYSTEM.md` already contains the canonical rules needed for all four corrections. It is not missing a design decision and must not be changed merely to justify the current implementation.
- `uikit/index.html` remains the approved visual reference where `DESIGN_SYSTEM.md` does not already resolve intent.
- The current File Upload component says the user may choose or drag a file, and renders a drag-over state, but its JS does not actually transfer dropped files into the native file input.
- The current two-column Description List uses `1fr 1fr` and `12px 32px`, while `DESIGN_SYSTEM.md §4.38` requires `grid-template-columns:max-content max-content`, `justify-content:start`, and `gap:8px 16px`.
- The current psychologist detail page nests standalone `x-ui.confirmation` inside `x-ui.modal`. The Modal design specification defines confirmation/destructive confirmation as a Modal usage pattern whose short message lives in the body and whose actions live in the Modal footer.
- The current psychologist list wraps Toolbar, Table, and Pagination in one bordered `.ui-table-wrap`, with the Table starting immediately under the toolbar. `DESIGN_SYSTEM.md §6` requires the List page sequence `PageHeader -> TableToolbar -> 16px gap -> Table -> Pagination`, with Pagination as the Table footer and no gap between Table and Pagination.
- `DESIGN_SYSTEM.md` and `uikit/` are reference/governance sources only and remain outside runtime.

## Assumptions

- Fix the shared components and shared layout primitives where the defect is generic; do not patch only one psychologist page if the same shared component would remain wrong elsewhere.
- File Upload remains a normal multipart form control. This task does not introduce AJAX upload or fake progress behavior. Drag-and-drop only needs to select the dropped file(s) into the existing native input so the existing form submit flow works.
- The current File Upload is single-file. A dropped selection should obey the native single-file semantics rather than silently adding multi-file behavior.
- The existing `x-ui.modal` footer slot is the correct primitive for confirmation actions. Do not create a new confirmation-modal component unless the existing shared Modal API proves insufficient.
- Standalone `x-ui.confirmation` remains valid for standalone confirmation surfaces defined by `DESIGN_SYSTEM.md §4.32`; this task only removes its misuse inside Modal confirmation flows.
- The psychologist list may use a small generic shared list/table composition wrapper if useful, but it must not introduce psychologist-specific layout CSS.

## Unknowns

None that block this correction.

## Scope

### 1. Make File Upload drag-and-drop actually work

Correct the shared File Upload behavior in `resources/views/components/ui/file-upload.blade.php`, `public/app.js`, and shared CSS only as needed.

Required behavior:

- clicking the dropzone continues to open the native file picker;
- `dragenter` / `dragover` prevent the browser's default file-open/navigation behavior and activate the approved drag-over state;
- `dragleave` removes the drag-over state when appropriate;
- `drop` prevents default browser behavior, removes the drag-over state, and transfers the dropped file into the component's native `<input type="file">` using browser-supported file APIs;
- after a successful drop, the same visible filename label used by picker selection updates to the dropped filename;
- the existing form submit sends the dropped file through the same multipart field as picker selection;
- do not bypass the existing server-side MIME/type/size validation;
- do not implement client-side security validation as a replacement for server validation;
- do not implement AJAX upload, upload progress, multiple files, or a new upload workflow;
- if a drag payload contains no file, leave the current file selection unchanged and do not navigate/open it in the browser.

Keep the approved File Upload dimensions, colors, border, drag-over styling, error styling, and visual language unchanged unless a concrete existing implementation value differs from `DESIGN_SYSTEM.md`.

Add the smallest useful regression coverage. Because project Node/browser dependencies are prohibited, do not add a JS test framework. If existing browser tooling is available externally, verify a real drag-and-drop interaction there and record the result. Static PHP/UI tests may verify required hooks/markup but must not falsely claim they prove browser drop behavior.

### 2. Correct the shared two-column Description List

Align the shared `Description List / Key-Value` two-column variant exactly with `DESIGN_SYSTEM.md §4.38`.

For the two-column variant use:

- `display:grid`;
- `grid-template-columns:max-content max-content`;
- `justify-content:start`;
- `gap:8px 16px`;
- existing approved label/value typography.

Do not keep the current invented `1fr 1fr` / `12px 32px` desktop rule.

Preserve the approved responsive behavior for narrow screens without inventing a new component variant. If the existing mobile collapse is necessary for readability, ensure it composes from the same shared component and does not change the canonical desktop/tablet values.

Verify the psychologist detail page and `/ui-kit` use the corrected shared component with no page-specific override.

### 3. Recompose confirmation modals through the Modal footer

Fix all Stage 5 confirmation/destructive-confirmation flows on the psychologist detail page so they use the approved Modal usage pattern.

For Modal-based confirmations:

- Modal Header contains the modal title and close action;
- Modal Body contains the short explanatory/confirmation message;
- Modal Footer contains the actions;
- desktop/tablet action order remains Secondary/Cancel left and Primary/Danger right;
- mobile action behavior continues to follow the existing approved full-width stacked ordering;
- destructive actions use the Danger button variant;
- do not nest the standalone bordered/padded Confirmation component inside Modal body;
- remove any shared CSS special case whose only purpose was to neutralize a nested `.ui-confirmation` inside `.ui-modal`, unless another approved UI-kit example still requires it for a valid reason;
- keep standalone `x-ui.confirmation` itself correct and available for standalone use.

Apply this to all Stage 5 psychologist confirmation flows, including approve, reject, tariff change, enable/disable, psychologist delete, and document delete.

Do not change the underlying routes, HTTP methods, authorization, domain operations, session invalidation, audit, or confirmation wording except where minimal wording cleanup is required to fit the proper body/footer structure.

### 4. Restore the approved List page composition

Correct the psychologist list layout to match `DESIGN_SYSTEM.md §6` exactly at the composition level:

1. PageHeader;
2. TableToolbar with a 24px gap from PageHeader;
3. 16px gap between TableToolbar and Table;
4. Table;
5. Pagination immediately attached/rendered as the Table footer with 0px gap.

Requirements:

- do not wrap Toolbar + Table + Pagination into one invented bordered shell if that removes the required Toolbar-to-Table gap;
- the Table keeps its own approved border/radius/container appearance from §4.18;
- Pagination visually belongs to the Table footer and does not gain a separate arbitrary card/shell;
- preserve working search, desktop Filters, mobile Drawer Filters, chips, result count, empty/no-results behavior, non-selectable table mode, pagination URLs/query preservation, and responsive horizontal table behavior;
- if shared Table/Pagination components need a small generic composition adjustment to achieve the approved pattern, implement it generically and keep existing UI-kit examples valid;
- no psychologist-page-specific spacing, border, radius, or shadow values.

Update `/ui-kit` only if needed to keep the canonical shared List/Table/Pagination presentation inspectable.

### 5. Preserve Stage 5 backend/security behavior

Do not change product/domain behavior while fixing UI.

Explicitly preserve:

- admin-only psychologist CRUD;
- protected profile-field allowlist;
- `pending -> approved/rejected` domain transitions;
- tariff and enabled/disabled actions;
- `UserSessionInvalidator` behavior on reject/disable/delete;
- append-only actor audit;
- active-email uniqueness semantics;
- private storage and `serve=false` behavior;
- PDF/JPEG/PNG content/MIME validation;
- document owner/admin access and IDOR protections;
- no public `/api/v1`, email/onboarding, Stage 6 cabinet content, payment flow, or new role system.

### 6. Tests, browser verification, and report

Add/update focused tests only where they provide meaningful regression protection for these corrections.

At minimum verify:

- shared Description List renders the corrected canonical class/structure and Stage 5 detail still renders;
- Stage 5 confirmation modals render actions through Modal footer and do not render nested standalone confirmation wrappers inside those modals;
- psychologist list renders Toolbar, Table, and Pagination in the intended separate/attached composition without breaking search/filter/pagination behavior;
- File Upload still renders the shared native file input/dropzone hooks and normal multipart upload tests remain green;
- real browser drag-and-drop selection/upload if existing browser tooling supports synthetic drag/drop without adding repository dependencies;
- desktop and mobile Stage 5 flows remain visually usable after the layout correction.

Run the full existing project gate and preserve all Stage 5 backend/security tests.

Update `.ai/report.md` with the exact four corrections, changed files, test/check results, browser verification actually performed, and any limitation. Update factual docs only if the shared UI implementation description materially changes; avoid documentation churn.

## Out Of Scope

- Any Stage 6 psychologist cabinet product work.
- Changes to CRUD/domain/status/audit/session business behavior.
- New user fields or schema changes.
- New document types or upload business rules.
- AJAX upload or progress UI.
- Multiple-file upload.
- Email/onboarding/password setup.
- Public API/integration routes.
- Payment/bank functionality.
- Redesigning unrelated Stage 3/5 components.
- Changing `DESIGN_SYSTEM.md` to match the implementation; the design document already contains the required canonical rules.
- Changing `uikit/index.html` or `uikit/support.js`.
- Node/npm/Vite or new browser/frontend dependencies.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, `DESIGN_SYSTEM.md`, and current `SPEC.md`.
- Before implementation, inspect the exact relevant sections in `DESIGN_SYSTEM.md` and the corresponding `uikit/index.html` examples for File Upload, Key-Value/Description List, Modal/Confirmation, Table Toolbar/Table/Pagination, and List page composition.
- Explicit numeric values and implementation rules from `DESIGN_SYSTEM.md` take priority over the visual reference.
- Fix shared design-system primitives rather than creating page-specific copies or overrides.
- Use only the existing Blade + local Bootstrap + project CSS + Vanilla JS stack.
- Do not add dependencies, runtime CDN requests, Node/npm/Vite, or frontend build tooling.
- Preserve all Stage 4 authentication and Stage 5 security behavior.
- Keep the correction narrowly scoped to the four acceptance blockers.

## Acceptance Criteria

1. Dragging a valid file onto the shared File Upload selects that file in the native input and the existing form can submit it normally.
2. File Upload prevents default browser file-navigation behavior during drag/drop and retains the approved drag-over/error visual states.
3. File picker selection continues to work and uses the same filename display as drag/drop.
4. No AJAX/multiple-file/progress behavior is introduced.
5. The shared two-column Description List uses exactly `max-content max-content`, `justify-content:start`, and `8px 16px` gap on the canonical desktop/tablet presentation.
6. The psychologist detail page uses the corrected shared Description List without page-specific layout overrides.
7. Every Stage 5 Modal confirmation places message content in Modal Body and actions in Modal Footer; no standalone Confirmation shell is nested inside those Modal bodies.
8. Desktop/tablet and mobile confirmation action ordering/width remain compliant with the existing design-system rules.
9. The psychologist List page follows `PageHeader -> TableToolbar -> 16px gap -> Table -> Pagination`, with Pagination attached as the Table footer and no invented combined Toolbar/Table shell.
10. Search, filters, chips, pagination state/query parameters, empty/no-results behavior, and responsive table behavior remain functional.
11. `/ui-kit` continues to represent the corrected shared components and remains production-guarded.
12. No Stage 5 backend/security/domain behavior changes or regressions are introduced.
13. Existing auth/session, CRUD, audit, document security/MIME/IDOR, and UI-kit tests remain green.
14. Full PHPUnit/MySQL suite, Pint, Larastan/PHPStan, platform requirements, applicable JS syntax check, and `git diff --check` pass.
15. Browser verification covers the corrected desktop/mobile surfaces and real drag/drop if supported by the existing external browser tooling; any unsupported check is stated explicitly rather than claimed.
16. Final diff contains only files required for this Stage 5 UI correction.

## Checks

Run and report at minimum:

- focused Stage 5 UI/component tests for corrected Description List, Modal confirmation composition, and List layout;
- existing `AdminPsychologistCrudTest` and `PsychologistDocumentTest`;
- existing auth/session security regressions;
- existing and updated `UiKitPageTest`;
- full `php artisan test` on isolated MySQL;
- `composer check` including Pint and Larastan/PHPStan;
- `composer check-platform-reqs`;
- `node --check public/app.js` if Node is available externally without adding it as a project dependency;
- local HTTP checks for psychologist list/detail and `/ui-kit`;
- real browser desktop and mobile check of list spacing, filters, detail Description List, and confirmation modals;
- real browser File Upload picker and drag/drop interaction if supported by existing tooling;
- browser console/network inspection: no new errors/warnings and no runtime CDN requests;
- `git diff --check`;
- final `git status --short`, full diff, and staged-file inspection.

Final manual visual/product acceptance remains with the product owner.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, relevant Stage 5 `SPEC.md`, `DESIGN_SYSTEM.md`, and current Stage 5 report/status documentation;
- inspect current `public/app.css`, `public/app.js`, `x-ui.file-upload`, `x-ui.description-list`, `x-ui.modal`, `x-ui.confirmation`, Table/Toolbar/Pagination components, psychologist list/detail views, and relevant UI tests;
- inspect the matching `uikit/index.html` reference sections;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-11` is the current planned task created from `e06dd53b99938ee8280495747ef297f6947248c7` and Stage 5 is implemented but not accepted;
- do not touch unknown local changes.

Before commit:

- complete all applicable focused/full/browser checks;
- update `.ai/report.md` with actual results only;
- inspect final Git status, full diff, and staged files;
- stage only correction-related files;
- ensure no screenshots, browser artifacts, test uploads, logs, caches, secrets, or unrelated files are committed.

If the gate passes, commit with:

`codex: TASK-2026-08-19-11 fix Stage 5 UI design-system gaps`

If safe completion is impossible, report `partial`, `blocked`, or `failed` instead of weakening the design-system or existing security behavior.
