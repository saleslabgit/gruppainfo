# Task: TASK-2026-08-19-06

Status: planned
Created from: a5daaa772176f146d4157e6baa8f37afbccb222b

## Title

Implement the Stage 3 design-system foundation and baseline UI kit

## Goal

Implement the reusable visual foundation that all subsequent Gruppa Info interfaces will use, while avoiding unnecessary up-front implementation of every component described in the full design specification.

Stage 3 must establish the real production-ready design tokens, local visual assets, shared Blade UI primitives, responsive application shell, and a browser-visible UI-kit page. Later stages may add specialized components only when they are first needed, but they must add them to the shared design system before using them on a product page.

The implementation must match `DESIGN_SYSTEM.md` and the approved visual reference in `uikit/index.html`; Codex must not redesign, simplify, reinterpret, or "improve" the approved visual language.

## Facts

- `main` at task creation is `a5daaa772176f146d4157e6baa8f37afbccb222b`.
- Stage 1 and Stage 2 are completed and accepted.
- `TASK-2026-08-19-05` established mandatory design-system governance in `WORKFLOW.md` and `AGENTS.md`.
- `DESIGN_SYSTEM.md` is authoritative for explicit visual numeric values and design implementation rules.
- `uikit/index.html` is the approved visual reference for intent not already resolved by `DESIGN_SYSTEM.md`.
- `uikit/` is reference material only and must not become a Laravel runtime dependency.
- The project uses Blade, local Bootstrap 5.3.8, project CSS, and Vanilla JS only where needed.
- Node.js, npm, Vite, frontend build steps, runtime CDN dependencies, React, Vue, Inertia, Livewire, and Tailwind are prohibited.
- Existing runtime assets are loaded directly from `public/`.
- The application already has shared date/time and money formatters from Stage 1.
- The current page is only a technical placeholder and is not an approved product interface.
- The user has explicitly chosen an incremental design-system implementation strategy: implement the reusable foundation and components needed by the near-term stages now; add specialized components later when a real product screen first requires them.

## Assumptions

- Shared Blade UI components should live under a clear reusable namespace such as `resources/views/components/ui/`; use generic component names, never page-specific names such as `GroupCard` or `PaymentTable`.
- The design-system stylesheet may reorganize the existing project CSS as needed, but runtime CSS remains directly served from `public/` with no compilation step.
- Bootstrap may provide low-level layout/behavior where appropriate, but Bootstrap's default visual appearance is not the design system. Approved visual output must come from the project design tokens and component styles.
- Montserrat and Lucide must be available locally at runtime. Vendoring pinned browser-ready assets with their licenses is preferred over introducing a package/build workflow.
- The UI-kit route is a development/testing verification surface, not a production product page, and should not expose internal design documentation in production.

## Unknowns

- The exact local Lucide distribution version and the exact local Montserrat font-file source/version are not currently fixed in the repository. Codex may select stable official distributable assets compatible with the no-build architecture, must pin/record what was selected, include the applicable license files, and must not use runtime CDN loading.
- Some values/patterns are intentionally marked unresolved in `DESIGN_SYSTEM.md`. They remain unresolved and must not be invented in this task.

## Scope

### 1. Align the staged implementation plan

Update the relevant Stage 3/design-system planning wording in `SPEC.md` and project documentation so it matches the user's approved incremental strategy without weakening the final design-system rules:

- Stage 3 implements the foundation and baseline reusable components listed in this task;
- specialized components from the broader design-system catalogue are implemented only when a subsequent product stage first needs them;
- a product page may never introduce its own visual replacement for a missing shared component;
- when a later screen needs a component not yet implemented, that component is first added to the shared UI system according to `DESIGN_SYSTEM.md`, then consumed by the screen;
- unresolved design values still require an explicit user/design decision before implementation.

Keep the full component catalogue in `DESIGN_SYSTEM.md` as the target design language. Do not remove or redesign components merely because their implementation is deferred.

### 2. Design tokens and global foundations

Implement the shared CSS foundation from `DESIGN_SYSTEM.md`, including the approved values needed by the baseline system:

- color tokens;
- typography tokens and responsive heading rules;
- spacing scale;
- control sizes;
- radii;
- borders and focus system;
- shadows/elevation;
- base page/surface/text styles;
- responsive breakpoints and container/application-shell behavior required by the implemented components.

Use named CSS custom properties/tokens consistently. Do not scatter duplicated raw values across component styles when an approved token applies.

The custom design-system styles must load after Bootstrap so they control the final appearance.

### 3. Local typography and icons

Make the approved visual assets production-compatible with the project's no-CDN architecture:

- Montserrat is the only UI font; provide local runtime files for weights 400, 500, 600, and 700 and wire them through `@font-face` or the simplest equivalent direct-CSS mechanism;
- Lucide is the only icon library; provide a pinned browser-ready local distribution suitable for direct loading without Node/npm/build tooling;
- include the relevant font/icon license files in the vendored asset directories;
- record the selected versions/source facts in development/architecture documentation;
- do not load Google Fonts, unpkg, jsDelivr, or any other runtime CDN.

