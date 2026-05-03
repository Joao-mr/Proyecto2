# Database Reset and Clean Bootstrap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Leave the repository able to bootstrap a clean database from `main` using migrations, seeders, and documented install commands so a fresh clone can run without manual data fixes, legacy residue, or hidden schema dependencies.

**Architecture:** Split the work into four layers: installation baseline, schema cleanup, deterministic seed data, and clean-room verification. Keep schema changes in migrations only, move all mutable/demo data to seeders, and generate one project-local `.sql` artifact from the final clean state as a support asset rather than as the primary installation path.

**Tech Stack:** Laravel 12, PHP 8.2, MySQL/MariaDB, Vue 3, Vite, Laravel Sanctum, Spatie Permission, PHPUnit, Node smoke tests

**Constraint:** Commit steps are intentionally omitted because the user requested no commits in the plan.

---

## File Map

- `README.md`
  Responsibility: primary local setup guide; currently outdated in framework versioning and too optimistic about the current seed/bootstrap flow.
- `.env.example`
  Responsibility: default environment contract for a fresh clone; must exactly match the installation guide.
- `composer.json`
  Responsibility: backend install hooks; confirm no post-install step assumes a dirty local database.
- `package.json`
  Responsibility: frontend build/test commands used in clean-room verification.
- `database/migrations/2026_04_17_000001_add_elo_partidas_titulo_to_users_table.php`
  Responsibility: introduces `elo`, `partidas_jugadas`, and `titulo`; must stay consistent with later stats migrations.
- `database/migrations/2026_04_22_000008_add_stats_columns_to_users_table.php`
  Responsibility: introduces persisted stats columns; likely part of the final canonical user stats schema.
- `database/migrations/2026_04_20_000007_align_game_schema_with_diagram.php`
  Responsibility: destructive schema alignment migration; must be audited so fresh installs and reruns stay deterministic.
- `database/migrations/2026_04_30_000001_seed_local_game_images.php`
  Responsibility: currently mixes schema history with data seeding; must be replaced by a proper seeder.
- `database/seeders/DatabaseSeeder.php`
  Responsibility: root seeder orchestration; must become deterministic and ordered by dependency.
- `database/seeders/UsersTableSeeder.php`
  Responsibility: currently imports historical user rows; must be reduced to intentional bootstrap users or rewritten with factories/explicit inserts.
- `database/seeders/RolesTableSeeder.php`
  Responsibility: seeds application roles.
- `database/seeders/PermissionsTableSeeder.php`
  Responsibility: seeds application permissions.
- `database/seeders/RoleHasPermissionsTableSeeder.php`
  Responsibility: attaches permissions to roles.
- `database/seeders/ModelHasRolesTableSeeder.php`
  Responsibility: assigns roles to bootstrap users.
- `database/seeders/ModelHasPermissionsTableSeeder.php`
  Responsibility: seeds direct model permissions if still required.
- `database/seeders/CategoriasTableSeeder.php`
  Responsibility: seeds game categories; currently hard-codes IDs and deletes related rows manually.
- `database/seeders/SalasTableSeeder.php`
  Responsibility: seeds rooms if demo gameplay data is kept.
- `database/seeders/ImagenesTableSeeder.php`
  Responsibility: seeds image rows; must align with `GameImageCatalog` and final category IDs.
- `database/seeders/ImagenCategoriaTableSeeder.php`
  Responsibility: seeds image-category pivots; must become idempotent.
- `database/seeders/PartidasTableSeeder.php`
  Responsibility: seeds match history; likely demo-only and should not pollute a clean installation unless intentionally separated.
- `database/seeders/UsuarioPartidaTableSeeder.php`
  Responsibility: seeds user match stats; currently contaminates Elo/stat fields with historical data.
- `database/seeders/UsuarioSalaTableSeeder.php`
  Responsibility: seeds room participation if demo mode is preserved.
- `app/Support/GameImageCatalog.php`
  Responsibility: filesystem-backed image catalog; should be the source of truth for image seed generation.
