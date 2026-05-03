import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { authStore } from '@/store/auth'
import { useToast } from './useToast'
import { useValidation } from './useValidation'
import { createLoadingGuard, unwrapApiData } from './crud-helpers'

export default function useImagen() {
  const imagenes = ref([])
  const imagen = ref({ id: null, url: '', respuesta_correcta: '', categoria_id: null })
  const isLoading = ref(false)
  const uploadProgress = ref(0)
  const toast = useToast()
  const auth = authStore()

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
      .nullable()
      .max(255, 'La URL no puede superar 255 caracteres'),
    respuesta_correcta: yup
      .string()
      .trim()
      .nullable()
      .max(255, 'La respuesta correcta no puede superar 255 caracteres')
  })

  const withLoading = createLoadingGuard(isLoading)

  const buildImagenPayload = (currentImagen) => {
    const payload = {
      categoria_id: currentImagen.categoria_id ?? null
    }

    const url = typeof currentImagen.url === 'string' ? currentImagen.url.trim() : ''
    const respuestaCorrecta = typeof currentImagen.respuesta_correcta === 'string'
      ? currentImagen.respuesta_correcta.trim()
      : ''

    if (url !== '') payload.url = url
    if (respuestaCorrecta !== '') payload.respuesta_correcta = respuestaCorrecta

    return payload
  }

  const validateFile = (file) => {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']
    const maxSize = 5 * 1024 * 1024 // 5MB

    if (!file) {
      throw new Error('No se selecciono archivo')
    }

    if (!allowedTypes.includes(file.type)) {
      throw new Error('El archivo debe ser una imagen (JPEG, PNG, GIF, WebP, SVG)')
    }

    if (file.size > maxSize) {
      throw new Error('La imagen no debe exceder 5MB')
    }

    return true
  }

  const getImageUrl = (imagenData, type = 'original') => {
    if (!imagenData) return ''
    
    // Si tiene URLs de Spatie (respuesta del API)
    if (imagenData.urls) {
      if (type === 'thumb') return imagenData.urls.thumb || imagenData.urls.original || ''
      if (type === 'preview') return imagenData.urls.preview || imagenData.urls.original || ''
      return imagenData.urls.original || ''
    }

    // Si tiene thumb_url/preview_url directos (respuesta de getList)
    if (type === 'thumb' && imagenData.thumb_url) return imagenData.thumb_url
    if (type === 'preview' && imagenData.preview_url) return imagenData.preview_url
    if (imagenData.url) return imagenData.url

    return ''
  }

  const resetImagen = () => {
    imagen.value = { id: null, url: '', respuesta_correcta: '', categoria_id: null }
    clearErrors()
  }

  const setImagen = (data = {}) => {
    imagen.value = {
      id: data.id ?? null,
      url: data.url ?? data.urls?.original ?? '',
      respuesta_correcta: data.respuesta_correcta ?? '',
      categoria_id: data.categoria_id ?? null
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
    isLoading.value = true
    try {
      const cleanParams = params instanceof Event ? {} : params
      const query = new URLSearchParams({ page: 1, per_page: 1000, ...cleanParams }).toString()
      const response = await axios.get(`/api/imagenes?${query}`)
      const responseData = unwrapApiData(response)
      imagenes.value = Array.isArray(responseData) ? responseData : []
      return response
    } catch (error) {
      toast.error(error.response?.data?.message || 'Error al cargar imagenes')
      throw error
    } finally {
      isLoading.value = false
    }
  }

  const createImagen = async () => {
    const { isValid } = await validate(imagenSchema, imagen.value)
    if (!isValid) {
      toast.error('Error de validacion', 'Revisa los campos resaltados.')
      throw new Error('Validacion')
    }

    try {
      const payload = buildImagenPayload(imagen.value)

      const response = await withLoading(() =>
        axios.post('/api/imagenes', payload)
      )
      const data = unwrapApiData(response)
      toast.crud.created('Imagen')
      upsertImagenRecord(data)
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo crear la imagen',
        onValidationError: () =>
          toast.error('Error de validacion', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
      throw error
    }
  }

  const updateImagen = async () => {
    const { isValid } = await validate(imagenSchema, imagen.value)
    if (!isValid) {
      toast.error('Error de validacion', 'Revisa los campos resaltados.')
      throw new Error('Validacion')
    }

    try {
      const payload = buildImagenPayload(imagen.value)

      const response = await withLoading(() =>
        axios.put(`/api/imagenes/${imagen.value.id}`, payload)
      )
      const data = unwrapApiData(response)
      toast.crud.updated('Imagen')
      upsertImagenRecord(data)
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo actualizar la imagen',
        onValidationError: () =>
          toast.error('Error de validacion', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
      throw error
    }
  }

  const deleteImagen = async (id) => {
    isLoading.value = true
    try {
      await axios.delete(`/api/imagenes/${id}`, {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })

      imagenes.value = imagenes.value.filter(img => img.id !== id)
      toast.crud.deleted('Imagen')
      return true
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Error al eliminar imagen'
      toast.error(message)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  const uploadImageToExisting = async (file, imagenId) => {
    validateFile(file)
    
    const formData = new FormData()
    formData.append('image', file)

    isLoading.value = true
    uploadProgress.value = 0

    try {
      const response = await axios.post(`/api/imagenes/${imagenId}/upload`, formData, {
        headers: {
          'Authorization': `Bearer ${auth.token}`
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      })

      toast.success('Imagen subida correctamente')
      return response.data
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Error al subir imagen'
      toast.error(message)
      throw error
    } finally {
      isLoading.value = false
      uploadProgress.value = 0
    }
  }

  const uploadImagenNew = async (file, respuestaCorrecta = '') => {
    validateFile(file)

    const formData = new FormData()
    formData.append('image', file)
    if (respuestaCorrecta) {
      formData.append('respuesta_correcta', respuestaCorrecta)
    }

    isLoading.value = true
    uploadProgress.value = 0

    try {
      const response = await axios.post('/api/imagenes/store-with-upload', formData, {
        headers: {
          'Authorization': `Bearer ${auth.token}`
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      })

      toast.success('Imagen creada y subida correctamente')
      const data = unwrapApiData(response)
      upsertImagenRecord(data?.imagen ?? data)
      return data
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Error al subir imagen'
      toast.error(message)
      throw error
    } finally {
      isLoading.value = false
      uploadProgress.value = 0
    }
  }

  const getMediaInfo = async (imagenId) => {
    isLoading.value = true
    try {
      const response = await axios.get(`/api/imagenes/${imagenId}/media-info`, {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
      
      toast.success('Informacion de media obtenida')
      return unwrapApiData(response)
    } catch (error) {
      toast.error(error.response?.data?.message || 'Error al obtener informacion')
      return null
    } finally {
      isLoading.value = false
    }
  }

  return {
    imagenes,
    imagen,
    isLoading,
    uploadProgress,
    hasError,
    getError,
    resetImagen,
    setImagen,
    upsertImagenRecord,
    getImagenes,
    createImagen,
    updateImagen,
    deleteImagen,
    uploadImageToExisting,
    uploadImagenNew,

    // Utilities
    getImageUrl,
    getMediaInfo,
    validateFile
  }
}
