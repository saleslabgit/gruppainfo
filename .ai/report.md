# Report: TASK-2026-08-19-13

Status: done

## Summary

Implemented the complete Stage 7 internal group CRUD and moderation workflow. Psychologists now create direct `draft` groups for either tariff, save partial drafts, submit complete data, see feedback/history, revise/resubmit, list/view every own group, and soft-delete only policy-permitted groups. Administrators can create groups for active psychologists, list/search/filter/sort/paginate, view/edit content, moderate through the existing state machine, manually clean up draft states, and activate approved groups with configured placement dates and an optional external catalog ID.

All status changes remain inside `GroupStatusTransitionService`. `GroupWorkflowService` coordinates row locking, repeated authorization, side-field updates, and outer transactions. No Stage 7 action creates a payment, redirects to a bank, or reaches `awaiting_payment`. Shared Money Input and Timeline components were implemented from the approved design system and demonstrated on `/ui-kit`.

## Changed Files

- `app/Domain/Group/GroupStatus.php`, `GroupWorkflowService.php` — centralized Russian labels/badge variants and atomic Stage 7 workflow coordination.
- `app/Policies/GroupPolicy.php`, `app/Models/Group.php`, `bootstrap/app.php` — ownership/status/payment guards, model type facts, and safe stale/invalid-transition response.
- `app/Support/MoneyParser.php` — exact string-based BYN-to-minor-unit conversion without floats.
- `app/Http/Requests/**Group*.php` — explicit authorization, partial/complete group validation, active dictionary constraints, protected-field exclusion, moderation comments, activation input, and mutation requests.
- `app/Http/Controllers/Cabinet/GroupController.php`, `app/Http/Controllers/Admin/GroupController.php`, `GroupModerationController.php` — thin psychologist/admin CRUD, list and action endpoints.
- `routes/web.php`, `CabinetController.php` — protected Stage 7 routes and removal of the obsolete empty-only handler.
- `resources/views/cabinet/groups/**`, `resources/views/admin/groups/**`, `resources/views/groups/**` — approved List/Form/Detail group pages, shared form/history compositions and compliant action modals.
- `resources/views/components/ui/money-input.blade.php`, `timeline*.blade.php`, `table.blade.php`, `textarea.blade.php`, `public/app.css`, `resources/views/ui-kit.blade.php` — generic Money Input/Timeline, eight-column table support, explicit textarea IDs, responsive styles and UI-kit examples.
- `resources/views/layouts/admin.blade.php`, `layouts/cabinet.blade.php` — real group navigation/active states and cabinet flash feedback.
- `tests/Feature/PsychologistGroupCrudTest.php`, `AdminGroupWorkflowTest.php`, `PsychologistCabinetTest.php`, `UiKitPageTest.php` — Stage 7 workflow, policy/IDOR, payment absence, activation, filters, query bounds and shared-component coverage.
- `docs/architecture.md`, `docs/development.md`, `docs/project-status.md` — implemented Stage 7 boundaries, routes/manual verification, unresolved thresholds and Stage 8 next step.
- Removed the obsolete `resources/views/cabinet/groups.blade.php`; the real group views now live under `resources/views/cabinet/groups/`.

## Checks

- Focused Stage 7 tests (`PsychologistGroupCrudTest`, `AdminGroupWorkflowTest`, `UiKitPageTest`) — passed: 17 tests / 176 assertions.
- Existing Stage 4–6/domain regression subsets (`AdminPsychologistCrudTest`, `DomainModelTest`, `PsychologistCabinetTest`, `StatusTransitionServiceTest`) — passed: 23 tests / 396 assertions.
- Final `docker compose exec -T app composer check` — passed: Pint checked 112 files, Larastan/PHPStan reported no errors, isolated-MySQL suite passed with 86 tests / 957 assertions.
- `docker compose exec -T app composer check-platform-reqs` — passed for PHP 8.2.32 and every declared extension.
- `php artisan route:list --path=groups --except-vendor` — confirmed 18 protected admin/cabinet group routes; `/api/v1` route inspection returned no matching routes. No payment/bank routes were added.
- Real Chromium workflow at 1440px and 390px — passed: psychologist create/save/submit; admin revision; visible psychologist feedback and Timeline; edit/resubmit; approve/activate with calculated dates/external ID; separate rejection with visible reason; admin search/status/sort query; mobile Drawer/Timeline; no page-level horizontal overflow, console errors, or external/CDN requests.
- Temporary browser dictionary values, groups/history, Playwright spec and results were removed. Post-cleanup development DB counts for both temporary group names and dictionary codes were `0`.
- `git diff --check` — passed.
- `node --check public/app.js` — not applicable because JavaScript did not change.

## Facts

- Both `free=true` and `free=false` psychologists create a direct `draft`; `group.free` preserves the owner tariff snapshot and `gp_payments` remains unchanged.
- Psychologist ownership is derived only from the authenticated user; client owner filters and psychologist owner writes are absent.
- Draft/revision updates accept incomplete values but validate every supplied value. Submission validates all required business fields and combines content persistence with the transition atomically.
- Dictionary IDs must be active items of the active `group_format` or `gender` dictionary.
- Protected/system fields are absent from validated group data. Admin generic edit also cannot change ownership, tariff snapshot, status or publication fields.
- Revision/rejection comments are trimmed, required and written to both the current field and immutable transition history. Previous history is not overwritten.
- Activation locks the current row, reads a positive `placement_duration_days`, transitions only `approved → active`, stores the duration snapshot, sets UTC publication/expiry timestamps, clears the warning timestamp and stores a trimmed optional external ID in one transaction.
- Psychologist/admin list relations and history actors are eager-loaded; focused tests bound query counts.
- Admin manual cleanup is limited to `draft` and existing `awaiting_payment`; it does not expose generic deletion of moderation/approved/active/expired groups.

## Assumptions

- A zero meeting price remains structurally valid because the approved task explicitly requires positive integers only for duration and participant count and does not define a positive minimum price.
- Existing generic Modal responsive behavior remains the approved implementation used by the new moderation/activation dialogs.

## Unknowns

- The numeric minimum length for a rejection reason remains undefined. Stage 7 enforces a required, trimmed, non-empty reason and does not invent `min:N`.
- The age threshold for an “abandoned draft” quick filter remains undefined. Stage 7 provides normal `draft` filtering and manual draft cleanup but no fabricated age filter.
- Production dictionary item values for `group_format` and `gender` remain unapproved and unseeded.

## Risks / Next Step

- No known Stage 7 implementation blocker remains. Product-owner visual/product acceptance is still required.
- Stage 8 can add dictionary/settings CRUD and complete the remaining administrative surfaces without changing the Stage 7 state-machine or no-bank boundary.
