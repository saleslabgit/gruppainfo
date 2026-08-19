# Report: TASK-2026-08-19-14

Status: done

## Summary

Closed the four Stage 7 acceptance blockers. Administrative cleanup now denies both `draft` and `awaiting_payment` groups while a current successful payment exists, hides the destructive action, and renders only safe payment facts. Refunded payments no longer block cleanup. Paid moderation rejection remains available but explicitly requires a separate manual refund.

Soft-deleted group owners and user history actors remain resolvable for truthful administrative reads, while actual system history remains attributed to `Система`. Money Input now keeps the approved outer `:focus-within` halo without a second native-input ring. Normal local/testing seed supplies clearly technical group format/gender fixtures, and incomplete submit now returns to a visible actionable error state. A session-based regression proves normal-seed login, create, edit and `draft → moderation` submission.

## Changed Files

- `app/Models/Group.php`, `GroupStatusHistory.php`, `app/Policies/GroupPolicy.php`, `app/Http/Controllers/Admin/GroupController.php` — successful-payment relation/guards, soft-deleted identity reads and eager-loaded payment facts.
- `resources/views/admin/groups/show.blade.php`, `_payment-facts.blade.php`, `resources/views/groups/_history.blade.php` — safe cleanup/rejection warnings and actor-type-aware Timeline attribution.
- `public/app.css`, `tests/Feature/UiKitPageTest.php` — inner Money Input focus suppression with retained outer focus regression.
- `database/seeders/DevelopmentGroupDictionarySeeder.php`, `DatabaseSeeder.php`, `tests/Feature/DatabaseSeederTest.php` — idempotent local/testing-only `group_format`/`gender` fixtures and production exclusion.
- `app/Http/Requests/GroupDataRequest.php`, `resources/views/cabinet/groups/show.blade.php` — actionable Russian submit validation messages, shared danger Alert and edit link.
- `tests/Feature/AdminGroupWorkflowTest.php`, `PsychologistGroupCrudTest.php` — payment/refund, deleted identity, failed submit and normal-seed session-flow regressions.
- `docs/architecture.md`, `docs/development.md`, `docs/project-status.md` — factual environment boundary for technical fixtures and still-unresolved production values.

## Checks

- Final combined focused plus Stage 4–6 regression run — passed: 62 tests / 589 assertions.
- Final `docker compose exec -T app composer check` — passed: Pint checked 113 files, Larastan/PHPStan reported no errors, isolated-MySQL suite passed with 90 tests / 1001 assertions.
- `docker compose exec -T app composer check-platform-reqs` — passed for PHP 8.2.32 and every declared extension.
- Real headless Chromium at 1440px and 390px — passed normal-seed psychologist login/create/edit/save/submit with visible success and `На модерации`; incomplete submit rendered actual validation feedback and edit action; Money Input had the outer edge/halo and computed inner `box-shadow: none`/transparent border at both widths.
- Chromium admin checks — passed blocked cleanup warning with amount/currency/transaction/date-safe surface and no cleanup/raw bank response, paid rejection manual-refund reminder, and retained soft-deleted owner/user actor plus actual `Система` history.
- Browser console errors: 0; external/CDN requests: 0; checked mobile pages had no horizontal overflow.
- Temporary browser groups, payments, history and user were force-removed by their unique `Task14 browser` markers; post-cleanup counts were `0 0 0`. Temporary scripts, Playwright output and snapshots were removed.
- `git diff --check` — passed.

## Facts

- A current `PaymentStatus::Succeeded` payment blocks psychologist deletion and administrative cleanup server-side. Changing the existing payment state to `refunded` removes the cleanup block; no refund mutation or bank action was added.
- Admin payment surfaces render only amount/currency, transaction ID and paid timestamp. `bank_response` is never rendered.
- `Group::owner()` and `GroupStatusHistory::actor()` include soft-deleted users for retained identity reads only. Authentication/session eligibility was not changed.
- Timeline attribution is derived from `actor_type`; a missing user relation is displayed as `Удалённый пользователь`, never falsely as `Система`.
- Local/testing fixtures use codes `development-test-format` and `development-test-gender`; production continues to seed no dictionary items.
- Failed complete-data validation preserves `draft`, writes no history, renders the validation messages and links back to the still-authorized edit form.
- Successful normal-seed submission creates exactly one `draft → moderation` user-actor history row through the existing workflow/state-transition boundary.

## Unknowns Preserved

- Production values for `group_format` and `gender` remain unapproved and unseeded.
- Rejection minimum length and abandoned-draft age threshold remain undefined; this correction does not invent them.

## Risks / Next Step

- No known TASK-2026-08-19-14 blocker remains. Stage 8 may define production dictionary values and administrative dictionary/settings CRUD after product approval.
