# Task: TASK-2026-08-19-03

Status: planned
Created from: 6bd522196fbe5c4222861dfb3821a39c681a706f

## Title

Build Stage 2 database schema and domain model

## Goal

Implement Stage 2 from `SPEC.md`: create the complete MVP data foundation, Eloquent models and relationships, status enums/state-transition services, group status history, database-backed sessions/queues, dictionaries/settings foundation, deterministic seed data, and automated tests.

At the end of this task, the database must be reproducible from zero on MySQL, the approved domain invariants must be enforced outside controllers, and later stages must be able to build authentication, groups, applications and payments on top of this foundation without redesigning the schema.

Do not implement Stage 3 UI-kit or any Stage 4+ user-facing/API behavior.

## Facts

- Repository: `saleslabgit/gruppainfo`.
- Planning base: commit `6bd522196fbe5c4222861dfb3821a39c681a706f` on `main`.
- Stage 1 is accepted and provides Laravel 12, PHP 8.2.32, MySQL 8.4.7, Docker Compose, MySQL-backed tests, Pint, Larastan, local Bootstrap and baseline documentation.
- Existing architecture reserves `app/Domain` for domain enums/services and `app/Models` for Eloquent models.
- All automated database behavior must be tested against MySQL, not SQLite.
- `status` is the source of truth for user/group lifecycle. Legacy `accept` must never drive business logic.
- User, group and payment statuses must use string-backed PHP enums and Laravel casts; MySQL `ENUM` is forbidden.
- Multi-record operations such as group status change + status-history write must be transactional.
- Money is stored only as integer minor units in `unsigned bigint` columns; no float/decimal money storage.
- Dates are stored/processed in UTC.
- Main entities use soft deletes where required by `SPEC.md`; physical deletion must not cascade away payments, applications or status history.
- The corrected active-email uniqueness requirement uses a technical generated column with a unique index; `(email, deleted_at)` must not be used.
- Stage 2 must also create standard Laravel database tables for sessions, queued jobs and failed jobs.
- The concrete payment provider is still unknown. No provider-specific fields/protocols beyond the provider-neutral schema in `SPEC.md` may be invented.

## Assumptions

- Use conventional Laravel/Eloquent patterns and the existing documented project structure; do not introduce repositories, event buses, generic state-machine packages or other abstractions not required by this stage.
- `gp_payments.transaction_id` must be nullable but uniquely indexed because the provider is unknown and a payment record may exist before an external transaction identifier is available.
- Dictionary categories required by the specification may be seeded with stable dictionary records, but dictionary item values must not be invented because the repository does not yet contain the approved source lists.
- Known business-setting defaults may be seeded from `SPEC.md`; placement and extension price values must remain explicitly unconfigured/null rather than inventing a monetary amount.
- A development/test administrator may be seeded from clearly non-production environment/config values. No real credential may be committed.

## Unknowns

- The exact legacy compatibility mapping from user/group `status` to `accept` is not specified. Stage 2 must keep the `accept` columns but must not read them for business logic or invent a mapping. Do not add direct application writes to `accept`; record this compatibility mapping as deferred until the integration/moderation stage where its semantics are required.
- Exact dictionary item values are not present in the repository. Do not invent them.
- Final placement/extension prices are not specified. Do not invent them.
- The final audit-storage design for administrator actions on users/payments is not fully specified for Stage 2. Implement the explicitly defined group status history now; do not create a speculative generic audit framework. Later stages must add the remaining audit behavior when those actions are implemented.

These unknowns do not block Stage 2 as long as the implementation preserves the explicit schema and invariants without inventing product data or provider behavior.

## Scope

### 1. Migrations and database schema

Create migrations for the Stage 2 tables required by `SPEC.md`:

- `gp_users`;
- `gp_user_documents`;
- `gp_groups`;
- `gp_group_status_history`;
- `gp_payments`;
- `gp_payment_webhooks`;
- `gp_group_applications`;
- `gp_dictionaries`;
- `gp_dictionary_items`;
- `gp_settings`;
- Laravel `sessions`;
- Laravel `jobs`;
- Laravel `failed_jobs`.

Do not create Stage 4+ integration/idempotency tables unless they are explicitly required by the current schema above.

Use the fields and semantics defined in `SPEC.md`. In particular:

#### `gp_users`

Include the psychologist profile fields from §6 plus:

- `status` string;
- legacy `accept` boolean, never a business-logic source;
- `disabled`, `free`, `admin` booleans;
- `password`, remember token, timestamps and soft delete;
- consent timestamp/version fields;
- nullable profile fields so an administrator can exist in the same table with only the fields required by the specification.