Do not copy `uikit/support.js` into the application and do not make `uikit/index.html` a runtime dependency.

### 4. Baseline reusable Blade components

Implement a coherent baseline set of generic shared Blade components sufficient for Stages 4-7 and common application states. Follow the exact states, variants, dimensions, typography, spacing, focus behavior, responsive rules, and visual intent defined in `DESIGN_SYSTEM.md` and `uikit/index.html`.

At minimum implement shared primitives for:

#### Actions

- Button with the approved baseline variants needed by the system (including primary, secondary, danger, and any other variants already defined by `DESIGN_SYSTEM.md` that naturally belong to the same button primitive);
- Icon Button where required by baseline navigation/table/modal interactions.

#### Forms

- Label / required indicator;
- Text Input;
- Textarea;
- Select;
- Checkbox;
- Radio;
- helper text / validation error presentation;
- reusable form field composition/rhythm.

Do not invent Small/Large form-control variants: the design specification explicitly leaves them unresolved.

#### Feedback and data display

- Alert;
- Badge / semantic status badge primitive;
- Card;
- Empty State;
- Loading presentation sufficient for normal page/component loading;
- Error State sufficient for inline and failed-block/page states.

#### Data/list administration foundation

- Table styling/component structure;
- Table toolbar primitives needed for search/filter/result-count/action composition;
- Pagination;
- Dropdown Menu.

These must be generic building blocks, not psychologist/group/payment-specific implementations.

#### Navigation and page structure

- responsive application shell;
- desktop sidebar/navigation;
- mobile navigation replacement required by the approved responsive shell (implement the generic Drawer primitive if that is the design-system pattern needed to do this correctly);
- page header;
- basic header/topbar behavior only where required by the approved shell.

#### Overlays / confirmation

- Modal;
- Confirmation pattern/dialog using the approved neutral/destructive action ordering and variants.

#### Existing domain presentation helpers

- reusable Blade presentation for date/time using the existing centralized formatter;
- reusable Blade presentation for money using the existing centralized formatter;
- do not duplicate timezone or money-formatting logic in Blade components.

### 5. Explicitly deferred components

Do **not** implement components only for catalogue completeness when they are not required by the baseline above.

Unless they are genuinely required internally to satisfy the responsive shell or another baseline component, defer specialized components such as:

- Stepper;
- Choice Card;
- Timeline;
- Metric;
- Progress;
- specialized document/file item;
- specialized upload/dropzone interaction;
- Tabs/Breadcrumbs/Popover/Tooltip/Toast/Chips/Switch and other catalogue items that are not needed by the baseline acceptance surface.

When a later stage first needs one of these, it must be implemented as a shared design-system component before product-page use.

Do not use this deferred list as permission to invent a page-specific substitute.

### 6. UI-kit verification page

Create a Laravel/Blade UI-kit page that renders the implemented system from the actual production CSS/JS and Blade components, not copied markup/styles from `uikit/index.html`.

The page must allow visual verification of the implemented baseline, including as applicable:

- typography and core colors;
- buttons and icon buttons;
- form controls and labels;
- normal, disabled, validation/error, and success/semantic states;
- alerts and badges;
- cards;
- table + toolbar + pagination;
- dropdown;
- modal and confirmation behavior;
- navigation/application shell;
- empty/loading/error states;
- date/time and money presentation;
- keyboard focus behavior;
- desktop and mobile responsive behavior.

The UI-kit route must be available for local development/testing and must not become a normal public production route.

Keep this page a verification surface. Do not rebuild the huge source reference document one-to-one and do not copy `uikit/index.html` into a Blade template.

### 7. JavaScript behavior

Use the minimum JavaScript required for the implemented interactive components.

- Prefer existing local Bootstrap behavior where it satisfies interaction needs without changing the approved visual design.
- Keep project-specific JS in the existing direct-runtime approach (`public/`, no build step).
- Ensure keyboard interaction and focus behavior work for interactive baseline components.
- Do not add a frontend framework or package manager.

### 8. Documentation and report

Update documentation to describe the actual implemented state:

- `docs/architecture.md` — design-system implementation structure, component location, token/runtime-asset boundaries;
- `docs/development.md` — UI-kit route, local font/icon assets, and how to verify the design system;
- `docs/project-status.md` — Stage 3 baseline implementation status and the rule that specialized components remain incremental;
- `README.md` only if a small orientation link/change is genuinely useful;
- `.ai/report.md` — factual implementation report and actual checks.

Do not claim deferred components are implemented.

## Out Of Scope

