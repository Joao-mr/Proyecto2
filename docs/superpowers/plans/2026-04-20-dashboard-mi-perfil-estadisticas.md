# Dashboard y Mi Perfil con Estadisticas Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Limitar los dashboards accesibles segun rol y construir una seccion de Mi Perfil compartida (admin y usuario) con datos de perfil, cambio de contrasena y estadisticas (partidas, ELO, imagenes acertadas, titulo por ELO).

**Architecture:** Se centraliza la logica de estadisticas y titulo en backend (ProfileController + PlayerTitleResolver) para evitar duplicacion en frontend y mantener una fuente de verdad unica. El frontend consumira `/api/user` y `/api/user/stats`, mostrara una vista unificada de perfil y aplicara reglas de navegacion para que el usuario normal solo use `/app/profile`, mientras que admin use `/admin` y `/admin/profile`.

**Tech Stack:** Laravel 9 (API + FormRequest + Sanctum), Vue 3 + Vue Router + Pinia + Axios + Yup, PHPUnit.

---

## File Structure

**Create**
- `config/player_titles.php` - Umbrales ELO => titulo.
- `app/Support/PlayerTitleResolver.php` - Resolver de titulo actual por ELO total.
- `tests/Feature/ProfileApiTest.php` - Pruebas API de estadisticas y cambio de contrasena.
- `resources/js/views/shared/MyProfileView.vue` - Vista unificada de Mi Perfil (admin + usuario).

**Modify**
- `app/Http/Controllers/Api/ProfileController.php` - Endpoint de estadisticas + update con password.
- `app/Http/Requests/UpdateProfileRequest.php` - Reglas para nombre y cambio de contrasena.
- `routes/api.php` - Nueva ruta `GET /api/user/stats`.
- `resources/js/composables/profile.js` - Cargar stats y enviar cambio de contrasena.
- `resources/js/routes/routes.js` - Guard para impedir dashboard usuario a admin, redirect `/app` -> `/app/profile`, usar vista compartida.
- `resources/js/layouts/UserLayout.vue` - Menu de usuario solo con Mi Perfil.
- `resources/js/layouts/MainHeader.vue` - Dropdown con Mi Perfil y Panel Admin solo para admin.

**Optional cleanup after verification**
- `resources/js/views/user/profile.vue` y `resources/js/views/admin/profile/index.vue` pueden mantenerse deprecadas en primer commit para evitar riesgo de regresion, y borrarse en una tarea posterior.

---

### Task 1: Backend - Titulos por ELO y estadisticas de perfil

**Files:**
- Create: `config/player_titles.php`
- Create: `app/Support/PlayerTitleResolver.php`
- Modify: `app/Http/Controllers/Api/ProfileController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/ProfileApiTest.php`

- [ ] **Step 1: Write failing tests for `/api/user/stats`**

```php
<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private function createPartidaForUser(User $user, int $puntuacion): void
    {
        $sala = Sala::create([
            'nombre' => 'Sala '.uniqid(),
            'codigo' => 'COD'.uniqid(),
            'id_creador' => $user->id,
        ]);

        $partida = Partida::create([
            'id_sala' => $sala->id,
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
        ]);

        DB::table('usuario_partida')->insert([
            'id_usuario' => $user->id,
            'id_partida' => $partida->id,
            'puntuacion' => $puntuacion,
        ]);
    }

    public function test_user_stats_returns_matches_elo_correct_images_and_title(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createPartidaForUser($user, 150);
        $this->createPartidaForUser($user, 200);

        $response = $this->getJson('/api/user/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'partidas_jugadas',
                'elo_total',
                'imagenes_acertadas',
                'titulo' => ['slug', 'label', 'min_elo'],
            ])
            ->assertJson([
                'partidas_jugadas' => 2,
                'elo_total' => 350,
                'imagenes_acertadas' => 7,
            ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/ProfileApiTest.php --filter=user_stats_returns_matches_elo_correct_images_and_title`
Expected: FAIL with `404` on `/api/user/stats`.

- [ ] **Step 3: Implement title config + resolver + endpoint**

```php
// config/player_titles.php
<?php

return [
    ['slug' => 'bronze', 'label' => 'Bronce', 'min_elo' => 0],
    ['slug' => 'silver', 'label' => 'Plata', 'min_elo' => 500],
    ['slug' => 'gold', 'label' => 'Oro', 'min_elo' => 1200],
    ['slug' => 'platinum', 'label' => 'Platino', 'min_elo' => 2200],
    ['slug' => 'diamond', 'label' => 'Diamante', 'min_elo' => 3500],
    ['slug' => 'master', 'label' => 'Master', 'min_elo' => 5000],
];
```

