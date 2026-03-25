import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useImagenes() {
  const imagenes = ref([])
  const imagen = ref({ id: null, url: '', respuesta_correcta: '' })
  const isLoading = ref(false)
  const toast = useToast()

  const {
    validate,
    handleRequestError,
    clearErrors,
    hasError,
    getError
  } = useValidation()

  const imagenSchema = yup.object({
    url: yup
      .string()
      .trim()
      .required('La URL es obligatoria')
      .max(255, 'La URL no puede superar 255 caracteres'),
    respuesta_correcta: yup
      .string()
      .trim()
      .required('La respuesta correcta es obligatoria')
      .max(255, 'La respuesta correcta no puede superar 255 caracteres')
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

  const resetImagen = () => {
    imagen.value = { id: null, url: '', respuesta_correcta: '' }
    clearErrors()
  }

  const setImagen = (data = {}) => {
    imagen.value = {
      id: data.id ?? null,
      url: data.url ?? '',
      respuesta_correcta: data.respuesta_correcta ?? ''
    }
    clearErrors()
  }

  const upsertImagenRecord = (imagenRecord) => {
    if (!imagenRecord?.id) return
    imagenes.value = [
      imagenRecord,
      ...imagenes.value.filter(item => item.id !== imagenRecord.id)
    ]
  }

  const getImagenes = async (params = {}) => {
    const defaultParams = { page: 1 }
    const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
    const response = await axios.get(`/api/imagenes?${query}`)
    imagenes.value = response.data?.data ?? []
    return response
  }

  const createImagen = async () => {
    const { isValid } = await validate(imagenSchema, imagen.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/imagenes', {
          url: imagen.value.url,
          respuesta_correcta: imagen.value.respuesta_correcta
        })
      )
      const data = response.data
      toast.crud.created('Imagen')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo crear la imagen',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const updateImagen = async () => {
    const { isValid } = await validate(imagenSchema, imagen.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.put(`/api/imagenes/${imagen.value.id}`, {
          url: imagen.value.url,
          respuesta_correcta: imagen.value.respuesta_correcta
        })
      )
      const data = response.data
      toast.crud.updated('Imagen')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo actualizar la imagen',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const deleteImagen = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/imagenes/${id}`))
      imagenes.value = imagenes.value.filter(item => item.id !== id)
      toast.crud.deleted('Imagen')
      return response
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo eliminar la imagen',
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  return {
    imagenes,
    imagen,
    isLoading,
    hasError,
    getError,
    resetImagen,
    setImagen,
    upsertImagenRecord,
    getImagenes,
    createImagen,
    updateImagen,
    deleteImagen
  }
}
