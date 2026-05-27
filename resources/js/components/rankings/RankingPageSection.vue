<template>

  <section class="ranking-section">

    <div class="container-home">

      <h2 class="ranking-title">
        Mejores Jugadores
      </h2>

      <button
       class="ranking-order-btn"
        @click="toggleOrder"
        >
        Orden:
        {{ order === 'asc' ? 'ASC' : 'DESC' }}
      </button>

      <div class="ranking-card">

        <div class="ranking-head">
          <div>Jugador</div>
          <div>ELO</div>
          <div>P. Jugadas</div>
          <div>Título</div>
        </div>

        <div
          class="ranking-body"
          v-if="sortedPlayers.length"
        >

          <div
            v-for="(player, index) in sortedPlayers"
            :key="index"
            class="ranking-row"
          >

            <div class="ranking-player">

              <span class="ranking-pos">
                {{ index + 1 }}.
              </span>

              <span class="ranking-name">
                {{ player.name }}
              </span>

            </div>

            <div class="ranking-elo">
              {{ player.elo }}
            </div>

            <div>
              {{ player.matches }}
            </div>

            <div>
              {{ player.title }}
            </div>

          </div>

        </div>

        <div
          v-else
          class="ranking-row"
        >

          <div>
            {{ loading ? 'Cargando ranking...' : 'No hay datos de ranking.' }}
          </div>

        </div>

      </div>

    </div>

  </section>

</template>

<script setup>
import { onMounted } from 'vue'
import { useRanking } from '@/composables/useRanking'

const {
    sortedPlayers,
    order,
    loading,
    getRanking,
    toggleOrder
} = useRanking()

onMounted(() => {

    getRanking()
})
</script>

<style scoped>

.ranking-order-btn {

    background: #ff7b54;
    border: none;
    color: white;

    padding: 12px 20px;

    border-radius: 12px;

    font-weight: 700;

    margin-bottom: 20px;

    transition: 0.2s;
    cursor: pointer;
}

.ranking-order-btn:hover {

    transform: scale(1.03);

    background: #ff6a3d;
}

</style>