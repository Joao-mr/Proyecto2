<template>
  <div v-if="gameOver" class="game-page d-flex align-items-center justify-content-center">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
          <div class="card border-0 shadow-lg text-center" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(14px); border-radius: 20px;">
            <div class="card-body p-5">
              <div class="display-1 mb-3">🏆</div>
              <h1 class="card-title fw-bold text-white mb-2">Partida finalizada</h1>
              <p class="text-white-50 mb-4">Has completado todas las rondas de <strong>{{ categoriaName }}</strong></p>
              <div class="display-4 fw-black text-warning mb-1">{{ score }}</div>
              <p class="text-white-50 small mb-4">puntos totales</p>
              <div v-if="statsSaveState === 'saving'" class="alert alert-info py-2 small mb-3">
                Guardando estadisticas...
              </div>
              <div v-else-if="statsSaveState === 'saved'" class="alert alert-success py-2 small mb-3">
                Estadisticas guardadas correctamente.
              </div>
              <div v-else-if="statsSaveState === 'error'" class="alert alert-danger py-2 small mb-3">
                {{ statsSaveMessage || 'No se pudieron guardar las estadisticas.' }}
              </div>
              <RouterLink to="/categorias" class="btn btn-warning btn-lg fw-bold px-5 rounded-pill">
                <i class="pi pi-arrow-left me-2"></i>Volver a categorias
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else-if="!isLoading && rounds.length === 0" class="game-page d-flex align-items-center justify-content-center">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
          <div class="card border-0 shadow-lg text-center" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(14px); border-radius: 20px;">
            <div class="card-body p-5">
              <div class="display-1 mb-3">📭</div>
              <h1 class="card-title fw-bold text-white mb-2">Sin imagenes</h1>
              <p class="text-white-50 mb-4">Esta categoria no tiene imagenes disponibles todavia.</p>
              <RouterLink to="/categorias" class="btn btn-warning btn-lg fw-bold px-5 rounded-pill">
                <i class="pi pi-arrow-left me-2"></i>Volver a categorias
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else-if="isLoading" class="game-page d-flex align-items-center justify-content-center">
    <div class="text-center text-white">
      <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
      <p class="fs-5 fw-semibold">Cargando...</p>
    </div>
  </div>

  <div v-else class="game-page">
    <GameNavbar :sala-name="categoriaName" @exit="handleExit" />

    <div class="game-progress-bar">
      <div class="game-progress-bar__inner">
        <span class="game-progress-bar__label">Progreso</span>
        <div class="progress flex-grow-1" style="height: 8px; background: rgba(255,255,255,0.15); border-radius: 999px;">
          <div
            class="progress-bar bg-warning"
            role="progressbar"
            :style="{ width: `${((round - 1) / totalRounds) * 100}%`, borderRadius: '999px' }"
            :aria-valuenow="round - 1"
            :aria-valuemin="0"
            :aria-valuemax="totalRounds"
          ></div>
        </div>
        <span class="game-progress-bar__round">
          <span class="badge bg-light text-dark">Ronda {{ round }} / {{ totalRounds }}</span>
        </span>
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

const rounds = computed(() =>
  imagenes.value.map((img) => ({
    image: img.urls?.preview || img.urls?.original || img.url || null,
    answer: img.respuesta_correcta,
  })),
)

const TOTAL_TIME = 30
const round = ref(1)
const totalRounds = computed(() => rounds.value.length)
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

const currentRound = computed(() => rounds.value[round.value - 1] ?? null)

let timerInterval = null

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
  answerDisabled.value = true
  revealAnswer.value = true
  feedback.value = 'timeout'
  scheduleNextRound()
}

function handleAnswer(value) {
  stopTimer()
  answerDisabled.value = true
  revealAnswer.value = true

  const correct = (value ?? '').toLowerCase().trim()
  const expected = (currentRound.value?.answer ?? '').toLowerCase().trim()

  if (correct === expected) {
    score.value += 50
    feedback.value = 'correct'
  } else {
    feedback.value = 'wrong'
  }

  scheduleNextRound()
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

async function finishGame() {
  stopTimer();
  await persistMatchStats();
  gameOver.value = true;
}

async function persistMatchStats() {
  if (hasSavedStats.value || isSavingStats.value) {
    return;
  }

  isSavingStats.value = true;
  try {
    await axios.post('/api/usuario-partidas/finalizar', {
      id_categoria: Number(categoriaId.value),
      fecha_inicio: new Date().toISOString(),
      fecha_fin: new Date().toISOString(),
      puntuacion: score.value,
    });

    await auth.getUser();
    hasSavedStats.value = true;
  } catch (error) {
    console.error('Error al guardar estadísticas de partida:', error);
  } finally {
    isSavingStats.value = false;
  }
}

function handleExit() {
  stopTimer()
  router.push('/categorias')
}

onMounted(async () => {
  await Promise.all([getImagenes({ categoria_id: categoriaId.value }), getCategorias()])

  if (rounds.value.length > 0) {
    matchStartedAt.value = new Date().toISOString()
    startTimer()
    nextTick(() => answerInputRef.value?.focus())
  }
})

onUnmounted(() => stopTimer())
</script>
