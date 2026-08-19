# Task: TASK-2026-08-19-01

Status: planned
Created from: e2fa5f40505414f09be12ccbf62e223733a60870

## Title

Bootstrap the Laravel project foundation, Docker development environment, quality tooling, and baseline documentation

## Goal

Implement Stage 1 from `SPEC.md` as a complete, runnable project foundation.

At the end of this task, a clean checkout must contain a working Laravel 12 application that runs in Docker Compose with PHP 8.2 and MySQL, serves a basic Blade page with locally stored Bootstrap 5 plus project CSS/JS, requires no Node/npm/Vite frontend build, has Pint and Larastan configured and passing, uses the required timezone/money helpers, enforces production PHP platform compatibility in Composer, and contains the initial project documentation that accurately describes the implemented state.

This task establishes the foundation for all later stages. Do not implement Stage 2 domain entities or later product features.

## Facts

- Repository: `saleslabgit/gruppainfo`.
- Current planning base: commit `e2fa5f40505414f09be12ccbf62e223733a60870` on `main`.
- Before this task the repository contains project process/specification files and no Laravel application.
- `WORKFLOW.md` defines the collaboration, task, reporting, commit, and acceptance process.
- `AGENTS.md` defines Codex implementation behavior.
- `SPEC.md` is the product/technical specification.
- Stage 1 in `SPEC.md` requires Laravel 12.x, PHP 8.2, MySQL, a web server, Composer, Docker Compose, `.env.example`, database connectivity, a basic Blade layout, local Bootstrap 5, `public/app.css`, `public/app.js`, a test page, Pint, Larastan, UTC application time, Europe/Minsk display helpers, money formatting helpers, Composer platform requirements, and baseline project documentation.
- Production does not use Docker. It is expected to run PHP 8.2.32 or a compatible version, MySQL, and use `public/` as the document root.
- `composer.json` must set `config.platform.php` to the production PHP version and explicitly require all PHP extensions needed by the application.
- Frontend constraints are strict: do not use Vue, React, Inertia, Livewire, Tailwind, Vite, npm, or Node.js.
- CSS and JS used by the application must live directly under `public/`.
- Bootstrap must be served from a local pinned file, not a CDN.
- Global project requirements say automated tests must run against MySQL in Docker rather than SQLite.
- Baseline documentation required in Stage 1:
  - `README.md`;
  - `docs/architecture.md`;
  - `docs/development.md`;
  - `docs/project-status.md`.
- Documentation must describe only the actually implemented state. `docs/project-status.md` must be maintained on every later stage.

## Assumptions

- Use the simplest conventional Laravel 12 structure compatible with the specification; do not introduce a custom framework structure.
- The exact local web-server implementation is not prescribed by the specification. Choose the simplest Docker approach that keeps the Laravel source portable to non-Docker production and document the choice.
- The exact MySQL minor version is not prescribed. Use a supported MySQL version and pin it in Docker Compose rather than using an unbounded `latest` tag.
- The exact Bootstrap 5 patch version is not prescribed. Use one fixed Bootstrap 5 release, store the required distributable files locally, retain applicable license information, and document the version.
- The exact Larastan level is not prescribed. Configure an explicit fixed level appropriate for the initial Laravel codebase, make it pass without broad suppressions, and document the chosen level.
- A "one command" development start means that after cloning the repository on a machine with Docker Compose, the documented normal startup path is a single command. Any initialization required to make a clean checkout runnable must be handled as part of that startup flow or otherwise structured so the acceptance criterion is genuinely true; do not require a separate host installation of PHP, Composer, Node, or npm.
- Development and test database credentials may use non-sensitive local defaults committed through `.env.example` / Docker configuration. No real credentials or secrets may be committed.

## Unknowns

- Production web server is not specified. Do not make production depend on the local Docker web-server choice.
- Exact production MySQL version is not specified.
- Exact production PHP extension inventory is not known in advance. Determine the extensions actually required by Laravel and this Stage 1 implementation, declare them in Composer, and document them. Do not add speculative extensions.
- No final UI visual design is defined yet. Stage 1 needs only a neutral technical test page/layout. Full visual design belongs to Stage 3.

