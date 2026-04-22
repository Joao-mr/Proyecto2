<template>
  <section class="ranking-section">
    <div class="container-home">
      <h2 class="ranking-title">Mejores Jugadores</h2>

      <div class="ranking-switch">
        <button
          class="ranking-switch__btn"
          :class="{ 'is-active': mode === 'individual' }"
          @click="changeMode('individual')"
        >
          Individual
        </button>
        <button
          class="ranking-switch__btn"
          :class="{ 'is-active': mode === 'multijugador' }"
          @click="changeMode('multijugador')"
        >
          Multijugador
        </button>
      </div>

      <div class="ranking-card">
        <div class="ranking-head">
          <div>Jugador</div>
          <div>ELO</div>
          <div>P. Jugadas</div>
          <div>Título</div>
        </div>

        <div class="ranking-body" v-if="currentRows.length">
          <div
            v-for="(player, index) in currentRows"
            :key="`${mode}-${player.name}-${index}`"
            class="ranking-row"
            :class="getRankClass(index)"
          >
            <div class="ranking-player">
              <span class="ranking-pos">{{ index + 1 }}.</span>
              <span class="ranking-avatar">👤</span>
              <span class="ranking-name">{{ player.name }}</span>
            </div>

            <div class="ranking-elo">
              <span class="ranking-dot"></span>
              {{ formatElo(player.elo) }}
            </div>

            <div>{{ player.matches }}</div>
            <div class="ranking-rank">{{ player.title }}</div>
          </div>
        </div>

        <div class="ranking-body" v-else>
          <div class="ranking-row">
            <div>{{ loading ? 'Cargando ranking...' : (error || 'Sin datos de ranking.') }}</div>
            <div>-</div>
            <div>-</div>
            <div>-</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRanking } from '../../composables/useRanking'

const {
  mode,
  currentRows,
  loading,
  error,
  fetchRanking,
  setMode,
  getRankClass,
  formatElo
} = useRanking()

onMounted(async () => {
  await fetchRanking('individual')
  fetchRanking('multijugador')
})

const changeMode = async (value) => {
  await setMode(value)
}
</script>