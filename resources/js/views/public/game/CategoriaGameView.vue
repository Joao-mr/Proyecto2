<template>
  <div v-if="gameOver" class="game-page game-state-page">
    <div class="game-state-card">
      <div class="game-state-icon-wrap">
        <i class="pi pi-trophy game-state-icon"></i>
      </div>
      <h1 class="game-state-title">Partida finalizada</h1>
      <p class="game-state-subtitle">Has completado todas las rondas de <strong>{{ categoriaName }}</strong></p>
      <div class="game-state-score">{{ score }}</div>
      <p class="game-state-score-label">puntos totales</p>

      <RouterLink to="/categorias" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a categorias
      </RouterLink>
    </div>
  </div>

  <div v-else-if="pageLoading" class="game-page game-state-page">
    <div class="game-loading-card">
      <div class="game-loading-ring"></div>
      <p class="game-loading-text">Cargando partida...</p>
    </div>
  </div>

  <div v-else-if="loadError" class="game-page game-state-page">
    <div class="game-state-card">
      <div class="game-state-icon-wrap game-state-icon-wrap--warn">
        <i class="pi pi-exclamation-triangle game-state-icon"></i>
      </div>
      <h1 class="game-state-title">Error al cargar la partida</h1>
      <p class="game-state-subtitle">{{ loadError }}</p>
      <RouterLink to="/categorias" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a categorias
      </RouterLink>
    </div>
  </div>

  <div v-else-if="rounds.length === 0" class="game-page game-state-page">
    <div class="game-state-card">
      <div class="game-state-icon-wrap">
        <i class="pi pi-images game-state-icon"></i>
      </div>
      <h1 class="game-state-title">Sin imagenes</h1>
      <p class="game-state-subtitle">Esta categoria no tiene imagenes disponibles todavia.</p>
      <RouterLink to="/categorias" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a categorias
      </RouterLink>
    </div>
  </div>

  <div v-else class="game-page">
    <GameNavbar :sala-name="categoriaName" @exit="handleExit" />

    <div class="game-progress-bar">
      <div class="game-progress-bar__inner">
        <span class="game-progress-bar__label">Progreso</span>
        <div class="game-progress-bar__track">
          <div
            class="game-progress-bar__fill"
            role="progressbar"
            :style="{ width: `${progressPercent}%` }"
            :aria-valuenow="round - 1"
            :aria-valuemin="0"
            :aria-valuemax="totalRounds"
          ></div>
        </div>
        <span class="game-progress-bar__round">Ronda {{ round }} / {{ totalRounds }}</span>
      </div>
    </div>

    <main class="game-main">
      <GameImage :image-src="currentRound?.image ?? null" :round="round" :total-rounds="totalRounds" />
      <PlayerPanel :player-name="playerName" :score="score" :time-left="timeLeft" :total-time="totalTime" />
      <AnswerInput
        ref="answerInputRef"
        :feedback="feedback"
        :disabled="answerDisabled"
        :correct-answer="revealAnswer ? currentRound?.answer : null"
        @submit="handleAnswer"
      />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { authStore } from '@/store/auth'
import '../../../../css/game.css'
import GameNavbar from '@/components/game/GameNavbar.vue'
import GameImage from '@/components/game/GameImage.vue'
import PlayerPanel from '@/components/game/PlayerPanel.vue'
import AnswerInput from '@/components/game/AnswerInput.vue'
import { buildGameRounds, useGameSession } from '@/composables/useGameSession'
import useImagen from '@/composables/useImagen'
import { usePublicCategories } from '@/composables/useCategories'
const route = useRoute()
const router = useRouter()
const auth = authStore()
const totalTime = ref(30)
const pageLoading = ref(true)

const { imagenes, getImagenes } = useImagen()

const {
  categories,
  fetchCategories
} = usePublicCategories()

const routeCategoryId = computed(() => route.params.id)

const currentCategory = computed(() =>
  categories.value.find(
    (c) => String(c.id) === String(routeCategoryId.value)
  )
)

const categoriaId = computed(() =>
  currentCategory.value?.id ?? null
)

const categoriaName = computed(() =>
  currentCategory.value?.name ?? 'Categoria'
)

const playerName = computed(() => auth.user?.name ?? 'Jugador')
const loadError = ref('')

const {
  rounds,
  round,
  totalRounds,
  progressPercent,
  score,
  timeLeft,
  feedback,
  revealAnswer,
  answerDisabled,
  gameOver,
  answerInputRef,
  currentRound,
  startMatch,
  handleAnswer,
  stopTimer,
} = useGameSession({
  totalTime,
  persistResult: async ({ score, startedAt, finishedAt }) => {
    try {
      await axios.post('/api/partidas/registrar-resultado', {
        id_categoria: Number(categoriaId.value),
        puntuacion: score,
        fecha_inicio: startedAt,
        fecha_fin: finishedAt,
      })
    } catch (error) {
      throw error
    }
  },
})

function handleExit() {
  stopTimer()
  router.push('/categorias')
}

onMounted(async () => {

  try {

    // 1. cargar categorías
    await fetchCategories()

    // 2. validar categoría
    if (!categoriaId.value) {
      throw new Error('Categoría no encontrada')
    }

    // 3. cargar imágenes
    await getImagenes({
      categoria_id: categoriaId.value,
      per_page: 1000,
      random: 1
    })

    // 4. iniciar partida
    await startMatch(
      buildGameRounds(imagenes.value)
    )

  } catch (error) {

    loadError.value =
      error?.response?.data?.message
      ?? error?.message
      ?? 'No fue posible cargar la partida.'

  } finally {

    pageLoading.value = false

  }
})
</script>
