import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function usePartidas() {
  const partidas = ref([])
  const salasDisponibles = ref([])
  const isLoading = ref(false)

  const initialPartida = {
    id: null,
    id_sala: null,
    fecha_inicio: '',
    fecha_fin: ''
  }

  const partida = ref({ ...initialPartida })
  const toast = useToast()

  const { errors, validate, handleRequestError, clearErrors, hasError, getError } = useValidation()

  const partidaSchema = yup.object({
    id_sala: yup.number().typeError('La sala es obligatoria').required('La sala es obligatoria'),
    fecha_inicio: yup.string().nullable(),
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

  const resetPartida = () => {
    partida.value = { ...initialPartida }
    clearErrors()
  }

  const setPartida = (data = {}) => {
    partida.value = {
      id: data.id ?? null,
      id_sala: data.id_sala ?? null,
      fecha_inicio: data.fecha_inicio ? toDateTimeLocal(data.fecha_inicio) : '',
      fecha_fin: data.fecha_fin ? toDateTimeLocal(data.fecha_fin) : ''
    }
    clearErrors()
  }

  const upsertPartidaRecord = (record) => {
    if (!record?.id) return
    partidas.value = [record, ...partidas.value.filter((item) => item.id !== record.id)]
  }

  const getPartidas = async (params = {}) => {
    const query = new URLSearchParams({ page: 1, per_page: 1000, ...params }).toString()
    const response = await axios.get(`/api/partidas?${query}`)
    partidas.value = response.data?.data ?? []
    return response
  }

  const getSalasDisponibles = async () => {
    const response = await axios.get('/api/salas?page=1')
    salasDisponibles.value = response.data?.data ?? []
    return response
  }

  const createPartida = async () => {
    const { isValid } = await validate(partidaSchema, partida.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/partidas', {
          id_sala: partida.value.id_sala,
          fecha_inicio: partida.value.fecha_inicio || null,
          fecha_fin: partida.value.fecha_fin || null
        })
      )
      const data = response.data?.data ?? response.data
      toast.crud.created('Partida')
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo crear la partida' })
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
        axios.put(`/api/partidas/${partida.value.id}`, {
          id_sala: partida.value.id_sala,
          fecha_inicio: partida.value.fecha_inicio || null,
          fecha_fin: partida.value.fecha_fin || null
        })
      )
      const data = response.data?.data ?? response.data
      toast.crud.updated('Partida')
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo actualizar la partida' })
    }
  }

  const deletePartida = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/partidas/${id}`))
      partidas.value = partidas.value.filter((item) => item.id !== id)
      toast.crud.deleted('Partida')
      return response
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo eliminar la partida' })
    }
  }

  const getPartida = async (id) => {
    const response = await axios.get(`/api/partidas/${id}`)
    const data = response.data?.data ?? response.data
    setPartida(data)
    return response
  }

  return {
    partidas,
    partida,
    salasDisponibles,
    isLoading,
    errors,
    hasError,
    getError,
    getPartidas,
    getSalasDisponibles,
    createPartida,
    updatePartida,
    deletePartida,
    setPartida,
    resetPartida,
    upsertPartidaRecord,
    getPartida
  }
}

function toDateTimeLocal(value) {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  const offset = date.getTimezoneOffset()
  const local = new Date(date.getTime() - offset * 60000)
  return local.toISOString().slice(0, 16)
}
