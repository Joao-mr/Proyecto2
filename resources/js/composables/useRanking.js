import { computed, reactive, ref } from 'vue'

const API_BASE = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/$/, '')
const DEFAULT_GLOBAL_ENDPOINT = `${API_BASE}/public/rankings`
const DEFAULT_CATEGORY_ENDPOINT = `${API_BASE}/public/rankings/category`
const VALID_MODES = ['individual', 'multijugador']

const toNumber = (value) => {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

const toArray = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const buildUrl = (endpoint, params = {}) => {
  const url = new URL(endpoint, window.location.origin)
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && String(value) !== '') {
      url.searchParams.set(key, String(value))
    }
  })
  return url.toString()
}

const inferTitle = (elo) => {
  if (elo >= 13000) return 'RADIANT'
  if (elo >= 11000) return 'MASTER'
  if (elo >= 10000) return 'UNREAL'
  if (elo >= 9000) return 'CHALLENGER'
  return 'CHAMPION'
}

const normalizeGlobalRow = (item = {}) => {
  const elo = toNumber(item.elo ?? item.rating ?? item.puntos ?? item.score)
  const matches = toNumber(item.matches ?? item.partidas ?? item.partidas_jugadas ?? item.games)

  const name = String(item.name ?? item.username ?? item.usuario ?? item.player ?? item.jugador ?? 'SIN NOMBRE')
    .trim()
    .toUpperCase()

  const title = String(item.title ?? item.titulo ?? item.rank ?? item.rango ?? inferTitle(elo))
    .trim()
    .toUpperCase()

  return {
    name: name || 'SIN NOMBRE',
    elo,
    matches,
    title: title || inferTitle(elo)
  }
}

const normalizeCategoryRow = (item = {}) => {
  return {
    user_id: item.user_id ?? item.userId ?? null,
    usuario: String(item.usuario ?? item.user_name ?? item.name ?? item.nombre ?? 'Sin nombre').trim(),
    puntuacion_total: toNumber(item.puntuacion_total ?? item.puntuacion ?? item.points ?? item.score ?? item.elo),
    partidas_jugadas: toNumber(item.partidas_jugadas ?? item.partidas ?? item.matches ?? item.games)
  }
}

export function useRanking({
  globalEndpoint = DEFAULT_GLOBAL_ENDPOINT,
  categoryEndpoint = DEFAULT_CATEGORY_ENDPOINT
} = {}) {
  // GLOBAL
  const mode = ref('individual')
  const rowsByMode = reactive({
    individual: [],
    multijugador: []
  })
  const currentRows = computed(() => rowsByMode[mode.value] ?? [])
  const loading = ref(false)
  const errorGlobal = ref('')

  // CATEGORY
  const categoryRows = ref([])
  const loadingCategory = ref(false)
  const errorCategory = ref('')
  const categoryCache = reactive({})

  // Helpers UI
  const formatElo = (value) => toNumber(value).toLocaleString('es-ES')

  const getRankClass = (index) => {
    if (index === 0) return 'ranking-row--gold'
    if (index === 1) return 'ranking-row--silver'
    if (index === 2) return 'ranking-row--bronze'
    return ''
  }

  // GLOBAL: fetch
  const fetchRanking = async (targetMode = mode.value, { limit = 10, force = false } = {}) => {
    const safeMode = VALID_MODES.includes(targetMode) ? targetMode : 'individual'
    if (!force && rowsByMode[safeMode].length > 0) return

    loading.value = true
    errorGlobal.value = ''

    try {
      const url = buildUrl(globalEndpoint, { mode: safeMode, limit })
      const res = await fetch(url, { headers: { Accept: 'application/json' } })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const payload = await res.json()
      rowsByMode[safeMode] = toArray(payload).map(normalizeGlobalRow)
    } catch (e) {
      rowsByMode[safeMode] = []
      errorGlobal.value = e instanceof Error ? e.message : 'Error al cargar ranking global.'
    } finally {
      loading.value = false
    }
  }

  const setMode = async (nextMode) => {
    if (!VALID_MODES.includes(nextMode)) return
    mode.value = nextMode
    await fetchRanking(nextMode)
  }

  // CATEGORY: fetch
  const fetchCategoryRanking = async (categoriaId, { limit = 10, force = false } = {}) => {
    const safeCategoryId = String(categoriaId ?? '').trim()
    const safeLimit = Math.max(1, Number(limit) || 10)

    if (!safeCategoryId) {
      categoryRows.value = []
      return
    }

    const cacheKey = `${safeCategoryId}:${safeLimit}`
    if (!force && Array.isArray(categoryCache[cacheKey])) {
      categoryRows.value = categoryCache[cacheKey]
      return
    }

    loadingCategory.value = true
    errorCategory.value = ''

    try {
      const endpoint = `${categoryEndpoint.replace(/\/$/, '')}/${encodeURIComponent(safeCategoryId)}`
      const url = buildUrl(endpoint, { limit: safeLimit })
      const res = await fetch(url, { headers: { Accept: 'application/json' } })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const payload = await res.json()
      const normalized = toArray(payload).map(normalizeCategoryRow)

      categoryCache[cacheKey] = normalized
      categoryRows.value = normalized
    } catch (e) {
      categoryRows.value = []
      errorCategory.value = e instanceof Error ? e.message : 'Error al cargar ranking por categoría.'
    } finally {
      loadingCategory.value = false
    }
  }

  // Compatibilidad con tu componente viejo de categoría
  const ranking = categoryRows
  const isLoading = computed(() => loadingCategory.value)
  const error = computed(() => errorCategory.value || errorGlobal.value)
  const getRanking = fetchCategoryRanking

  return {
    // global
    mode,
    rowsByMode,
    currentRows,
    loading,
    error, // compat para componentes que usan `error`
    errorGlobal,
    fetchRanking,
    setMode,
    formatElo,
    getRankClass,

    // category (nuevo)
    categoryRows,
    loadingCategory,
    errorCategory,
    fetchCategoryRanking,

    // category (legacy)
    ranking,
    isLoading,
    getRanking
  }
}

export default useRanking