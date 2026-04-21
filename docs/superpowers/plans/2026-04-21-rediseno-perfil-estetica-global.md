# Rediseno Frontend de Mi Perfil con Estetica Global Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transformar `Mi Perfil` en una experiencia visual premium, coherente con el estilo publico de la web (home/ranking), y mostrar estadisticas de jugador de forma mas rica que solo 4 metricas planas.

**Architecture:** Se mantiene una sola vista compartida para admin y usuario (`MyProfileView.vue`), pero se refactoriza en componentes de UI de perfil. El backend amplifica `GET /api/user/stats` con metricas derivadas y serie reciente para renderizar progreso, actividad y contexto competitivo. El look & feel usa los mismos tokens visuales (paleta, gradientes, sombras y tipografia) ya presentes en `home.css`, pero encapsulados en un stylesheet de perfil para evitar acoplar estilos de landing con el area autenticada.

**Tech Stack:** Laravel 9 API + Sanctum + Query Builder, Vue 3 + PrimeVue + Bootstrap utility classes + Axios + Yup, PHPUnit, Vite build.

---

## Scope Check

Este esfuerzo cae en un solo subsistema (perfil de usuario en area autenticada), con dos capas dependientes:
1. contrato de datos de estadisticas;
2. presentacion visual de perfil.

No se parte en subplanes porque el rediseno visual depende del nuevo contrato de stats.

---

## File Structure

**Create**
- `resources/js/components/profile/ProfileHeroCard.vue` - Cabecera visual del jugador (avatar grande, nombre, titulo, resumen rapido).
- `resources/js/components/profile/ProfileStatsGrid.vue` - Grid de KPIs con jerarquia visual y variaciones de color.
- `resources/js/components/profile/ProfilePerformanceCard.vue` - Bloque de progreso (promedio, mejor partida, consistencia, progreso al siguiente titulo).
- `resources/js/components/profile/ProfileRecentMatchesCard.vue` - Lista de ultimas partidas con fecha, puntuacion y micro-tendencia.
- `resources/css/profile.css` - Estilos del modulo perfil alineados con paleta y ritmo visual global.

**Modify**
- `app/Http/Controllers/Api/ProfileController.php` - Enriquecer payload de `/api/user/stats` con agregados y actividad reciente.
- `tests/Feature/ProfileApiTest.php` - Pruebas del nuevo contrato de stats (estructura + valores derivados + orden de actividad).
- `resources/js/composables/profile.js` - Nuevo shape de stats y normalizacion robusta de payload.
- `resources/js/views/shared/MyProfileView.vue` - Composicion por secciones visuales y layout responsive.
- `resources/css/app.css` - Importar `profile.css`.
- `resources/js/lang/es.json` - Labels de nuevas secciones en espanol.
- `resources/js/lang/en.json` - Labels equivalentes en ingles para no romper i18n.

**Test/Verification**
- `tests/Feature/ProfileApiTest.php`

---

### Task 1: Definir contrato de estadisticas enriquecido en backend

**Files:**
- Modify: `app/Http/Controllers/Api/ProfileController.php`
- Test: `tests/Feature/ProfileApiTest.php`

- [ ] **Step 1: Write the failing test**

```php
public function test_user_stats_returns_extended_profile_payload(): void
{
    $user = $this->makeUser();
    Sanctum::actingAs($user);

    $sala = $this->createSala($user);
    $partida1 = $this->createPartida($sala->id);
    $partida2 = $this->createPartida($sala->id);
    $partida3 = $this->createPartida($sala->id);

    $user->partidas()->attach($partida1->id, ['puntuacion' => 120]);
    $user->partidas()->attach($partida2->id, ['puntuacion' => 280]);
    $user->partidas()->attach($partida3->id, ['puntuacion' => 80]);

    $this->getJson('/api/user/stats')
        ->assertOk()
        ->assertJsonStructure([
            'partidas_jugadas',
            'elo_total',
            'imagenes_acertadas',
            'titulo' => ['slug', 'label', 'min_elo'],
            'resumen' => [
                'promedio_puntos',
                'mejor_puntuacion',
                'ultima_puntuacion',
                'consistencia_pct',
                'progreso_siguiente_titulo_pct',
            ],
            'actividad_reciente' => [
                '*' => ['id_partida', 'puntuacion', 'fecha_inicio', 'fecha_fin'],
            ],
        ]);
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/ProfileApiTest.php --filter=extended_profile_payload`
Expected: FAIL because `resumen` y `actividad_reciente` aun no existen.

