# Codebase Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Leave the Laravel + Vue class project cleaner, smaller, easier to read, and free of dead code, duplicate logic, noisy comments, and clearly unused files without changing visible behavior.

**Architecture:** Work in small, verifiable cleanup passes. First establish a baseline and inventory, then remove obvious noise, then simplify frontend and backend hotspots, and only after that delete files or dependencies that are proven unused.

**Tech Stack:** Laravel 12, PHP 8.2, Vue 3, Vite, Pinia, PrimeVue, CASL, PHPUnit, Node-based frontend smoke test

---

## File Map

- `routes/api.php`
  Responsibility: API route registry; currently contains section comments and can be flattened for readability.
- `routes/web.php`
  Responsibility: SPA entry and auth endpoints; still contains Laravel boilerplate comments and spacing noise.
- `resources/views/main-view.blade.php`
  Responsibility: SPA shell; still shows generic title and commented font line.
- `resources/js/app.js`
  Responsibility: Vue bootstrap; contains commented imports, block comments, and dark-mode boot code that should be trimmed.
- `resources/js/routes/index.js`
  Responsibility: router bootstrap.
- `resources/js/routes/routes.js`
  Responsibility: all SPA routes and route guards; one of the main readability hotspots.
- `resources/js/composables/categorias.js`
  Responsibility: admin/private category CRUD composable; naming is inconsistent with the rest of the composables.
- `resources/js/composables/useCategories.js`
  Responsibility: public home category composable; overlaps in naming with the admin category composable.
- `resources/js/composables/useImagen.js`
  Responsibility: image CRUD/upload/info logic; currently the largest frontend composable and a likely line-count reduction candidate.
- `resources/js/composables/auth.js`
  Responsibility: auth flow; contains commented dead code.
- `resources/js/composables/layout.js`
  Responsibility: theme state syncing; contains many explanatory comments that can be reduced once logic is simplified.
- `resources/js/views/admin/imagenes/Index.vue`
  Responsibility: admin image list; uses modal/delete flows and category loading.
- `resources/js/views/admin/imagenes/Upload.vue`
  Responsibility: admin image upload UI; large file with several comment blocks.
- `resources/js/views/admin/categorias/Index.vue`
  Responsibility: admin category list; depends on `categorias.js`.
- `resources/js/views/shared/MyProfileView.vue`
  Responsibility: user profile UI; includes external avatar fallback URLs and defensive comments.
- `resources/js/views/user/MisSalasView.vue`
  Responsibility: user room list/create UI; contains removable style comments and inline cleanup notes.
- `resources/js/layouts/HomeFooter.vue`
  Responsibility: project footer; verify external social links are intentional for a class project.
- `app/Http/Controllers/Api/CategoriaController.php`
  Responsibility: category API; has noisy comments and a stray `///aaaaaaaaaaaaaaaaa` marker.
- `app/Http/Controllers/Api/ProfileController.php`
  Responsibility: profile update + stats endpoint; good candidate for extracting private pure helpers.
- `app/Http/Controllers/Api/RankingPublicController.php`
  Responsibility: public ranking endpoints; dynamic schema compatibility makes it longer than needed.
- `package.json`
  Responsibility: frontend dependencies and scripts; review for unused dependencies such as `quill` if truly unreferenced.
- `composer.json`
  Responsibility: backend dependencies; verify older project dependencies still match current scope.
- `public/images/*`
  Responsibility: project images and branding assets; delete only after proving they are unreferenced.

### Task 1: Create a Safe Cleanup Baseline

**Files:**
- Modify: `docs/superpowers/plans/2026-05-03-codebase-cleanup.md`
- Inspect: `package.json`
- Inspect: `composer.json`
- Inspect: `tests/router-profile-route.test.mjs`
- Inspect: `tests/Feature/*.php`

- [x] **Step 1: Capture the current behavior baseline**

Run: `npm run test`
Expected: frontend smoke test passes.

- [x] **Step 2: Capture backend baseline**

Run: `php vendor/bin/phpunit`
Expected: backend feature tests pass or existing failures are listed before cleanup begins.

- [x] **Step 3: Capture build baseline**

Run: `npm run build`
Expected: Vite build succeeds without introducing new warnings.

- [x] **Step 4: Record the cleanup inventory**

Run: `rg -n "/\\*|//|swal\\(|Noir|aaaaaaaa|bootdey|quill" routes app resources\\js package.json`
Expected: concrete list of noise, dead comments, suspicious fallbacks, and potentially unused dependencies to process in later tasks.