- `app/Models/User.php`
  Responsibility: user fillable/casts; must reflect the canonical stats schema after cleanup.
- `app/Services/UserStatsService.php`
  Responsibility: recalculates persisted stats from `usuario_partida`; must stay aligned with whichever demo data remains seeded.
- `tests/Feature/AuthTest.php`
  Responsibility: verifies auth flow on a fresh migrated database.
- `tests/Feature/PartidaResultadoApiTest.php`
  Responsibility: verifies game result flow and stats persistence.
- `tests/Feature/UsuarioPartidaStatsSyncTest.php`
  Responsibility: verifies stats synchronization rules.
- `tests/Feature/AdminPlayerStatsResetTest.php`
  Responsibility: verifies admin reset behavior after historical gameplay data is cleared.
- `tests/Feature/DatabaseBootstrapTest.php`
  Responsibility: new integration test file for seed/bootstrap expectations.
- `database/sql/clean-bootstrap.sql`
  Responsibility: project-local SQL artifact generated from the final clean schema+seed state for inspection/support use.
- `docs/setup/clean-install.md`
  Responsibility: explicit installation recipe for a new clone, including DB creation, migrations, seeders, frontend build, and verification.
- `docs/superpowers/plans/2026-05-03-database-reset-and-bootstrap.md`
  Responsibility: this implementation plan.

### Task 1: Capture the Real Bootstrap Baseline

**Files:**
- Inspect: `README.md`
- Inspect: `.env.example`
- Inspect: `composer.json`
- Inspect: `package.json`
- Inspect: `database/migrations/*.php`
- Inspect: `database/seeders/*.php`
- Inspect: `tests/Feature/*.php`

- [x] **Step 1: Record current migration status expectations**

Run:

```bash
php artisan migrate:status
```

Expected: Laravel lists every migration file, including `2026_04_30_000001_seed_local_game_images`, so we know exactly which data mutations are currently embedded in migration history.

Result (2026-05-03): command succeeded. `2026_04_30_000001_seed_local_game_images` appears in migration history with status `Ran` (batch 13).

- [x] **Step 2: Run the backend suite on the untouched branch**

Run:

```bash
php vendor/bin/phpunit
```

Expected: either green tests or a concrete failure list that becomes the acceptance baseline for the cleanup.

Result (2026-05-03): command failed. Error: `Could not open input file: vendor/bin/phpunit`.

- [x] **Step 3: Run the frontend smoke/build baseline**

Run:

```bash
npm run test
npm run build
```

Expected: current frontend status is captured before touching database/bootstrap code.

Result (2026-05-03):
- `npm run test`: executed `npm run test:frontend` -> `node tests/router-profile-route.test.mjs`; result `router-profile-route.test: ok`.
- `npm run build`: executed `vite build` with `vite v7.3.2`; build succeeded (`✓ built in 2.59s`).
- Build warning captured: `Some chunks are larger than 500 kB after minification.`

- [x] **Step 4: Write a bootstrap audit checklist in the plan file before changing code**

Baseline findings checklist (2026-05-03):