These unknowns do not block Stage 1 because they do not require a product decision. If implementation reveals a material incompatibility with PHP 8.2.32, Laravel 12, the no-Node constraint, or non-Docker production portability, stop and report it instead of silently changing the specification.

## Scope

### 1. Laravel application foundation

- Create a standard Laravel 12.x application in the repository root.
- Keep the existing `AGENTS.md`, `WORKFLOW.md`, `SPEC.md`, and `.ai/task.md` intact.
- Configure the application for PHP 8.2 compatibility and the production platform requirement from `SPEC.md`.
- Ensure the application can connect to MySQL in the Docker environment.
- Keep Stage 1 free of product/domain implementation.
- Do not introduce authentication, psychologist/group/payment/application domain logic, dictionaries, settings domain tables, status enums, or state-transition services yet.
- If the Laravel installer generates default scaffolding that conflicts with later `gp_*` domain requirements or with the no-Node/no-Vite constraint, remove or adapt that generated scaffolding now rather than carrying known conflicts forward. Do not replace it with Stage 2 functionality.

### 2. Docker Compose development environment

Provide a Docker Compose environment containing everything required for local development:

- PHP 8.2 runtime;
- web server;
- MySQL;
- Composer available inside the development environment.

Requirements:

- Source code remains ordinary repository files and is usable outside Docker for production deployment.
- Docker is a development tool only; application architecture must not depend on Docker-specific runtime behavior.
- Pin meaningful image versions; do not use unbounded `latest` tags for core services.
- Add health/wait behavior sufficient to prevent normal startup races with MySQL.
- The documented normal project start from a clean checkout must be one command.
- The application must be reachable in a browser after startup.
- Do not require host PHP or Composer for normal startup/checks.
- Do not commit container-generated caches, logs, database data, `vendor/`, secrets, or other local artifacts.

### 3. Environment configuration

- Add a safe `.env.example` covering the variables required for local and production setup.
- Configure `APP_TIMEZONE=UTC`.
- Configure Laravel to store/use application times in UTC.
- Configure database settings for the Docker development environment without embedding real secrets.
- Ensure `APP_DEBUG` and production-sensitive values are documented appropriately without pretending Stage 13 production configuration is already complete.

### 4. MySQL-backed test baseline

- Configure the test environment to use MySQL in Docker, not SQLite.
- Tests must use a test database/schema isolated from the normal development database so running `php artisan test` cannot destroy development data.
- Add only the minimal database bootstrap needed for this Stage 1 test setup.
- Do not create Stage 2 `gp_*` domain migrations early.

### 5. Frontend baseline without build tooling

- Create a basic Blade layout and a simple test/home page proving the layout and assets work.
- Use Bootstrap 5 from locally stored, pinned distributable files under `public/`; no CDN.
- Add project-owned `public/app.css` and `public/app.js` and load them directly from Blade.
- Project CSS must load after Bootstrap CSS.
- Remove/avoid all frontend build dependencies and references introduced by the Laravel starter skeleton.
- There must be no runtime or development requirement for Node.js, npm, Vite, or a frontend build command.
- Do not build the Stage 3 design system or reusable UI-kit components yet.

### 6. Date/time and money formatting foundation

Implement a small, explicit reusable foundation for:

- displaying UTC application dates/times in `Europe/Minsk`;
- formatting money stored as integer minor units for display, with `BYN` as the default currency context.

Requirements:

- Keep these helpers simple and reusable by later Blade pages.
- No manual timezone conversion should be needed in individual pages.
- No `float`-based money arithmetic.
- Add focused automated tests for the helper behavior that can be validated at this stage.

### 7. Composer platform and PHP extension requirements

- Set `composer.json` `config.platform.php` to `8.2.32` as the current production target stated in `SPEC.md`.
- Explicitly list the PHP extensions actually required by Laravel and this project in Composer `require`.
- Do not list speculative extensions that are not used.
- Ensure `composer check-platform-reqs` passes inside the Docker environment.