- [ ] **Step 3: Implement minimal backend changes**

```php
// app/Http/Controllers/Api/ProfileController.php (inside stats)
$userId = $request->user()->id;

$base = DB::table('usuario_partida as up')
    ->join('partidas as p', 'p.id', '=', 'up.id_partida')
    ->where('up.id_usuario', $userId);

$aggregate = (clone $base)
    ->selectRaw('COUNT(*) as partidas_jugadas')
    ->selectRaw('COALESCE(SUM(up.puntuacion), 0) as elo_total')
    ->selectRaw('COALESCE(AVG(up.puntuacion), 0) as promedio_puntos')
    ->selectRaw('COALESCE(MAX(up.puntuacion), 0) as mejor_puntuacion')
    ->first();

$recent = (clone $base)
    ->select('up.id_partida', 'up.puntuacion', 'p.fecha_inicio', 'p.fecha_fin')
    ->orderByDesc('p.fecha_inicio')
    ->limit(8)
    ->get();

$eloTotal = (int) ($aggregate->elo_total ?? 0);
$promedio = (float) ($aggregate->promedio_puntos ?? 0);
$mejor = (int) ($aggregate->mejor_puntuacion ?? 0);
$ultima = (int) ($recent->first()->puntuacion ?? 0);
$consistencia = $mejor > 0 ? min(100, (int) round(($promedio / $mejor) * 100)) : 0;

return response()->json([
    'partidas_jugadas' => (int) ($aggregate->partidas_jugadas ?? 0),
    'elo_total' => $eloTotal,
    'imagenes_acertadas' => (int) floor($eloTotal / self::POINTS_PER_CORRECT_IMAGE),
    'titulo' => $resolver->resolve($eloTotal),
    'resumen' => [
        'promedio_puntos' => (int) round($promedio),
        'mejor_puntuacion' => $mejor,
        'ultima_puntuacion' => $ultima,
        'consistencia_pct' => $consistencia,
        'progreso_siguiente_titulo_pct' => 0,
    ],
    'actividad_reciente' => $recent,
]);
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/ProfileApiTest.php --filter=extended_profile_payload`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/ProfileController.php tests/Feature/ProfileApiTest.php
git commit -m "feat(profile): expose enriched stats payload for redesigned profile"
```

---

### Task 2: Ajustar capa de datos frontend para nuevo contrato

**Files:**
- Modify: `resources/js/composables/profile.js`

- [ ] **Step 1: Write the failing behavior check**

Manual check target:
1. `stats.resumen` es `undefined` en `MyProfileView`.
2. `actividad_reciente` no se puede iterar sin romper.

- [ ] **Step 2: Implement minimal composable update**

```js
const initialStats = {
  partidas_jugadas: 0,
  elo_total: 0,
  imagenes_acertadas: 0,
  titulo: { slug: '', label: '', min_elo: 0 },
  resumen: {
    promedio_puntos: 0,
    mejor_puntuacion: 0,
    ultima_puntuacion: 0,
    consistencia_pct: 0,
    progreso_siguiente_titulo_pct: 0
  },
  actividad_reciente: []
}