- [x] Migration files that mutate data instead of schema
Evidence: `2026_04_30_000001_seed_local_game_images` is a migration explicitly named as seeding and runs during `migrate:fresh`.
- [x] Seeders that hard-delete tables or assume fixed IDs
Evidence: `database/seeders/CategoriasTableSeeder.php` performs `DB::table('categorias')->delete()`, deletes related rows by fixed ID (`whereIn('id_categoria', [1])`), and inserts explicit IDs (`'id' => 2..5`).
- [x] Seeders that contain private or historical user data
Evidence: `database/seeders/UsersTableSeeder.php` includes a non-demo personal email (`deivi7.hs@gmail.com`) and fixed historical timestamps (`created_at`/`updated_at` set to `2025-07-25 ...`).
- [x] Seeders that create demo gameplay history affecting Elo/stats
Evidence: `migrate:fresh --seed` currently runs `PartidasTableSeeder`, `UsuarioPartidaTableSeeder`, and `UsuarioSalaTableSeeder`.
- [x] README or .env setup instructions that do not match the real app
Evidence: `README.md` still presents the project as "Laravel 10 + Vue 3" and asks for `FRONTEND_URL` in `.env`, while `.env.example` does not define `FRONTEND_URL`; README also uses placeholder `DB_DATABASE=nombre_de_tu_bd` while `.env.example` sets `DB_DATABASE=base`.
- [x] Tests that already prove bootstrap correctness (partial)
Evidence: current tests (`PartidaResultadoApiTest`, `UsuarioPartidaStatsSyncTest`, `AdminPlayerStatsResetTest`) validate gameplay/stat flows with `RefreshDatabase`, but they do not fully validate fresh-clone bootstrap docs + seed contract end-to-end.
- [x] Missing tests for fresh-clone installation
Evidence: `tests/Feature/DatabaseBootstrapTest.php` is planned as new coverage, indicating a current gap.

- [x] **Step 5: Confirm the current install path from zero context**

Run:

```bash
php artisan migrate:fresh --seed
```

Expected: either a clean successful install or the exact failing migration/seeder that the rest of the plan must fix first.

Result (2026-05-03): command succeeded. `php artisan migrate:fresh --seed` completed migrations and all configured seeders without runtime errors.

### Task 2: Freeze the Target Data Strategy Before Editing Seeders

**Files:**
- Modify: `docs/setup/clean-install.md`
- Modify: `README.md`
- Inspect: `database/seeders/DatabaseSeeder.php`
- Inspect: `database/seeders/UsersTableSeeder.php`
- Inspect: `database/seeders/PartidasTableSeeder.php`
- Inspect: `database/seeders/UsuarioPartidaTableSeeder.php`
- Inspect: `app/Services/UserStatsService.php`

- [x] **Step 1: Document the canonical seed layers**

Add this section to `docs/setup/clean-install.md` before implementing seeders:

```md
## Seed layers

1. Foundation seed data
   - roles
   - permissions
   - admin user
   - one normal user
   - categories
   - image catalog
2. Optional demo seed data
   - salas
   - partidas
   - usuario_partida
   - usuario_sala
   - partida_imagen
3. Runtime-generated data
   - real gameplay history
   - recalculated user stats
```

- Result (2026-05-03): added `## Seed layers` section in `docs/setup/clean-install.md` with the exact canonical layer content.

- [x] **Step 2: Decide and write the clean-install rule for player stats**

Write this exact rule in `docs/setup/clean-install.md`:

```md
A clean installation from `main` must finish with user stat columns initialized to zero unless optional demo gameplay seeders are executed explicitly.
```

- Result (2026-05-03): added the exact clean-install stats rule to `docs/setup/clean-install.md`.

- [x] **Step 3: Decide and write the clean-install rule for demo users**

Use this explicit bootstrap user contract in docs and later in code:

```text
admin@demo.com / 12345678
user@demo.com / 12345678
```

Do not keep personal or historical local users such as private email addresses in the clean seed path.

- Result (2026-05-03): documented bootstrap demo user contract and explicit no-personal-user rule in `docs/setup/clean-install.md`.

- [x] **Step 4: Decide and write the `.sql` artifact rule**

Add this exact note to `docs/setup/clean-install.md`:

```md
`database/sql/clean-bootstrap.sql` is a support export generated from the final clean migrated-and-seeded state. The official installation path remains `php artisan migrate --seed`.
```

- Result (2026-05-03): added the exact SQL support-artifact rule to `docs/setup/clean-install.md`.

- [x] **Step 5: Update README goals before code changes**

Replace vague install wording in `README.md` with a short target statement like:

```md
This repository must be installable from a fresh clone using Composer, npm, `.env`, `php artisan migrate --seed`, and `npm run build` without manual database cleanup.
```

Result (2026-05-03): added explicit target statement in `README.md` (Spanish context) for fresh-clone install without manual DB cleanup.