- [x] **Step 5: Commit the untouched baseline**

```bash
git add docs/superpowers/plans/2026-05-03-codebase-cleanup.md
git commit -m "docs: add cleanup implementation plan"
```

### Task 2: Remove Obvious Noise, Boilerplate, and Comment Clutter

**Files:**
- Modify: `routes/api.php`
- Modify: `routes/web.php`
- Modify: `resources/views/main-view.blade.php`
- Modify: `resources/js/app.js`
- Modify: `app/Http/Controllers/Api/CategoriaController.php`
- Modify: `resources/js/views/user/MisSalasView.vue`
- Modify: `resources/js/composables/auth.js`

- [x] **Step 1: Remove non-functional boilerplate from web routing**

Edit `routes/web.php` to keep only route declarations and remove the default Laravel block comment plus extra blank lines.

- [x] **Step 2: Remove section banners and inline route noise from API routing**

Edit `routes/api.php` to delete the `/* ... */` section banners and convert the route file into compact grouped declarations without changing paths, middleware, or names.

- [x] **Step 3: Clean the SPA shell**

In `resources/views/main-view.blade.php`, remove the commented font line and replace the generic title:

```blade
<title>SQL Check</title>
```

with the real project title sourced from config:

```blade
<title>{{ config('app.name') }}</title>
```

- [x] **Step 4: Remove commented imports and banner comments from the Vue bootstrap**

In `resources/js/app.js`, delete:

```js
/*PRIMEVUE */
/**PRIMEVUE */
//import Noir from './presets/Noir.js';
```

Keep only comments that explain non-obvious initialization logic, and rewrite any remaining comments in simple English if they are truly necessary.

- [x] **Step 5: Delete obvious garbage markers and dead comments**

In `app/Http/Controllers/Api/CategoriaController.php`, remove the stray line:

```php
///aaaaaaaaaaaaaaaaa
```

Also delete comments that only narrate obvious CRUD code.

- [x] **Step 6: Remove inline "cleanup me" comments from Vue views**

Delete comments like:

```css
/* .salas-grid { ... } -> puedes eliminarlo si ya no se usa */
```

from `resources/js/views/user/MisSalasView.vue`, and remove commented SweetAlert snippets from `resources/js/composables/auth.js`.

- [x] **Step 7: Verify formatting and syntax after the noise pass**

Run: `npm run build`
Expected: build still passes after comment and boilerplate removal.

- [x] **Step 8: Commit the noise cleanup**

```bash
git add routes/api.php routes/web.php resources/views/main-view.blade.php resources/js/app.js app/Http/Controllers/Api/CategoriaController.php resources/js/views/user/MisSalasView.vue resources/js/composables/auth.js
git commit -m "refactor: remove boilerplate and dead comments"
```

### Task 3: Normalize Frontend Naming and Remove Duplicate Surface Area

**Files:**
- Modify: `resources/js/composables/categorias.js`
- Modify: `resources/js/composables/useCategories.js`
- Modify: `resources/js/views/CategoriasView.vue`
- Modify: `resources/js/views/user/MisSalasView.vue`
- Modify: `resources/js/components/RankingCategory/RankingCategorySection.vue`
- Modify: `resources/js/views/public/game/CategoriaGameView.vue`
- Modify: `resources/js/views/admin/index.vue`
- Modify: `resources/js/views/admin/imagenes/Index.vue`
- Modify: `resources/js/views/admin/categorias/Create.vue`
- Modify: `resources/js/views/admin/categorias/Edit.vue`
- Modify: `resources/js/views/admin/categorias/Index.vue`

- [x] **Step 1: Decide the naming split and keep behavior unchanged**

Use this rule:

```text
Private/admin CRUD composable => useCategorias
Public home carousel composable => usePublicCategories
```

Do not merge them; they solve different problems and forced unification would add condition flags.

- [x] **Step 2: Rename the public composable API for clarity**

In `resources/js/composables/useCategories.js`, change:

```js
export function useCategories() {
```

to:

```js
export function usePublicCategories() {
```

and update its imports in `resources/js/components/home/CategorySection.vue`.

- [x] **Step 3: Standardize the private category composable name**

In `resources/js/composables/categorias.js`, change the default function signature to:

```js
export default function useCategorias() {
```

and keep that name consistent in every importing view listed above.

- [x] **Step 4: Remove duplicated router fallback code where route names are already stable**

In `resources/js/views/CategoriasView.vue`, replace manual `router.resolve(...)` fallback logic with a direct named navigation if `public.rankings` is guaranteed by `resources/js/routes/routes.js`.

