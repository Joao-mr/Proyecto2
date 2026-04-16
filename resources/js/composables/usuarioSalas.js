import { ref } from 'vue'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useUsuarioSalas() {
  const usuarioSalas = ref([])
  const isLoading = ref(false)

  const toast = useToast()
  const { errors, handleRequestError, clearErrors, hasError, getError } = useValidation()

  const withLoading = async (fn) => {
    if (isLoading.value) throw new Error('Operación en curso')
    isLoading.value = true
    try {
      return await fn()
    } finally {
      isLoading.value = false
    }
  }

  const getUsuarioSalas = async (params = {}) => {
    const query = new URLSearchParams({ page: 1, ...params }).toString()
    const response = await axios.get(`/api/usuario-salas?${query}`)
    usuarioSalas.value = response.data?.data ?? []
    return response
  }

  const getUsuarioSalasBySala = async (idSala) => {
    const response = await axios.get(`/api/usuario-salas/${idSala}`)
    usuarioSalas.value = response.data ?? []
    return response
  }

  const joinSala = async (idSala) => {
    try {
      const response = await withLoading(() =>
        axios.post('/api/usuario-salas', { id_sala: idSala })
      )
      toast.crud.created('Entrada a sala')
      return response.data?.data ?? response.data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo unir a la sala' })
    }
  }

  const leaveSala = async (idSala) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/usuario-salas/${idSala}`))
      toast.crud.deleted('Entrada a sala')
      return response
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo salir de la sala' })
    }
  }

  return {
    usuarioSalas,
    isLoading,
    errors,
    hasError,
    getError,
    getUsuarioSalas,
    getUsuarioSalasBySala,
    joinSala,
    leaveSala
  }
}