Result (2026-05-03): Task 2 quality findings addressed in `README.md` (Laravel 12, PHP >= 8.2, removed `FRONTEND_URL`, and ASCII wording in edited lines to reduce mojibake impact).

### Task 3: Make Migrations Pure Schema History

**Files:**
- Modify: `database/migrations/2026_04_30_000001_seed_local_game_images.php`
- Modify: `database/migrations/2026_04_20_000007_align_game_schema_with_diagram.php`
- Modify: `database/migrations/2026_04_17_000001_add_elo_partidas_titulo_to_users_table.php`
- Modify: `database/migrations/2026_04_22_000008_add_stats_columns_to_users_table.php`
- Create: `database/seeders/GameImageCatalogSeeder.php`
- Inspect: `app/Support/GameImageCatalog.php`

- [x] **Step 1: Extract image seeding out of migration history**

Replace the body of `database/migrations/2026_04_30_000001_seed_local_game_images.php` so the migration becomes a no-op with an explanatory comment only:

```php
public function up(): void
{
    // Data loading moved to GameImageCatalogSeeder to keep migrations schema-only.
}

public function down(): void
{
    // No schema changes to reverse.
}
```

Result (2026-05-03): `2026_04_30_000001_seed_local_game_images` converted to migration no-op with explanatory schema-only comments in `up()` and `down()`.

- [x] **Step 2: Create a dedicated seeder for filesystem-backed game images**

Create `database/seeders/GameImageCatalogSeeder.php` with a deterministic upsert flow based on `GameImageCatalog::records()`:

```php
<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameImageCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

            foreach (GameImageCatalog::records() as $record) {
                $categoryId = $categoryIds[$record['category_name']] ?? null;

                if ($categoryId === null) {
                    throw new \RuntimeException('Missing category for image catalog seeding.');
                }

                DB::table('imagenes')->updateOrInsert(
                    ['url' => $record['url']],
                    [
                        'respuesta_correcta' => $record['respuesta_correcta'],
                        'categoria_id' => $categoryId,
                    ]
                );
            }
        });
    }
}
```

Result (2026-05-03): added `database/seeders/GameImageCatalogSeeder.php` using `GameImageCatalog::records()` with deterministic `updateOrInsert` and explicit `RuntimeException` when `category_name` has no mapped category ID.

- [x] **Step 3: Audit destructive compatibility logic in schema alignment migration**

In `database/migrations/2026_04_20_000007_align_game_schema_with_diagram.php`, keep only schema operations that are valid on a fresh install and remove branches whose only purpose is cleaning dirty historical rows during migration. If a branch exists only to rewrite live data, move that behavior into a one-off seeder or drop it entirely.

Result (2026-05-03): removed only clearly data-cleanup updates (`whereNull(...)->update(...)`) from `imagenes` branch; preserved schema-history operations (column/timestamp alterations, table drop/create guards, and MySQL/MariaDB driver checks).

- [x] **Step 4: Reconcile duplicated user stat schema history**

Compare `2026_04_17_000001_add_elo_partidas_titulo_to_users_table.php` and `2026_04_22_000008_add_stats_columns_to_users_table.php` against `app/Models/User.php` and `app/Services/UserStatsService.php`, then make the final schema explicit:

```text
rol
elo
partidas_jugadas
titulo
elo_total
imagenes_acertadas
promedio_puntos
mejor_puntuacion
ultima_puntuacion
consistencia_pct
```

If any migration adds overlapping columns without guards, add safe guards or a dedicated consolidation migration instead of relying on lucky execution order.

Result (2026-05-03): added `Schema::hasColumn` guards and typed signatures to `2026_04_17_000001_add_elo_partidas_titulo_to_users_table.php` for `elo`, `partidas_jugadas`, and `titulo`, plus guarded `down()` drops. `2026_04_22_000008_add_stats_columns_to_users_table.php` already guarded overlap columns, so no extra rewrite was required.