### 8. Code quality tooling

- Configure Laravel Pint with a committed project configuration.
- Configure Larastan/PHPStan with an explicit committed level/configuration.
- Keep the configuration minimal and understandable.
- Do not add broad ignore patterns merely to make the initial analysis green.
- Add convenient Composer scripts for the normal checks if that keeps commands consistent and documented.

### 9. Baseline documentation

Create and populate:

#### `README.md`
Document only the implemented Stage 1 state:

- what the project is;
- current technical stack;
- prerequisites (primarily Docker Compose);
- the one-command startup path;
- local URL;
- common development/check commands;
- where detailed documentation lives.

#### `docs/architecture.md`
Document:

- current Laravel application structure;
- where future domain services, enums, HTTP code, Blade views/components, and infrastructure integrations are intended to live based on the approved specification;
- boundaries that must be preserved;
- UTC / Europe-Minsk date handling rule;
- integer-minor-unit money rule;
- no-Node frontend rule.

Do not describe future modules as already implemented.

#### `docs/development.md`
Document:

- Docker services and versions/roles;
- environment setup;
- startup/shutdown;
- Composer usage inside the environment;
- MySQL development/test database separation;
- tests;
- Pint;
- Larastan;
- `composer check-platform-reqs`;
- asset rules (local Bootstrap + direct `public/` CSS/JS, no build step);
- relevant troubleshooting that is actually verified during this task.

#### `docs/project-status.md`
Record:

- Stage 1 as the current implemented stage only after its acceptance criteria are actually satisfied;
- what works now;
- what is intentionally not implemented yet;
- known technical limitations/open questions discovered during implementation;
- next planned stage as Stage 2, without describing it as implemented.

### 10. Repository hygiene

- Add/update `.gitignore` for Laravel/Docker/local artifacts as needed.
- Do not commit `.env`, database data, logs, caches, IDE files, `vendor/`, test outputs, or secrets.
- Preserve the existing project process/specification files unless a concrete contradiction discovered during implementation requires a report and user decision.

## Out Of Scope

Do not implement any of the following in this task:

- Stage 2 database/domain model (`gp_users`, `gp_groups`, payments, applications, histories, dictionaries/settings, etc.);
- authentication or authorization;
- psychologist registration API;
- admin panel;
- psychologist cabinet;
- group creation/moderation;
- scheduler business flows or queues for product emails;
- participant applications;
- payment provider integration;
- bank-specific code;
- placement/extension business logic;
- the Stage 3 design system/UI-kit;
- production deployment automation;
- automatic publication to the existing catalog;
- any Vue/React/Inertia/Livewire/Tailwind/Vite/npm/Node tooling.

Do not add packages unrelated to the Stage 1 requirements.

## Constraints

- Follow `WORKFLOW.md` and `AGENTS.md` exactly.
- Work only on this task.
- Keep implementation simple and conventional.
- Prefer Laravel/framework primitives over custom abstractions.
- No speculative architecture beyond what is needed to give later stages clean, documented locations.
- Laravel must remain deployable as ordinary files to non-Docker production hosting.
- No CDN dependency for Bootstrap.
- No frontend build pipeline.
- Tests must run on MySQL in Docker, not SQLite.
- Never commit real credentials, secrets, production data, or generated local artifacts.
- Do not change `SPEC.md`, `WORKFLOW.md`, `AGENTS.md`, or `.ai/task.md` unless a blocking contradiction is found and the user explicitly authorizes the change.

## Acceptance Criteria

