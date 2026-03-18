import { ref } from 'vue'
import axios from 'axios'
import { authStore } from '@/store/auth'
import { useToast } from './useToast'

export default function useImagen() {
  const imagenes = ref([])
  const isLoading = ref(false)
  const uploadProgress = ref(0)
  const toast = useToast()
  const auth = authStore()

  /**
   * Obtener lista de imágenes
   */
  const getImagenes = async () => {
    isLoading.value = true
    try {
      const response = await axios.get('/api/imagenes', {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
      imagenes.value = response.data.data || response.data
      toast.success('Imágenes cargadas correctamente')
    } catch (error) {
      console.error('Error al obtener imágenes:', error)
      toast.error(error.response?.data?.message || 'Error al cargar imágenes')
    } finally {
      isLoading.value = false
    }
  }

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
          'Authorization': `Bearer ${auth.token}`,
          'Content-Type': 'multipart/form-data'
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
   * Crear imagen y subir archivo en una sola petición
   * @param {File} file - El archivo de imagen
   * @param {boolean} respuestaCorrecta - Si es respuesta correcta (opcional)
   * @returns {Promise}
   */
  const uploadImagenNew = async (file, respuestaCorrecta = false) => {
    validateFile(file)

    const formData = new FormData()
    formData.append('image', file)
    formData.append('respuesta_correcta', respuestaCorrecta)

    isLoading.value = true
    uploadProgress.value = 0

    try {
      const response = await axios.post('/api/imagenes/store-with-upload', formData, {
        headers: {
          'Authorization': `Bearer ${auth.token}`,
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      })

      toast.success('Imagen creada y subida correctamente')
      
      // Agregar a la lista
      if (response.data.data?.imagen) {
        imagenes.value.push(response.data.data.imagen)
      }

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

  /**
   * Eliminar imagen
   */
  const deleteImagen = async (id) => {
    if (!confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
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
      toast.success('Imagen eliminada correctamente')
    } catch (error) {
      console.error('Error al eliminar imagen:', error)
      toast.error(error.response?.data?.message || 'Error al eliminar imagen')
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Validar archivo de imagen
   */
  const validateFile = (file) => {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']
    const maxSize = 5 * 1024 * 1024 // 5MB

    if (!file) {
      throw new Error('No se seleccionó archivo')
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
  const getImageUrl = (imagen, type = 'original') => {
    if (!imagen) return ''
    
    // Si tiene media (subida con Spatie)
    if (imagen.media && imagen.media.length > 0) {
      const media = imagen.media[0]
      if (type === 'thumb') return media.getUrl ? media.getUrl('thumb') : media.original_url
      if (type === 'preview') return media.getUrl ? media.getUrl('preview') : media.original_url
      return media.original_url || media.url
    }

    // Si tiene URL directa
    return imagen.url || ''
  }

  /**
   * Obtener información detallada de media de una imagen
   */
  const getMediaInfo = async (imagenId) => {
    isLoading.value = true
    try {
      const response = await axios.get(`/api/imagenes/${imagenId}/media-info`, {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
      
      toast.success('Información de media obtenida')
      return response.data.data
    } catch (error) {
      console.error('Error al obtener info de media:', error)
      toast.error(error.response?.data?.message || 'Error al obtener información')
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Obtener todas las imágenes/media de una imagen
   */
  const getAllMedia = async (imagenId) => {
    isLoading.value = true
    try {
      const response = await axios.get(`/api/imagenes/${imagenId}/all-media`, {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
      
      return response.data.data
    } catch (error) {
      console.error('Error al obtener todas las media:', error)
      toast.error(error.response?.data?.message || 'Error al obtener media')
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Obtener una imagen con todas sus URLs
   */
  const getImagenWithUrls = async (imagenId) => {
    isLoading.value = true
    try {
      const response = await axios.get(`/api/imagenes/${imagenId}`, {
        headers: {
          Authorization: `Bearer ${auth.token}`
        }
      })
      
      return response.data.data || response.data
    } catch (error) {
      console.error('Error al obtener imagen con URLs:', error)
      toast.error(error.response?.data?.message || 'Error al obtener imagen')
      return null
    } finally {
      isLoading.value = false
    }
  }

  return {
    // State
    imagenes,
    isLoading,
    uploadProgress,

    // Methods
    getImagenes,
    uploadImagenNew,
    uploadImageToExisting,
    deleteImagen,
    validateFile,
    getImageUrl,
    getMediaInfo,
    getAllMedia,
    getImagenWithUrls
  }
}