- [x] **Step 5: Verify migrations can rebuild from scratch after the schema cleanup**

Run:

```bash
php artisan migrate:fresh
```

Expected: schema rebuild succeeds without any data-loading side effects and without depending on preexisting rows.

Result (2026-05-03): `php artisan migrate:fresh` succeeded; all migrations, including the now schema-only `2026_04_30_000001_seed_local_game_images`, ran cleanly.
Quality fix note (2026-05-03): strengthened Task 3 safety by blocking non-empty `respuestas` drops with actionable `RuntimeException`, handling timestamp columns independently for partial states in `up()`/`down()`, and adding explicit zero-record failure in `GameImageCatalogSeeder`.

### Task 4: Rewrite Seeders Into Deterministic Foundation and Optional Demo Layers

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `database/seeders/UsersTableSeeder.php`
- Modify: `database/seeders/RolesTableSeeder.php`
- Modify: `database/seeders/PermissionsTableSeeder.php`
- Modify: `database/seeders/RoleHasPermissionsTableSeeder.php`
- Modify: `database/seeders/ModelHasRolesTableSeeder.php`
- Modify: `database/seeders/ModelHasPermissionsTableSeeder.php`
- Modify: `database/seeders/CategoriasTableSeeder.php`
- Modify: `database/seeders/ImagenesTableSeeder.php`
- Modify: `database/seeders/ImagenCategoriaTableSeeder.php`
- Modify: `database/seeders/SalasTableSeeder.php`
- Modify: `database/seeders/PartidasTableSeeder.php`
- Modify: `database/seeders/UsuarioPartidaTableSeeder.php`
- Modify: `database/seeders/UsuarioSalaTableSeeder.php`
- Create: `database/seeders/FoundationSeeder.php`
- Create: `database/seeders/DemoGameSeeder.php`

- [x] **Step 1: Replace table-wide `delete()` seed style with explicit deterministic writes**

Wherever a seeder currently does this:

```php
DB::table('users')->delete();
DB::table('users')->insert([...]);
```

rewrite it to deterministic `updateOrInsert()` or model `upsert()` keyed by stable natural keys such as `email`, `name`, `guard_name`, `codigo`, or `url`.

Result (2026-05-03): replaced blanket `delete()` patterns in Task 4 seeders with deterministic `updateOrInsert()` flows keyed by natural keys (`email`, `name+guard_name`, `codigo`, `url`, and composite pivots). Demo pivot seeders were converted to explicit composite key upserts.

- [x] **Step 2: Remove private and historical user rows from the clean path**

Rewrite `database/seeders/UsersTableSeeder.php` to seed only the bootstrap users:

```php
[
    [
        'name' => 'Admin',
        'surname1' => 'Demo',
        'surname2' => null,
        'alias' => 'admin',
        'email' => 'admin@demo.com',
        'password' => bcrypt('12345678'),
        'rol' => 'admin',
    ],
    [
        'name' => 'User',
        'surname1' => 'Demo',
        'surname2' => null,
        'alias' => 'user',
        'email' => 'user@demo.com',
        'password' => bcrypt('12345678'),
        'rol' => 'player',
    ],
]
```

Keep all stat columns at zero in the foundation seeder.

Result (2026-05-03): `UsersTableSeeder` now seeds only `admin@demo.com` and `user@demo.com`; removed personal/historical rows and explicit legacy timestamps; bootstrap stat columns are initialized to zero.

- [x] **Step 3: Make category seeding independent from historical numeric IDs**

Refactor `CategoriasTableSeeder.php` so it stops deleting relations for hard-coded category IDs like `1`, `2`, `3`, `4`, `5`. Seed by `nombre` instead and let later seeders resolve category IDs dynamically:

```php
$categories = [
    ['nombre' => 'Deportes', 'descripcion' => '...', 'imagen' => '/images/deportes.webp'],
    ['nombre' => 'Peliculas', 'descripcion' => '...', 'imagen' => '/images/pelicula.webp'],
    ['nombre' => 'Videojuegos', 'descripcion' => '...', 'imagen' => '/images/videojuegos.webp'],
    ['nombre' => 'Geografia', 'descripcion' => '...', 'imagen' => '/images/geografia.webp'],
];
```