1. A clean checkout of the repository can be started through the documented single normal Docker Compose command without requiring host PHP, Composer, Node, or npm.
2. After startup, the Laravel application is reachable in a browser and renders the Stage 1 Blade test/home page.
3. Laravel successfully connects to the Docker MySQL service.
4. The normal development database and automated test database are separated; automated tests do not use SQLite.
5. Bootstrap 5 is loaded from a pinned local file in `public/`, not a CDN, and the test page visibly demonstrates Bootstrap styling.
6. `public/app.css` and `public/app.js` are loaded directly and work without any build step.
7. The repository contains no required Node/npm/Vite workflow; there is no `package.json`/Vite configuration or equivalent build dependency required to run the application.
8. `APP_TIMEZONE` is UTC and the application has a single reusable tested path to render date/time values in `Europe/Minsk`.
9. The application has a single reusable tested path to format integer minor-unit monetary values without float arithmetic.
10. `composer.json` pins `config.platform.php` to `8.2.32` and explicitly declares the PHP extensions actually required by the implemented project.
11. `composer check-platform-reqs` passes in the development environment.
12. `php artisan test` passes against MySQL in Docker.
13. Laravel Pint is configured, runs, and passes.
14. Larastan/PHPStan is configured at an explicit fixed level, runs, and passes without broad suppressions.
15. `README.md`, `docs/architecture.md`, `docs/development.md`, and `docs/project-status.md` exist and accurately describe the implemented Stage 1 state and verified commands.
16. `docs/project-status.md` clearly states that later domain/product stages are not yet implemented.
17. No secrets, `.env`, database files, logs, caches, `vendor/`, or unrelated generated artifacts are committed.
18. No Stage 2+ business functionality is implemented as part of this task.
19. All committed project files remain suitable for later non-Docker production deployment with document root `public/`.

## Checks

Run the smallest sufficient set of checks, but at minimum perform and report the following from the actual Docker development environment:

1. `docker compose config`
2. Start the project using the documented single startup command from a clean/reproducible state.
3. Verify the HTTP test/home page in the running application.
4. Verify MySQL connectivity from Laravel.
5. Run the full current automated test suite with `php artisan test` and confirm it uses MySQL, not SQLite.
6. Run Pint in check/test mode.
7. Run Larastan/PHPStan using the committed project configuration.
8. Run `composer check-platform-reqs`.
9. Verify the date/time helper with an automated test that checks UTC input is rendered in `Europe/Minsk` correctly.
10. Verify the money helper with automated tests for representative integer minor-unit values and no float input path.
11. Inspect rendered HTML/network paths or equivalent runtime evidence confirming Bootstrap, `app.css`, and `app.js` are served locally and no CDN/build-tool asset is required.
12. Verify the repository does not contain a required `package.json`, Vite config, npm lockfile, or Node-based startup/build command.
13. Review documentation commands against what was actually run so documentation does not contain unverified instructions.

If any required check cannot be run, record exactly why in `.ai/report.md` and do not mark the task `done` unless the remaining gap is genuinely non-blocking under the acceptance criteria.

## Hard Workflow Gate

Before starting implementation:

- run `git log --oneline -5` and `git status --short`;
- read `WORKFLOW.md`, `AGENTS.md`, and this `.ai/task.md`;
- confirm the current task is `TASK-2026-08-19-01` and that the current relevant planner commit has not already been completed by a `codex:` commit;
- do not touch unknown local changes.

During implementation:

- work only within this task scope;
- do not expand into Stage 2 or later functionality;
- do not change user-approved product/architecture requirements silently;
- stop and report a material conflict instead of guessing;
- do not commit secrets, sensitive data, local database content, caches, logs, or unrelated generated artifacts.

Before commit:

- run all applicable checks listed above;
- perform the relevant runtime/browser flow, not only static checks;
- update `.ai/report.md` with actual results, changed files, facts, assumptions, unknowns, risks, and manual verification steps;
- update Stage 1 documentation so it describes the final implemented state;
- inspect `git status --short`;
- inspect the full diff;
- stage only files related to this task plus `.ai/report.md`;
- inspect staged files/diff and confirm no unrelated files or secrets are staged.

Commit only after the gate passes:

```text
codex: TASK-2026-08-19-01 bootstrap project foundation
```

If the task cannot be completed correctly, set `.ai/report.md` status to `partial`, `blocked`, or `failed` as appropriate and do not present the result as successfully completed.
