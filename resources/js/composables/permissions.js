import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'
import { createLoadingGuard, unwrapApiData, upsertById } from './crud-helpers'

export default function usePermissions() {
  const permissions = ref([])
  const permissionList = ref([])
  const initialPermission = { id: null, name: '' }
  const permission = ref({ ...initialPermission })
  const isLoading = ref(false)
  const toast = useToast()

  const { errors, validate, handleRequestError, clearErrors, hasError, getError } = useValidation()

  const permissionSchema = yup.object({
    name: yup.string().trim().required('El nombre es obligatorio').min(3, 'Debe tener al menos 3 caracteres')
  })

  const withLoading = createLoadingGuard(isLoading)

  const resetPermission = () => {
    permission.value = { ...initialPermission }
    clearErrors()
  }

  const getRolePermissions = async (roleId) => {
    if (!roleId) return []
    try {
      const response = await withLoading(() => axios.get(`/api/role-permissions/${roleId}`))
      return unwrapApiData(response) ?? []
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudieron obtener los permisos del rol',
        onGenericError: (message) => toast.error('Error', message)
      })
      return []
    }
  }

  const updateRolePermissions = async (roleId, permissionIds = []) => {
    try {
      const response = await withLoading(() => axios.put('/api/role-permissions', {
        role_id: roleId,
        permissions: JSON.stringify(permissionIds)
      }))
      toast.crud.updated('Permisos del rol')
      return unwrapApiData(response) ?? []
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudieron actualizar los permisos',
        onGenericError: (message) => toast.error('Error', message)
      })
      throw error
    }
  }

  const setPermission = (data = {}) => {
    permission.value = {
      id: data.id ?? null,
      name: data.name ?? ''
    }
    clearErrors()
  }

  const upsertPermissionRecord = (permissionRecord) => {
    permissions.value = upsertById(permissions.value, permissionRecord)
  }

  const getPermissions = async (params = {}) => {
    const defaultParams = {
      page: 1,
      search_id: '',
      search_title: '',
      search_global: '',
      order_column: 'created_at',
      order_direction: 'desc'
    }

    const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
    const response = await axios.get(`/api/permissions?${query}`)
    permissions.value = unwrapApiData(response) ?? []
    return response
  }

  const getPermissionList = async () => {
    try {
      const response = await axios.get('/api/permissions')
      permissionList.value = unwrapApiData(response) ?? []
      return response
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo obtener la lista de permisos',
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const createPermission = async () => {
    const { isValid } = await validate(permissionSchema, permission.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.post('/api/permissions', { name: permission.value.name })
      )
      const data = unwrapApiData(response)
      toast.crud.created('Permiso')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo crear el permiso',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const updatePermission = async () => {
    const { isValid } = await validate(permissionSchema, permission.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() =>
        axios.put(`/api/permissions/${permission.value.id}`, {
          name: permission.value.name
        })
      )
      const data = unwrapApiData(response)
      toast.crud.updated('Permiso')
      return data
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo actualizar el permiso',
        onValidationError: () =>
          toast.error('Error de validación', 'Revisa los campos resaltados.'),
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  const deletePermission = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/permissions/${id}`))
      permissions.value = permissions.value.filter(item => item.id !== id)
      toast.crud.deleted('Permiso')
      return response
    } catch (error) {
      handleRequestError(error, {
        fallbackMessage: 'No se pudo eliminar el permiso',
        onGenericError: (message) => toast.error('Error', message)
      })
    }
  }

  return {
    permissions,
    permission,
    permissionList,
    isLoading,
    errors,
    hasError,
    getError,
    resetPermission,
    setPermission,
    upsertPermissionRecord,
    getPermissions,
    getPermissionList,
    getRolePermissions,
    updateRolePermissions,
    createPermission,
    updatePermission,
    deletePermission
  }
}