Result (2026-05-03): `CategoriasTableSeeder` now seeds deterministically by `nombre` without hardcoded IDs and without fixed-ID relation cleanup.

- [x] **Step 4: Separate foundation data from optional demo gameplay data**

Create `database/seeders/FoundationSeeder.php` with only base application data:

```php
$this->call([
    UsersTableSeeder::class,
    RolesTableSeeder::class,
    PermissionsTableSeeder::class,
    RoleHasPermissionsTableSeeder::class,
    ModelHasRolesTableSeeder::class,
    ModelHasPermissionsTableSeeder::class,
    CategoriasTableSeeder::class,
    GameImageCatalogSeeder::class,
]);
```

Result (2026-05-03): created `FoundationSeeder` and `DemoGameSeeder` with the planned split. Foundation path includes `GameImageCatalogSeeder`; demo gameplay data is isolated in `DemoGameSeeder`.

Create `database/seeders/DemoGameSeeder.php` with optional demo gameplay tables only:

```php
$this->call([
    SalasTableSeeder::class,
    PartidasTableSeeder::class,
    UsuarioPartidaTableSeeder::class,
    UsuarioSalaTableSeeder::class,
    PartidaImagenTableSeeder::class,
]);
```

- [x] **Step 5: Make `DatabaseSeeder` choose the clean default path**

Change `database/seeders/DatabaseSeeder.php` so the default `php artisan db:seed` path calls only the foundation layer:

```php
public function run(): void
{
    $this->call([
        FoundationSeeder::class,
    ]);
}
```

If demo gameplay data is still useful, document it as a separate explicit command:

```bash
php artisan db:seed --class=DemoGameSeeder
```

Result (2026-05-03): `DatabaseSeeder` now calls only `FoundationSeeder` by default.

- [x] **Step 6: Re-sync stat expectations after removing historical gameplay seeds**

After foundation seeding, verify that `UserStatsService` sees zero stats for bootstrap users until demo data or real gameplay is inserted.

Run:

```bash
php artisan migrate:fresh --seed
```

Expected: two bootstrap users, categories, roles, permissions, and image catalog exist; gameplay tables are empty or contain only intentionally documented demo rows.

Result (2026-05-03): `php artisan migrate:fresh --seed` succeeded with `FoundationSeeder` path only. Verification snapshot via `php artisan tinker --execute=...`: bootstrap users count (`admin@demo.com`,`user@demo.com`) = `2`, `usuario_partida` count = `0`, `partidas` count = `0`.
Quality note (2026-05-03): Task 4 determinism fixes applied. `UsersTableSeeder` no longer rewrites password hashes on every run (hash is set on insert or when stored password is empty), and `PartidaImagenTableSeeder` now resolves demo image links by stable catalog URL lookup instead of unordered image ID indexes.

### Task 5: Align Models and Runtime Code With the Clean Seed Contract

**Files:**
- Modify: `app/Models/User.php`
- Modify: `app/Services/UserStatsService.php`
- Modify: `app/Models/Sala.php`
- Modify: `app/Models/Partida.php`
- Inspect: `app/Models/Categoria.php`
- Inspect: `app/Models/Imagen.php`

- [ ] **Step 1: Make `User` fillable/casts match the final schema exactly**

In `app/Models/User.php`, ensure the final `$fillable` and `$casts` contain the persisted stats contract actually created by migrations and seeded by the clean install:

```php
protected $casts = [
    'email_verified_at' => 'datetime',
    'elo' => 'integer',
    'partidas_jugadas' => 'integer',
    'elo_total' => 'integer',
    'imagenes_acertadas' => 'integer',
    'promedio_puntos' => 'integer',
    'mejor_puntuacion' => 'integer',
    'ultima_puntuacion' => 'integer',
    'consistencia_pct' => 'integer',
];
```