- [x] **Step 5: Simplify route guards without changing redirects**

In `resources/js/routes/routes.js`, extract repeated auth checks into tiny pure helpers and keep only four guard cases:

```text
guest
requireLogin
requireAdmin
requireAppUser
```

Do not add new flags or route meta behavior in this pass.

- [x] **Step 6: Run the existing frontend route smoke test**

Run: `npm run test`
Expected: `tests/router-profile-route.test.mjs` passes after the route/composable naming cleanup.

- [x] **Step 7: Commit the naming cleanup**

```bash
git add resources/js/composables/categorias.js resources/js/composables/useCategories.js resources/js/components/home/CategorySection.vue resources/js/views/CategoriasView.vue resources/js/views/user/MisSalasView.vue resources/js/components/RankingCategory/RankingCategorySection.vue resources/js/views/public/game/CategoriaGameView.vue resources/js/views/admin/index.vue resources/js/views/admin/imagenes/Index.vue resources/js/views/admin/categorias/Create.vue resources/js/views/admin/categorias/Edit.vue resources/js/views/admin/categorias/Index.vue resources/js/routes/routes.js
git commit -m "refactor: normalize category composable naming"
```

### Task 4: Reduce the Largest Frontend Files Without Rebuilding the App

**Files:**
- Modify: `resources/js/composables/useImagen.js`
- Modify: `resources/js/views/admin/imagenes/Index.vue`
- Modify: `resources/js/views/admin/imagenes/Upload.vue`
- Modify: `resources/js/views/shared/MyProfileView.vue`
- Modify: `resources/js/composables/layout.js`

- [x] **Step 1: Reduce `useImagen.js` by extracting pure local helpers first**

Keep the file single-purpose and split only within the same module at first. Extract and reuse helpers for:

```text
normalizing image payloads
building request payloads
resolving preview URLs
validating uploaded files
```

Do not introduce a class or a generic utility bucket.

- [x] **Step 2: Remove duplicated explanation comments from `useImagen.js`**

Delete banner sections such as:

```js
// ============================================
// STATE
// ============================================
```

and keep only short comments above non-obvious transformations.

- [x] **Step 3: Simplify admin image views**

In `resources/js/views/admin/imagenes/Index.vue` and `Upload.vue`, move repeated image URL/fetch/delete handler glue into the composable return API so the views become mostly template + event wiring.

- [x] **Step 4: Replace external placeholder dependencies that make the project look unfinished**

In `resources/js/views/shared/MyProfileView.vue` and `resources/js/views/admin/users/Edit.vue`, replace the `bootdey.com` fallback avatar URL with a local asset under `public/images/` if the project does not already ship a local default avatar.

- [x] **Step 5: Trim theme boot logic comments**

In `resources/js/composables/layout.js`, keep the DOM sync logic but remove long explanatory comment chains once the code is small enough to read directly.

- [x] **Step 6: Verify the admin image flow still builds**

Run: `npm run build`
Expected: build passes and no import path breaks were introduced.

- [x] **Step 7: Commit the frontend reduction pass**

```bash
git add resources/js/composables/useImagen.js resources/js/views/admin/imagenes/Index.vue resources/js/views/admin/imagenes/Upload.vue resources/js/views/shared/MyProfileView.vue resources/js/views/admin/users/Edit.vue resources/js/composables/layout.js
git commit -m "refactor: reduce frontend file size and cleanup assets"
```

### Task 5: Simplify Backend Controllers and Keep Only Necessary Compatibility

**Files:**
- Modify: `app/Http/Controllers/Api/ProfileController.php`
- Modify: `app/Http/Controllers/Api/RankingPublicController.php`
- Modify: `app/Http/Controllers/Api/ImagenController.php`
- Modify: `app/Services/UserStatsService.php`
- Inspect: `database/migrations/*.php`

- [x] **Step 1: Refactor `ProfileController` into smaller private pure methods**

Extract private methods for:

```text
loading recent activity
reading persisted stats
reading fallback stats
building the response payload
```

Keep request validation and persistence behavior unchanged.

- [x] **Step 2: Reduce `RankingPublicController` duplication**

Extract private helpers for:

```text
resolving ranking mode columns
formatting public ranking rows
returning empty ranking responses
```

Keep schema-compatibility checks only where they are actually used.

- [x] **Step 3: Audit defensive compatibility in `ImagenController`**

Keep only compatibility branches that match the current schema and current frontend payloads. If a branch exists only for an old response shape and no current caller uses it, delete it.

