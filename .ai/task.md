# Task: TASK-2026-08-19-02

Status: planned
Created from: bfe0c4157944d9ac9461c6fce3f1369e6d6c6aa7

## Title

Correct unsupported project license metadata

## Goal

Fix the Stage 1 review blocker: the project was assigned an MIT license in `composer.json` without any approved licensing decision.

The project must not claim an open-source license that was never specified. For the current private/proprietary application, set Composer package metadata to `"license": "proprietary"` until the user explicitly chooses another project license in the future.

## Facts

- `TASK-2026-08-19-01` was implemented in commit `bfe0c4157944d9ac9461c6fce3f1369e6d6c6aa7`.
- Review found one blocker: root `composer.json` contains `"license": "MIT"` although neither the user, `SPEC.md`, nor the workflow defines the project as MIT-licensed.
- Third-party dependencies/assets keep their own licenses. In particular, the committed Bootstrap license is third-party license information and must not be removed or changed.
- No other Stage 1 defect was identified in review.

## Assumptions

- `proprietary` is the intended temporary Composer metadata for this application unless the user later makes an explicit licensing decision.

## Unknowns

- The final long-term project license is not defined. This does not block the correction because the current task explicitly uses `proprietary` rather than inventing an open-source license.

## Scope

- Change the root project license in `composer.json` from `MIT` to `proprietary`.
- Update `composer.lock` only if Composer requires it as a consequence of the metadata change.
- Check the repository for any other statement that incorrectly claims the **project itself** is MIT-licensed; correct such a statement only if it exists.
- Do not alter valid third-party license files or third-party attribution.
- Run the relevant Composer and project quality checks.
- Replace `.ai/report.md` with the factual report for this task.

## Out Of Scope

- Any Stage 2 functionality.
- Any product, architecture, Docker, frontend, database, dependency, or documentation redesign.
- Choosing a different open-source license.
- Removing or changing Bootstrap or dependency license notices.
- Unrelated cleanup or refactoring.

## Constraints

- Follow `WORKFLOW.md` and `AGENTS.md`.
- Keep the diff surgical.
- Do not change `SPEC.md`, `WORKFLOW.md`, `AGENTS.md`, or `.ai/task.md`.
- Do not introduce any new dependency.
- Do not modify unrelated files.

## Acceptance Criteria

1. Root `composer.json` contains `"license": "proprietary"` and no longer claims the project is MIT-licensed.
2. Valid third-party license information remains unchanged.
3. `composer validate --strict` passes.
4. `composer check-platform-reqs` passes.
5. The existing project quality command (`composer check`) passes.
6. `.ai/report.md` accurately records the change and checks performed.
7. Final diff contains only files necessary for this correction.

## Checks

Run and report at minimum:

- `composer validate --strict`;
- `composer check-platform-reqs`;
- `composer check`;
- inspect the final diff and staged files;
- verify no project-level MIT claim remains outside legitimate third-party license/attribution files.

Use the existing Docker environment for commands if required by the project documentation. Do not repeat unrelated browser/runtime acceptance work unless a change unexpectedly affects runtime behavior.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, and `.ai/task.md`;
- run `git log --oneline -5` and `git status --short`;
- confirm this task is the latest relevant `planner:` task and has not already been completed;
- do not touch unknown local changes.

Before commit:

- complete the required checks;
- update `.ai/report.md` with actual results;
- inspect `git status --short`, full diff, and staged files;
- stage only files related to this task;
- ensure no secrets or unrelated artifacts are included.

If the gate passes, commit with:

`codex: TASK-2026-08-19-02 correct project license metadata`

If safe completion is impossible, report `partial`, `blocked`, or `failed` instead of claiming success.