- [ ] **Step 2: Remove hidden seed assumptions from runtime services**

In `app/Services/UserStatsService.php`, verify every stat is derived from `usuario_partida` and not from the existence of historical seed data. The service should work identically when the clean install has zero matches.

- [ ] **Step 3: Validate optional demo seed compatibility with runtime code**

If demo gameplay seeders remain, confirm the resulting `salas`, `partidas`, `usuario_partida`, and `usuario_sala` rows match model fillable fields and foreign keys exactly. Remove any seeder column that no longer exists in the models or schema.

- [ ] **Step 4: Verify profile and ranking endpoints on a clean database**

Run:

```bash
php vendor/bin/phpunit --filter ProfileApiTest
php vendor/bin/phpunit --filter UsuarioPartidaStatsSyncTest
php vendor/bin/phpunit --filter PartidaResultadoApiTest
```

Expected: all endpoints behave correctly even when the initial database starts with zero historical Elo.

### Task 6: Add Bootstrap-Focused Integration Coverage

**Files:**
- Create: `tests/Feature/DatabaseBootstrapTest.php`
- Modify: `tests/Feature/AuthTest.php`
- Modify: `tests/Feature/AdminPlayerStatsResetTest.php`
- Inspect: `tests/TestCase.php`

- [ ] **Step 1: Add a fresh-install database bootstrap test file**

Create `tests/Feature/DatabaseBootstrapTest.php` with three integration assertions:

```php
public function test_foundation_seed_creates_bootstrap_users_roles_permissions_and_categories(): void
public function test_foundation_seed_does_not_create_historical_match_stats(): void
public function test_game_image_catalog_seed_is_idempotent(): void
```

- [ ] **Step 2: Write the bootstrap user assertion**

Use assertions like:

```php
$this->assertDatabaseHas('users', ['email' => 'admin@demo.com']);
$this->assertDatabaseHas('users', ['email' => 'user@demo.com']);
$this->assertDatabaseHas('roles', ['name' => 'admin']);
$this->assertDatabaseHas('permissions', ['name' => 'user-list']);
$this->assertDatabaseHas('categorias', ['nombre' => 'Deportes']);
```

- [ ] **Step 3: Write the zero-stats assertion for the clean path**

Use assertions like:

```php
$this->assertDatabaseCount('usuario_partida', 0);
$this->assertDatabaseMissing('users', ['email' => 'admin@demo.com', 'elo_total' => 850]);
$this->assertDatabaseHas('users', ['email' => 'admin@demo.com', 'elo_total' => 0]);
```

- [ ] **Step 4: Write the image seeder idempotency assertion**

Seed the catalog twice and assert the image count remains stable:

```php
$this->seed(GameImageCatalogSeeder::class);
$countAfterFirstRun = DB::table('imagenes')->count();
$this->seed(GameImageCatalogSeeder::class);
$countAfterSecondRun = DB::table('imagenes')->count();

$this->assertSame($countAfterFirstRun, $countAfterSecondRun);
```

- [ ] **Step 5: Run only the new database bootstrap tests first**

Run:

```bash
php vendor/bin/phpunit --filter DatabaseBootstrapTest
```

Expected: red first, then green after the seeder refactor is complete.

### Task 7: Generate the Project SQL Artifact and Update Installation Docs

**Files:**
- Create: `database/sql/clean-bootstrap.sql`
- Modify: `README.md`
- Modify: `.env.example`
- Create: `docs/setup/clean-install.md`

- [ ] **Step 1: Create the SQL artifact directory in the repository**

Create this folder structure exactly:

```text
database/
  sql/
    clean-bootstrap.sql
```

- [ ] **Step 2: Generate the SQL artifact from the final clean state**

After `php artisan migrate:fresh --seed` succeeds, export the database with a non-interactive command such as:

```bash
mysqldump --no-tablespaces --skip-comments -u root -pYOUR_PASSWORD YOUR_DATABASE_NAME > database/sql/clean-bootstrap.sql
```

