<template>
  <div class="profile-page vstack gap-3 gap-md-4">
    <ProfileHeroCard
      :avatar="userDetails.avatar || '/images/avatar-default.svg'"
      :name="profile.name || auth.user?.name || 'Jugador'"
      :subtitle="$t('profile_ui.hero_subtitle')"
      :title-label="stats.titulo?.label || '-'"
      :sessions-label="$t('profile_ui.stats.matches')"
      :sessions-value="stats.partidas_jugadas"
      :elo-label="$t('profile_ui.stats.elo')"
      :elo-value="stats.elo_total"
    />

    <div class="d-flex justify-content-center">
      <div class="ranking-switch">
        <button
          type="button"
          class="ranking-switch__btn"
          :class="{ 'is-active': activeTab === 'personal' }"
          @click="activeTab = 'personal'"
        >
          {{ $t('profile_ui.tabs.personal') }}
        </button>
        <button
          type="button"
          class="ranking-switch__btn"
          :class="{ 'is-active': activeTab === 'stats' }"
          @click="activeTab = 'stats'"
        >
          {{ $t('profile_ui.tabs.stats') }}
        </button>
      </div>
    </div>

    <template v-if="activeTab === 'stats'">
      <ProfileStatsGrid :items="statItems" />

      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <ProfilePerformanceCard
            :title="$t('profile_ui.performance.title')"
            :average-label="$t('profile_ui.performance.average')"
            :best-label="$t('profile_ui.performance.best')"
            :last-label="$t('profile_ui.performance.last')"
            :consistency-label="$t('profile_ui.performance.consistency')"
            :progress-label="$t('profile_ui.performance.progress')"
            :resumen="stats.resumen"
          />
        </div>

        <div class="col-12 col-lg-6">
          <ProfileRecentMatchesCard
            :title="$t('profile_ui.recent_activity')"
            :empty-label="$t('profile_ui.recent_empty')"
            :matches="stats.actividad_reciente"
          />
        </div>
      </div>
    </template>

    <form v-else class="profile-surface p-3 p-md-4 vstack gap-4" @submit.prevent="submitForm">
      <section class="vstack gap-3">
        <div>
          <h3 class="text-base fw-semibold mb-1 text-white">{{ $t('profile_ui.personal_data') }}</h3>
          <p class="text-sm mb-0 text-white-50">{{ $t('profile_ui.email_note') }}</p>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 p-3 border rounded profile-panel-soft">
              <Avatar
                :image="userDetails.avatar || '/images/avatar-default.svg'"
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
                  :chooseLabel="$t('profile_ui.change_avatar')"
                  class="w-100"
                  @before-upload="onBeforeUpload"
                  @upload="onTemplatedUpload"
                />
                <small class="text-white-50">{{ $t('profile_ui.avatar_note') }}</small>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="vstack gap-1">
              <label for="profile-name" class="text-sm fw-medium text-white">{{ $t('name') }}</label>
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
              <label for="profile-email" class="text-sm fw-medium text-white">{{ $t('email') }}</label>
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
              <label class="text-sm fw-medium text-white">{{ $t('surname1') }}</label>
              <InputText :model-value="userDetails.surname1 || '-'" class="w-100" readonly disabled />
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="vstack gap-1">
              <label class="text-sm fw-medium text-white">{{ $t('surname2') }}</label>
              <InputText :model-value="userDetails.surname2 || '-'" class="w-100" readonly disabled />
            </div>
          </div>
        </div>
      </section>

      <section class="vstack gap-3">
        <div>
          <h3 class="text-base fw-semibold mb-1 text-white">{{ $t('profile_ui.password_change') }}</h3>
          <p class="text-sm mb-0 text-white-50">{{ $t('profile_ui.password_note') }}</p>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-4">
            <div class="vstack gap-1">
              <label for="current-password" class="text-sm fw-medium text-white">{{ $t('profile_ui.current_password') }}</label>
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
              <label for="new-password" class="text-sm fw-medium text-white">{{ $t('new_password') }}</label>
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
              <label for="password-confirmation" class="text-sm fw-medium text-white">{{ $t('confirm_password') }}</label>
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

      <div class="d-flex justify-content-end gap-3 pt-2">
        <Button
          type="button"
          :label="$t('profile_ui.reset')"
          icon="pi pi-refresh"
          severity="secondary"
          text
          :disabled="isLoading"
          @click="loadProfile"
        />
        <Button type="submit" :label="$t('profile_ui.save_changes')" icon="pi pi-save" :loading="isLoading" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import '../../../css/profile.css'
import useProfile from '@/composables/profile'
import useUsers from '@/composables/users'
import { authStore } from '@/store/auth'
import ProfileHeroCard from '@/components/profile/ProfileHeroCard.vue'
import ProfileStatsGrid from '@/components/profile/ProfileStatsGrid.vue'
import ProfilePerformanceCard from '@/components/profile/ProfilePerformanceCard.vue'
import ProfileRecentMatchesCard from '@/components/profile/ProfileRecentMatchesCard.vue'

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
const { t } = useI18n()
const { getUser } = useUsers()
const activeTab = ref('stats')
const userDetails = ref({
  avatar: null,
  surname1: '',
  surname2: ''
})

const statItems = computed(() => [
  { key: 'matches', label: t('profile_ui.stats.matches'), value: stats.value.partidas_jugadas },
  { key: 'elo', label: t('profile_ui.stats.elo'), value: stats.value.elo_total },
  { key: 'hits', label: t('profile_ui.stats.hits'), value: stats.value.imagenes_acertadas },
  { key: 'title', label: t('profile_ui.stats.title'), value: stats.value.titulo?.label || '-' }
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
