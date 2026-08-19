# Task: TASK-2026-08-19-07

Status: planned
Created from: 91193a555848be8024a4ae2476e35bb51e91415a

## Title

Fix Stage 3 visual acceptance and reusable-component gaps

## Goal

Correct the Stage 3 baseline before acceptance.

`TASK-2026-08-19-06` implemented the design-system foundation, but product-owner visual review and ChatGPT acceptance review found concrete visual and reusable-component gaps. Fix those gaps without starting Stage 4, without redesigning unrelated components, and without broadening the deferred component catalogue.

The result must remain a single shared design system governed by `DESIGN_SYSTEM.md` and `uikit/index.html`. Where the latest product-owner feedback below changes an existing visual rule, update `DESIGN_SYSTEM.md` so the correction becomes the new durable source of truth rather than a UI-kit-only patch.

## Facts

- Current `main` at task creation is `91193a555848be8024a4ae2476e35bb51e91415a` (`codex: TASK-2026-08-19-06 implement design system foundation`).
- Stage 1 and Stage 2 are accepted.
- Stage 3 baseline is implemented but **not accepted yet**.
- The current `/ui-kit` is the acceptance surface for this correction.
- The product owner reviewed `/ui-kit` at a wide desktop viewport and reported seven visual issues listed below.
- ChatGPT review also found reusable-component and responsive gaps listed below.
- Latest product-owner visual feedback has priority over older design documentation where the two conflict.
- `DESIGN_SYSTEM.md` must be updated minimally when a newly approved visual rule changes the existing system.
- `uikit/` remains reference-only and must not become a runtime dependency.
- No Node/npm/Vite/runtime-CDN/frontend framework may be introduced.

## Assumptions

- Fix visual problems at the shared component/token level whenever the issue is systemic; do not patch only the demo markup if the same defect would appear on product pages.
- If an issue exists only because the UI-kit displays multiple examples side-by-side, fix the comparison/demo layout without adding an unnecessary fixed size to the production component.
- Reuse the existing approved spacing scale. For the newly requested separation between adjacent select/navigation items, use the existing `4px` spacing token rather than inventing a new spacing value.
- Add a minimal shared motion system for interactive micro-transitions. Use one project token pair: `160ms` duration with `ease-out`; respect `prefers-reduced-motion: reduce`. Do not animate layout dimensions.

## Unknowns

None that block this correction.

## Scope

### 1. Form focus treatment — product-owner issues 1 and 2

The owner reports an unclear/broken inner outline on focused fields, including:

- normal text input (`#name`, `.ui-input`);
- search input inside `.ui-search`.

Correct the focus system so form controls have **one clean visible focus treatment**, with no double/internal outline, no nested ring, and no layout shift.

Requirements:

- preserve an accessible, clearly visible primary-colored focus indication and the approved subtle halo;
- apply focus to the correct visual control shell;
- composite controls such as Search must use the outer shell (`:focus-within` or equivalent) and must not apply a second focus border/ring to the inner native input;
- normal Input/Textarea/Select must not visually shrink because a thicker border is inserted inside the control;
- update `DESIGN_SYSTEM.md` focus wording where necessary so the new owner-approved treatment is canonical;
- do not remove keyboard focus visibility.

The same principle must be applied consistently to implemented form controls, not just the two examples marked by the owner.

### 2. Form composition rhythm — ChatGPT review

The current `.ui-field` uses one `6px` gap for everything, while the canonical form-composition rule is:

- Label → Control: `8px`;
- Control → Helper/Error: `12px`;
- Helper and Error are mutually exclusive.

Implement the `8px / 12px` rhythm explicitly instead of one generic gap.

`DESIGN_SYSTEM.md` currently contains older conflicting `6px` label/helper wording in component sections. Normalize those conflicting lines to the canonical §5 form-composition rhythm so future implementations do not regress.

### 3. Select option separation and open-focus state — product-owner issue 4 + ChatGPT review