The `User` model must explicitly use `protected $table = 'gp_users';` and remain suitable for later Laravel authentication.

Active email uniqueness:

- create a technical generated column (for example `active_email`) whose value is the email only while `deleted_at IS NULL`, otherwise `NULL`;
- create a unique index on that generated column;
- do not create unique `(email, deleted_at)`;
- do not use the generated column in business logic or normal forms.

#### `gp_user_documents`

Use the document metadata from §6: owner, type, storage path, original name, MIME type, size and timestamps. Storage/controller behavior belongs to Stage 4 and is out of scope here.

#### `gp_groups`

Include all fields from §11, including:

- integer primary key and unique `public_uuid`;
- owner relation;
- string `status`;
- `disabled`, legacy `accept`, snapshot `free`;
- name, description, schedule;
- dictionary references for group format and gender;
- meeting duration in minutes;
- participant count;
- per-meeting price as unsigned bigint minor units;
- moderator/rejection text fields;
- external catalog ID;
- `published_at`, `expires_at`, `expiry_warning_sent_at`, `placement_days`;
- timestamps and soft delete.

Generate `public_uuid` through a simple model-level invariant so later callers do not need to generate it manually.

#### `gp_group_status_history`

Implement exactly the history concept from §4.8:

- group;
- nullable `from_status`;
- `to_status`;
- nullable actor user ID;
- actor type (`user` / `system`);
- nullable comment;
- created timestamp.

Do not add unrelated audit machinery.

#### `gp_payments`

Include the provider-neutral fields from §19:

- owner and group;
- type (`placement` / `extension`);
- nullable unique external `transaction_id`;
- `amount` unsigned bigint minor units;
- currency default `BYN`;
- string status;
- `paid_at`, `refunded_at`, refund comment;
- nullable JSON bank response;
- timestamps and soft delete.

Do not add bank-specific columns.

#### `gp_payment_webhooks`

Include the raw webhook audit fields from §19:

- nullable payment relation;
- nullable external transaction ID;
- raw payload;
- signature-valid flag;
- processed flag;
- result;
- created timestamp.

Actual bank verification/processing belongs to Stage 11.

#### `gp_group_applications`

Include fields from §17:

- group;
- surname, name, phone;
- normalized phone;
- nullable `processed_at`;
- timestamps.

Do not soft-delete applications; later retention cleanup is a permanent delete by design.

#### Dictionaries

Create `gp_dictionaries` and `gp_dictionary_items` with stable codes, display names, sort order and active state. Items already referenced later must be deactivated rather than physically deleted.

Seed at least the required dictionary definitions for:

- education type;
- group format;
- gender.

Do not invent item options that are not present in the repository.

#### Settings

Create a simple `gp_settings` schema suitable for typed cached access. It must support at least:

- placement price in minor units — unconfigured/null initially;
- extension price in minor units — unconfigured/null initially;
- placement duration — 30 days;
- expiry warning threshold — 3 days;
- extension window after expiry — 30 days;
- participant-application retention — 12 months;
- password setup link lifetime — 72 hours.

Keep secrets out of this table; bank/SMTP/integration secrets remain in `.env`.

### 2. Indexes and referential integrity

Implement the indexes required by §28:

- `gp_users`: unique active-email generated-column index; indexes on `status`, `disabled`, `free`;
- `gp_groups`: unique `public_uuid`; indexes on owner, status, expires_at; composite `(status, expires_at)` and `(owner_id, status)`;
- `gp_payments`: unique nullable `transaction_id`; indexes on owner, group, status, type;
- `gp_group_applications`: indexes on group, processed_at, `(group_id, processed_at)`, created_at;
- `gp_group_status_history`: group index;
- `gp_dictionary_items`: unique `(dictionary_id, code)`.

Use foreign-key delete behavior that preserves historical/financial/application data. Do not cascade-delete payments, applications or status histories when a user/group is soft-deleted.

### 3. Eloquent models and relationships

Create the required models with explicit table names where appropriate, casts, soft-delete behavior and relationships.

At minimum support and test the real relationships among:

- User → documents;
- User → groups;
- User → payments;
- Group → owner;
- Group → applications;
- Group → payments;
- Group → status history;
- Group → format/gender dictionary items;
- Payment → owner/group;
- PaymentWebhook → payment;
- Dictionary → items;
- DictionaryItem → dictionary.

Do not add controllers, routes or UI for these models in this stage.

### 4. Status enums and transition rules

Create backed string enums in `app/Domain` and Laravel model casts for:

#### User status

Values:

- `pending`;
- `approved`;
- `rejected`.

Allowed transitions only:

- `pending → approved`;
- `pending → rejected`;
- `rejected → pending`.

#### Group status

Values:

- `awaiting_payment`;
- `draft`;
- `moderation`;
- `revision`;
- `rejected`;
- `approved`;
- `active`;
- `expired`.

Allowed transitions only:

- `awaiting_payment → draft`;
- `draft → moderation`;
- `moderation → approved`;
- `moderation → revision`;
- `moderation → rejected`;
- `revision → moderation`;
- `approved → active`;
- `active → expired`;
- `expired → approved`.

#### Payment status

Values:

- `created`;
- `pending`;
- `succeeded`;
- `failed`;
- `cancelled`;
- `refunded`.

Allowed transitions only:

- `created → pending`;
- `pending → succeeded`;
- `pending → failed`;
- `pending → cancelled`;
- `succeeded → refunded`.

Each enum must expose an explicit transition check such as `canTransitionTo()` and must have automated coverage for allowed and forbidden transitions.

### 5. Domain transition services

Implement simple domain services in `app/Domain` for user, group and payment status changes.

Requirements:

- callers do not directly assign a lifecycle status;
- services validate `canTransitionTo()` and throw a clear domain exception on forbidden transitions;
- group transition + group status-history insert happen in the same DB transaction;
- group history records correct from/to status, actor type, nullable actor and comment;
- services do not read `accept` to decide anything;
- no controller/API/UI integration yet.

Do not build a generic state-machine framework.

### 6. Settings service

Implement a small typed settings service with caching.

Requirements:

- application code obtains settings through the service, not direct queries from templates/controllers;
- support typed integer/string/nullable values needed by the seeded settings;
- cache keys/invalidations are deterministic and testable;
- do not invent a broad configuration framework.

### 7. Seed data

Create idempotent seeders that create/update:

- one development/test administrator in `gp_users` using safe non-production values from config/environment;
- required dictionary definitions;
- known default settings listed above.

Requirements:

- administrator profile-only fields may remain nullable;
- administrator password must be hashed;
- administrator must have the appropriate administrator flag and an approved lifecycle status so later authentication can use it;
- do not write legacy `accept` directly in the seeder;
- do not invent dictionary items or payment prices;
- running seeders repeatedly must not create duplicates or fail.

If new seed-admin environment variables are added to `.env.example`, make them unmistakably local/development placeholders and document that production must override them.

### 8. Database sessions and queues

Create the required Laravel database tables and set the project defaults/configuration for:

- database session storage;
- database queue driver;
- failed jobs.

Do not implement product jobs or emails yet.

### 9. Automated tests

Add focused tests sufficient to prove Stage 2 behavior on MySQL. At minimum cover:

- clean migrations create all required tables;
- seeding creates the administrator, dictionary definitions and settings;
- seeding is idempotent;
- representative model relationships work;
- enum values and every allowed/forbidden status transition matrix are enforced;
- a forbidden transition throws and does not mutate the model;
- group status transition creates exactly one history record with correct actor/system metadata;
- failed group transition does not leave a history record;
- two active users cannot share an email;
- after soft delete, a new user with the same email can be created at DB level;
- generated active-email column is technical only and the forbidden `(email, deleted_at)` unique strategy is not used;
- money columns preserve integer minor-unit values;
- settings are returned with correct types and cache behavior;
- existing Stage 1 tests remain green.

Use database-reset tooling compatible with the dedicated MySQL test schema. Never point destructive tests at the development database.

### 10. Documentation

Update documentation to the implemented Stage 2 state:

- `docs/architecture.md` — actual domain/model/service structure, status transition boundaries, data/soft-delete rules;
- `docs/development.md` — migration, fresh-seed, seed and relevant database/session/queue commands/settings;
- `docs/project-status.md` — Stage 2 only after it is actually complete, what now works, known deferred unknowns (`accept` compatibility mapping, dictionary item values, payment prices/provider), and Stage 3 as next;
- update `README.md` only where Stage 2 changes commands/setup facts that belong in the README.

Documentation must distinguish implemented behavior from future behavior.

## Out Of Scope

Do not implement:

- Stage 3 design system/UI-kit;
- registration/integration API;
- HMAC or request idempotency;
- password-reset/install flow;
- login/logout or authorization policies/middleware;
- administrator pages;
- psychologist cabinet;
- group CRUD/moderation UI;
- scheduler business commands;
- participant-application API/UI;
- payment creation, redirect, webhook processing or any bank-specific integration;
- extension behavior;
- email jobs;
- public catalog publication;
- dictionary/settings CRUD UI;
- speculative generic audit/event frameworks;
- Node/npm/Vite or new frontend tooling.

