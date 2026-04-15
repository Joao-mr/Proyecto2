import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useRespuestas() {
  const respuestas = ref([])
  const isLoading = ref(false)

  const initialRespuesta = {
    id: null,
    id_imagen: null,
    respuesta: '',
    es_correcta: false,
    tiempo: 0
  }

  const respuesta = ref({ ...initialRespuesta })
  const toast = useToast()

  const { errors, validate, handleRequestError, clearErrors, hasError, getError } = useValidation()

  const respuestaSchema = yup.object({
    id_imagen: yup
      .number()
      .typeError('La imagen es obligatoria')
      .required('La imagen es obligatoria'),
    respuesta: yup.string().required('La respuesta es obligatoria').max(255),
    es_correcta: yup.boolean().required('Indica si es correcta'),
    tiempo: yup.number().typeError('El tiempo es obligatorio').required('El tiempo es obligatorio').min(0, 'El tiempo no puede ser negativo')
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

  const resetRespuesta = () => {
    respuesta.value = { ...initialRespuesta }
    clearErrors()
  }

  const setRespuesta = (data = {}) => {
    respuesta.value = {
      id: data.id ?? null,
      id_imagen: data.id_imagen ?? null,
      respuesta: data.respuesta ?? '',
      es_correcta: data.es_correcta ?? false,
      tiempo: data.tiempo ?? 0
    }
    clearErrors()
  }

  const getRespuestas = async (params = {}) => {
    const query = new URLSearchParams({ page: 1, ...params }).toString()
    const response = await axios.get(`/api/respuestas?${query}`)
    respuestas.value = response.data?.data ?? []
    return response
  }

  const createRespuesta = async () => {
    const { isValid } = await validate(respuestaSchema, respuesta.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/respuestas', {
          id_imagen: respuesta.value.id_imagen,
          respuesta: respuesta.value.respuesta,
          es_correcta: respuesta.value.es_correcta,
          tiempo: respuesta.value.tiempo
        })
      )
      const data = response.data?.data ?? response.data
      toast.crud.created('Respuesta')
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo crear la respuesta' })
    }
  }

  const updateRespuesta = async () => {
    const { isValid } = await validate(respuestaSchema, respuesta.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.put(`/api/respuestas/${respuesta.value.id}`, {
          id_imagen: respuesta.value.id_imagen,
          respuesta: respuesta.value.respuesta,
          es_correcta: respuesta.value.es_correcta,
          tiempo: respuesta.value.tiempo
        })
      )
      const data = response.data?.data ?? response.data
      toast.crud.updated('Respuesta')
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo actualizar la respuesta' })
    }
  }

  const deleteRespuesta = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/respuestas/${id}`))
      respuestas.value = respuestas.value.filter((item) => item.id !== id)
      toast.crud.deleted('Respuesta')
      return response
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo eliminar la respuesta' })
    }
  }

  const getRespuesta = async (id) => {
    const response = await axios.get(`/api/respuestas/${id}`)
    const data = response.data?.data ?? response.data
    setRespuesta(data)
    return response
  }

  return {
    respuestas,
    respuesta,
    isLoading,
    errors,
    hasError,
    getError,
    getRespuestas,
    createRespuesta,
    updateRespuesta,
    deleteRespuesta,
    setRespuesta,
    resetRespuesta,
    getRespuesta
  }
}
