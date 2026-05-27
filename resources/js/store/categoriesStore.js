import { defineStore } from 'pinia'

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
  const id = item.id ?? item.ID ?? null
  const name = String(item.name ?? item.nombre ?? '').trim()
  const slug = String(item.slug ?? '').trim()

  return {
    id,
    slug: slug || normalizeSlug(name),
    name: (name || 'SIN NOMBRE').toUpperCase(),
    description: String(item.description ?? item.descripcion ?? '').trim(),
    image: String(item.image ?? item.imagen ?? '').trim() || FALLBACK_IMAGE
  }
}

export const useCategoriesStore = defineStore('categories', {
  state: () => ({
    categories: [],
    index: 0,
    loading: false,
    error: '',
    loaded: false,
    _pendingPromise: null // <-- nuevo
  }),

  getters: {
    current: (state) => state.categories[state.index] ?? null,
    hasCategories: (state) => state.categories.length > 0,
    categoryBySlug: (state) => (slug) =>
      state.categories.find((category) => category.slug === String(slug).trim()) ?? null,
    categoryById: (state) => (id) =>
      state.categories.find((category) => String(category.id) === String(id)) ?? null
  },

  actions: {
    async fetchCategories(force = false) {
      if (this.loading) return this._pendingPromise ?? Promise.resolve()
      if (this.loaded && !force) return Promise.resolve(this.categories)

      if (this._pendingPromise) return this._pendingPromise

      this.loading = true
      this.error = ''

      this._pendingPromise = (async () => {
        try {
          const response = await fetch(ENDPOINT, { headers: { Accept: 'application/json' } })
          if (!response.ok) throw new Error('No se pudieron cargar las categorías.')
          const payload = await response.json()
          const raw = Array.isArray(payload) ? payload : (Array.isArray(payload?.data) ? payload.data : [])
          this.categories = raw.map(normalizeCategory).filter((c) => c.slug && c.name)
          if (this.index >= this.categories.length) this.index = 0
          this.loaded = true
          return this.categories
        } catch (e) {
          this.categories = []
          this.loaded = false
          this.error = e instanceof Error ? e.message : 'Error al cargar categorías.'
          throw e
        } finally {
          this.loading = false
          this._pendingPromise = null
        }
      })()

      return this._pendingPromise
    },

    next() {
      if (this.categories.length <= 1) return
      this.index = (this.index + 1) % this.categories.length
    },

    prev() {
      if (this.categories.length <= 1) return
      this.index = (this.index - 1 + this.categories.length) % this.categories.length
    },

    setIndex(value) {
      const nextIndex = Number(value)
      if (!Number.isFinite(nextIndex)) return
      if (nextIndex < 0 || nextIndex >= this.categories.length) return
      this.index = nextIndex
    },

    setActiveBySlug(slug) {
      const safeSlug = String(slug ?? '').trim()
      if (!safeSlug) return

      const nextIndex = this.categories.findIndex((category) => category.slug === safeSlug)
      if (nextIndex !== -1) {
        this.index = nextIndex
      }
    },

    setActiveById(id) {
      const nextIndex = this.categories.findIndex((category) => String(category.id) === String(id))
      if (nextIndex !== -1) {
        this.index = nextIndex
      }
    },

    clearActiveCategory() {
      this.index = 0
    }
  }
})