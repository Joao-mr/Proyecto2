# Second Cleanup Pass Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Perform a second cleanup pass after commit `79826ad` to simplify the code further, remove repeated patterns, and make the project easier to maintain while preserving behavior.

**Architecture:** This pass reviews the first cleanup as a structural refactor, not a cosmetic one. Keep behavior stable, reduce duplication across composables/controllers/views, normalize API response handling, and move view logic into focused helpers when it meaningfully lowers complexity.

**Tech Stack:** Laravel 12, PHP 8.2, Vue 3, Vite, Pinia, PrimeVue, Axios, PHPUnit

---

## Commit Review Summary

The cleanup commit `79826ad` already improved several files:

- `ProfileController.php` and `RankingPublicController.php` were simplified.
- `layout.js` and `useImagen.js` were reduced.
- local avatar fallback was introduced.
- some route and component naming cleanup landed.

The next opportunities are now more structural than superficial:

- `resources/js/composables/useImagen.js` still mixes validation, CRUD, upload, delete confirmation, API normalization, and view concerns.
- `app/Http/Controllers/Api/ImagenController.php` still returns multiple response shapes for similar resources.
- admin views such as `resources/js/views/admin/permissions/Index.vue`, `resources/js/views/admin/imagenes/Upload.vue`, and `resources/js/views/admin/index.vue` still contain logic that should live in composables or focused helpers.
- repeated composable patterns exist across `roles.js`, `permissions.js`, `categorias.js`, `salas.js`, `partidas.js`, `users.js`, and `profile.js`.
- mojibake/inconsistent text remains in routes and views (`CategorÃ­a`, `GestiÃ³n`, `Â¿`, `SÃ­`, `ImÃ¡genes`).
- several files still use `console.error`, `console.warn`, `window.confirm`, or inline `try/catch` where the project already has toast/composable patterns.

## File Map

- `docs/superpowers/plans/2026-05-03-codebase-cleanup.md`
  Responsibility: first cleanup plan; keep as historical reference only.
- `resources/js/composables/useImagen.js`
  Responsibility: image CRUD/upload/info composable; main frontend simplification target.
- `app/Http/Controllers/Api/ImagenController.php`
  Responsibility: image API; main backend simplification target.
- `resources/js/views/admin/imagenes/Upload.vue`
  Responsibility: upload page; still owns preview, reset, delete, and refresh logic.
- `resources/js/views/admin/permissions/Index.vue`
  Responsibility: permissions table + filters + dialog orchestration; currently too much local state and browser-storage logic.
- `resources/js/views/admin/index.vue`
  Responsibility: admin dashboard; still fetches data manually and uses direct confirms/logging.
- `resources/js/routes/routes.js`
  Responsibility: route definitions and guards; still verbose and contains mojibake labels.
- `resources/js/composables/permissions.js`
  Responsibility: permission CRUD and role-permission mapping; repeated patterns with other CRUD composables.
- `resources/js/composables/roles.js`
  Responsibility: role CRUD; repeated with permissions and categories.
- `resources/js/composables/categorias.js`
  Responsibility: category CRUD; same CRUD structure repeated again.
- `resources/js/composables/profile.js`
  Responsibility: profile load/update flow; nested async/error structure can still be flattened.
- `app/Http/Controllers/Api/ProfileController.php`
  Responsibility: profile update/stats endpoint; improved, but still mixes fallback aggregation and response assembly.
- `app/Http/Controllers/Api/RankingPublicController.php`
  Responsibility: public ranking endpoints; improved, but category/index logic still share repeated normalization concerns.
- `app/Services/UserStatsService.php`
  Responsibility: stats persistence/reset logic; repeated column-update assembly can be simplified.
- `resources/js/views/public/game/CategoriaGameView.vue`
  Responsibility: category game loading/persist flow; still performs API logic inline.

### Task 1: Freeze the Post-Cleanup Baseline

**Files:**
- Inspect: `app/Http/Controllers/Api/ImagenController.php`
- Inspect: `resources/js/composables/useImagen.js`
- Inspect: `resources/js/views/admin/permissions/Index.vue`
- Inspect: `resources/js/routes/routes.js`
- Test: `tests/router-profile-route.test.mjs`
- Test: `tests/Feature/*.php`

- [ ] **Step 1: Verify the current state after the first cleanup commit**

