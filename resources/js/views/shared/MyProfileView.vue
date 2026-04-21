<template>
  <div class="my-profile-view d-flex justify-content-center">
    <Card class="w-full max-w-4xl">
      <template #title>
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div>
            <h2 class="fs-4 fw-semibold mb-1">Mi perfil</h2>
            <p class="text-sm text-surface-500 mb-0">Actualiza tus datos y revisa tus estadisticas.</p>
          </div>
          <Tag :value="stats.titulo?.label || 'Sin titulo'" severity="secondary" rounded />
        </div>
      </template>

      <template #content>
        <form class="vstack gap-4" @submit.prevent="submitForm">
          <section class="vstack gap-3">
            <div>
              <h3 class="text-base font-medium text-surface-700 mb-1">Datos personales</h3>
              <p class="text-sm text-surface-500 mb-0">El email se muestra solo como referencia.</p>
            </div>

            <div class="row g-3">
              <div class="col-12">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 p-3 border rounded bg-light">
                  <Avatar
                    :image="userDetails.avatar || 'https://bootdey.com/img/Content/avatar/avatar7.png'"
                    style="width: 4.5rem; height: 4.5rem;"
                    size="xlarge"
                    shape="circle"
                  />
                  <div class="d-flex flex-column gap-2 flex-grow-1">
                    <FileUpload
                      name="picture"
                      url="/api/users/updateimg"
                      mode="basic"
                      :auto="true"
                      accept="image/*"
                      :maxFileSize="1500000"
                      chooseLabel="Cambiar avatar"
                      class="w-100"
                      @before-upload="onBeforeUpload"
                      @upload="onTemplatedUpload"
                    />
                    <small class="text-surface-500">Formato recomendado: imagen cuadrada, maximo 1.5MB.</small>
                  </div>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="vstack gap-1">
                  <label for="profile-name" class="text-sm font-medium text-surface-700">Nombre</label>
                  <InputText
                    id="profile-name"
                    v-model="profile.name"
                    class="w-100"
                    autocomplete="name"
                    :invalid="hasError('name')"
                    :disabled="isLoading"
                  />
                  <small v-if="hasError('name')" class="p-error">{{ getError('name') }}</small>
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="vstack gap-1">
                  <label for="profile-email" class="text-sm font-medium text-surface-700">Email</label>
                  <InputText
                    id="profile-email"
                    v-model="profile.email"
                    class="w-100"
                    type="email"
                    readonly
                    disabled
                  />
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="vstack gap-1">
                  <label class="text-sm font-medium text-surface-700">Primer apellido</label>
                  <InputText :model-value="userDetails.surname1 || '-'" class="w-100" readonly disabled />
                </div>
              </div>

              <div class="col-12 col-md-6">
                <div class="vstack gap-1">
                  <label class="text-sm font-medium text-surface-700">Segundo apellido</label>
                  <InputText :model-value="userDetails.surname2 || '-'" class="w-100" readonly disabled />
                </div>
              </div>
            </div>
          </section>

          <Divider />

          <section class="vstack gap-3">
            <div>
              <h3 class="text-base font-medium text-surface-700 mb-1">Cambio de contrasena</h3>
              <p class="text-sm text-surface-500 mb-0">Deja estos campos vacios si no quieres cambiarla.</p>
            </div>

            <div class="row g-3">
              <div class="col-12 col-md-4">
                <div class="vstack gap-1">
                  <label for="current-password" class="text-sm font-medium text-surface-700">Contrasena actual</label>
                  <InputText
                    id="current-password"
                    v-model="profile.current_password"
                    class="w-100"
                    type="password"
                    autocomplete="current-password"
                    :invalid="hasError('current_password')"
                    :disabled="isLoading"
                  />
                  <small v-if="hasError('current_password')" class="p-error">{{ getError('current_password') }}</small>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="vstack gap-1">
                  <label for="new-password" class="text-sm font-medium text-surface-700">Nueva contrasena</label>
                  <InputText
                    id="new-password"
                    v-model="profile.password"
                    class="w-100"
                    type="password"
                    autocomplete="new-password"
                    :invalid="hasError('password')"
                    :disabled="isLoading"
                  />
                  <small v-if="hasError('password')" class="p-error">{{ getError('password') }}</small>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="vstack gap-1">
                  <label for="password-confirmation" class="text-sm font-medium text-surface-700">Confirmar contrasena</label>
                  <InputText
                    id="password-confirmation"
                    v-model="profile.password_confirmation"
                    class="w-100"
                    type="password"
                    autocomplete="new-password"
                    :invalid="hasError('password_confirmation')"
                    :disabled="isLoading"
                  />
                  <small v-if="hasError('password_confirmation')" class="p-error">{{ getError('password_confirmation') }}</small>
                </div>
              </div>
            </div>
          </section>

          <Divider />

          <section class="vstack gap-3">
            <div>
              <h3 class="text-base font-medium text-surface-700 mb-1">Estadisticas</h3>
              <p class="text-sm text-surface-500 mb-0">Resumen de tu progreso en partidas.</p>
            </div>

            <div class="row g-3">
              <div class="col-12 col-sm-6 col-lg-3" v-for="item in statItems" :key="item.label">
                <div class="border rounded p-3 h-100 bg-light">
                  <span class="d-block text-sm text-surface-500 mb-1">{{ item.label }}</span>
                  <strong class="fs-5">{{ item.value }}</strong>
                </div>
              </div>
            </div>
          </section>

          <div class="d-flex justify-content-end gap-3 pt-2">
            <Button
              type="button"
              label="Restablecer"
              icon="pi pi-refresh"
              severity="secondary"
              text
              :disabled="isLoading"
              @click="loadProfile"
            />
            <Button type="submit" label="Guardar cambios" icon="pi pi-save" :loading="isLoading" />
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import useProfile from '@/composables/profile'
import useUsers from '@/composables/users'
import { authStore } from '@/store/auth'

const {
  profile,
  stats,
  isLoading,
  hasError,
  getError,
  getProfile,
  updateProfile
} = useProfile()

const auth = authStore()
const { getUser } = useUsers()
const userDetails = ref({
  avatar: null,
  surname1: '',
  surname2: ''
})

const statItems = computed(() => [
  { label: 'Partidas jugadas', value: stats.value.partidas_jugadas },
  { label: 'Elo total', value: stats.value.elo_total },
  { label: 'Imagenes acertadas', value: stats.value.imagenes_acertadas },
  { label: 'Titulo', value: stats.value.titulo?.label || '-' }
])

const loadProfile = async () => {
  try {
    await getProfile()
  } catch (error) {
    // El composable muestra el mensaje de error.
  }
}

const loadUserDetails = async () => {
  try {
    if (!auth.user?.id) return
    const data = await getUser(auth.user.id)
    userDetails.value = {
      avatar: data?.avatar ?? null,
      surname1: data?.surname1 ?? '',
      surname2: data?.surname2 ?? ''
    }
  } catch (error) {
    // El perfil principal sigue funcionando aunque falle este bloque.
  }
}

const onBeforeUpload = (event) => {
  event.formData.append('id', auth.user.id)
}

const onTemplatedUpload = async () => {
  await loadUserDetails()
}

const submitForm = async () => {
  try {
    await updateProfile()
  } catch (error) {
    // El composable muestra errores de validacion o guardado.
  }
}

onMounted(() => {
  loadProfile()
  loadUserDetails()
})
</script>
