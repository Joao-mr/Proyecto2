<template>
  <!-- GAME OVER -->
  <div v-if="gameOver" class="game-page">
    <div class="game-over">
      <div class="game-over__card">
        <div class="game-over__icon">🏆</div>
        <h1 class="game-over__title">¡Partida finalizada!</h1>
        <p class="game-over__subtitle">Has completado todas las rondas de <strong>{{ salaName }}</strong></p>
        <div class="game-over__score">{{ score }}</div>
        <div class="game-over__score-label">puntos totales</div>
        <RouterLink to="/" class="game-over__btn">
          Volver al inicio
          <span aria-hidden="true">›</span>
        </RouterLink>
      </div>
    </div>
  </div>

  <!-- SALA DE JUEGO -->
  <div v-else class="game-page">
    <!-- Navbar -->
    <GameNavbar :sala-name="salaName" @exit="handleExit" />

    <!-- Barra de progreso de rondas -->
    <div class="game-progress-bar">
      <div class="game-progress-bar__inner">
        <span class="game-progress-bar__label">Progreso</span>
        <div class="game-progress-bar__track">
          <div
            class="game-progress-bar__fill"
            :style="{ width: `${((round - 1) / totalRounds) * 100}%` }"
          ></div>
        </div>
        <span class="game-progress-bar__round">Ronda {{ round }} / {{ totalRounds }}</span>
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

import GameNavbar   from '@/components/game/GameNavbar.vue';
import GameImage    from '@/components/game/GameImage.vue';
import PlayerPanel  from '@/components/game/PlayerPanel.vue';
import AnswerInput  from '@/components/game/AnswerInput.vue';

/* ── Router & Auth ── */
const route  = useRoute();
const router = useRouter();
const auth   = authStore();

/* ── Sala identity ── */
const salaId   = computed(() => route.params.id);
const salaName = ref('Sala de Naturaleza');
const playerName = computed(() => auth.user?.name ?? 'Jugador');

/* ── Mock rounds data ── */
const mockRounds = [
  {
    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Sunflower_from_Silesia2.jpg/800px-Sunflower_from_Silesia2.jpg',
    answer: 'girasol',
  },
  {
    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Dog_Breeds.jpg/800px-Dog_Breeds.jpg',
    answer: 'perro',
  },
  {
    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Cat03.jpg/800px-Cat03.jpg',
    answer: 'gato',
  },
  {
    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Marigold_in_Hyderabad%2C_AP_W_IMG_0526.jpg/800px-Marigold_in_Hyderabad%2C_AP_W_IMG_0526.jpg',
    answer: 'flor',
  },
  {
    image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d9/Collage_of_Nine_Dogs.jpg/800px-Collage_of_Nine_Dogs.jpg',
    answer: 'perros',
  },
];

/* ── Game state ── */
const TOTAL_TIME    = 30;
const round         = ref(1);
const totalRounds   = ref(mockRounds.length);
const score         = ref(0);
const timeLeft      = ref(TOTAL_TIME);
const feedback      = ref(null);       // 'correct' | 'wrong' | 'timeout' | null
const revealAnswer  = ref(false);
const answerDisabled = ref(false);
const gameOver      = ref(false);
const answerInputRef = ref(null);

const currentRound = computed(() => mockRounds[round.value - 1] ?? null);

/* ── Timer ── */
let timerInterval = null;

function startTimer() {
  clearInterval(timerInterval);
  timeLeft.value = TOTAL_TIME;
  timerInterval = setInterval(() => {
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval);
      onTimeout();
      return;
    }
    timeLeft.value--;
  }, 1000);
}

function stopTimer() {
  clearInterval(timerInterval);
}

function onTimeout() {
  answerDisabled.value = true;
  revealAnswer.value   = true;
  feedback.value       = 'timeout';
  scheduleNextRound();
}

/* ── Answer handling ── */
function handleAnswer(value) {
  stopTimer();
  answerDisabled.value = true;
  revealAnswer.value   = true;

  const correct = (value ?? '').toLowerCase().trim();
  const expected = (currentRound.value?.answer ?? '').toLowerCase().trim();
  const isCorrect = correct === expected;

  if (isCorrect) {
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
      gameOver.value = true;
    } else {
      round.value++;
      feedback.value      = null;
      revealAnswer.value  = false;
      answerDisabled.value = false;
      nextTick(() => {
        startTimer();
        answerInputRef.value?.focus();
      });
    }
  }, 2200);
}

/* ── Exit ── */
function handleExit() {
  stopTimer();
  router.push('/');
}

/* ── Lifecycle ── */
onMounted(() => {
  startTimer();
  nextTick(() => answerInputRef.value?.focus());
});

onUnmounted(() => {
  stopTimer();
});
</script>
