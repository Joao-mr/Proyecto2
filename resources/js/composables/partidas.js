import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function usePartidas() {
  const partidas = ref([])
  const salasDisponibles = ref([])
  const isLoading = ref(false)
  const toast = useToast()

  const initialPartida = {
    id: null,
    id_sala: null,
    fecha_inicio: '',
    fecha_fin: ''
  }

  const partida = ref({ ...initialPartida })

  const {
    validate,
    clearErrors,
    hasError,
    getError
  } = useValidation()

  const partidaSchema = yup.object({
    id_sala: yup
      .number()
      .typeError('La sala es obligatoria')
      .required('La sala es obligatoria'),
    fecha_inicio: yup
      .string()
      .nullable(),
    fecha_fin: yup
      .string()
      .nullable()
      .test('after-start', 'La fecha fin debe ser mayor o igual a la fecha inicio', function (value) {
        const { fecha_inicio } = this.parent
        if (!value || !fecha_inicio) return true
        return new Date(value) >= new Date(fecha_inicio)
      })
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

  const toInputDateTime = (dateValue) => {
    if (!dateValue) return ''
    const date = new Date(dateValue)
    if (Number.isNaN(date.getTime())) return ''
    const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000)
    return localDate.toISOString().slice(0, 16)
  }

  const normalizePayload = () => ({
    id_sala: Number(partida.value.id_sala),
    fecha_inicio: partida.value.fecha_inicio || null,
    fecha_fin: partida.value.fecha_fin || null
  })

  const resetPartida = () => {
    partida.value = { ...initialPartida }
    clearErrors()
  }

  const setPartida = (data = {}) => {
    partida.value = {
      id: data.id ?? null,
      id_sala: data.id_sala ?? null,
      fecha_inicio: toInputDateTime(data.fecha_inicio),
      fecha_fin: toInputDateTime(data.fecha_fin)
    }
    clearErrors()
  }

  const upsertPartidaRecord = (partidaRecord) => {
    if (!partidaRecord?.id) return
    partidas.value = [
      partidaRecord,
      ...partidas.value.filter(item => item.id !== partidaRecord.id)
    ]
  }

  const getPartidas = async (params = {}) => {
    const defaultParams = { page: 1 }
    const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
    const response = await axios.get(`/api/partidas?${query}`)
    partidas.value = response.data?.data ?? []
    return response
  }

  const getSalasDisponibles = async () => {
    try {
      const response = await axios.get('/api/salas?page=1')
      salasDisponibles.value = response.data?.data ?? []
      return response
    } catch {
      toast.error('Error', 'No se pudo obtener la lista de salas')
    }
  }

  const createPartida = async () => {
    const { isValid } = await validate(partidaSchema, partida.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/partidas', normalizePayload())
      )
      const data = response.data
      toast.crud.created('Partida')
      return data
    } catch (error) {
      if (error?.response?.status === 422) {
        toast.error('Error de validación', 'Revisa los datos de la partida.')
      } else {
        toast.error('Error', 'No se pudo crear la partida')
      }
    }
  }

  const updatePartida = async () => {
    const { isValid } = await validate(partidaSchema, partida.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.put(`/api/partidas/${partida.value.id}`, normalizePayload())
      )
      const data = response.data
      toast.crud.updated('Partida')
      return data
    } catch (error) {
      if (error?.response?.status === 422) {
        toast.error('Error de validación', 'Revisa los datos de la partida.')
      } else {
        toast.error('Error', 'No se pudo actualizar la partida')
      }
    }
  }

  const deletePartida = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/partidas/${id}`))
      partidas.value = partidas.value.filter(item => item.id !== id)
      toast.crud.deleted('Partida')
      return response
    } catch {
      toast.error('Error', 'No se pudo eliminar la partida')
    }
  }

  return {
    partidas,
    partida,
    salasDisponibles,
    isLoading,
    hasError,
    getError,
    resetPartida,
    setPartida,
    upsertPartidaRecord,
    getPartidas,
    getSalasDisponibles,
    createPartida,
    updatePartida,
    deletePartida
  }
}
