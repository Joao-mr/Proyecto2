<template>
  <section class="ranking-category py-4 py-lg-5">
    <div class="container ranking-category__container">
      <div class="ranking-category__inner">
        <header class="ranking-category__header text-center mb-3 mb-lg-4">
          <h1 class="ranking-category__title mb-1">Ranking por categoría</h1>
          <p class="ranking-category__subtitle mb-0">
            Consulta quién domina cada categoría según la puntuación acumulada.
          </p>
        </header>

        <div class="card border-0 ranking-category__toolbar mb-4">
          <div class="card-body p-3 p-lg-4">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-lg">
                <label class="ranking-control__label mb-2">Categoría</label>

                <div class="d-none d-md-flex flex-wrap gap-2">
                  <button
                    v-for="categoria in categoryOptions"
                    :key="`chip-${getCategoriaId(categoria)}`"
                    type="button"
                    class="btn ranking-chip"
                    :class="{ 'is-active': String(getCategoriaId(categoria)) === String(selectedCategoriaId) }"
                    @click="selectedCategoriaId = String(getCategoriaId(categoria))"
                  >
                    {{ getCategoriaNombre(categoria) }}
                  </button>
                </div>

                <select
                  v-model="selectedCategoriaId"
                  class="form-select ranking-select d-md-none"
                  :disabled="categoriasLoading"
                >
                  <option value="" disabled>Selecciona una categoría</option>
                  <option
                    v-for="categoria in categoryOptions"
                    :key="`opt-${getCategoriaId(categoria)}`"
                    :value="String(getCategoriaId(categoria))"
                  >
                    {{ getCategoriaNombre(categoria) }}
                  </option>
                </select>
              </div>

              <div class="col-12 col-md-6 col-lg-3">
                <label class="ranking-control__label mb-2">Mostrar</label>
                <div class="btn-group w-100" role="group" aria-label="Top limit">
                  <button
                    type="button"
                    class="btn ranking-top-btn"
                    :class="{ 'is-active': topLimit === 10 }"
                    @click="setTopLimit(10)"
                  >
                    Top 10
                  </button>
                  <button
                    type="button"
                    class="btn ranking-top-btn"
                    :class="{ 'is-active': topLimit === 50 }"
                    @click="setTopLimit(50)"
                  >
                    Top 50
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row g-4 justify-content-center">
          <div class="col-12 col-lg-4">
            <aside class="card border-0 ranking-summary h-100">
              <div class="card-body p-3 p-lg-4">
                <small class="ranking-summary__label">Categoría activa</small>
                <h3 class="ranking-summary__category mb-3">
                  {{ selectedCategory ? getCategoriaNombre(selectedCategory) : 'Sin categoría' }}
                </h3>

                <div class="row g-2 text-center mb-3">
                  <div class="col-4">
                    <div class="ranking-stat">
                      <div class="ranking-stat__value">{{ formatNumber(totalPlayers) }}</div>
                      <div class="ranking-stat__label">Jug.</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="ranking-stat">
                      <div class="ranking-stat__value">{{ formatNumber(totalPoints) }}</div>
                      <div class="ranking-stat__label">Pts</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="ranking-stat">
                      <div class="ranking-stat__value">{{ formatNumber(totalGames) }}</div>
                      <div class="ranking-stat__label">Part.</div>
                    </div>
                  </div>
                </div>

                <h4 class="ranking-summary__mini-title">Podio</h4>
                <ul class="list-unstyled mb-0 ranking-podium">
                  <li
                    v-for="(player, index) in podium"
                    :key="`podium-${index}-${getUsuario(player)}`"
                    class="ranking-podium__item"
                  >
                    <span class="ranking-podium__pos">#{{ index + 1 }}</span>
                    <span class="text-truncate">{{ getUsuario(player) }}</span>
                    <strong>{{ formatNumber(getPuntuacion(player)) }}</strong>
                  </li>
                  <li v-if="podium.length === 0" class="ranking-podium__empty">Sin datos</li>
                </ul>
              </div>
            </aside>
          </div>

          <div class="col-12 col-lg-8">
            <div class="card border-0 ranking-table-card shadow-sm h-100">
              <div class="card-body p-0">
                <div v-if="isLoading" class="ranking-loading p-4">
                  <div class="spinner-border text-light" role="status" aria-hidden="true"></div>
                  <p class="mt-2 mb-0">Cargando ranking...</p>
                </div>

                <div v-else-if="categoryError || error" class="alert alert-danger m-3">
                  {{ categoryError || error }}
                </div>

                <div v-else-if="rows.length === 0" class="ranking-empty m-3">
                  No hay resultados para esta categoría.
                </div>

                <template v-else>
                  <div class="table-responsive d-none d-md-block">
                    <table class="table table-borderless align-middle mb-0 ranking-table">
                      <colgroup>
                        <col style="width: 10%" />
                        <col style="width: 38%" />
                        <col style="width: 20%" />
                        <col style="width: 16%" />
                        <col style="width: 16%" />
                      </colgroup>
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Usuario</th>
                          <th>Puntuación</th>
                          <th>Partidas</th>
                          <th>Media</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(item, index) in rows"
                          :key="`row-${selectedCategoriaId}-${index}-${getUsuario(item)}`"
                          :class="getRowClass(index)"
                        >
                          <td class="text-center fw-bold">{{ index + 1 }}</td>
                          <td class="fw-semibold">{{ getUsuario(item) }}</td>
                          <td class="text-center fw-bold">{{ formatNumber(getPuntuacion(item)) }}</td>
                          <td class="text-center">{{ formatNumber(getPartidas(item)) }}</td>
                          <td class="text-center">{{ getAverage(item) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div class="d-md-none ranking-mobile-list p-3">
                    <article
                      v-for="(item, index) in rows"
                      :key="`mobile-${selectedCategoriaId}-${index}-${getUsuario(item)}`"
                      class="ranking-mobile-card"
                      :class="getRowClass(index)"
                    >
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                          <span class="ranking-mobile-card__position">#{{ index + 1 }}</span>
                          <strong>{{ getUsuario(item) }}</strong>
                        </div>
                        <span class="ranking-mobile-card__points">{{ formatNumber(getPuntuacion(item)) }}</span>
                      </div>
                      <div class="d-flex justify-content-between mt-2">
                        <small class="ranking-mobile-card__meta">Partidas: {{ formatNumber(getPartidas(item)) }}</small>
                        <small class="ranking-mobile-card__meta">Media: {{ getAverage(item) }}</small>
                      </div>
                    </article>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, unref, watch } from 'vue'
import axios from 'axios'
import useCategorias from '@/composables/categorias'
import { useRanking } from '@/composables/useRanking'

const {
  categoryRows: ranking,
  loadingCategory: isLoading,
  errorCategory: error,
  fetchCategoryRanking: getRanking
} = useRanking()

const categoriasModule = useCategorias()
const categorias = categoriasModule?.categorias
const getCategorias = categoriasModule?.getCategorias
const isLoadingCategorias = categoriasModule?.isLoading

const categoriasFallback = ref([])
const loadingCategoriasFallback = ref(false)
const categoryError = ref(null)
const selectedCategoriaId = ref('')
const topLimit = ref(10)
const initialized = ref(false)

const normalizeCategoria = (categoria = {}) => ({
  ...categoria,
  id: categoria?.id ?? categoria?.categoria_id ?? categoria?.idCategoria ?? categoria?.slug ?? '',
  nombre: categoria?.nombre ?? categoria?.name ?? categoria?.titulo ?? categoria?.title ?? 'Sin nombre'
})

const normalizeCategorias = (list = []) =>
  list
    .map(normalizeCategoria)
    .filter((c) => String(c.id ?? '').trim() !== '')

const categoriasLoading = computed(() =>
  Boolean(unref(isLoadingCategorias)) || loadingCategoriasFallback.value
)

const categoryOptions = computed(() => {
  const source = categorias ? unref(categorias) : categoriasFallback.value
  const raw = Array.isArray(source) ? source : (Array.isArray(source?.data) ? source.data : [])
  return normalizeCategorias(raw)
})

const rows = computed(() => {
  const source = Array.isArray(ranking.value) ? ranking.value : []
  return source.slice(0, topLimit.value)
})

const selectedCategory = computed(() =>
  categoryOptions.value.find((c) => String(getCategoriaId(c)) === String(selectedCategoriaId.value)) ?? null
)

const podium = computed(() => rows.value.slice(0, 3))
const totalPlayers = computed(() => rows.value.length)
const totalPoints = computed(() => rows.value.reduce((acc, row) => acc + getPuntuacion(row), 0))
const totalGames = computed(() => rows.value.reduce((acc, row) => acc + getPartidas(row), 0))

const getCategoriaId = (categoria) =>
  categoria?.id ?? categoria?.categoria_id ?? categoria?.idCategoria ?? categoria?.slug ?? ''

const getCategoriaNombre = (categoria) =>
  categoria?.nombre ?? categoria?.name ?? 'Sin nombre'

const getUsuario = (row) =>
  row?.usuario ?? row?.user?.name ?? row?.nombre ?? row?.name ?? 'Sin nombre'

const getPuntuacion = (row) =>
  Number(row?.puntuacion_total ?? row?.puntuacion ?? row?.points ?? row?.score ?? row?.elo ?? 0)

const getPartidas = (row) =>
  Number(row?.partidas_jugadas ?? row?.partidas ?? row?.matches ?? row?.games ?? 0)

const formatNumber = (value) => new Intl.NumberFormat('es-ES').format(Number(value) || 0)

const getAverage = (row) => {
  const partidas = getPartidas(row)
  if (!partidas) return '0'
  return (getPuntuacion(row) / partidas).toFixed(1)
}

const getRowClass = (index) => ({
  'ranking-row--top-1': index === 0,
  'ranking-row--top-2': index === 1,
  'ranking-row--top-3': index === 2
})

const setTopLimit = (limit) => {
  topLimit.value = limit
}

const loadCategorias = async () => {
  categoryError.value = null
  loadingCategoriasFallback.value = true

  try {
    let loaded = false

    // 1) Intentar composable existente
    if (typeof getCategorias === 'function') {
      try {
        await getCategorias()
        loaded = categoryOptions.value.length > 0
      } catch (_) {
        loaded = false
      }
    }

    // 2) Fallback público si falla el privado o no devuelve datos
    if (!loaded) {
      const { data } = await axios.get('/api/public/categories')
      const raw = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : [])
      categoriasFallback.value = normalizeCategorias(raw)
    }

    if (!selectedCategoriaId.value && categoryOptions.value.length > 0) {
      selectedCategoriaId.value = String(getCategoriaId(categoryOptions.value[0]))
    }
  } catch (err) {
    categoryError.value = err?.response?.data?.message ?? 'No se pudieron cargar las categorías.'
  } finally {
    loadingCategoriasFallback.value = false
  }
}