Run: `git --no-pager show --stat 79826ad`
Expected: confirm the first cleanup scope and use it as the re-cleanup baseline.

- [ ] **Step 2: Verify current frontend behavior**

Run: `npm run test`
Expected: `tests/router-profile-route.test.mjs` passes before new refactors.

- [ ] **Step 3: Verify current backend behavior**

Run: `php vendor/bin/phpunit`
Expected: existing feature tests pass or current failures are recorded before touching code.

- [ ] **Step 4: Verify build stability**

Run: `npm run build`
Expected: build passes before the second cleanup starts.

- [ ] **Step 5: Capture remaining structural smells**

Run:

```bash
rg -n "console\\.error|console\\.warn|confirm\\(|response\\.data\\?\\.data \\?\\? response\\.data|withLoading|GestiÃ³n|CategorÃ|ImÃ|Â¿|SÃ" app resources\\js routes resources\\views
```

Expected: concrete list of repeated patterns, noisy UX strings, and unfinished simplification targets.

### Task 2: Simplify the Image Flow End-to-End

**Files:**
- Modify: `resources/js/composables/useImagen.js`
- Modify: `resources/js/views/admin/imagenes/Upload.vue`
- Modify: `resources/js/views/admin/imagenes/Index.vue`
- Modify: `app/Http/Controllers/Api/ImagenController.php`

- [ ] **Step 1: Normalize image payload shaping in the frontend composable**

In `resources/js/composables/useImagen.js`, replace duplicated request payload construction:

```js
const payload = {
  categoria_id: imagen.value.categoria_id ?? null
}

if (typeof imagen.value.url === 'string' && imagen.value.url.trim() !== '') {
  payload.url = imagen.value.url.trim()
}

if (typeof imagen.value.respuesta_correcta === 'string' && imagen.value.respuesta_correcta.trim() !== '') {
  payload.respuesta_correcta = imagen.value.respuesta_correcta.trim()
}
```

with a single helper:

```js
const buildImagenPayload = (currentImagen) => {
  const payload = {
    categoria_id: currentImagen.categoria_id ?? null,
  }

  const url = typeof currentImagen.url === 'string' ? currentImagen.url.trim() : ''
  const respuestaCorrecta = typeof currentImagen.respuesta_correcta === 'string'
    ? currentImagen.respuesta_correcta.trim()
    : ''

  if (url !== '') payload.url = url
  if (respuestaCorrecta !== '') payload.respuesta_correcta = respuestaCorrecta

  return payload
}
```

- [ ] **Step 2: Normalize image response reading in the composable**

Add a single helper:

```js
const unwrapApiData = (response) => response.data?.data ?? response.data
```

and use it consistently in `getImagenes`, `createImagen`, `updateImagen`, `uploadImagenNew`, and `getMediaInfo`.

- [ ] **Step 3: Remove UI confirmation responsibility from the composable**

In `resources/js/composables/useImagen.js`, replace:

```js
if (!confirm('¿Estas seguro de que deseas eliminar esta imagen?')) {
  return
}
```

by making `deleteImagen(id)` a pure action that only deletes. Keep the confirmation in the calling view.

- [ ] **Step 4: Move upload-page helper logic into focused pure functions**

In `resources/js/views/admin/imagenes/Upload.vue`, reduce local logic by extracting:

```js
const getSelectedFile = (event) => event?.target?.files?.[0] ?? null
const getDroppedFile = (event) => event?.dataTransfer?.files?.[0] ?? null
```

and reuse a single `setSelectedFile(file)` helper that also triggers preview generation.

- [ ] **Step 5: Make the image API return fewer shapes**

In `app/Http/Controllers/Api/ImagenController.php`, standardize on one formatter for image resource payloads so these methods stop returning ad-hoc structures:

```php
store()
show()
update()
getList()
storeWithUpload()
```

Introduce a pair of helpers:

```php
private function formatImagenPayload(Imagen $imagen): array
private function formatMediaPayload($media): array
```

and reuse them instead of rebuilding nested arrays inline.

- [ ] **Step 6: Replace broad exception response duplication in image upload endpoints**

In `app/Http/Controllers/Api/ImagenController.php`, unify error responses from `uploadImage()` and `storeWithUpload()` into one helper:

