<template>
  <div class="home-page">
    <HomeNavbar />

    <main class="info-index">
      <div class="container-home">
        <header class="text-center mb-4">
          <h1 class="info-index__title">INFORMACIÓN DEL JUEGO</h1>
          <p class="info-index__subtitle">Consulta cómo jugar, normas y ranking.</p>
        </header>

        <div class="ranking-switch mb-4">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            class="ranking-switch__btn"
            :class="{ 'is-active': activeSection === tab.key }"
            @click="setSection(tab.key)"
          >
            {{ tab.label }}
          </button>
        </div>

        <transition name="info-fade" mode="out-in">
          <component :is="currentComponent" :key="activeSection" />
        </transition>
      </div>
    </main>

    <HomeFooter />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import HomeNavbar from '@/layouts/HomeNavbar.vue'
import HomeFooter from '@/layouts/HomeFooter.vue'
import InfoHowToPlay from '@/components/Information/InfoHowToPlay.vue'
import InfoRules from '@/components/Information/InfoRules.vue'
import InfoRanking from '@/components/Information/InfoRanking.vue'

const route = useRoute()

const tabs = [
  { key: 'como-jugar', label: 'Cómo jugar' },
  { key: 'normas', label: 'Normas' },
  { key: 'ranking', label: 'Ranking' }
]

const activeSection = ref('como-jugar')

const normalizeTab = (tab) => {
  const value = String(tab || 'como-jugar')
  if (value === 'como-jugar' || value === 'normas' || value === 'ranking') return value
  if (value === 'ranking-info') return 'ranking'
  return 'como-jugar'
}

watch(
  () => route.query.tab,
  (tab) => { activeSection.value = normalizeTab(tab) },
  { immediate: true }
)

const setSection = (section) => {
  activeSection.value = normalizeTab(section)
}

const currentComponent = computed(() => {
  if (activeSection.value === 'normas') return InfoRules
  if (activeSection.value === 'ranking') return InfoRanking
  return InfoHowToPlay
})
</script>

<style scoped>
.info-index { padding: 1rem 0 3.5rem; }
.info-index__title { color: #f2f5ff; font-weight: 800; letter-spacing: .04em; }
.info-index__subtitle { color: #d7def5; margin: 0; }

.info-fade-enter-active,
.info-fade-leave-active { transition: opacity .2s ease, transform .2s ease; }
.info-fade-enter-from,
.info-fade-leave-to { opacity: 0; transform: translateY(5px); }

@media (max-width: 991.98px) {
  .info-index { padding-top: .5rem; }
}
</style>