const loadRanking = async () => {
  if (!selectedCategoriaId.value) return
  await getRanking(selectedCategoriaId.value, { limit: topLimit.value, force: true })
}

watch([selectedCategoriaId, topLimit], async () => {
  if (!initialized.value) return
  await loadRanking()
})

onMounted(async () => {
  await loadCategorias()
  initialized.value = true
  await loadRanking()
})
</script>

<style scoped>
.ranking-category {
  --rk-surface: #7382ab;
  --rk-surface-dark: #5f6d96;
  --rk-accent: #ff744f;
  --rk-text: #f3f6ff;
  --rk-soft: #d7def5;
}

.ranking-category__container {
  max-width: 1320px;
  margin-inline: auto;
  display: flex;
  justify-content: center;
}

.ranking-category__inner {
  width: 100%;
  max-width: 1200px;
  margin-inline: auto;
}

.ranking-category__title {
  color: var(--rk-text);
  font-weight: 800;
  letter-spacing: .04em;
}

.ranking-category__subtitle {
  color: var(--rk-soft);
}

.ranking-category__toolbar,
.ranking-summary,
.ranking-table-card {
  background: var(--rk-surface);
  border-radius: 18px;
  margin-inline: auto;
}

.ranking-control__label {
  color: #ecf1ff;
  font-weight: 700;
  font-size: .85rem;
}

