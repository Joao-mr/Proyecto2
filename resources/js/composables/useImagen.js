import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { authStore } from '@/store/auth'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useImagen() {
  // ============================================
  // STATE
  // ============================================
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

  // ============================================
  // SCHEMA DE VALIDACION
  // ============================================
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

  // ============================================
  // UTILIDADES
  // ============================================
  const withLoading = async (fn) => {
    if (isLoading.value) throw new Error('Operacion en curso')
    isLoading.value = true
    try {
      return await fn()
    } finally {
      isLoading.value = false
    }
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

  /**
   * Obtener URL de preview de la imagen
   */
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

  // ============================================
  // FUNCIONES DE ESTADO (CRUD basico)
  // ============================================
  const resetImagen = () => {
    imagen.value = { id: null, url: '', respuesta_correcta: '', categoria_id: null }
    clearErrors()
  }

  const setImagen = (data = {}) => {
    imagen.value = {
      id: data.id ?? null,
      // En listados de admin la URL viene en data.urls.original
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

  // ============================================
  // FUNCIONES DE API - CRUD
  // ============================================
  
  /**
   * Obtener lista de imagenes
   */
  const getImagenes = async (params = {}) => {
    isLoading.value = true
    try {
      const cleanParams = params instanceof Event ? {} : params
      const query = new URLSearchParams({ page: 1, per_page: 1000, ...cleanParams }).toString()
      const response = await axios.get(`/api/imagenes?${query}`)
      imagenes.value = response.data?.data ?? response.data ?? []
      return response
    } catch (error) {
      console.error('Error al obtener imagenes:', error)
      toast.error(error.response?.data?.message || 'Error al cargar imagenes')
      throw error
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Crear imagen (CRUD basico)
   */
  const createImagen = async () => {
    const { isValid } = await validate(imagenSchema, imagen.value)
    if (!isValid) {
      toast.error('Error de validacion', 'Revisa los campos resaltados.')
      throw new Error('Validacion')
    }

    try {
      const payload = {
        categoria_id: imagen.value.categoria_id ?? null
      }

      // Evitamos enviar strings vacios porque Laravel los convierte a null.
      if (typeof imagen.value.url === 'string' && imagen.value.url.trim() !== '') {
        payload.url = imagen.value.url.trim()
      }

      if (typeof imagen.value.respuesta_correcta === 'string' && imagen.value.respuesta_correcta.trim() !== '') {
        payload.respuesta_correcta = imagen.value.respuesta_correcta.trim()
      }

      const response = await withLoading(() =>
        axios.post('/api/imagenes', payload)
      )
      const data = response.data
      toast.crud.created('Imagen')
      upsertImagenRecord(data.data || data)
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

  /**
   * Actualizar imagen (CRUD basico)
   */
  const updateImagen = async () => {
    const { isValid } = await validate(imagenSchema, imagen.value)
    if (!isValid) {
      toast.error('Error de validacion', 'Revisa los campos resaltados.')
      throw new Error('Validacion')
    }

    try {
      const payload = {
        categoria_id: imagen.value.categoria_id ?? null
      }

      // Evitamos enviar strings vacios porque Laravel los convierte a null.
      if (typeof imagen.value.url === 'string' && imagen.value.url.trim() !== '') {
        payload.url = imagen.value.url.trim()
      }

      if (typeof imagen.value.respuesta_correcta === 'string' && imagen.value.respuesta_correcta.trim() !== '') {
        payload.respuesta_correcta = imagen.value.respuesta_correcta.trim()
      }

      const response = await withLoading(() =>
        axios.put(`/api/imagenes/${imagen.value.id}`, payload)
      )
      const data = response.data
      toast.crud.updated('Imagen')
      upsertImagenRecord(data.data || data)
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

  /**
   * Eliminar imagen
   */
  const deleteImagen = async (id) => {
    if (!confirm('¿Estas seguro de que deseas eliminar esta imagen?')) {
      return
    }

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
      console.error('Error al eliminar imagen:', error)
      const message = error.response?.data?.message || error.message || 'Error al eliminar imagen'
      toast.error(message)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  // ============================================
  // FUNCIONES DE UPLOAD
  // ============================================

  /**
   * Subir imagen a un modelo existente
   * @param {File} file - El archivo de imagen
   * @param {number} imagenId - ID de la imagen existente
   * @returns {Promise}
   */
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
      console.error('Error al subir imagen:', error)
      const message = error.response?.data?.message || error.message || 'Error al subir imagen'
      toast.error(message)
      throw error
    } finally {
      isLoading.value = false
      uploadProgress.value = 0
    }
  }

  /**
   * Crear imagen y subir archivo en una sola peticion
   * @param {File} file - El archivo de imagen
   * @param {string} respuestaCorrecta - Texto de la respuesta correcta (opcional)
   * @returns {Promise}
   */
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
      const data = response.data?.data ?? response.data
      imagenes.value = [data, ...imagenes.value]
      return response.data
    } catch (error) {
      console.error('Error al crear y subir imagen:', error)
      const message = error.response?.data?.message || error.message || 'Error al subir imagen'
      toast.error(message)
      throw error
    } finally {
      isLoading.value = false
      uploadProgress.value = 0
    }
  }

  // ============================================
  // FUNCIONES DE INFORMACION
  // ============================================

  /**
   * Obtener informacion detallada de media de una imagen
   */
  const getMediaInfo = async (imagenId) => {
    isLoading.value = true
    try {
      const response = await axios.get(`/api/imagenes/${imagenId}/media-info`, {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
      
      toast.success('Informacion de media obtenida')
      return response.data.data
    } catch (error) {
      console.error('Error al obtener info de media:', error)
      toast.error(error.response?.data?.message || 'Error al obtener informacion')
      return null
    } finally {
      isLoading.value = false
    }
  }

  // ============================================
  // EXPORT
  // ============================================
  return {
    // State
    imagenes,
    imagen,
    isLoading,
    uploadProgress,

    // Validation
    hasError,
    getError,

    // CRUD utilities
    resetImagen,
    setImagen,
    upsertImagenRecord,

    // CRUD API
    getImagenes,
    createImagen,
    updateImagen,
    deleteImagen,

    // Upload API
    uploadImageToExisting,
    uploadImagenNew,

    // Utilities
    getImageUrl,
    getMediaInfo,
    validateFile
  }
}