## Constraints

- Follow `WORKFLOW.md`, `AGENTS.md` and `SPEC.md`.
- Work only on this task.
- Keep the implementation conventional and explicit.
- Do not add third-party packages unless Stage 2 cannot reasonably be implemented with Laravel/PHP primitives; if such a dependency appears necessary, stop and report instead of adding it silently.
- Do not change `SPEC.md`, `WORKFLOW.md`, `AGENTS.md` or `.ai/task.md`.
- Do not invent product data, prices, bank behavior or legacy `accept` semantics.
- Status columns are varchar/string, never MySQL enum.
- Money columns are integer minor units, never float/decimal.
- Tests use the isolated MySQL test database only.
- Do not commit secrets, real credentials, local `.env`, database data, logs, caches or unrelated artifacts.

## Acceptance Criteria

1. A clean MySQL database can be built from zero using Laravel migrations.
2. All Stage 2 tables listed in this task exist with the required columns, relationships, soft-delete behavior and indexes.
3. Active email uniqueness is enforced with the approved generated-column strategy; duplicate active email fails, while reuse after soft delete succeeds.
4. Seeders create one administrator, required dictionary definitions and settings and can be run repeatedly without duplicates/errors.
5. User, Group and Payment status enums are string-backed, cast by models and implement the exact approved transition matrices.
6. Forbidden status transitions throw and cannot mutate lifecycle state.
7. Group status transitions are transactional and create the required history record with actor/system metadata.
8. Required Eloquent relationships work and historical/financial/application data is not configured for destructive cascade deletion from soft-deleted owners/groups.
9. Money is stored as unsigned-bigint integer minor units and retains exact integer values.
10. `public_uuid` is unique and automatically assigned for new groups.
11. Settings are available through a typed cached service; known defaults are seeded, while unknown prices remain unconfigured rather than invented.
12. Required dictionary definitions exist, with no invented item values.
13. Database sessions, jobs and failed-jobs tables exist and default project configuration reflects database-backed session/queue usage.
14. `php artisan migrate:fresh --seed` succeeds on the isolated MySQL test/development verification database as appropriate.
15. Re-running seeders succeeds.
16. Full automated tests pass on MySQL.
17. Pint and Larastan pass.
18. `composer check-platform-reqs` still passes.
19. Existing Stage 1 runtime/home page remains functional after migrations.
20. Documentation accurately describes the implemented Stage 2 state and deferred unknowns.
21. No Stage 3+ functionality or unrelated refactoring is included.

## Checks

Run and report at minimum from the Docker environment:

1. `docker compose config` if Compose/environment defaults are changed.
2. Build/start the existing environment and confirm services are healthy.
3. Run a clean MySQL schema build with `php artisan migrate:fresh --seed` against a disposable/test schema, never destructively against unknown user data.
4. Run the seeder again to prove idempotency.
5. Inspect migration status/schema/indexes sufficiently to verify the generated active-email index and required indexes.
6. Run the complete automated test suite on MySQL.
7. Run Pint in check mode.
8. Run Larastan/PHPStan.
9. Run `composer check-platform-reqs`.
10. Run the project aggregate quality command if still applicable.
11. Verify the home page still returns HTTP 200 in the running environment after migrations/config changes.
12. Inspect the final diff and staged files for scope, secrets and generated artifacts.

Do not claim a check was run if it was not actually run. If destructive migration verification cannot be safely isolated, stop and report the limitation rather than touching an unknown database.

## Hard Workflow Gate

Before implementation:

- read `WORKFLOW.md`, `AGENTS.md`, `SPEC.md`, `.ai/task.md`, and current project documentation;
- run `git log --oneline -5` and `git status --short`;
- confirm `TASK-2026-08-19-03` is the latest relevant planner task and has not already been completed;
- do not touch unknown local changes.

During implementation:

- remain inside Stage 2 scope;
- do not invent missing business values or external-integration behavior;
- stop and report any material schema/product contradiction that cannot be resolved from the repository;
- preserve the accepted Stage 1 runtime and no-Node architecture.

Before commit:

- run the required relevant checks;
- update `docs/architecture.md`, `docs/development.md`, `docs/project-status.md` and any other directly affected documentation;
- replace `.ai/report.md` with an accurate report for this task;
- inspect `git status --short`, full diff and staged files;
- stage only files belonging to this task;
- verify no secrets, local DB data or unrelated generated artifacts are staged.

If the gate passes, commit with:

`codex: TASK-2026-08-19-03 build domain model and database schema`

If safe completion is impossible, set `.ai/report.md` to `partial`, `blocked` or `failed` and do not claim success.