.ranking-chip {
  border: 1px solid rgba(255, 255, 255, .2);
  background: rgba(87, 102, 147, .85);
  color: #ecf1ff;
  font-weight: 700;
  border-radius: 999px;
  padding: .4rem .9rem;
}

.ranking-chip.is-active {
  background: #ecf1ff;
  color: #556799;
}

.ranking-select {
  border: 1px solid rgba(255, 255, 255, .2);
  background: rgba(87, 102, 147, .85);
  color: #fff;
}

.ranking-top-btn {
  border: 1px solid rgba(255, 255, 255, .2);
  background: rgba(87, 102, 147, .85);
  color: #ecf1ff;
  font-weight: 700;
}

.ranking-top-btn.is-active {
  background: #ecf1ff;
  color: #556799;
}

.ranking-summary__label {
  color: #cfd8f7;
  text-transform: uppercase;
  font-size: .72rem;
  letter-spacing: .05em;
}

.ranking-summary__category {
  color: var(--rk-text);
  font-weight: 800;
}

.ranking-summary__mini-title {
  color: #ffb085;
  font-size: .86rem;
  font-weight: 800;
  margin-bottom: .6rem;
}

.ranking-stat {
  background: rgba(89, 103, 150, .9);
  border: 1px solid rgba(255, 255, 255, .12);
  border-radius: 10px;
  padding: .55rem .35rem;
}