If the local setup uses an empty password in XAMPP, document that exact variant in `docs/setup/clean-install.md` instead of hard-coding it in code.

- [ ] **Step 3: Add SQL artifact usage guidance without replacing migrations**

Document this exact rule in `README.md` and `docs/setup/clean-install.md`:

```md
Use `database/sql/clean-bootstrap.sql` only as a reference/support export. The supported installation flow for contributors is still `php artisan migrate --seed`.
```

- [ ] **Step 4: Rewrite the installation section for a fresh clone**

Make `README.md` and `docs/setup/clean-install.md` include this exact command sequence:

```bash
composer install
cp .env.example .env
php artisan key:generate
# create empty MySQL database manually or with your local tool
php artisan migrate --seed
npm install
npm run build
```

Add the optional local run commands after that:

```bash
php artisan serve
npm run dev
```

- [ ] **Step 5: Make `.env.example` match the documented workflow exactly**

Keep these keys explicit and aligned with the install guide:

```dotenv
APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:8000,127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=base
DB_USERNAME=root
DB_PASSWORD=
```

Only add new environment keys if the current code truly requires them for a clean install.

### Task 8: Run the Clean-Room Verification Matrix

**Files:**
- Modify: only files touched in Tasks 2-7
- Inspect: `database/sql/clean-bootstrap.sql`
- Inspect: `README.md`
- Inspect: `docs/setup/clean-install.md`

- [ ] **Step 1: Verify the official bootstrap path end-to-end**

Run:

```bash
php artisan migrate:fresh --seed
php vendor/bin/phpunit
npm run test
npm run build
```

Expected: the repository installs, seeds, tests, and builds successfully without any manual SQL edits.

- [ ] **Step 2: Verify the optional demo path separately**

Run:

```bash
php artisan db:seed --class=DemoGameSeeder
php vendor/bin/phpunit --filter AdminPlayerStatsResetTest
php vendor/bin/phpunit --filter PartidaResultadoApiTest
```

Expected: demo gameplay data can be loaded intentionally without breaking stats reset or result registration.

- [ ] **Step 3: Verify the SQL artifact matches the final clean state**

Run:

```bash
git diff -- database/sql/clean-bootstrap.sql README.md docs/setup/clean-install.md
```

Expected: the SQL artifact and docs correspond to the same final schema/seed contract.

- [ ] **Step 4: Review the remaining seeders for forbidden patterns**

Search for these patterns:

```bash
Select-String -Path database\seeders\*.php -Pattern "delete\(|truncate\(|gmail.com|seed_local_game_images|id' => 1|id\) => 1"
```

Expected: no private email addresses, no historical migration naming, and no destructive table wipes remain in the clean installation path.

- [ ] **Step 5: Perform the final acceptance check against the original request**

Confirm all of the following are true:

```md
- Current database data can be reset cleanly
- Elo and user stats do not ship polluted by old match history
- Migrations are schema-only and replay cleanly
- Seeders are ordered, deterministic, and intentional
- A `.sql` file exists inside the repository
- A fresh clone can follow docs and install without hidden manual fixes
- Backend tests pass
- Frontend smoke/build passes
```

## Self-Review

- Spec coverage:
  This plan covers current data cleanup, Elo/user stat reset strategy, migration cleanup, seeder normalization, `.sql` artifact creation, model/runtime alignment, documentation rewrite, and full fresh-clone verification.
- Placeholder scan:
  No `TODO`, `TBD`, or “implement later” placeholders remain; every task points to exact files and commands.
- Type consistency:
  The plan consistently distinguishes `FoundationSeeder` as the clean default path, `DemoGameSeeder` as the optional demo data path, and `GameImageCatalogSeeder` as the replacement for migration-based image loading.

Plan complete and saved to `docs/superpowers/plans/2026-05-03-database-reset-and-bootstrap.md`. Two execution options:

**1. Subagent-Driven (recommended)** - I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints

**Which approach?**

