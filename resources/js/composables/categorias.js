import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useCategorias() {
  const categorias = ref([])
  const categoria = ref({ id: null, nombre: '', descripcion: '' })
  const isLoading = ref(false)
  const toast = useToast()

  const {
    validate,
    handleRequestError,
    clearErrors,
    hasError,
    getError
  } = useValidation()

  const categoriaSchema = yup.object({
    nombre: yup
      .string()
      .trim()
      .required('El nombre es obligatorio')
      .min(2, 'Debe tener al menos 2 caracteres')
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

  const resetCategoria = () => {
    categoria.value = { id: null, nombre: '', descripcion: '' }
    clearErrors()
  }

  const setCategoria = (data = {}) => {
    categoria.value = {
      id: data.id ?? null,
      nombre: data.nombre ?? '',
      descripcion: data.descripcion ?? ''
    }
    clearErrors()
  }

  const upsertCategoriaRecord = (categoriaRecord) => {
    if (!categoriaRecord?.id) return
    categorias.value = [
      categoriaRecord,
      ...categorias.value.filter(item => item.id !== categoriaRecord.id)
    ]
  }

  const getCategorias = async (params = {}) => {
    const defaultParams = { page: 1 }
    const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
    const response = await axios.get(`/api/categorias?${query}`)
    categorias.value = response.data?.data ?? []
    return response
  }

  const createCategoria = async () => {
    const { isValid } = await validate(categoriaSchema, categoria.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/categorias', { nombre: categoria.value.nombre, descripcion: categoria.value.descripcion })
      )
      const data = response.data
      toast.crud.created('Categoría')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo crear la categoría',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const updateCategoria = async () => {
    const { isValid } = await validate(categoriaSchema, categoria.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.put(`/api/categorias/${categoria.value.id}`, {
          nombre: categoria.value.nombre,
          descripcion: categoria.value.descripcion
        })
      )
      const data = response.data
      toast.crud.updated('Categoría')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo actualizar la categoría',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const deleteCategoria = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/categorias/${id}`))
      categorias.value = categorias.value.filter(item => item.id !== id)
      toast.crud.deleted('Categoría')
      return response
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo eliminar la categoría',
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  return {
    categorias,
    categoria,
    isLoading,
    hasError,
    getError,
    resetCategoria,
    setCategoria,
    upsertCategoriaRecord,
    getCategorias,
    createCategoria,
    updateCategoria,
    deleteCategoria
  }
}