```php
private function mediaUploadErrorResponse(\Throwable $exception, string $message)
{
    return response()->json([
        'success' => false,
        'message' => $message,
        'error' => $exception->getMessage(),
    ], 500);
}
```

- [ ] **Step 7: Verify the image refactor**

Run:

```bash
npm run build
php vendor/bin/phpunit --filter Imagen
```

Expected: build passes and image-related backend tests, if matched, still pass.

- [ ] **Step 8: Commit the image-flow simplification**

```bash
git add resources/js/composables/useImagen.js resources/js/views/admin/imagenes/Upload.vue resources/js/views/admin/imagenes/Index.vue app/Http/Controllers/Api/ImagenController.php
git commit -m "refactor: simplify image flow and normalize payloads"
```

### Task 3: Extract Repeated CRUD Composable Patterns

**Files:**
- Modify: `resources/js/composables/permissions.js`
- Modify: `resources/js/composables/roles.js`
- Modify: `resources/js/composables/categorias.js`
- Modify: `resources/js/composables/partidas.js`
- Modify: `resources/js/composables/salas.js`
- Modify: `resources/js/composables/users.js`
- Modify: `resources/js/composables/profile.js`
- Create: `resources/js/composables/crud-helpers.js`

- [ ] **Step 1: Create focused shared CRUD helpers**

Create `resources/js/composables/crud-helpers.js` with only low-level pure helpers that already repeat across composables:

```js
export const unwrapApiData = (response) => response.data?.data ?? response.data

export const createLoadingGuard = (isLoading) => async (fn) => {
  if (isLoading.value) {
    throw new Error('Operacion en curso')
  }

  isLoading.value = true

  try {
    return await fn()
  } finally {
    isLoading.value = false
  }
}

export const upsertById = (items, item) => {
  if (!item?.id) {
    return items
  }

  return [item, ...items.filter((currentItem) => currentItem.id !== item.id)]
}
```

- [ ] **Step 2: Replace local `withLoading` duplicates**

In each listed composable, replace the local `withLoading` implementation with `createLoadingGuard(isLoading)`.

- [ ] **Step 3: Replace local upsert duplicates**

Replace patterns such as:

```js
permissions.value = [
  permissionRecord,
  ...permissions.value.filter(item => item.id !== permissionRecord.id)
]
```

with:

```js
permissions.value = upsertById(permissions.value, permissionRecord)
```

- [ ] **Step 4: Normalize `response.data?.data ?? response.data` usage**

Use the shared `unwrapApiData()` helper in all listed composables.

- [ ] **Step 5: Keep composables domain-specific**

Do not create a generic mega-CRUD composable. Keep:

```text
validation schema
domain endpoints
domain-specific payload shape
domain-specific toast labels
```

inside each composable.

- [ ] **Step 6: Verify the shared-helper refactor**

Run:

```bash
npm run test
npm run build
```

Expected: route smoke test and build both pass with the shared CRUD helpers.

- [ ] **Step 7: Commit the composable simplification**

```bash
git add resources/js/composables/crud-helpers.js resources/js/composables/permissions.js resources/js/composables/roles.js resources/js/composables/categorias.js resources/js/composables/partidas.js resources/js/composables/salas.js resources/js/composables/users.js resources/js/composables/profile.js
git commit -m "refactor: extract shared crud composable helpers"
```

### Task 4: Move Admin View Logic Out of Big Pages

**Files:**
- Modify: `resources/js/views/admin/permissions/Index.vue`
- Modify: `resources/js/views/admin/index.vue`
- Modify: `resources/js/views/admin/roles/Index.vue`
- Create: `resources/js/composables/useStoredTableFilters.js`
- Create: `resources/js/composables/useAdminDashboardStats.js`

- [ ] **Step 1: Extract reusable persisted-filter behavior**

Create `resources/js/composables/useStoredTableFilters.js` with:

```js
import { watch } from 'vue'

export function useStoredTableFilters(storageKey, filters, mergeFilters) {
  const canUseBrowserStorage = typeof window !== 'undefined'

  const restore = () => {
    if (!canUseBrowserStorage) return

    try {
      const stored = window.localStorage.getItem(storageKey)
      if (!stored) return
      mergeFilters(JSON.parse(stored))
    } catch (_) {
      return
    }
  }

  const persist = () => {
    if (!canUseBrowserStorage) return

    try {
      window.localStorage.setItem(storageKey, JSON.stringify(filters.value))
    } catch (_) {
      return
    }
  }

  watch(filters, persist, { deep: true })

  return { restore }
}
```