- [x] **Step 4: Verify schema assumptions before deleting compatibility code**

Run:

```bash
php artisan migrate:status
php artisan route:list
```

Expected: current migrations and active routes confirm which controller branches are still relevant.

- [x] **Step 5: Run backend tests after controller cleanup**

Run: `php vendor/bin/phpunit`
Expected: feature tests for profile, ranking, salas, stats, and auth still pass.

- [x] **Step 6: Commit the backend simplification**

```bash
git add app/Http/Controllers/Api/ProfileController.php app/Http/Controllers/Api/RankingPublicController.php app/Http/Controllers/Api/ImagenController.php app/Services/UserStatsService.php
git commit -m "refactor: simplify profile ranking and image controllers"
```

### Task 6: Delete Proven-Unused Dependencies, Assets, and Files

**Files:**
- Modify: `package.json`
- Modify: `package-lock.json`
- Delete: only files proven unused by search
- Inspect: `public/images/*`
- Inspect: `resources/js/**/*`

- [x] **Step 1: Prove frontend dependency usage before deleting anything**

Run:

```bash
rg -n "from 'quill'|from \"quill\"|quill" resources/js
rg -n "from 'vue-sweetalert2'|from \"vue-sweetalert2\"|swal\\(" resources/js
rg -n "from '@primevue/themes'|from \"@primevue/themes\"" resources/js
```

Expected: each dependency is either actively used or clearly removable.

- [x] **Step 2: Remove unused dependencies from project config, not manually**

If `quill` is still unused after the search above, remove it with:

```bash
npm uninstall quill
```

Repeat only for dependencies proven unused.

- [x] **Step 3: Prove asset usage before deleting files**

Run:

```bash
rg -n "logo\\.svg|logowhatizit\\.svg|icono1\\.svg|icono2\\.svg|icono3\\.svg|icono4\\.svg|discord\\.svg|instagram\\.svg|tiktok\\.svg|x\\.svg" resources/js resources/views routes
```

Delete only assets with zero references and no class-project value.

- [x] **Step 4: Remove class-project-inappropriate leftovers**

If the social links in `resources/js/layouts/HomeFooter.vue` are not part of the class deliverable, replace them with neutral project/contact content or delete the block entirely instead of keeping dead branding.

- [x] **Step 5: Rebuild after dependency and asset deletion**

Run: `npm run build`
Expected: successful build with no missing-module or missing-asset errors.

- [x] **Step 6: Commit the deletion pass**

```bash
git add package.json package-lock.json public/images resources/js/layouts/HomeFooter.vue
git commit -m "chore: remove unused dependencies and assets"
```

### Task 7: Final Presentation Pass for the Class Project

**Files:**
- Modify: only files touched in Tasks 2-6
- Inspect: `README.md`

- [x] **Step 1: Do a final search for noisy comment styles**

Run:

```bash
rg -n "/\\*|//|TODO|FIXME|console\\.log|aaaaaaaa" app resources/js routes
```

Expected: only meaningful comments remain; no garbage markers, temporary notes, or dead debug output.

- [x] **Step 2: Make naming and wording consistent**

Check for mixed labels such as `CategorÃ­a`, `ImÃ¡genes`, `Profile`, `Permissions`, and normalize obvious mojibake or inconsistent UI wording where cleanup touched the file.

- [x] **Step 3: Run the full validation set**

Run:

```bash
npm run test
php vendor/bin/phpunit
npm run build
php vendor/bin/pint --test
```

Expected: route smoke test passes, backend tests pass, build passes, and PHP formatting checks pass.

- [x] **Step 4: Review final diff for accidental behavior changes**

Run: `git --no-pager diff --stat HEAD~4..HEAD`
Expected: mostly deletions, simplifications, and small refactors; no surprise feature work.

- [x] **Step 5: Commit the final polish**

```bash
git add -A
git commit -m "refactor: finish codebase cleanup for class presentation"
```

## Self-Review

- Spec coverage:
  This plan covers dead comments, duplicate naming, file-size reduction, unused dependency checks, unused asset checks, presentation polish, and validation after each pass.
- Placeholder scan:
  No `TODO`, `TBD`, or "implement later" markers are left in the plan.
- Type consistency:
  Cleanup naming is consistent across the plan: `useCategorias` for private CRUD and `usePublicCategories` for the public category carousel.

Plan complete and saved to `docs/superpowers/plans/2026-05-03-codebase-cleanup.md`. Two execution options:

**1. Subagent-Driven (recommended)** - I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?