The owner reports that Select options have no visual separation and hover visually merges with the selected option.

Requirements:

- add `4px` vertical separation between adjacent options inside the Select panel;
- keep the selected item clearly distinct from a hovered unselected item;
- hover/focus must not visually join two adjacent rounded option surfaces;
- while the Select is open, the trigger must retain the approved open/focus treatment even when keyboard focus moves into an option;
- keyboard ArrowUp/ArrowDown/Escape/selection behavior must remain correct;
- update `DESIGN_SYSTEM.md` Select rules with the approved option gap/open-focus clarification.

### 4. Sidebar navigation separation — product-owner issue 5

The owner reports that hovered navigation items visually merge with the active item.

Requirements:

- add `4px` vertical separation between adjacent sidebar/drawer navigation items;
- active and hovered items must remain visually separate rounded surfaces;
- desktop Sidebar and mobile Drawer navigation must use the same shared rule;
- update `DESIGN_SYSTEM.md` navigation rule accordingly.

### 5. Shared motion / transitions — product-owner issue 6

The owner explicitly requires smooth transitions on buttons and other interactive elements across the application.

Add a small shared motion layer to the design system:

- `--motion-duration-fast: 160ms`;
- `--motion-ease-standard: ease-out`.

Apply transitions consistently to implemented interactive baseline components where state changes are visual, including at minimum:

- buttons and icon buttons;
- links/navigation items;
- Input/Textarea/Search/Select shells;
- Select and Dropdown items;
- pagination controls;
- interactive cards;
- table-row hover state;
- other existing baseline interactive controls where the same treatment is appropriate.

Transition only visual micro-interaction properties such as `color`, `background-color`, `border-color`, `box-shadow`, and `opacity` where applicable. Do not animate width/height/layout geometry.

Respect `prefers-reduced-motion: reduce` by effectively disabling nonessential project-defined transitions/animations.

Do not replace or fight Bootstrap's existing Modal/Offcanvas transition mechanism unless a concrete visual defect requires it.

Add the motion rule/tokens to `DESIGN_SYSTEM.md` so this is a permanent site-wide rule.

### 6. Confirmation patterns shown at inconsistent sizes — product-owner issue 3

On the UI-kit page, the two side-by-side confirmation examples have visibly different heights because their text wraps differently.

Correct the comparison presentation so paired confirmation examples in the same UI-kit row appear as equal-height cards.

Important boundary:

- keep the production Confirmation component's approved `340px` width and content-driven intrinsic height;
- do **not** invent a global fixed confirmation height;
- equalize the side-by-side demo/comparison layout through its parent layout or an appropriate reusable equal-height layout treatment.

### 7. Card body phantom bottom spacing — product-owner issue 7

The owner reports an unexplained bottom gap inside the Section Card body.

Remove default descendant margins that create phantom extra space at the bottom of Card content.

At minimum:

- the final child inside Card body/header/footer must not introduce an unintended trailing margin;
- preserve intentional component padding from `DESIGN_SYSTEM.md`;
- solve this generically for the Card component rather than only changing the sample paragraph.

### 8. Production-ready Pagination — ChatGPT review blocker

The current `x-ui.pagination` hard-codes pages `1`, `2`, `…` and is a demo rather than a reusable application component.

Replace it with a real generic pagination component suitable for upcoming Laravel list pages.

Requirements:

- no hard-coded page numbers or totals in the production component;
- primary API should work naturally with Laravel paginator data (prefer accepting a Laravel paginator object or an equally reusable generic pagination state that can be built from it without page-specific code);
- render current page, previous/next availability, page links/ellipsis as applicable, and range/total information from real state;
- preserve the visual specification for pagination;
- use links/URLs for real navigation rather than inert demo-only buttons when URLs are available;
- keep a deterministic UI-kit example by constructing/passing demo pagination data **from the UI-kit page/test**, not embedding fake data inside the component;
- add focused tests proving different current-page/total states render correctly.

### 9. Mobile Modal / Confirmation actions — ChatGPT review blocker

