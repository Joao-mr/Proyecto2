import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { authStore } from '@/store/auth'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useSalas() {
  const salas = ref([])
  const categoriasDisponibles = ref([])
  const isLoading = ref(false)
  const toast = useToast()
  const auth = authStore()

  const initialSala = {
    id: null,
    nombre: '',
    codigo: '',
    tiempo_respuesta: 30,
    categorias: []
  }

  const sala = ref({ ...initialSala })

  const {
    validate,
    handleRequestError,
    clearErrors,
    hasError,
    getError
  } = useValidation()

  const salaSchema = yup.object({
    nombre: yup
      .string()
      .trim()
      .required('El nombre es obligatorio')
      .min(2, 'Debe tener al menos 2 caracteres'),
    codigo: yup
      .string()
      .trim()
      .required('El código es obligatorio')
      .min(2, 'Debe tener al menos 2 caracteres'),
    tiempo_respuesta: yup
      .number()
      .typeError('Debe ser un número')
      .required('El tiempo es obligatorio')
      .min(5, 'Mínimo 5 segundos')
      .max(300, 'Máximo 300 segundos'),
    categorias: yup.array().nullable()
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

  const resetSala = () => {
    sala.value = { ...initialSala }
    clearErrors()
  }

  const setSala = (data = {}) => {
    sala.value = {
      id: data.id ?? null,
      nombre: data.nombre ?? '',
      codigo: data.codigo ?? '',
      tiempo_respuesta: data.tiempo_respuesta ?? 30,
      categorias: (data.categorias ?? []).map((categoria) => categoria.id)
    }
    clearErrors()
  }

  const upsertSalaRecord = (salaRecord) => {
    if (!salaRecord?.id) return
    salas.value = [
      salaRecord,
      ...salas.value.filter(item => item.id !== salaRecord.id)
    ]
  }

  const getSalas = async (params = {}) => {
    const defaultParams = { page: 1, per_page: 1000 }
    const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
    const response = await axios.get(`/api/salas?${query}`)
    salas.value = response.data?.data ?? []
    return response
  }

  const getCategoriasDisponibles = async () => {
    try {
      const response = await axios.get('/api/categorias-list')
      categoriasDisponibles.value = response.data ?? []
      return response
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo obtener la lista de categorías',
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const createSala = async () => {
    const { isValid } = await validate(salaSchema, sala.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    const idCreador = auth.user?.id
    if (!idCreador) {
      toast.error('Error', 'No se pudo identificar el usuario creador.')
      throw new Error('Usuario no autenticado')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/salas', {
          nombre: sala.value.nombre,
          codigo: sala.value.codigo,
          id_creador: idCreador,
          tiempo_respuesta: sala.value.tiempo_respuesta,
          categorias: sala.value.categorias
        })
      )
      const data = response.data
      toast.crud.created('Sala')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo crear la sala',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const updateSala = async () => {
    const { isValid } = await validate(salaSchema, sala.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.put(`/api/salas/${sala.value.id}`, {
          nombre: sala.value.nombre,
          codigo: sala.value.codigo,
          tiempo_respuesta: sala.value.tiempo_respuesta,
          categorias: sala.value.categorias
        })
      )
      const data = response.data
      toast.crud.updated('Sala')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo actualizar la sala',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const deleteSala = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/salas/${id}`))
      salas.value = salas.value.filter(item => item.id !== id)
      toast.crud.deleted('Sala')
      return response
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo eliminar la sala',
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  return {
    salas,
    sala,
    categoriasDisponibles,
    isLoading,
    hasError,
    getError,
    resetSala,
    setSala,
    upsertSalaRecord,
    getSalas,
    getCategoriasDisponibles,
    createSala,
    updateSala,
    deleteSala
  }
}