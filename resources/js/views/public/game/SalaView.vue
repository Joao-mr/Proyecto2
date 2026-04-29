<template>
  <div v-if="gameOver" class="game-page game-state-page">
    <div class="game-state-card">
      <div class="game-state-icon-wrap">
        <i class="pi pi-trophy game-state-icon"></i>
      </div>
      <h1 class="game-state-title">Partida finalizada</h1>
      <p class="game-state-subtitle">Has completado todas las rondas de <strong>{{ salaName }}</strong></p>
      <div class="game-state-score">{{ score }}</div>
      <p class="game-state-score-label">puntos totales</p>
      <RouterLink to="/mis-salas" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a tus salas
      </RouterLink>
    </div>
  </div>

  <div v-else-if="pageLoading" class="game-page game-state-page">
    <div class="game-loading-card">
      <div class="game-loading-ring"></div>
      <p class="game-loading-text">Cargando sala...</p>
    </div>
  </div>

  <div v-else-if="loadError" class="game-page game-state-page">
    <div class="game-state-card">
      <div class="game-state-icon-wrap game-state-icon-wrap--warn">
        <i class="pi pi-exclamation-triangle game-state-icon"></i>
      </div>
      <h1 class="game-state-title">Error al cargar la sala</h1>
      <p class="game-state-subtitle">{{ loadError }}</p>
      <RouterLink to="/mis-salas" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a tus salas
      </RouterLink>
    </div>
  </div>

  <div v-else-if="rounds.length === 0" class="game-page game-state-page">
    <div class="game-state-card">
      <div class="game-state-icon-wrap">
        <i class="pi pi-images game-state-icon"></i>
      </div>
      <h1 class="game-state-title">Sin imágenes</h1>
      <p class="game-state-subtitle">Las categorías de esta sala no tienen imágenes todavía.</p>
      <RouterLink to="/mis-salas" class="game-state-btn">
        <i class="pi pi-arrow-left"></i>
        Volver a tus salas
      </RouterLink>
    </div>
  </div>

  <div v-else class="game-page">
    <GameNavbar :sala-name="salaName" @exit="handleExit" />

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
      <GameImage
        :image-src="currentRound?.image ?? null"
        :round="round"
        :total-rounds="totalRounds"
      />

      <PlayerPanel
        :player-name="playerName"
        :score="score"
        :time-left="timeLeft"
        :total-time="totalTime"
      />

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
import { authStore } from '@/store/auth'
import axios from 'axios'
import '../../../../css/game.css'

import GameNavbar from '@/components/game/GameNavbar.vue'
import GameImage from '@/components/game/GameImage.vue'
import PlayerPanel from '@/components/game/PlayerPanel.vue'
import AnswerInput from '@/components/game/AnswerInput.vue'
import { buildGameRounds, shuffleGameRounds, useGameSession } from '@/composables/useGameSession'

const route = useRoute()
const router = useRouter()
const auth = authStore()

const salaId = computed(() => route.params.id)
const salaName = ref('')
const playerName = computed(() => auth.user?.name ?? 'Jugador')
const pageLoading = ref(true)
const loadError = ref('')
const totalTime = ref(30)

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
  advanceOnWrongAnswer: true,
  persistResult: async ({ score, startedAt, finishedAt }) => {
    try {
      await axios.post('/api/partidas/registrar-resultado', {
        id_sala: Number(salaId.value),
        puntuacion: score,
        fecha_inicio: startedAt,
        fecha_fin: finishedAt,
      })
    } catch (error) {
      console.error('Error saving match stats:', error)
      throw error
    }
  },
})

function handleExit() {
  stopTimer()
  router.push('/mis-salas')
}

onMounted(async () => {
  try {
    const { data: sala } = await axios.get(`/api/salas/${salaId.value}`)
    salaName.value = sala.nombre ?? 'Sala'
    totalTime.value = sala.tiempo_respuesta ?? 30

    const categoriaIds = (sala.categorias ?? []).map((categoria) => categoria.id)

    if (categoriaIds.length === 0) {
      await startMatch([])
      return
    }

    const requests = categoriaIds.map((catId) =>
      axios.get(`/api/imagenes?categoria_id=${catId}&per_page=100&page=1`)
        .then((response) => response.data?.data ?? [])
        .catch(() => [])
    )

    const results = await Promise.all(requests)
    const allImagenes = results.flat()
    await startMatch(shuffleGameRounds(buildGameRounds(allImagenes, { filterMissingImage: true })))
  } catch (err) {
    console.error('Error loading sala:', err)
    loadError.value = err?.response?.data?.message ?? 'No fue posible cargar la sala en este momento.'
  } finally {
    pageLoading.value = false
  }
})
</script>