const setStats = (data = {}) => {
  stats.value = {
    partidas_jugadas: data.partidas_jugadas ?? 0,
    elo_total: data.elo_total ?? 0,
    imagenes_acertadas: data.imagenes_acertadas ?? 0,
    titulo: {
      slug: data.titulo?.slug ?? '',
      label: data.titulo?.label ?? '',
      min_elo: data.titulo?.min_elo ?? 0
    },
    resumen: {
      promedio_puntos: data.resumen?.promedio_puntos ?? 0,
      mejor_puntuacion: data.resumen?.mejor_puntuacion ?? 0,
      ultima_puntuacion: data.resumen?.ultima_puntuacion ?? 0,
      consistencia_pct: data.resumen?.consistencia_pct ?? 0,
      progreso_siguiente_titulo_pct: data.resumen?.progreso_siguiente_titulo_pct ?? 0
    },
    actividad_reciente: Array.isArray(data.actividad_reciente) ? data.actividad_reciente : []
  }
}
```

- [ ] **Step 3: Run build to verify it passes**

Run: `npm run build`
Expected: PASS.

- [ ] **Step 4: Commit**

```bash
git add resources/js/composables/profile.js
git commit -m "refactor(profile): support enriched stats contract in composable"
```

---

### Task 3: Crear base visual del nuevo perfil alineada al estilo global

**Files:**
- Create: `resources/css/profile.css`
- Modify: `resources/css/app.css`

- [ ] **Step 1: Add profile stylesheet scaffold**

```css
/* resources/css/profile.css */
.profile-page {
  background: linear-gradient(180deg, #6675a5 0%, #6f7ead 48%, #7e89ad 100%);
  border-radius: 24px;
  padding: 1.25rem;
  color: #eef2ff;
}

.profile-surface {
  background: rgba(77, 90, 132, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(23, 33, 68, 0.24);
}

.profile-kpi {
  background: rgba(95, 109, 150, 0.9);
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
```

- [ ] **Step 2: Import profile stylesheet in global CSS**

```css
/* resources/css/app.css */
@import "./profile.css";
```

- [ ] **Step 3: Run build to verify CSS integration**

Run: `npm run build`
Expected: PASS and no unresolved import.

- [ ] **Step 4: Commit**

```bash
git add resources/css/profile.css resources/css/app.css
git commit -m "feat(profile-ui): add profile visual layer aligned to global theme"
```

---

### Task 4: Refactorizar MyProfileView a composicion por componentes

**Files:**
- Create: `resources/js/components/profile/ProfileHeroCard.vue`
- Create: `resources/js/components/profile/ProfileStatsGrid.vue`
- Create: `resources/js/components/profile/ProfilePerformanceCard.vue`
- Create: `resources/js/components/profile/ProfileRecentMatchesCard.vue`
- Modify: `resources/js/views/shared/MyProfileView.vue`

- [ ] **Step 1: Build profile hero component**

```vue
<!-- resources/js/components/profile/ProfileHeroCard.vue -->
<template>
  <section class="profile-surface p-3 p-md-4">
    <div class="d-flex align-items-center gap-3">
      <Avatar :image="avatar" style="width: 5rem; height: 5rem" shape="circle" />
      <div>
        <h2 class="mb-1">{{ name }}</h2>
        <p class="mb-2 text-white-50">{{ subtitle }}</p>
        <Tag :value="titleLabel" rounded />
      </div>
    </div>
  </section>
</template>
```

- [ ] **Step 2: Build KPI grid component**

```vue
<!-- resources/js/components/profile/ProfileStatsGrid.vue -->
<template>
  <section class="row g-3">
    <div v-for="item in items" :key="item.key" class="col-12 col-sm-6 col-xl-3">
      <article class="profile-kpi p-3 h-100">
        <small class="text-white-50 d-block">{{ item.label }}</small>
        <strong class="fs-4">{{ item.value }}</strong>
      </article>
    </div>
  </section>
</template>
```

- [ ] **Step 3: Build performance + recent matches components**

```vue
<!-- resources/js/components/profile/ProfilePerformanceCard.vue -->
<template>
  <section class="profile-surface p-3 p-md-4">
    <h3 class="h5 mb-3">Rendimiento</h3>
    <div class="row g-3">
      <div class="col-6"><span>Promedio</span><strong>{{ resumen.promedio_puntos }}</strong></div>
      <div class="col-6"><span>Mejor</span><strong>{{ resumen.mejor_puntuacion }}</strong></div>
      <div class="col-6"><span>Ultima</span><strong>{{ resumen.ultima_puntuacion }}</strong></div>
      <div class="col-6"><span>Consistencia</span><strong>{{ resumen.consistencia_pct }}%</strong></div>
    </div>
  </section>
</template>
```

```vue
<!-- resources/js/components/profile/ProfileRecentMatchesCard.vue -->
<template>
  <section class="profile-surface p-3 p-md-4">
    <h3 class="h5 mb-3">Actividad reciente</h3>
    <ul class="list-unstyled mb-0">
      <li v-for="match in matches" :key="match.id_partida" class="d-flex justify-content-between py-2 border-bottom">
        <span>#{{ match.id_partida }}</span>
        <strong>{{ match.puntuacion }} pts</strong>
      </li>
    </ul>
  </section>
</template>
```

- [ ] **Step 4: Compose new page layout in shared view**

```vue
<!-- resources/js/views/shared/MyProfileView.vue (template skeleton) -->
<template>
  <div class="profile-page vstack gap-3 gap-md-4">
    <ProfileHeroCard ... />
    <ProfileStatsGrid :items="kpiItems" />
    <div class="row g-3">
      <div class="col-12 col-lg-6"><ProfilePerformanceCard :resumen="stats.resumen" /></div>
      <div class="col-12 col-lg-6"><ProfileRecentMatchesCard :matches="stats.actividad_reciente" /></div>
    </div>
    <!-- mantener bloque de datos personales y cambio de contrasena -->
  </div>
</template>
```

- [ ] **Step 5: Run build and manual smoke test**

Run: `npm run build`
Expected: PASS.

Manual:
1. `/app/profile` carga sin errores.
2. `/admin/profile` mantiene mismo diseno (vista compartida).
3. Mobile (`<=768px`) no rompe layout.

- [ ] **Step 6: Commit**

```bash
git add resources/js/components/profile resources/js/views/shared/MyProfileView.vue
git commit -m "feat(profile-ui): redesign shared profile page with modular sections"
```

---

### Task 5: i18n y consistencia visual de copys del perfil

**Files:**
- Modify: `resources/js/lang/es.json`
- Modify: `resources/js/lang/en.json`
- Modify: `resources/js/views/shared/MyProfileView.vue`

- [ ] **Step 1: Add translation keys**

```json
{
  "profile": {
    "hero_subtitle": "Tu progreso competitivo en Whatizit",
    "stats": {
      "matches": "Partidas jugadas",
      "elo": "ELO total",
      "hits": "Imagenes acertadas",
      "title": "Titulo actual"
    },
    "performance": "Rendimiento",
    "recent_activity": "Actividad reciente"
  }
}
```

- [ ] **Step 2: Replace hardcoded strings in view/components**

```vue
<h3 class="h5 mb-3">{{ $t('profile.performance') }}</h3>
```

- [ ] **Step 3: Build and quick locale verification**

Run: `npm run build`
Expected: PASS.

Manual:
1. Cambiar idioma desde `LocaleSwitcher`.
2. Confirmar titulos de perfil en ES/EN.

- [ ] **Step 4: Commit**

```bash
git add resources/js/lang/es.json resources/js/lang/en.json resources/js/views/shared/MyProfileView.vue resources/js/components/profile
git commit -m "chore(profile-ui): internationalize redesigned profile copy"
```

---

### Task 6: Verificacion funcional y visual de extremo a extremo

**Files:**
- Test: `tests/Feature/ProfileApiTest.php`
- Modify (optional): `docs/superpowers/plans/2026-04-21-rediseno-perfil-estetica-global.md`

- [ ] **Step 1: Run backend profile test suite**

Run: `php artisan test tests/Feature/ProfileApiTest.php`
Expected: PASS.

- [ ] **Step 2: Run full build verification**

Run: `npm run build`
Expected: PASS.

- [ ] **Step 3: Manual visual acceptance checklist**

Checklist:
1. Header visual del perfil usa estilo coherente con la landing (paleta azul + acento naranja).
2. Estadisticas ya no son solo "4 cajas"; existe jerarquia visual con bloques de rendimiento y actividad.
3. El formulario de datos personales y password sigue operativo.
4. En desktop (>=992px) se ve estructura de 2 columnas y en mobile se apila correctamente.
5. El contraste de texto sobre fondos oscuros es legible.

- [ ] **Step 4: Commit verification notes**

```bash
git add -A
git commit -m "test(profile): verify redesigned profile experience and stats flow"
```

---

## Self-Review Checklist (Completed)

- Spec coverage:
  - Alinear perfil a estetica global: cubierto por Task 3 y Task 4.
  - Evitar vista plana de "solo numeros y 4 secciones": cubierto por Task 4 (hero + kpi + rendimiento + actividad).
  - Mantener informacion de cuenta editable: cubierto en Task 4 (se conserva bloque de datos/password).
  - Mostrar estadisticas del jugador con mas contexto: cubierto por Task 1 y Task 2.

- Placeholder scan:
  - Sin TODO/TBD.
  - Cada tarea tiene archivos, pasos, comandos y expected output.

- Type consistency:
  - Contrato de stats consistente en backend y frontend:
    - `partidas_jugadas`
    - `elo_total`
    - `imagenes_acertadas`
    - `titulo.{slug,label,min_elo}`
    - `resumen.{promedio_puntos,mejor_puntuacion,ultima_puntuacion,consistencia_pct,progreso_siguiente_titulo_pct}`
    - `actividad_reciente[]`
