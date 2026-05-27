<template>
  <section class="ranking-section">

    <div class="container-home">

      <h2 class="ranking-title">
        Mejores Jugadores
      </h2>

      <button class="ranking-order-btn" @click="toggleOrder">
        Orden: {{ order === 'asc' ? 'ASC' : 'DESC' }}
      </button>

      <div class="ranking-card">

        <div class="ranking-head">
          <div>Jugador</div>
          <div>ELO</div>
          <div>P. Jugadas</div>
          <div>Título</div>
        </div>

        <div
          v-for="(player, index) in topPlayers"
          :key="player.id ?? index"
          class="ranking-row"
        >
          <div class="ranking-player">{{ index + 1 }}. {{ player.name }}</div>
          <div class="ranking-elo">{{ player.elo ?? 0 }}</div>
          <div>{{ player.matches ?? 0 }}</div>
          <div>{{ player.title ?? '' }}</div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRanking } from '@/composables/useRanking'

const { sortedPlayers, getRanking, order, toggleOrder } = useRanking()

onMounted(() => {
  getRanking()
})

const topPlayers = computed(() => {
  return (sortedPlayers.value || []).slice(0, 5)
})
</script>

<style scoped>
.ranking-order-btn {
  margin: 0 auto 20px; 
  background: #ff7b54;
  border: none;
  color: white;
  padding: 12px 20px;
  border-radius: 12px;
  font-weight: 700;
  transition: 0.2s;
  cursor: pointer;
}

.ranking-order-btn:hover {
  transform: scale(1.03);
  background: #ff6a3d;
}
</style>