- [ ] **Step 2: Apply the filter composable to permissions and roles**

Use `useStoredTableFilters()` in:

```text
resources/js/views/admin/permissions/Index.vue
resources/js/views/admin/roles/Index.vue
```

to remove duplicated localStorage code.

- [ ] **Step 3: Extract dashboard stats loading**

Create `resources/js/composables/useAdminDashboardStats.js` that owns:

```text
loadStats()
resolveCollectionCount()
isResettingStats
resetPlayerStats()
```

so `resources/js/views/admin/index.vue` becomes a thin UI layer.

- [ ] **Step 4: Replace direct browser confirms where the project already uses SweetAlert or toasts**

In `resources/js/views/admin/index.vue`, remove:

```js
const confirmed = window.confirm(...)
```

and use the same confirmation pattern already used in admin index/list pages.

- [ ] **Step 5: Remove leftover logging from admin views**

Delete `console.error` and `console.warn` calls from the affected admin views once errors are already surfaced by toast or modal UX.

- [ ] **Step 6: Verify the admin view refactor**

Run:

```bash
npm run build
```

Expected: build passes after moving page logic into composables.

- [ ] **Step 7: Commit the admin-page simplification**

```bash
git add resources/js/views/admin/permissions/Index.vue resources/js/views/admin/index.vue resources/js/views/admin/roles/Index.vue resources/js/composables/useStoredTableFilters.js resources/js/composables/useAdminDashboardStats.js
git commit -m "refactor: slim admin views and extract page logic"
```

### Task 5: Tighten Backend Stats and Ranking Logic Again

**Files:**
- Modify: `app/Http/Controllers/Api/ProfileController.php`
- Modify: `app/Http/Controllers/Api/RankingPublicController.php`
- Modify: `app/Services/UserStatsService.php`

- [ ] **Step 1: Introduce a dedicated stats payload builder in `ProfileController`**

Replace the inline response body in `stats()` with:

```php
private function buildStatsResponse(array $statsData, array $currentTitle, ?array $nextTitle, $recentActivity): array
{
    return [
        'partidas_jugadas' => $statsData['partidas_jugadas'],
        'elo_total' => $statsData['elo_total'],
        'imagenes_acertadas' => $statsData['imagenes_acertadas'],
        'titulo' => $currentTitle,
        'resumen' => [
            'promedio_puntos' => $statsData['promedio'],
            'mejor_puntuacion' => $statsData['mejor_puntuacion'],
            'ultima_puntuacion' => $statsData['ultima_puntuacion'],
            'consistencia_pct' => $statsData['consistencia'],
            'progreso_siguiente_titulo_pct' => $this->calculateNextTitleProgress($statsData['elo_total'], $currentTitle, $nextTitle),
        ],
        'actividad_reciente' => $recentActivity,
    ];
}
```

- [ ] **Step 2: Reduce duplicate empty-response handling in ranking**

In `app/Http/Controllers/Api/RankingPublicController.php`, replace repeated:

```php
return response()->json(['mode' => $mode, 'data' => []]);
return response()->json(['category_id' => $categoriaId, 'data' => []]);
```

with focused helpers:

```php
private function emptyIndexResponse(string $mode): JsonResponse
private function emptyCategoryResponse($categoriaId): JsonResponse
```

- [ ] **Step 3: Make ranking normalization explicit**

Extract category-row normalization into:

```php
private function formatCategoryRow(object $row): array
```

to mirror `formatIndexRow()` and reduce inline mapping noise.

- [ ] **Step 4: Simplify repeated user-stats column updates**

In `app/Services/UserStatsService.php`, replace repeated:

```php
if (Schema::hasColumn('users', '...')) {
    $updates['...'] = ...
}
```

with one helper:

```php
private function setColumnIfExists(array &$updates, string $column, mixed $value): void
{
    if (Schema::hasColumn('users', $column)) {
        $updates[$column] = $value;
    }
}
```

Use it in both `syncForUser()` and `resetAllUserStats()`.

- [ ] **Step 5: Verify backend simplification**

Run:

```bash
php vendor/bin/phpunit --filter "Profile|Ranking|UsuarioPartidaStats|AdminPlayerStatsReset"
php vendor/bin/pint --test
```

