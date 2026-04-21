import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useUsuarioPartidas() {
  const usuarioPartidas = ref([])
  const isLoading = ref(false)

  const initialUsuarioPartida = {
    id_partida: null,
    puntuacion: 0
  }

  const usuarioPartida = ref({ ...initialUsuarioPartida })
  const toast = useToast()

  const { errors, validate, handleRequestError, clearErrors, hasError, getError } = useValidation()

  const usuarioPartidaSchema = yup.object({
    id_partida: yup
      .number()
      .typeError('La partida es obligatoria')
      .required('La partida es obligatoria'),
    puntuacion: yup
      .number()
      .typeError('La puntuación es obligatoria')
      .required('La puntuación es obligatoria')
      .min(0, 'La puntuación no puede ser negativa')
  })

  const withLoading = async (fn) => {
    if (isLoading.value) throw new Error('Operación en curso')
    isLoading.value = true
    try {
      return await fn()
    } finally {
      isLoading.value = false
    }
  }

  const resetUsuarioPartida = () => {
    usuarioPartida.value = { ...initialUsuarioPartida }
    clearErrors()
  }

  const getUsuarioPartidas = async (params = {}) => {
    const query = new URLSearchParams({ page: 1, ...params }).toString()
    const response = await axios.get(`/api/usuario-partidas?${query}`)
    usuarioPartidas.value = response.data?.data ?? []
    return response
  }

  const getUsuarioPartidasByPartida = async (idPartida) => {
    const response = await axios.get(`/api/usuario-partidas/${idPartida}`)
    usuarioPartidas.value = response.data ?? []
    return response
  }

  const createUsuarioPartida = async () => {
    const { isValid } = await validate(usuarioPartidaSchema, usuarioPartida.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/usuario-partidas', {
          id_partida: usuarioPartida.value.id_partida,
          puntuacion: usuarioPartida.value.puntuacion
        })
      )
      const data = response.data?.data ?? response.data
      toast.crud.created('Participación')
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo registrar la participación' })
    }
  }

  const updateUsuarioPartida = async (idPartida) => {
    try {
      const response = await withLoading(() =>
        axios.put(`/api/usuario-partidas/${idPartida}`, {
          puntuacion: usuarioPartida.value.puntuacion
        })
      )
      const data = response.data?.data ?? response.data
      toast.crud.updated('Participación')
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo actualizar la participación' })
    }
  }

  const deleteUsuarioPartida = async (idPartida) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/usuario-partidas/${idPartida}`))
      toast.crud.deleted('Participación')
      return response
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo eliminar la participación' })
    }
  }

  return {
    usuarioPartidas,
    usuarioPartida,
    isLoading,
    errors,
    hasError,
    getError,
    getUsuarioPartidas,
    getUsuarioPartidasByPartida,
    createUsuarioPartida,
    updateUsuarioPartida,
    deleteUsuarioPartida,
    resetUsuarioPartida
  }
}
