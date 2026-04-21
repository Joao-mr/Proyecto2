<template>
  <section class="ranking-section">
    <div class="container-home">
      <h2 class="ranking-title">Mejores Jugadores</h2>

      <div class="ranking-switch">
        <button
          class="ranking-switch__btn"
          :class="{ 'is-active': mode === 'individual' }"
          @click="mode = 'individual'"
        >
          Individual
        </button>
        <button
          class="ranking-switch__btn"
          :class="{ 'is-active': mode === 'multijugador' }"
          @click="mode = 'multijugador'"
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

        <div class="ranking-body">
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
              {{ player.elo }}
            </div>

            <div>{{ player.matches }}</div>
            <div class="ranking-rank">{{ player.title }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const mode = ref('individual')

const individual = [
  { name: 'ROBER', elo: '13.749', matches: 567, title: 'RADIANT' },
  { name: 'LAURA', elo: '11.026', matches: 435, title: 'MASTER' },
  { name: 'JOAO', elo: '9.925', matches: 530, title: 'UNREAL' },
  { name: 'CARLOS', elo: '9.335', matches: 386, title: 'CHALLENGER' },
  { name: 'XD', elo: '8.932', matches: 324, title: 'CHAMPION' }
]

const multijugador = [
  { name: 'MARIO', elo: '14.201', matches: 612, title: 'RADIANT' },
  { name: 'SARA', elo: '12.488', matches: 502, title: 'MASTER' },
  { name: 'ALAN', elo: '10.114', matches: 447, title: 'UNREAL' },
  { name: 'NORA', elo: '9.604', matches: 399, title: 'CHALLENGER' },
  { name: 'LUIS', elo: '9.020', matches: 341, title: 'CHAMPION' }
]

const currentRows = computed(() =>
  mode.value === 'individual' ? individual : multijugador
)

const getRankClass = (index) => {
  if (index === 0) return 'ranking-row--gold'
  if (index === 1) return 'ranking-row--silver'
  if (index === 2) return 'ranking-row--bronze'
  return ''
}
</script>