Expected: targeted backend tests and formatting check pass.

- [ ] **Step 6: Commit the second backend pass**

```bash
git add app/Http/Controllers/Api/ProfileController.php app/Http/Controllers/Api/RankingPublicController.php app/Services/UserStatsService.php
git commit -m "refactor: simplify stats and ranking internals"
```

### Task 6: Clean Text Encoding and Route Metadata

**Files:**
- Modify: `resources/js/routes/routes.js`
- Modify: `resources/js/views/admin/permissions/Index.vue`
- Modify: `resources/js/views/admin/index.vue`
- Modify: `resources/js/views/admin/imagenes/Upload.vue`
- Modify: `resources/js/views/public/game/CategoriaGameView.vue`
- Modify: any touched file still containing mojibake

- [ ] **Step 1: Search for mojibake in touched files**

Run:

```bash
rg -n "GestiÃ³n|CreaciÃ³n|bÃºsqueda|CategorÃ|ImÃ|Â¿|SÃ|OperaciÃ³n|validaciÃ³n" resources\\js routes resources\\views app
```

Expected: list of strings that need normalization.

- [ ] **Step 2: Normalize route metadata labels**

In `resources/js/routes/routes.js`, replace broken labels such as:

```js
{ breadCrumb: 'CategorÃ­as Juego' }
{ breadCrumb: 'Crear CategorÃ­a' }
{ breadCrumb: 'ImÃ¡genes Juego' }
```

with:

```js
{ breadCrumb: 'Categorias juego' }
{ breadCrumb: 'Crear categoria' }
{ breadCrumb: 'Imagenes juego' }
```

Use ASCII where possible to avoid encoding regressions.

- [ ] **Step 3: Normalize user-facing prompts in touched views**

Fix strings like:

```js
'Â¿Eliminar permiso?'
'SÃ­, eliminar'
'Esto reiniciara ... Â¿Quieres continuar?'
```

to readable Spanish or plain ASCII Spanish.

- [ ] **Step 4: Verify no broken text remains in touched areas**

Run:

```bash
rg -n "Ã|Â" resources\\js routes resources\\views app
```

Expected: zero hits in files modified by this pass.

- [ ] **Step 5: Commit the text normalization**

```bash
git add resources/js/routes/routes.js resources/js/views/admin/permissions/Index.vue resources/js/views/admin/index.vue resources/js/views/admin/imagenes/Upload.vue resources/js/views/public/game/CategoriaGameView.vue
git commit -m "chore: normalize text encoding in cleaned files"
```

### Task 7: Final Re-Cleanup Validation

**Files:**
- Modify: only files touched in Tasks 2-6

- [ ] **Step 1: Verify remaining high-signal smells after the second pass**

Run:

```bash
rg -n "console\\.error|console\\.warn|confirm\\(|response\\.data\\?\\.data \\?\\? response\\.data|withLoading" resources\\js app
```

Expected: only intentional remaining cases survive.

- [ ] **Step 2: Run the full validation set**

Run:

```bash
npm run test
php vendor/bin/phpunit
npm run build
php vendor/bin/pint --test
```

Expected: route smoke test passes, backend tests pass, build passes, and PHP formatting checks pass.

- [ ] **Step 3: Review the simplification delta**

Run:

```bash
git --no-pager diff --stat 79826ad..HEAD
```

Expected: mostly deletions, helper extraction, normalization, and shorter big files.

- [ ] **Step 4: Commit the final second-pass cleanup**

```bash
git add -A
git commit -m "refactor: finish second cleanup pass"
```

## Self-Review

- Spec coverage:
  This plan specifically re-analyzes the post-cleanup commit and targets a second-pass simplification of what was already cleaned, with a focus on making code easier, smaller, and still behaviorally identical.
- Placeholder scan:
  No placeholders, `TODO`, or undefined “clean later” steps remain.
- Type consistency:
  Shared helpers are named consistently across the plan: `unwrapApiData`, `createLoadingGuard`, `upsertById`, `useStoredTableFilters`, and `useAdminDashboardStats`.

Plan complete and saved to `docs/superpowers/plans/2026-05-03-second-cleanup-pass.md`. Two execution options:

**1. Subagent-Driven (recommended)** - I dispatch a fresh subagent per task, review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints

Which approach?
