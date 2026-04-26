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

      <div v-if="statsSaveState === 'saving'" class="game-state-alert game-state-alert--info">
        Guardando estadisticas...
      </div>
      <div v-else-if="statsSaveState === 'saved'" class="game-state-alert game-state-alert--ok">
        Estadisticas guardadas correctamente.
      </div>
      <div v-else-if="statsSaveState === 'error'" class="game-state-alert game-state-alert--error">
        {{ statsSaveMessage || 'No se pudieron guardar las estadisticas.' }}
      </div>

      <RouterLink to="/categorias" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a categorias
      </RouterLink>
    </div>
  </div>

  <div v-else-if="!isLoading && rounds.length === 0" class="game-page game-state-page">
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

  <div v-else-if="isLoading" class="game-page game-state-page">
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
      <PlayerPanel :player-name="playerName" :score="score" :time-left="timeLeft" :total-time="TOTAL_TIME" />
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
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { authStore } from '@/store/auth'
import '../../../../css/game.css'
import GameNavbar from '@/components/game/GameNavbar.vue'
import GameImage from '@/components/game/GameImage.vue'
import PlayerPanel from '@/components/game/PlayerPanel.vue'
import AnswerInput from '@/components/game/AnswerInput.vue'
import useImagenes from '@/composables/imagenes'
import useCategorias from '@/composables/categorias'

const route = useRoute()
const router = useRouter()
const auth = authStore()

const { imagenes, getImagenes, isLoading } = useImagenes()
const { categorias, getCategorias } = useCategorias()

const categoriaId = computed(() => route.params.id)
const categoriaName = computed(() => {
  const cat = categorias.value.find((c) => String(c.id) === String(categoriaId.value))
  return cat?.nombre ?? 'Categoria'
})
const playerName = computed(() => auth.user?.name ?? 'Jugador')

const rounds = ref([])

const TOTAL_TIME = 30
const round = ref(1)
const totalRounds = computed(() => rounds.value.length)
const progressPercent = computed(() => {
  if (!totalRounds.value) return 0
  const value = ((round.value - 1) / totalRounds.value) * 100
  return Math.max(0, Math.min(100, value))
})
const score = ref(0)
const timeLeft = ref(TOTAL_TIME)
const feedback = ref(null)
const revealAnswer = ref(false)
const answerDisabled = ref(false)
const gameOver = ref(false)
const answerInputRef = ref(null)
const statsSaveState = ref('idle')
const statsSaveMessage = ref('')
const matchStartedAt = ref(null)
const matchPersisted = ref(false)
const loadError = ref('')

const currentRound = computed(() => rounds.value[round.value - 1] ?? null)

let timerInterval = null
let wrongFeedbackTimeout = null

function shuffleArray(list = []) {
  const result = [...list]
  for (let i = result.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1))
    const tmp = result[i]
    result[i] = result[j]
    result[j] = tmp
  }
  return result
}

function startTimer() {
  clearInterval(timerInterval)
  timeLeft.value = TOTAL_TIME
  timerInterval = setInterval(() => {
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval)
      onTimeout()
      return
    }
    timeLeft.value--
  }, 1000)
}

function stopTimer() {
  clearInterval(timerInterval)
}

function onTimeout() {
  clearTimeout(wrongFeedbackTimeout)
  answerDisabled.value = true
  revealAnswer.value = true
  feedback.value = 'timeout'
  scheduleNextRound()
}

function handleAnswer(value) {
  const correct = (value ?? '').toLowerCase().trim()
  const expected = (currentRound.value?.answer ?? '').toLowerCase().trim()

  if (correct === expected) {
    clearTimeout(wrongFeedbackTimeout)
    stopTimer()
    answerDisabled.value = true
    revealAnswer.value = true
    score.value += 50
    feedback.value = 'correct'
    scheduleNextRound()
  } else {
    // Intentos ilimitados hasta que acabe el tiempo: no pasamos de ronda al fallar.
    feedback.value = 'wrong'
    revealAnswer.value = false
    answerDisabled.value = false
    clearTimeout(wrongFeedbackTimeout)
    wrongFeedbackTimeout = setTimeout(() => {
      if (feedback.value === 'wrong') feedback.value = null
    }, 900)
    nextTick(() => answerInputRef.value?.focus())
  }
}

function scheduleNextRound() {
  setTimeout(() => {
    if (round.value >= totalRounds.value) {
      finishGame()
    } else {
      round.value++
      feedback.value = null
      revealAnswer.value = false
      answerDisabled.value = false
      nextTick(() => {
        startTimer()
        answerInputRef.value?.focus()
      })
    }
  }, 2200)
}

function finishGame() {
  gameOver.value = true
  void persistMatchResult()
}

async function persistMatchResult() {
  if (matchPersisted.value) return

  matchPersisted.value = true
  statsSaveState.value = 'saving'
  statsSaveMessage.value = ''

  try {
    await axios.post('/api/partidas/registrar-resultado', {
      id_categoria: Number(categoriaId.value),
      puntuacion: score.value,
      fecha_inicio: matchStartedAt.value ?? new Date().toISOString(),
      fecha_fin: new Date().toISOString(),
    })

    statsSaveState.value = 'saved'
  } catch (error) {
    console.error('Error saving match stats by category:', error)
    statsSaveState.value = 'error'
    statsSaveMessage.value = 'Error al guardar tus estadisticas. Intentalo de nuevo.'
    matchPersisted.value = false
  }
}

function handleExit() {
  stopTimer()
  router.push('/categorias')
}

onMounted(async () => {
  try {
    await Promise.all([
      getImagenes({ categoria_id: categoriaId.value, per_page: 1000, random: 1 }),
      getCategorias(),
    ])

    rounds.value = shuffleArray(
      imagenes.value.map((img) => ({
        image: img.urls?.preview || img.urls?.original || img.url || null,
        answer: img.respuesta_correcta,
      })),
    )

    if (rounds.value.length > 0) {
      matchStartedAt.value = new Date().toISOString()
      startTimer()
      nextTick(() => answerInputRef.value?.focus())
    }
  } catch (error) {
    loadError.value = error?.response?.data?.message ?? 'No fue posible cargar la partida en este momento.'
  }
})

onUnmounted(() => {
  stopTimer()
  clearTimeout(wrongFeedbackTimeout)
})
</script>
