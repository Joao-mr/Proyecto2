import { computed, ref } from 'vue'

const API_BASE = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/$/, '')
const ENDPOINT = `${API_BASE}/public/categories`
const FALLBACK_IMAGE = '/images/categoria-placeholder.webp'

const normalizeSlug = (value) =>
  String(value ?? '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')

const normalizeCategory = (item = {}) => {
  const name = String(item.name ?? item.nombre ?? '').trim()
  const slug = String(item.slug ?? '').trim()

  return {
    slug: slug || normalizeSlug(name),
    name: (name || 'SIN NOMBRE').toUpperCase(),
    description: String(item.description ?? item.descripcion ?? '').trim(),
    image: String(item.image ?? item.imagen ?? '').trim() || FALLBACK_IMAGE
  }
}

export function useCategories() {
  const categories = ref([])
  const index = ref(0)
  const loading = ref(false)
  const error = ref('')

  const current = computed(() => categories.value[index.value] ?? null)

  const fetchCategories = async () => {
    loading.value = true
    error.value = ''

    try {
      const response = await fetch(ENDPOINT, {
        headers: { Accept: 'application/json' }
      })

      if (!response.ok) {
        throw new Error('No se pudieron cargar las categorías.')
      }

      const payload = await response.json()
      const raw = Array.isArray(payload)
        ? payload
        : (Array.isArray(payload.data) ? payload.data : [])

      categories.value = raw.map(normalizeCategory).filter((c) => c.slug && c.name)

      if (index.value >= categories.value.length) {
        index.value = 0
      }
    } catch (e) {
      categories.value = []
      error.value = e instanceof Error ? e.message : 'Error al cargar categorías.'
    } finally {
      loading.value = false
    }
  }

  const next = () => {
    if (categories.value.length <= 1) return
    index.value = (index.value + 1) % categories.value.length
  }

  const prev = () => {
    if (categories.value.length <= 1) return
    index.value = (index.value - 1 + categories.value.length) % categories.value.length
  }

  return {
    categories,
    current,
    index,
    loading,
    error,
    fetchCategories,
    next,
    prev
  }
}