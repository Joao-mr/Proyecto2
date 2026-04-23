import { ref } from 'vue'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function usePartidaImagenes() {
  const partidaImagenes = ref([])
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

  const getPartidaImagenes = async (params = {}) => {
    const query = new URLSearchParams({ page: 1, ...params }).toString()
    const response = await axios.get(`/api/partida-imagenes?${query}`)
    partidaImagenes.value = response.data?.data ?? []
    return response
  }

  const getPartidaImagenesByPartida = async (idPartida) => {
    const response = await axios.get(`/api/partida-imagenes/${idPartida}`)
    partidaImagenes.value = response.data ?? []
    return response
  }

  const createPartidaImagen = async (data) => {
    try {
      const response = await withLoading(() =>
        axios.post('/api/partida-imagenes', {
          id_partida: data.id_partida,
          id_imagen: data.id_imagen,
          ronda: data.ronda
        })
      )
      toast.crud.created('Imagen de partida')
      return response.data?.data ?? response.data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo asignar la imagen a la partida' })
    }
  }

  const deletePartidaImagen = async (idPartida, idImagen) => {
    try {
      const response = await withLoading(() =>
        axios.delete(`/api/partida-imagenes/${idPartida}/${idImagen}`)
      )
      toast.crud.deleted('Imagen de partida')
      return response
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo eliminar la imagen de la partida' })
    }
  }

  return {
    partidaImagenes,
    isLoading,
    errors,
    hasError,
    getError,
    getPartidaImagenes,
    getPartidaImagenesByPartida,
    createPartidaImagen,
    deletePartidaImagen
  }
}
