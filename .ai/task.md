# Task: TASK-2026-08-19-05

Status: planned
Created from: d57e0c57102e36dd6e694d5615336d5872b08861

## Title

Establish design system governance for all future UI work

## Goal

Make the approved Gruppa Info design system and visual UI Kit mandatory sources for every future interface task, without starting Stage 3 implementation yet.

After this task, both ChatGPT planning/review and Codex implementation must have an explicit, durable repository rule that future UI must follow `DESIGN_SYSTEM.md` and the visual reference in `uikit/index.html`, must reuse the approved component system, and must not invent missing visual rules.

## Facts

- `main` at task creation is `d57e0c57102e36dd6e694d5615336d5872b08861`.
- Stage 1 and Stage 2 are completed and accepted.
- Stage 3 UI Kit / Design System implementation has not started.
- The repository now contains `DESIGN_SYSTEM.md` with implementation-ready design tokens, component rules, page composition rules, AI/developer rules, visual invariants, forbidden patterns, and unresolved design values.
- `DESIGN_SYSTEM.md` explicitly states that it is the source of truth for numeric design values and design implementation rules.
- The approved visual reference is stored in `uikit/index.html` with its supporting reference runtime in `uikit/support.js`.
- `SPEC.md` already requires a reusable design system before mass page implementation and forbids page-specific duplicated styling.
- `WORKFLOW.md` defines planning and acceptance behavior for ChatGPT.
- `AGENTS.md` defines implementation behavior for Codex.
- Project-wide technical constraints still apply to UI work, including Blade + Bootstrap + project CSS/Vanilla JS and the existing no-Node/no-Vite/no-runtime-CDN architecture. A design reference must not silently override unrelated technical, security, or delivery constraints.

## Assumptions

- `DESIGN_SYSTEM.md` is authoritative for visual design, design tokens, component appearance/state rules, spacing, typography, responsive behavior, and page composition.
- `uikit/index.html` is the approved visual reference and is used to resolve visual intent not already fixed by `DESIGN_SYSTEM.md`.
- Where `DESIGN_SYSTEM.md` and `uikit/index.html` differ on numeric values or explicit implementation rules, `DESIGN_SYSTEM.md` wins, matching its own stated rule.
- The `uikit/` files are reference artifacts only and must not become runtime dependencies of the Laravel application unless a future approved task explicitly changes that architecture.
- Project-wide architecture, security, dependency, and asset-delivery constraints remain governed by `SPEC.md`, `WORKFLOW.md`, `AGENTS.md`, and current architecture documentation. If a design instruction conflicts with such a non-visual technical constraint, Codex must stop/report the conflict rather than silently choosing one.

## Unknowns

- `DESIGN_SYSTEM.md` currently mentions the source UI Kit by its former/generated name rather than the repository path `uikit/index.html`; references should be normalized so future agents cannot misunderstand the canonical file.
- Some design values are intentionally listed as unresolved in `DESIGN_SYSTEM.md`; those gaps must remain unresolved until an explicit product/design decision is made.

## Scope

### 1. WORKFLOW.md — planning and acceptance rule

Add a concise design-system governance section or equivalent rule that applies to every task affecting UI, visual presentation, layout, frontend components, or responsive behavior.

It must establish that:

- ChatGPT must inspect `DESIGN_SYSTEM.md` and the current `uikit/index.html` before planning a UI task;
- UI task acceptance must verify conformance to both sources, not only functional behavior;
- for visual/design questions, `DESIGN_SYSTEM.md` is authoritative for explicit numeric values and implementation rules;
- `uikit/index.html` is the approved visual reference for visual intent not already resolved by `DESIGN_SYSTEM.md`;
- if a required UI pattern/value is absent or explicitly unresolved, do not invent it; surface the gap for a user decision;
- project-wide architecture/security/runtime constraints remain in force and cannot be silently overridden by a design reference.

Keep `WORKFLOW.md` a process document. Do not duplicate token tables or component specifications there.

### 2. AGENTS.md — mandatory implementation behavior

Add a dedicated UI / Design System section or equivalent mandatory rules for Codex.

For any interface/frontend task, Codex must:

- read `DESIGN_SYSTEM.md` before implementation;
- inspect the relevant visual reference in `uikit/index.html`;
- implement using shared reusable components/tokens instead of page-specific visual copies;
- use only approved design tokens, component variants, spacing, typography, colors, radii, states, and page patterns;
- not restyle or "improve" approved components based on personal preference;
- not introduce a new visual variant or one-off design primitive without an explicit approved design-system change;
- stop/report when the design system has no answer or contains an unresolved value needed by the task;
- treat `uikit/` as reference material, not production runtime code/dependency;
- continue to obey project-level stack, security, and asset-delivery constraints.

Keep the detailed values in `DESIGN_SYSTEM.md`; `AGENTS.md` should enforce behavior, not duplicate the design specification.

### 3. DESIGN_SYSTEM.md — canonical repository references and boundary clarity

Make only minimal documentation corrections needed to make the governance unambiguous:

