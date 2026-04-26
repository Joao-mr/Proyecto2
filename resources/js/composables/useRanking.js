import { ref } from 'vue'
import axios from 'axios'

export default function useRanking() {
  const ranking = ref([])
  const isLoading = ref(false)
  const error = ref(null)
  const pagination = ref({
    page: 1,
    perPage: 10,
    total: 0,
    lastPage: 1
  })

  const normalizeRows = (payload) => {
    if (Array.isArray(payload)) return payload
    if (Array.isArray(payload?.data)) return payload.data
    return []
  }

  const getRanking = async (categoriaId, options = {}) => {
    const { page = 1, limit = 10 } = options

    isLoading.value = true
    error.value = null

    try {
      const { data } = await axios.get('/api/rankings', {
        params: {
          categoria_id: categoriaId,
          page,
          per_page: limit,
          limit
        }
      })

      const rows = normalizeRows(data)
      ranking.value = rows

      pagination.value = {
        page: Number(data?.current_page ?? data?.meta?.current_page ?? page),
        perPage: Number(data?.per_page ?? data?.meta?.per_page ?? limit),
        total: Number(data?.total ?? data?.meta?.total ?? rows.length),
        lastPage: Number(data?.last_page ?? data?.meta?.last_page ?? 1)
      }

      return rows
    } catch (err) {
      ranking.value = []
      error.value = err?.response?.data?.message ?? 'No se pudo cargar el ranking.'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    ranking,
    isLoading,
    error,
    pagination,
    getRanking
  }
}