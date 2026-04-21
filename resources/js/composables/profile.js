import { inject, ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'
import { authStore } from '../store/auth'

export default function useProfile() {
  const auth = authStore()
  const initialProfile = {
    name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: ''
  }

  const initialStats = {
    partidas_jugadas: 0,
    elo_total: 0,
    imagenes_acertadas: 0,
    titulo: {
      slug: '',
      label: '',
      min_elo: 0
    }
  }

  const profile = ref({ ...initialProfile })
  const stats = ref({ ...initialStats, titulo: { ...initialStats.titulo } })
  const isLoading = ref(false)
  const swal = inject('$swal')
  const toast = useToast()
  const { errors, validate, clearErrors, hasError, getError } = useValidation()

  const profileSchema = yup.object({
    name: yup.string().trim().required('El nombre es obligatorio').min(3, 'Debe tener al menos 3 caracteres'),
    current_password: yup.string().transform((value, originalValue) => (originalValue === '' ? undefined : value)).when('password', {
      is: (password) => !!password,
      then: (schema) => schema.required('La contraseña actual es obligatoria'),
      otherwise: (schema) => schema.notRequired().nullable()
    }),
    password: yup.string().transform((value, originalValue) => (originalValue === '' ? undefined : value)).trim().min(8, 'La nueva contraseña debe tener al menos 8 caracteres').notRequired().nullable(),
    password_confirmation: yup.string().transform((value, originalValue) => (originalValue === '' ? undefined : value)).when('password', {
      is: (password) => !!password,
      then: (schema) => schema.required('La confirmación de la contraseña es obligatoria').oneOf([yup.ref('password')], 'Las contraseñas no coinciden'),
      otherwise: (schema) => schema.notRequired().nullable()
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

  const resetProfile = () => {
    profile.value = { ...initialProfile }
    stats.value = { ...initialStats, titulo: { ...initialStats.titulo } }
    clearErrors()
  }

  const setStats = (data = {}) => {
    stats.value = {
      partidas_jugadas: data.partidas_jugadas ?? 0,
      elo_total: data.elo_total ?? 0,
      imagenes_acertadas: data.imagenes_acertadas ?? 0,
      titulo: {
        slug: data.titulo?.slug ?? '',
        label: data.titulo?.label ?? '',
        min_elo: data.titulo?.min_elo ?? 0
      }
    }
  }

  const setProfile = (data = {}, statsData = {}) => {
    profile.value = {
      name: data.name ?? '',
      email: data.email ?? '',
      current_password: '',
      password: '',
      password_confirmation: ''
    }
    setStats(statsData)
    clearErrors()
  }

  const getProfile = async () => {
    return withLoading(async () => {
      try {
        const userResponse = await axios.get('/api/user')
        const userData = userResponse.data?.data ?? userResponse.data ?? {}

        setProfile(userData)

        try {
          const statsResponse = await axios.get('/api/user/stats')
          const statsData = statsResponse.data?.data ?? statsResponse.data ?? {}
          setStats(statsData)
        } catch (statsError) {
          setStats()
          toast.error('Aviso', 'No se pudieron cargar las estadísticas del perfil')
        }

        return { user: userData, stats: stats.value }
      } catch (error) {
        toast.error('Error', 'No se pudo cargar el perfil')
        throw error
      }
    })
  }

  const updateProfile = async () => {
    const { isValid } = validate(profileSchema, profile.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const normalizedProfile = profileSchema.cast(profile.value, { stripUnknown: false })
      const payload = {
        name: normalizedProfile.name
      }

      if (normalizedProfile.password) {
        payload.current_password = normalizedProfile.current_password
        payload.password = normalizedProfile.password
        payload.password_confirmation = normalizedProfile.password_confirmation
      }

      const response = await withLoading(() => axios.put('/api/user', payload))
      const data = response.data?.data ?? response.data
      if (auth.user) {
        auth.user.name = normalizedProfile.name
      }
      profile.value.current_password = ''
      profile.value.password = ''
      profile.value.password_confirmation = ''
      toast.crud.updated('Usuario')
      return data
    } catch (error) {
      toast.error('Error', 'No se pudo actualizar el usuario')
      throw error
    }
  }

  return {
    profile,
    stats,
    errors,
    isLoading,
    hasError,
    getError,
    clearErrors,
    resetProfile,
    setProfile,
    setStats,
    getProfile,
    updateProfile
  }
}