- replace/clarify references to the source UI Kit so the canonical repository reference is `uikit/index.html`;
- preserve the existing rule that `DESIGN_SYSTEM.md` wins over the visual reference for numeric values and explicit design implementation rules;
- clarify, if necessary, that design-system authority is scoped to visual/design rules and does not silently override project-wide technical/security/runtime constraints;
- do not redesign, retokenize, or change approved visual values in this task.

If the existing Lucide "via CDN" wording conflicts with the project's no-runtime-CDN architecture, resolve the documentation conflict without changing the approved design intent: Lucide remains the only icon library, but the production asset-delivery mechanism must conform to project architecture. Do not add a new frontend dependency or implement asset delivery in this task.

### 4. Architecture/status documentation

Update the smallest relevant documentation so future project orientation makes the rule visible:

- `docs/architecture.md`: document the frontend design-system boundary and canonical sources (`DESIGN_SYSTEM.md` + `uikit/index.html`), and state that future pages compose the shared Blade design-system components rather than local page-specific styling;
- `docs/project-status.md`: record that the approved design specification/reference are present and govern the upcoming Stage 3, while Stage 3 implementation itself is still not started/completed.

Do not claim that Stage 3 components or product UI already exist.

### 5. Report

Replace `.ai/report.md` with a factual report for this governance task.

## Out Of Scope

- Implementing Stage 3 CSS tokens, Blade components, or the Laravel UI Kit page.
- Changing application UI, routes, controllers, views, `public/app.css`, or `public/app.js` for Stage 3.
- Importing/copying the reference UI Kit implementation into the Laravel runtime.
- Adding npm, Node.js, Vite, React, Vue, Livewire, Inertia, Tailwind, or new frontend packages.
- Adding runtime CDN dependencies.
- Redesigning the approved UI Kit.
- Changing approved colors, spacing, typography, radii, shadows, component sizes, responsive rules, or page patterns.
- Filling unresolved design values without a user decision.
- Changing product/business behavior or Stage 2 domain logic.
- Broad documentation cleanup unrelated to design-system governance.

## Constraints

- Follow `WORKFLOW.md` and `AGENTS.md` as they exist at task start.
- Keep changes concise and avoid duplicating the full design specification across documents.
- Preserve `DESIGN_SYSTEM.md` as the detailed design source of truth.
- Preserve `uikit/index.html` and `uikit/support.js` as reference artifacts; do not modify them unless a path/reference defect makes a tiny correction strictly necessary, and do not convert them into production assets.
- Do not change `SPEC.md` unless a direct contradiction cannot be resolved by the scoped governance wording; prefer leaving SPEC unchanged because §24/§25 already establish the design-system requirement.
- Do not implement Stage 3 in this task.
- Do not commit secrets, local data, logs, caches, or unrelated artifacts.

## Acceptance Criteria

1. `WORKFLOW.md` explicitly requires `DESIGN_SYSTEM.md` + `uikit/index.html` to be consulted when planning and accepting UI work.
2. `AGENTS.md` explicitly requires Codex to follow `DESIGN_SYSTEM.md` + `uikit/index.html` for UI implementation and forbids visual invention outside the approved system.
3. The precedence is unambiguous: explicit design values/rules in `DESIGN_SYSTEM.md` override the visual reference; the visual reference guides unresolved visual intent; missing/unresolved patterns require a user decision rather than invention.
4. Project-wide technical/security/runtime constraints remain explicitly in force and are not silently overridden by design reference implementation details.
5. Repository references in `DESIGN_SYSTEM.md` point unambiguously to `uikit/index.html` as the canonical visual reference.
6. The documentation makes clear that `uikit/` is reference material, not a Laravel runtime dependency.
7. `docs/architecture.md` records the design-system boundary for future frontend work.
8. `docs/project-status.md` records that the approved design sources are present but Stage 3 implementation is still pending.
9. No Stage 3 UI code or product functionality is implemented.
10. No approved visual token/value is changed.
11. `.ai/report.md` accurately describes the documentation/governance changes and checks.
12. Final diff contains only files necessary to establish design-system governance.

## Checks

Run and report at minimum:

- inspect all changed Markdown files for contradictory precedence/source-of-truth wording;
- search changed governance documents for the canonical references `DESIGN_SYSTEM.md` and `uikit/index.html`;
- verify no application/runtime/frontend implementation files changed;
- `git diff --check`;
- final `git status`, diff, and staged-file inspection.

Full Laravel tests, Pint, Larastan, and browser acceptance are not required if the task changes Markdown/governance documentation only. If any runtime/code file changes unexpectedly become necessary, run the relevant project checks and explain why.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `.ai/task.md`, `DESIGN_SYSTEM.md`, `SPEC.md` §24/§25/§31-32 as relevant, `docs/architecture.md`, and `docs/project-status.md`;
- inspect `uikit/index.html` sufficiently to confirm it is the current approved visual reference;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-05` is the latest relevant planner task and has not already been completed;
- do not touch unknown local changes.

Before commit:

- complete the required checks;
- update `.ai/report.md` with actual results;
- inspect `git status --short`, full diff, and staged files;
- stage only files related to this task;
- ensure no secrets or unrelated artifacts are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-05 establish design system governance`

If safe completion is impossible or a material source-of-truth conflict cannot be resolved within the boundaries above, report `partial`, `blocked`, or `failed` instead of guessing.