```php
// app/Support/PlayerTitleResolver.php
<?php

namespace App\Support;

class PlayerTitleResolver
{
    public function resolve(int $eloTotal): array
    {
        $tiers = collect(config('player_titles', []))->sortBy('min_elo')->values();

        if ($tiers->isEmpty()) {
            return ['slug' => 'unranked', 'label' => 'Sin rango', 'min_elo' => 0];
        }

        return $tiers
            ->filter(fn (array $tier) => $eloTotal >= (int) $tier['min_elo'])
            ->last() ?? $tiers->first();
    }
}
```

```php
// app/Http/Controllers/Api/ProfileController.php (add method)
use App\Support\PlayerTitleResolver;
use Illuminate\Support\Facades\DB;

public function stats(Request $request, PlayerTitleResolver $resolver)
{
    $userId = $request->user()->id;

    $aggregate = DB::table('usuario_partida')
        ->where('id_usuario', $userId)
        ->selectRaw('COUNT(*) as partidas_jugadas')
        ->selectRaw('COALESCE(SUM(puntuacion), 0) as elo_total')
        ->first();

    $eloTotal = (int) ($aggregate->elo_total ?? 0);

    return response()->json([
        'partidas_jugadas' => (int) ($aggregate->partidas_jugadas ?? 0),
        'elo_total' => $eloTotal,
        'imagenes_acertadas' => (int) floor($eloTotal / 50),
        'titulo' => $resolver->resolve($eloTotal),
    ]);
}
```

```php
// routes/api.php (inside auth:sanctum group)
Route::get('user/stats', [ProfileController::class, 'stats']);
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/ProfileApiTest.php --filter=user_stats_returns_matches_elo_correct_images_and_title`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add config/player_titles.php app/Support/PlayerTitleResolver.php app/Http/Controllers/Api/ProfileController.php routes/api.php tests/Feature/ProfileApiTest.php
git commit -m "feat(profile): add stats endpoint and title resolver by elo"
```

---

### Task 2: Backend - Cambio de contrasena desde Mi Perfil

**Files:**
- Modify: `app/Http/Requests/UpdateProfileRequest.php`
- Modify: `app/Http/Controllers/Api/ProfileController.php`
- Test: `tests/Feature/ProfileApiTest.php`

- [ ] **Step 1: Write failing tests for password update flow**

```php
public function test_user_can_change_password_with_current_password(): void
{
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/user', [
        'name' => $user->name,
        'current_password' => 'secret123',
        'password' => 'newSecret123',
        'password_confirmation' => 'newSecret123',
    ]);

    $response->assertOk();

    $user->refresh();
    $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newSecret123', $user->password));
}