The design system requires mobile action groups in forms/modals to stack vertically, use full-width buttons, and show the primary/destructive action first visually.

Fix the responsive baseline for:

- `.modal-footer` action groups;
- `.ui-confirmation__actions`;
- any shared action-row primitive already used for the same pattern.

Desktop/tablet ordering must remain the approved Secondary-left / Primary-or-Danger-right arrangement.

On mobile:

- buttons are full width;
- actions stack vertically;
- Primary/Danger appears first visually;
- spacing follows existing design tokens.

Do not introduce duplicate mobile-only component markup if CSS/shared component structure can solve the behavior cleanly.

### 10. Input password and readonly states — ChatGPT review blocker

The baseline Input must be usable by the upcoming authentication stage.

Implement the already-specified states that are currently missing/inaccurate:

- `readonly` must use the approved readonly presentation (`#71695F` text), not disabled text styling;
- `type=password` must include the specified trailing eye control/icon and allow the user to reveal/hide the password;
- the password toggle must be keyboard-accessible and have a meaningful accessible label/state;
- preserve the 40px control shell and approved suffix-icon sizing/position;
- do not add a new form-control size.

Use minimal Vanilla JS in the existing direct-runtime file for the password toggle.

### 11. UI-kit acceptance coverage

Update `/ui-kit` so every corrected behavior can be inspected without product pages.

At minimum expose/test examples for:

- focused normal Input and focused Search without an inner/double outline;
- correct Label→Control→Helper/Error rhythm;
- Select with separated options and distinct hover/selected/open focus behavior;
- adjacent active/hover sidebar navigation items;
- transitions on representative interactive controls;
- equal-height side-by-side Confirmation examples;
- Section Card body with no phantom trailing gap;
- Pagination in at least two meaningful states;
- readonly Input and password reveal/hide behavior;
- mobile Modal/Confirmation action stacking.

Do not turn the UI-kit page into product functionality.

### 12. Design-system and documentation synchronization

Update `DESIGN_SYSTEM.md` only where the latest owner-approved visual feedback changes or resolves a rule:

- form-control focus treatment;
- canonical 8px/12px form rhythm where old 6px wording conflicts;
- Select option `4px` gap/open-focus behavior;
- Sidebar/Drawer nav-item `4px` gap;
- shared 160ms/ease-out motion rule and reduced-motion behavior;
- generic Card trailing-content margin rule if needed to prevent future recurrence.

Do not rewrite unrelated tokens/components.

Update `docs/project-status.md`, `docs/architecture.md`, or `docs/development.md` only if needed to keep factual Stage 3 implementation/verification guidance current. Do not add documentation churn.

Replace `.ai/report.md` with the factual report for this correction.

## Out Of Scope

- Stage 4 authentication/integration implementation.
- Admin or psychologist product pages.
- New business/domain behavior.
- Implementing deferred design-system catalogue components unrelated to these fixes.
- Redesigning unrelated approved components or tokens.
- Changing `uikit/index.html` / `uikit/support.js`.
- Adding Node/npm/Vite, runtime CDN dependencies, frontend frameworks, or new build tooling.
- Broad CSS refactoring not required to fix the listed issues.
- Page-specific forks of shared components.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, and the updated `DESIGN_SYSTEM.md` governance.
- Treat the latest product-owner feedback in this task as an approved design correction and update `DESIGN_SYSTEM.md` where required.
- Keep all fixes shared/reusable where the defect is systemic.
- Keep `uikit/` reference-only.
- Preserve local Bootstrap, local Montserrat, local Lucide, and no-build runtime architecture.
- Preserve Stage 2 domain behavior and Stage 3 environment guard for `/ui-kit`.
- Do not commit screenshots, the visual-review ZIP, local browser artifacts, logs, caches, or secrets.

## Acceptance Criteria

