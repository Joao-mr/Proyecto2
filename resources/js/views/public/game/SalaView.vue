<template>
  <!-- GAME OVER -->
  <div v-if="gameOver" class="game-page d-flex align-items-center justify-content-center">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
          <div class="card border-0 shadow-lg text-center" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(14px); border-radius: 20px;">
            <div class="card-body p-5">
              <div class="display-1 mb-3">🏆</div>
              <h1 class="card-title fw-bold text-white mb-2">¡Partida finalizada!</h1>
              <p class="text-white-50 mb-4">Has completado todas las rondas de <strong>{{ salaName }}</strong></p>
              <div class="display-4 fw-black text-warning mb-1">{{ score }}</div>
              <p class="text-white-50 small mb-4">puntos totales</p>
              <RouterLink to="/mis-salas" class="btn btn-warning btn-lg fw-bold px-5 rounded-pill">
                <i class="pi pi-arrow-left me-2"></i>Volver a tus salas
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SIN IMÁGENES -->
  <div v-else-if="!isLoading && rounds.length === 0" class="game-page d-flex align-items-center justify-content-center">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
          <div class="card border-0 shadow-lg text-center" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(14px); border-radius: 20px;">
            <div class="card-body p-5">
              <div class="display-1 mb-3">📭</div>
              <h1 class="card-title fw-bold text-white mb-2">Sin imágenes</h1>
              <p class="text-white-50 mb-4">Las categorías de esta sala no tienen imágenes todavía.</p>
              <RouterLink to="/mis-salas" class="btn btn-warning btn-lg fw-bold px-5 rounded-pill">
                <i class="pi pi-arrow-left me-2"></i>Volver a tus salas
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CARGANDO -->
  <div v-else-if="isLoading" class="game-page d-flex align-items-center justify-content-center">
    <div class="text-center text-white">
      <div class="spinner-border text-warning mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
      <p class="fs-5 fw-semibold">Cargando sala...</p>
    </div>
  </div>

  <!-- SALA DE JUEGO -->
  <div v-else class="game-page">
    <!-- Navbar -->
    <GameNavbar :sala-name="salaName" @exit="handleExit" />

    <!-- Barra de progreso Bootstrap -->
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

    <!-- Área principal -->
    <main class="game-main">
      <!-- Imagen -->
      <GameImage
        :image-src="currentRound?.image ?? null"
        :round="round"
        :total-rounds="totalRounds"
      />

      <!-- Panel jugador -->
      <PlayerPanel
        :player-name="playerName"
        :score="score"
        :time-left="timeLeft"
        :total-time="TOTAL_TIME"
      />

      <!-- Input respuesta -->
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { authStore } from '@/store/auth';
import axios from 'axios';

import GameNavbar   from '@/components/game/GameNavbar.vue';
import GameImage    from '@/components/game/GameImage.vue';
import PlayerPanel  from '@/components/game/PlayerPanel.vue';
import AnswerInput  from '@/components/game/AnswerInput.vue';

/* ── Router & Auth ── */
const route  = useRoute();
const router = useRouter();
const auth   = authStore();

const salaId     = computed(() => route.params.id);
const salaName   = ref('');
const playerName = computed(() => auth.user?.name ?? 'Jugador');
const isLoading  = ref(true);

/* ── Rounds built from API images ── */
const rounds = ref([]);

function shuffle(arr) {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}

/* ── Game state ── */
const TOTAL_TIME     = ref(30);
const round          = ref(1);
const totalRounds    = computed(() => rounds.value.length);
const score          = ref(0);
const timeLeft       = ref(30);
const feedback       = ref(null);
const revealAnswer   = ref(false);
const answerDisabled = ref(false);
const gameOver       = ref(false);
const isSavingStats  = ref(false);
const hasSavedStats  = ref(false);
const answerInputRef = ref(null);

const currentRound = computed(() => rounds.value[round.value - 1] ?? null);

/* ── Timer ── */
let timerInterval = null;

function startTimer() {
  clearInterval(timerInterval);
  timeLeft.value = TOTAL_TIME.value;
  timerInterval = setInterval(() => {
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval);
      onTimeout();
      return;
    }
    timeLeft.value--;
  }, 1000);
}

function stopTimer() { clearInterval(timerInterval); }

function onTimeout() {
  answerDisabled.value = true;
  revealAnswer.value   = true;
  feedback.value       = 'timeout';
  scheduleNextRound();
}

/* ── Answer ── */
function handleAnswer(value) {
  stopTimer();
  answerDisabled.value = true;
  revealAnswer.value   = true;

  const correct  = (value ?? '').toLowerCase().trim();
  const expected = (currentRound.value?.answer ?? '').toLowerCase().trim();

  if (correct === expected) {
    score.value += 50;
    feedback.value = 'correct';
  } else {
    feedback.value = 'wrong';
  }

  scheduleNextRound();
}

function scheduleNextRound() {
  setTimeout(() => {
    if (round.value >= totalRounds.value) {
      finishGame();
    } else {
      round.value++;
      feedback.value       = null;
      revealAnswer.value   = false;
      answerDisabled.value = false;
      nextTick(() => {
        startTimer();
        answerInputRef.value?.focus();
      });
    }
  }, 2200);
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
      id_sala: Number(salaId.value),
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

/* ── Exit ── */
function handleExit() {
  stopTimer();
  router.push('/mis-salas');
}

/* ── Load sala data ── */
onMounted(async () => {
  try {
    // 1. Fetch sala with categories
    const { data: sala } = await axios.get(`/api/salas/${salaId.value}`);
    salaName.value = sala.nombre ?? 'Sala';
    TOTAL_TIME.value = sala.tiempo_respuesta ?? 30;

    const categoriaIds = (sala.categorias ?? []).map(c => c.id);

    if (categoriaIds.length === 0) {
      isLoading.value = false;
      return;
    }

    // 2. Fetch imagenes for each category in parallel
    const requests = categoriaIds.map(catId =>
      axios.get(`/api/imagenes?categoria_id=${catId}&per_page=100&page=1`)
        .then(r => r.data?.data ?? [])
        .catch(() => [])
    );

    const results = await Promise.all(requests);
    const allImagenes = results.flat();

    // 3. Build rounds (ignore images without media)
    const built = allImagenes
      .filter(img => img.urls?.original || img.urls?.preview || img.url)
      .map(img => ({
        image: img.urls?.preview || img.urls?.original || img.url,
        answer: img.respuesta_correcta,
      }));

    rounds.value = shuffle(built);
  } catch (err) {
    console.error('Error loading sala:', err);
  } finally {
    isLoading.value = false;
  }

  if (rounds.value.length > 0) {
    startTimer();
    nextTick(() => answerInputRef.value?.focus());
  }
});

onUnmounted(() => stopTimer());
</script>
