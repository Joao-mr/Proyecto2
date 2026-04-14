<template>
  <!-- GAME OVER -->
  <div v-if="gameOver" class="game-page">
    <div class="game-over">
      <div class="game-over__card">
        <div class="game-over__icon">🏆</div>
        <h1 class="game-over__title">¡Partida finalizada!</h1>
        <p class="game-over__subtitle">Has completado todas las rondas de <strong>{{ categoriaName }}</strong></p>
        <div class="game-over__score">{{ score }}</div>
        <div class="game-over__score-label">puntos totales</div>
        <RouterLink to="/categorias" class="game-over__btn">
          Volver a categorías
          <span aria-hidden="true">›</span>
        </RouterLink>
      </div>
    </div>
  </div>

  <!-- SIN IMÁGENES -->
  <div v-else-if="!isLoading && rounds.length === 0" class="game-page">
    <div class="game-over">
      <div class="game-over__card">
        <div class="game-over__icon">📭</div>
        <h1 class="game-over__title">Sin imágenes</h1>
        <p class="game-over__subtitle">Esta categoría no tiene imágenes disponibles todavía.</p>
        <RouterLink to="/categorias" class="game-over__btn">Volver a categorías <span>›</span></RouterLink>
      </div>
    </div>
  </div>

  <!-- CARGANDO -->
  <div v-else-if="isLoading" class="game-page game-loading">
    <p>Cargando...</p>
  </div>

  <!-- PARTIDA -->
  <div v-else class="game-page">
    <GameNavbar :sala-name="categoriaName" @exit="handleExit" />

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
        :total-time="TOTAL_TIME"
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { authStore } from '@/store/auth';
import GameNavbar  from '@/components/game/GameNavbar.vue';
import GameImage   from '@/components/game/GameImage.vue';
import PlayerPanel from '@/components/game/PlayerPanel.vue';
import AnswerInput from '@/components/game/AnswerInput.vue';
import useImagenes from '@/composables/imagenes';
import useCategorias from '@/composables/categorias';

const route  = useRoute();
const router = useRouter();
const auth   = authStore();

const { imagenes, getImagenes, isLoading } = useImagenes();
const { categorias, getCategorias } = useCategorias();

const categoriaId   = computed(() => route.params.id);
const categoriaName = computed(() => {
  const cat = categorias.value.find(c => String(c.id) === String(categoriaId.value));
  return cat?.nombre ?? 'Categoría';
});
const playerName = computed(() => auth.user?.name ?? 'Jugador');

/* ── Rounds built from API images ── */
const rounds = computed(() =>
  imagenes.value.map(img => ({
    image: img.urls?.preview || img.urls?.original || img.url || null,
    answer: img.respuesta_correcta,
  }))
);

/* ── Game state ── */
const TOTAL_TIME     = 30;
const round          = ref(1);
const totalRounds    = computed(() => rounds.value.length);
const score          = ref(0);
const timeLeft       = ref(TOTAL_TIME);
const feedback       = ref(null);
const revealAnswer   = ref(false);
const answerDisabled = ref(false);
const gameOver       = ref(false);
const answerInputRef = ref(null);

const currentRound = computed(() => rounds.value[round.value - 1] ?? null);

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
      gameOver.value = true;
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

function handleExit() {
  stopTimer();
  router.push('/categorias');
}

/* ── Lifecycle ── */
onMounted(async () => {
  await Promise.all([
    getImagenes({ categoria_id: categoriaId.value }),
    getCategorias(),
  ]);
  if (rounds.value.length > 0) {
    startTimer();
    nextTick(() => answerInputRef.value?.focus());
  }
});

onUnmounted(() => stopTimer());
</script>