- Stage 4 authentication/integration functionality.
- Admin psychologist management flows.
- Psychologist cabinet product pages.
- Group creation/moderation flows.
- Payment, scheduler, application, or external-integration behavior.
- Implementing every component in `DESIGN_SYSTEM.md` merely for completeness.
- Filling any unresolved design-system value.
- Redesigning approved tokens/components.
- Importing `uikit/index.html` or `uikit/support.js` into Laravel runtime.
- Page-specific CSS/component forks.
- Node.js, npm, Vite, frontend compilation, runtime CDN assets, or new frontend frameworks.
- Backend/domain refactoring unrelated to rendering the baseline UI system.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md`, `DESIGN_SYSTEM.md`, and the current `SPEC.md` source-of-truth hierarchy.
- Before UI work, inspect `uikit/index.html` as required by governance.
- Explicit numeric design values and implementation rules in `DESIGN_SYSTEM.md` win over visual approximation.
- When `DESIGN_SYSTEM.md` is silent, use `uikit/index.html` for approved visual intent; if neither source resolves a required decision, stop/report instead of inventing.
- Keep `uikit/` reference-only.
- Use generic reusable component names and APIs.
- No one-off or page-specific copies of shared UI primitives.
- Preserve the current Laravel/Blade/Bootstrap/no-build architecture.
- Do not alter Stage 2 business/domain behavior.
- Do not commit secrets, local data, caches, logs, temporary screenshots, or unrelated artifacts.

## Acceptance Criteria

1. The Laravel application has a real shared design-token layer matching the explicit applicable values in `DESIGN_SYSTEM.md`.
2. Montserrat 400/500/600/700 loads locally with no runtime font CDN request.
3. Lucide loads locally with a pinned/recorded distribution and no runtime icon CDN request.
4. The implemented baseline Blade components are generic, reusable, and centralized; no page-specific visual duplicates are introduced.
5. Buttons, forms, feedback states, badges, cards, tables, toolbar/pagination, dropdown, modal/confirmation, navigation/shell, empty/loading/error states, and date/money presentation can be inspected on the UI-kit page.
6. Implemented controls/components match the required visual states, dimensions, spacing, typography, colors, radii, and focus behavior from `DESIGN_SYSTEM.md`.
7. The application shell and UI-kit verification surface work at desktop and mobile widths according to the approved responsive rules.
8. Keyboard focus is visible and conforms to the approved focus system for implemented focusable controls.
9. Date/time rendering continues to use the existing centralized `Europe/Minsk` presentation path; money rendering continues to use the existing integer-minor-unit formatter.
10. The UI-kit page is development/testing-only and is not exposed as a normal production route.
11. `uikit/index.html` and `uikit/support.js` remain reference-only and unchanged unless a truly necessary reference-path correction is identified; they are not loaded by Laravel runtime.
12. No Node/npm/Vite/frontend build step or runtime CDN dependency is introduced.
13. Specialized deferred components are not implemented merely for completeness and product-specific pages are not started.
14. `SPEC.md`/documentation accurately record the incremental design-system strategy so later tasks cannot misread the deferred components as permission for page-specific styling.
15. Automated project checks remain green and targeted rendering/route tests cover the new UI-kit foundation where practical.
16. `.ai/report.md` accurately lists implemented vs deferred components, local assets, checks, visual/runtime verification, and any remaining gaps.
17. Final diff contains only changes necessary for the Stage 3 baseline design-system implementation.

## Checks

Run and report at minimum:

- targeted tests for the UI-kit route/component rendering and any new environment guard;
- full `php artisan test` on the isolated MySQL test database;
- `composer check` (or its current equivalent aggregate checks), including Pint and Larastan/PHPStan;
- `composer check-platform-reqs` if not already included in the aggregate command;
- verify the UI-kit page responds successfully in development/testing;
- inspect rendered HTML/runtime asset references and verify there are no Google Fonts, unpkg, jsDelivr, or other runtime CDN dependencies;
- verify local Montserrat and Lucide runtime assets exist and are actually referenced by the application;
- verify the UI-kit page in a real browser at desktop and mobile viewport sizes if browser tooling is available without adding project dependencies; if unavailable, report the limitation clearly for user manual acceptance rather than claiming visual verification;
- exercise keyboard focus and interactive baseline controls where browser tooling is available;
- `git diff --check`;
- final `git status --short`, full diff, and staged-file inspection.

The user remains the product tester for final visual acceptance. Automated tests cannot substitute for checking visual conformance to `DESIGN_SYSTEM.md` and `uikit/index.html`.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, `DESIGN_SYSTEM.md`, relevant `SPEC.md` sections, `docs/architecture.md`, `docs/development.md`, and `docs/project-status.md`;
- inspect `uikit/index.html` sufficiently to understand the implemented baseline components and responsive shell;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-06` is the current planned task and has not already been completed;
- do not touch unknown local changes.

Before commit:

- complete the applicable automated/runtime checks;
- update `.ai/report.md` with actual results and an explicit implemented/deferred component list;
- inspect `git status --short`, full diff, and staged files;
- stage only task-related files;
- ensure no secrets, unrelated artifacts, caches, generated screenshots, or local data are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-06 implement design system foundation`

If a required visual decision is unresolved or safe completion is impossible, report `partial`, `blocked`, or `failed` instead of inventing a design rule.