1. Focused Input/Textarea/Search/Select controls have one clean visible focus treatment with no inner/double outline or layout shift.
2. Search uses its outer shell for focus; the nested native search input does not get a second ring.
3. Form rhythm is `8px` Label→Control and `12px` Control→Helper/Error, with no conflicting `6px` implementation/documentation rule remaining for that composition.
4. Select options have `4px` separation; hover/focus never visually merges with the selected option; trigger retains open-focus treatment while the list is open.
5. Sidebar and mobile Drawer navigation items have `4px` separation and distinct active/hover surfaces.
6. Shared interactive baseline components use the approved `160ms ease-out` motion tokens, and reduced-motion users do not receive nonessential project animations/transitions.
7. Side-by-side UI-kit Confirmation examples render equal-height without imposing an arbitrary global fixed height on the production component.
8. Section Card content has no unintended trailing bottom margin beyond approved component padding.
9. Pagination is data-driven/reusable and can render real Laravel pagination state; no page numbers/totals are hard-coded in the production component.
10. Mobile Modal and Confirmation action groups stack full-width with Primary/Danger first visually; desktop/tablet Secondary-left / Primary-or-Danger-right remains correct.
11. Readonly Input uses approved readonly styling and is visually distinct from disabled.
12. Password Input has an accessible reveal/hide control using the approved icon system and minimal Vanilla JS.
13. `/ui-kit` demonstrates all corrected states and remains unavailable in production.
14. `DESIGN_SYSTEM.md` records the newly approved focus, form rhythm, spacing, and motion rules so later pages cannot regress.
15. No Stage 4/product functionality or deferred unrelated component is implemented.
16. Existing local asset/no-CDN/no-build constraints remain intact.
17. Full automated project checks pass and browser verification covers the corrected desktop/mobile interactions.
18. `.ai/report.md` accurately records all fixes, checks, remaining unresolved design values, and user visual acceptance as still pending.
19. Final diff contains only files necessary for this Stage 3 correction.

## Checks

Run and report at minimum:

- focused feature/component tests for Pagination data states and `/ui-kit` production guard;
- full `php artisan test` on the isolated MySQL test database;
- `composer check` including Pint and Larastan/PHPStan;
- `composer check-platform-reqs`;
- JS syntax check for `public/app.js`;
- local HTTP `/ui-kit` response check;
- real-browser desktop verification, including a wide viewport comparable to the owner's review (`2174×937`) and a normal desktop viewport;
- real-browser mobile verification around `390×844`;
- verify Input and Search focus computed styles and visually confirm there is no inner/double outline;
- verify Select open/hover/selected states and option separation;
- verify active + hovered nav items remain visually separate;
- verify representative computed `transition-duration` / easing and `prefers-reduced-motion` behavior;
- verify paired Confirmation examples have equal rendered height on the acceptance page;
- verify Section Card body has no unintended trailing margin;
- verify Pagination URLs/state change correctly from supplied paginator data;
- verify password toggle by keyboard and click, including accessible label/state;
- verify mobile Modal/Confirmation action width/order;
- browser console/network inspection: no errors/warnings caused by the changes and no runtime CDN requests;
- `git diff --check`;
- final `git status --short`, full diff, and staged-file inspection.

Final visual acceptance remains with the product owner.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, `DESIGN_SYSTEM.md`, relevant Stage 3 `SPEC.md`, and current Stage 3 documentation;
- inspect the current `/ui-kit` implementation and relevant shared Blade components/CSS/JS;
- inspect `uikit/index.html` where needed for unchanged visual intent;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-07` is the current planned task and `TASK-2026-08-19-06` has already been implemented but not accepted;
- do not touch unknown local changes.

Before commit:

- complete the required automated/browser checks;
- update `.ai/report.md` with actual results;
- inspect final Git status, full diff, and staged files;
- stage only files related to this correction;
- ensure no screenshots, review ZIPs, browser artifacts, secrets, logs, caches, or unrelated files are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-07 fix Stage 3 visual acceptance gaps`

If safe completion is impossible, report `partial`, `blocked`, or `failed` instead of claiming success.