.ranking-stat__value {
  color: var(--rk-text);
  font-weight: 800;
  font-size: .95rem;
}

.ranking-stat__label {
  color: #cfd8f7;
  font-size: .7rem;
  text-transform: uppercase;
}

.ranking-podium__item {
  display: grid;
  grid-template-columns: 46px 1fr auto;
  gap: .5rem;
  align-items: center;
  color: var(--rk-text);
  font-size: .9rem;
  padding: .42rem 0;
  border-top: 1px solid rgba(255, 255, 255, .1);
}

.ranking-podium__item:first-child {
  border-top: 0;
}

.ranking-podium__pos {
  color: #ffd56b;
  font-weight: 800;
}

.ranking-podium__empty {
  color: #d7def5;
  font-size: .9rem;
}

.ranking-loading {
  min-height: 260px;
  display: grid;
  place-items: center;
  color: #eaf0ff;
}

.ranking-empty {
  border: 1px dashed rgba(255, 255, 255, .25);
  border-radius: 12px;
  color: #dfe6ff;
  text-align: center;
  padding: 2rem 1rem;
}

.ranking-table {
  width: 100%;
  table-layout: fixed;
      --bs-table-bg: transparent;           
  --bs-table-accent-bg: transparent;
  --bs-table-striped-bg: transparent;
  --bs-table-active-bg: transparent;
  --bs-table-hover-bg: rgba(255, 255, 255, 0.06);
  --bs-table-color: var(--rk-text);
  --bs-table-border-color: rgba(255, 255, 255, 0.11);
}

.ranking-table.table {
  width: 100%;
  table-layout: fixed;
  margin-bottom: 0;

  --bs-table-bg: transparent;           
  --bs-table-accent-bg: transparent;
  --bs-table-striped-bg: transparent;
  --bs-table-active-bg: transparent;
  --bs-table-hover-bg: rgba(255, 255, 255, 0.06);
  --bs-table-color: var(--rk-text);
  --bs-table-border-color: rgba(255, 255, 255, 0.11);
}

.ranking-table > :not(caption) > * > * {
  background-color: transparent !important;
  box-shadow: none !important;
}

.ranking-table tbody td {
  background: rgba(95, 109, 150, 0.35) !important;
}

.ranking-row--top-1 td { background: rgba(255, 255, 255, 0.10) !important; }
.ranking-row--top-2 td { background: rgba(255, 255, 255, 0.07) !important; }
.ranking-row--top-3 td { background: rgba(255, 255, 255, 0.05) !important; }

.ranking-mobile-list {
  display: grid;
  gap: .65rem;
}

.ranking-mobile-card {
  border-radius: 12px;
  padding: .75rem .85rem;
  background: rgba(88, 102, 149, .8);
  border: 1px solid rgba(255, 255, 255, .12);
  color: var(--rk-text);
}

.ranking-mobile-card__position {
  color: #ffd56b;
  font-weight: 800;
}

.ranking-mobile-card__points {
  color: #ffb58b;
  font-weight: 800;
}

.ranking-mobile-card__meta {
  color: #d0dafc;
  font-size: .75rem;
}

@media (max-width: 768px) {
  .ranking-category__header {
    text-align: center !important;
  }
}

@media (max-width: 576px) {
  .ranking-category__toolbar .card-body,
  .ranking-summary .card-body {
    padding: 1rem !important;
  }
}
</style>