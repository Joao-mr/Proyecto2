<template>
  <div class="game-player-panel">
    <p class="game-player-panel__name">
      <i class="pi pi-user me-1"></i>{{ playerName }}
    </p>

    <div class="game-player-panel__score-label">Puntuación</div>
    <div class="game-player-panel__score" :class="{ 'game-player-panel__score--pop': scorePop }">
      <span class="game-player-panel__score-pill">{{ score }}</span>
    </div>

    <div class="game-player-panel__divider"></div>

    <!-- Cronómetro circular SVG -->
    <div class="game-timer">
      <svg
        class="game-timer__svg"
        :width="timerSize"
        :height="timerSize"
        :viewBox="`0 0 ${timerSize} ${timerSize}`"
        :aria-label="`Tiempo restante: ${timeLeft} segundos`"
      >
        <!-- track -->
        <circle
          class="game-timer__bg"
          :cx="cx"
          :cy="cy"
          :r="radius"
        />
        <!-- arc de progreso -->
        <circle
          class="game-timer__arc"
          :class="arcColorClass"
          :cx="cx"
          :cy="cy"
          :r="radius"
          :stroke-dasharray="circumference"
          :stroke-dashoffset="dashOffset"
        />
        <!-- valor numérico centrado -->
        <text
          class="game-timer__value"
          :x="cx"
          :y="cy"
        >{{ timeLeft }}</text>
      </svg>
      <span class="game-timer__label">segundos</span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  playerName: {
    type: String,
    default: 'Jugador',
  },
  score: {
    type: Number,
    default: 0,
  },
  timeLeft: {
    type: Number,
    default: 30,
  },
  totalTime: {
    type: Number,
    default: 30,
  },
});

/* ── Timer geometry ── */
const timerSize = 120;
const cx = timerSize / 2;
const cy = timerSize / 2;
const radius = 48;
const circumference = 2 * Math.PI * radius;

const dashOffset = computed(() => {
  const pct = props.timeLeft / props.totalTime;
  return circumference * (1 - pct);
});

const arcColorClass = computed(() => {
  const pct = props.timeLeft / props.totalTime;
  if (pct > 0.5) return 'game-timer__arc--ok';
  if (pct > 0.25) return 'game-timer__arc--warning';
  return 'game-timer__arc--danger';
});

/* ── Score pop animation ── */
const scorePop = ref(false);

watch(
  () => props.score,
  () => {
    scorePop.value = true;
    setTimeout(() => { scorePop.value = false; }, 300);
  },
);
</script>
