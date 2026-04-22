import { computed, reactive, ref } from 'vue'

const API_BASE = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/$/, '')
const DEFAULT_ENDPOINT = `${API_BASE}/public/rankings`
const VALID_MODES = ['individual', 'multijugador']

const toNumber = (value) => {
  const n = Number(value)
  return Number.isFinite(n) ? n : 0
}

const inferTitle = (elo) => {
  if (elo >= 13000) return 'RADIANT'
  if (elo >= 11000) return 'MASTER'
  if (elo >= 10000) return 'UNREAL'
  if (elo >= 9000) return 'CHALLENGER'
  return 'CHAMPION'
}

const normalizeRow = (item = {}) => {
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

export function useRanking({ endpoint = DEFAULT_ENDPOINT } = {}) {
  const mode = ref('individual')
  const loading = ref(false)
  const error = ref('')
  const rowsByMode = reactive({
    individual: [],
    multijugador: []
  })

  const currentRows = computed(() => rowsByMode[mode.value] ?? [])

  const formatElo = (value) => toNumber(value).toLocaleString('es-ES')

  const getRankClass = (index) => {
    if (index === 0) return 'ranking-row--gold'
    if (index === 1) return 'ranking-row--silver'
    if (index === 2) return 'ranking-row--bronze'
    return ''
  }

  const fetchRanking = async (targetMode = mode.value, { limit = 10, force = false } = {}) => {
    const safeMode = VALID_MODES.includes(targetMode) ? targetMode : 'individual'

    if (!force && rowsByMode[safeMode].length > 0) return

    loading.value = true
    error.value = ''

    try {
      const url = new URL(endpoint, window.location.origin)
      url.searchParams.set('mode', safeMode)
      url.searchParams.set('limit', String(limit))

      const res = await fetch(url.toString(), {
        headers: { Accept: 'application/json' }
      })

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}`)
      }

      const payload = await res.json()
      const raw = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : [])

      rowsByMode[safeMode] = raw.map(normalizeRow)
    } catch (e) {
      rowsByMode[safeMode] = []
      error.value = e instanceof Error ? e.message : 'Error al cargar ranking.'
    } finally {
      loading.value = false
    }
  }

  const setMode = async (nextMode) => {
    if (!VALID_MODES.includes(nextMode)) return
    mode.value = nextMode
    await fetchRanking(nextMode)
  }

  return {
    mode,
    loading,
    error,
    rowsByMode,
    currentRows,
    fetchRanking,
    setMode,
    formatElo,
    getRankClass
  }
}