public function test_user_cannot_change_password_with_wrong_current_password(): void
{
    $user = User::factory()->create([
        'password' => bcrypt('secret123'),
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/user', [
        'name' => $user->name,
        'current_password' => 'wrong-pass',
        'password' => 'newSecret123',
        'password_confirmation' => 'newSecret123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/ProfileApiTest.php --filter=password`
Expected: FAIL because update request/controller still ignore password change.

- [ ] **Step 3: Implement request validation + controller update**

```php
// app/Http/Requests/UpdateProfileRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'current_password' => ['nullable', 'required_with:password,password_confirmation', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }
}
```

```php
// app/Http/Controllers/Api/ProfileController.php (inside update)
use Illuminate\Support\Facades\Hash;

public function update(UpdateProfileRequest $request)
{
    $profile = Auth::user();
    $profile->name = $request->name;

    if ($request->filled('password')) {
        $profile->password = Hash::make($request->password);
    }

    $profile->save();

    return new UserResource($profile->load('roles'));
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/ProfileApiTest.php --filter=password`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/UpdateProfileRequest.php app/Http/Controllers/Api/ProfileController.php tests/Feature/ProfileApiTest.php
git commit -m "feat(profile): support password change with current password validation"
```

---

### Task 3: Frontend - Composable de perfil con stats y password change

**Files:**
- Modify: `resources/js/composables/profile.js`

- [ ] **Step 1: Write failing behavior check (manual)**

Run app and verify current behavior:
1. `Mi Perfil` no muestra estadisticas.
2. No existen campos de contrasena antigua/nueva.
3. `PUT /api/user` solo envia nombre.

- [ ] **Step 2: Implement composable changes**

```js
// resources/js/composables/profile.js (shape + stats + payload)
const initialProfile = {
  name: '',
  email: '',
  current_password: '',
  password: '',
  password_confirmation: ''
}

const stats = ref({
  partidas_jugadas: 0,
  elo_total: 0,
  imagenes_acertadas: 0,
  titulo: { slug: 'unranked', label: 'Sin rango', min_elo: 0 }
})

const profileSchema = yup.object({
  name: yup.string().trim().required('El nombre es obligatorio').min(3, 'Debe tener al menos 3 caracteres'),
  current_password: yup.string().when('password', {
    is: (value) => Boolean(value),
    then: (schema) => schema.required('La contrasena actual es obligatoria para cambiar la contrasena')
  }),
  password: yup.string().nullable().min(8, 'La nueva contrasena debe tener al menos 8 caracteres'),
  password_confirmation: yup.string().oneOf([yup.ref('password'), null], 'Las contrasenas no coinciden')
})

const getProfile = async () => {
  const [{ data: userResp }, { data: statsResp }] = await Promise.all([
    axios.get('/api/user'),
    axios.get('/api/user/stats')
  ])

  const userData = userResp?.data ?? userResp
  setProfile(userData)
  stats.value = statsResp
}

const updateProfile = async () => {
  const payload = {
    name: profile.value.name,
    ...(profile.value.password
      ? {
          current_password: profile.value.current_password,
          password: profile.value.password,
          password_confirmation: profile.value.password_confirmation
        }
      : {})
  }

  const response = await withLoading(() => axios.put('/api/user', payload))
  profile.value.current_password = ''
  profile.value.password = ''
  profile.value.password_confirmation = ''
  return response.data?.data ?? response.data
}
```

- [ ] **Step 3: Run build to verify no frontend errors**

Run: `npm run build`
Expected: PASS with no syntax errors.

- [ ] **Step 4: Commit**

```bash
git add resources/js/composables/profile.js
git commit -m "feat(profile-ui): load stats and support password change payload"
```

---

### Task 4: Frontend - Vista unificada de Mi Perfil (admin y usuario)

**Files:**
- Create: `resources/js/views/shared/MyProfileView.vue`
- Modify: `resources/js/routes/routes.js`

- [ ] **Step 1: Create the shared profile view with profile, password and stats sections**

```vue
<template>
  <div class="row g-4">
    <div class="col-12 col-lg-7">
      <Card>
        <template #title>Mi Perfil</template>
        <template #content>
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
              <label class="fw-bold d-block mb-2">Nombre</label>
              <InputText v-model="profile.name" class="w-100" :invalid="hasError('name')" />
              <small v-if="hasError('name')" class="p-error">{{ getError('name') }}</small>
            </div>
            <div class="col-12 col-md-6">
              <label class="fw-bold d-block mb-2">Correo electronico</label>
              <InputText :model-value="profile.email" class="w-100" disabled />
            </div>
          </div>

          <h5 class="mb-3">Cambiar contrasena</h5>
          <div class="row g-3">
            <div class="col-12">
              <Password v-model="profile.current_password" toggleMask :feedback="false" inputClass="w-100" placeholder="Contrasena actual" />
            </div>
            <div class="col-12 col-md-6">
              <Password v-model="profile.password" toggleMask :feedback="true" inputClass="w-100" placeholder="Nueva contrasena" />
            </div>
            <div class="col-12 col-md-6">
              <Password v-model="profile.password_confirmation" toggleMask :feedback="false" inputClass="w-100" placeholder="Confirmar nueva contrasena" />
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <Button label="Guardar cambios" icon="pi pi-save" :loading="isLoading" @click="submitForm" />
          </div>
        </template>
      </Card>
    </div>

    <div class="col-12 col-lg-5">
      <Card>
        <template #title>Estadisticas</template>
        <template #content>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between"><span>Partidas jugadas</span><strong>{{ stats.partidas_jugadas }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>ELO total</span><strong>{{ stats.elo_total }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Imagenes acertadas</span><strong>{{ stats.imagenes_acertadas }}</strong></li>
            <li class="list-group-item d-flex justify-content-between"><span>Titulo actual</span><strong>{{ stats.titulo?.label }}</strong></li>
          </ul>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import useProfile from '@/composables/profile'

const { profile, stats, getProfile, updateProfile, isLoading, hasError, getError } = useProfile()

const submitForm = () => {
  updateProfile()
}

onMounted(() => {
  getProfile()
})
</script>
```

- [ ] **Step 2: Route both admin and user profile pages to the shared component**

```js
// resources/js/routes/routes.js
{
  name: 'app.profile',
  path: 'profile',
  component: () => import('../views/shared/MyProfileView.vue'),
  meta: { breadCrumb: 'Mi Perfil' }
},

{
  name: 'profile.index',
  path: 'profile',
  component: () => import('../views/shared/MyProfileView.vue'),
  meta: { breadCrumb: 'Mi Perfil' }
},
```

- [ ] **Step 3: Run build and verify route rendering**

Run: `npm run build`
Expected: PASS.

Manual check:
1. Login como admin y abrir `/admin/profile`.
2. Login como usuario y abrir `/app/profile`.
3. Verificar datos, formulario de contrasena y tarjetas de estadisticas.

- [ ] **Step 4: Commit**

```bash
git add resources/js/views/shared/MyProfileView.vue resources/js/routes/routes.js
git commit -m "feat(profile): add shared my-profile page for admin and user"
```

---

### Task 5: Frontend - Reglas de acceso a dashboards y menu

**Files:**
- Modify: `resources/js/routes/routes.js`
- Modify: `resources/js/layouts/UserLayout.vue`
- Modify: `resources/js/layouts/MainHeader.vue`

- [ ] **Step 1: Add route guard so `/app` is only for non-admin users and redirect `/app` -> `/app/profile`**

```js
const hasAdmin = (roles = []) =>
  roles.some((role) => role?.name?.toLowerCase().includes('admin'))

async function requireNonAdmin(to, from, next) {
  const auth = authStore()
  const isLogin = !!auth.authenticated

  if (!isLogin) return next('/login')
  if (hasAdmin(auth.user?.roles || [])) return next('/admin')

  return next()
}

{
  path: '/app',
  component: AuthenticatedUserLayout,
  beforeEnter: requireNonAdmin,
  children: [
    { path: '', redirect: { name: 'app.profile' } },
    // app.profile...
  ]
}
```

- [ ] **Step 2: Make user layout expose only Mi Perfil**

```js
// resources/js/layouts/UserLayout.vue
const items = ref([
  {
    label: 'Cuenta',
    items: [
      {
        label: 'Mi Perfil',
        icon: 'pi pi-user',
        route: '/app/profile'
      }
    ]
  }
])
```

- [ ] **Step 3: Adjust header dropdown options by role**

```vue
<!-- resources/js/layouts/MainHeader.vue -->
<li>
  <router-link :to="route.path.startsWith('/admin') ? '/admin/profile' : '/app/profile'" class="dropdown-menu-item">
    <i class="pi pi-user"></i>
    <span>Mi Perfil</span>
  </router-link>
</li>
<li v-if="auth.is('admin') || auth.is('docent')">
  <router-link to="/admin" class="dropdown-menu-item">
    <i class="pi pi-shield"></i>
    <span>Dashboard</span>
  </router-link>
</li>
```

- [ ] **Step 4: Verify navigation behavior manually**

Manual check:
1. Usuario normal: no acceso funcional a `/admin`, `/app` redirige a `/app/profile`, menu solo "Mi Perfil".
2. Admin: acceso a `/admin` y `/admin/profile`.
3. Dropdown no muestra "Panel Usuario" para admin.

- [ ] **Step 5: Commit**

```bash
git add resources/js/routes/routes.js resources/js/layouts/UserLayout.vue resources/js/layouts/MainHeader.vue
git commit -m "feat(nav): enforce dashboard access by role and profile-first user area"
```

---

### Task 6: End-to-end verification and rollback-safe cleanup

**Files:**
- Optional modify/delete: `resources/js/views/user/profile.vue`
- Optional modify/delete: `resources/js/views/admin/profile/index.vue`
- Test: `tests/Feature/ProfileApiTest.php`

- [ ] **Step 1: Run backend tests**

Run: `php artisan test tests/Feature/ProfileApiTest.php`
Expected: PASS.

- [ ] **Step 2: Run full project checks used in this repo**

Run: `php artisan test`
Expected: PASS (or unrelated pre-existing failures documented).

Run: `npm run build`
Expected: PASS.

- [ ] **Step 3: Cleanup deprecated profile views only if no route imports remain**

```bash
# Verify no imports
findstr /s /n /i "views/user/profile.vue views/admin/profile/index.vue" resources\js\routes\routes.js
```

If no references remain, remove old files in a dedicated commit.

- [ ] **Step 4: Commit verification/cleanup**

```bash
git add -A
git commit -m "chore(profile): verify flows and remove deprecated profile views"
```

---

## Self-Review Checklist (Completed)

- Spec coverage:
  - Admin puede entrar a Dashboard y Mi Perfil: cubierto en Task 5 (guardas + dropdown + rutas).
  - Usuario solo accede a Mi Perfil en area dashboard: cubierto en Task 5 (`/app` restringido y redirigido).
  - Mi Perfil muestra nombre/correo y cambio de contrasena: cubierto en Task 2 + Task 4.
  - Estadisticas requeridas: partidas jugadas, ELO total, imagenes acertadas, titulo por ELO: cubierto en Task 1 + Task 4.
  - Sistema de titulo desbloqueado por ELO: cubierto en Task 1 (`player_titles.php` + resolver).

- Placeholder scan:
  - No `TODO`, `TBD` o "similar a" sin codigo.
  - Cada tarea con rutas exactas, comandos y expected output.

- Type consistency:
  - Campos API de stats consistentes en backend y frontend:
    - `partidas_jugadas`
    - `elo_total`
    - `imagenes_acertadas`
    - `titulo.{slug,label,min_elo}`
