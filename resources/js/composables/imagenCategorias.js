import { ref } from 'vue'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useImagenCategorias() {
  const imagenCategorias = ref([])
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

  const getImagenCategorias = async (params = {}) => {
    const query = new URLSearchParams({ page: 1, ...params }).toString()
    const response = await axios.get(`/api/imagen-categorias?${query}`)
    imagenCategorias.value = response.data?.data ?? []
    return response
  }

  const getImagenCategoriasByImagen = async (idImagen) => {
    const response = await axios.get(`/api/imagen-categorias/${idImagen}`)
    imagenCategorias.value = response.data ?? []
    return response
  }

  const createImagenCategoria = async (data) => {
    try {
      const response = await withLoading(() =>
        axios.post('/api/imagen-categorias', {
          id_imagen: data.id_imagen,
          id_categoria: data.id_categoria
        })
      )
      toast.crud.created('Categoría de imagen')
      return response.data?.data ?? response.data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo asignar la categoría a la imagen' })
    }
  }

  const deleteImagenCategoria = async (idImagen, idCategoria) => {
    try {
      const response = await withLoading(() =>
        axios.delete(`/api/imagen-categorias/${idImagen}/${idCategoria}`)
      )
      toast.crud.deleted('Categoría de imagen')
      return response
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo eliminar la categoría de la imagen' })
    }
  }

  return {
    imagenCategorias,
    isLoading,
    errors,
    hasError,
    getError,
    getImagenCategorias,
    getImagenCategoriasByImagen,
    createImagenCategoria,
    deleteImagenCategoria
  }
}
