<template>
  <section class="ranking-page py-4 py-lg-5">
    <div class="container ranking-page__container">
      <div class="row justify-content-center">
        <div class="col-12">
          <header class="ranking-page__header text-center mb-4">
            <h1 class="ranking-page__title">RANKING GLOBAL</h1>

            <div class="d-flex justify-content-center mt-3">
              <div class="ranking-mode-switch p-2 rounded-4 shadow-sm">
                <button
                  type="button"
                  class="btn mode-btn px-4 py-2 rounded-pill fw-bold"
                  :class="{ active: mode === 'individual' }"
                  @click="mode = 'individual'"
                >
                  Individual
                </button>
                <button
                  type="button"
                  class="btn mode-btn px-4 py-2 rounded-pill fw-bold"
                  :class="{ active: mode === 'multijugador' }"
                  @click="mode = 'multijugador'"
                >
                  Multijugador
                </button>
              </div>
            </div>
          </header>

          <div class="card border-0 ranking-card shadow-lg mx-auto">
            <div class="card-body p-0">
              <!-- Desktop / Tablet -->
              <div class="table-responsive ranking-table-wrap d-none d-md-block">
                <table class="table table-borderless align-middle mb-0 ranking-table">
                  <colgroup>
                    <col style="width: 38%" />
                    <col style="width: 20%" />
                    <col style="width: 20%" />
                    <col style="width: 22%" />
                  </colgroup>
                  <thead>
                    <tr>
                      <th>JUGADOR</th>
                      <th>ELO</th>
                      <th>P. JUGADAS</th>
                      <th>TÍTULO</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(player, index) in currentRows"
                      :key="`${mode}-${player.name}-${index}`"
                      :class="{ 'ranking-row--top': index === 0 }"
                    >
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <span class="rank-num" :class="rankClass(index)">{{ index + 1 }}.</span>
                          <span class="rank-avatar">👤</span>
                          <span class="fw-bold">{{ player.name }}</span>
                        </div>
                      </td>
                      <td class="fw-bold">
                        <span class="elo-dot me-2"></span>{{ player.elo }}
                      </td>
                      <td class="fw-bold">{{ player.matches }}</td>
                      <td class="fw-bold">{{ player.title }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile -->
              <div class="d-md-none p-3 ranking-mobile-list">
                <article
                  v-for="(player, index) in currentRows"
                  :key="`mobile-${mode}-${player.name}-${index}`"
                  class="ranking-mobile-card"
                  :class="{ 'ranking-mobile-card--top': index === 0 }"
                >
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                      <span class="rank-num" :class="rankClass(index)">{{ index + 1 }}.</span>
                      <span class="rank-avatar">👤</span>
                      <span class="fw-bold text-white">{{ player.name }}</span>
                    </div>
                    <span class="ranking-mobile-title">{{ player.title }}</span>
                  </div>

                  <div class="row g-2 mt-2">
                    <div class="col-6">
                      <div class="ranking-mobile-meta">ELO</div>
                      <div class="ranking-mobile-value fw-bold">
                        <span class="elo-dot me-2"></span>{{ player.elo }}
                      </div>
                    </div>
                    <div class="col-6 text-end">
                      <div class="ranking-mobile-meta">P. JUGADAS</div>
                      <div class="ranking-mobile-value fw-bold">{{ player.matches }}</div>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const mode = ref('individual')

const rankingData = {
  individual: [
    { name: 'ROBER', elo: '13.749', matches: 567, title: 'RADIANT' },
    { name: 'LAURA', elo: '11.026', matches: 435, title: 'MASTER' },
    { name: 'JOAO', elo: '9.925', matches: 530, title: 'UNREAL' },
    { name: 'CARLOS', elo: '9.335', matches: 386, title: 'CHALLENGER' },
    { name: 'XD', elo: '8.932', matches: 324, title: 'CHAMPION' },
    { name: 'MARTA', elo: '8.771', matches: 301, title: 'DIAMOND' },
    { name: 'IVÁN', elo: '8.420', matches: 289, title: 'DIAMOND' },
    { name: 'NEREA', elo: '8.122', matches: 277, title: 'PLATINUM' },
    { name: 'JULIO', elo: '7.980', matches: 265, title: 'PLATINUM' },
    { name: 'ALBA', elo: '7.811', matches: 252, title: 'PLATINUM' },
    { name: 'RUBÉN', elo: '7.655', matches: 244, title: 'GOLD' },
    { name: 'ANA', elo: '7.502', matches: 233, title: 'GOLD' },
    { name: 'PABLO', elo: '7.311', matches: 220, title: 'GOLD' },
    { name: 'SARA', elo: '7.140', matches: 214, title: 'GOLD' },
    { name: 'DANIEL', elo: '6.995', matches: 206, title: 'SILVER' },
    { name: 'ELENA', elo: '6.802', matches: 198, title: 'SILVER' },
    { name: 'MIGUEL', elo: '6.677', matches: 187, title: 'SILVER' },
    { name: 'LUCÍA', elo: '6.511', matches: 179, title: 'SILVER' },
    { name: 'ADRIÁN', elo: '6.300', matches: 166, title: 'BRONZE' },
    { name: 'PAULA', elo: '6.145', matches: 158, title: 'BRONZE' }
  ],
  multijugador: [
    { name: 'MARIO', elo: '14.201', matches: 612, title: 'RADIANT' },
    { name: 'NORA', elo: '12.488', matches: 502, title: 'MASTER' },
    { name: 'ALAN', elo: '10.114', matches: 447, title: 'UNREAL' },
    { name: 'SOFÍA', elo: '9.604', matches: 399, title: 'CHALLENGER' },
    { name: 'LUIS', elo: '9.020', matches: 341, title: 'CHAMPION' },
    { name: 'IRENE', elo: '8.864', matches: 330, title: 'DIAMOND' },
    { name: 'HUGO', elo: '8.502', matches: 314, title: 'DIAMOND' },
    { name: 'NOA', elo: '8.290', matches: 300, title: 'PLATINUM' },
    { name: 'DIEGO', elo: '8.111', matches: 288, title: 'PLATINUM' },
    { name: 'LAIA', elo: '7.922', matches: 275, title: 'PLATINUM' },
    { name: 'BRUNO', elo: '7.760', matches: 261, title: 'GOLD' },
    { name: 'OLGA', elo: '7.604', matches: 250, title: 'GOLD' },
    { name: 'SERGIO', elo: '7.433', matches: 241, title: 'GOLD' },
    { name: 'LARA', elo: '7.299', matches: 228, title: 'GOLD' },
    { name: 'RAÚL', elo: '7.122', matches: 214, title: 'SILVER' },
    { name: 'CARMEN', elo: '6.980', matches: 206, title: 'SILVER' },
    { name: 'MATEO', elo: '6.811', matches: 194, title: 'SILVER' },
    { name: 'JIMENA', elo: '6.670', matches: 186, title: 'SILVER' },
    { name: 'ERIC', elo: '6.501', matches: 172, title: 'BRONZE' },
    { name: 'VEGA', elo: '6.340', matches: 160, title: 'BRONZE' }
  ]
}

const currentRows = computed(() => rankingData[mode.value])

const rankClass = (index) => {
  if (index === 0) return 'first'
  if (index === 1) return 'second'
  if (index === 2) return 'third'
  return ''
}
</script>

<style scoped>
.ranking-page__container {
  max-width: 1320px;
  margin-inline: auto;
  padding-inline: 1rem;
}

.ranking-page__title {
  margin: 0;
  color: #f2f5ff;
  font-weight: 800;
  letter-spacing: 0.08em;
  font-size: clamp(1.05rem, 2vw, 1.55rem);
}

.ranking-mode-switch {
  background: rgba(95, 111, 153, 0.88);
  border: 1px solid rgba(255, 255, 255, 0.18);
  display: inline-flex;
  gap: 0.45rem;
}

.mode-btn {
  color: #eaf0ff;
  border: 0;
}

.mode-btn.active {
  background: #eef2ff;
  color: #556799;
}

.ranking-card {
  width: 100%;
  max-width: 100%;
  margin-inline: auto;
  background: #7382ab;
  border-radius: 20px;
  overflow: hidden;
}

.ranking-table-wrap {
  width: 100%;
}

.ranking-table {
  width: 100% !important;
  min-width: 100%;
  margin: 0 auto;
  table-layout: fixed;
}

/* DESKTOP TABLE */
.ranking-table thead th {
  background: #5f6d96;
  color: #ff6f4e;
  font-weight: 800;
  padding: 1rem 1.2rem;
  font-size: 0.92rem;
  letter-spacing: 0.03em;
}

.ranking-table thead th:first-child,
.ranking-table tbody td:first-child {
  text-align: left;
}

.ranking-table thead th:not(:first-child),
.ranking-table tbody td:not(:first-child) {
  text-align: center;
}

.ranking-table tbody td {
  color: #f2f5ff;
  padding: 0.95rem 1.2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 1rem;
  white-space: nowrap;
}

.ranking-row--top td {
  background: rgba(255, 255, 255, 0.08);
}

.rank-num {
  width: 2rem;
  font-weight: 800;
  text-align: right;
}

.rank-num.first { color: #ffd23c; }
.rank-num.second { color: #d8deef; }
.rank-num.third { color: #f2a45e; }

.rank-avatar {
  opacity: 0.95;
}

.elo-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #ff9a5c;
  border: 2px solid #ffd9bc;
  display: inline-block;
  vertical-align: middle;
}

/* MOBILE CARDS */
.ranking-mobile-list {
  display: grid;
  gap: 0.7rem;
}

.ranking-mobile-card {
  background: rgba(76, 89, 135, 0.45);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 14px;
  padding: 0.8rem 0.9rem;
}

.ranking-mobile-card--top {
  background: rgba(255, 255, 255, 0.12);
}

.ranking-mobile-title {
  font-size: 0.74rem;
  font-weight: 800;
  color: #ffb085;
  letter-spacing: 0.03em;
}

.ranking-mobile-meta {
  color: #cfd8f7;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.ranking-mobile-value {
  color: #f2f5ff;
  font-size: 0.95rem;
}

/* BREAKPOINTS */
@media (max-width: 768px) {
  .ranking-mode-switch {
    width: 100%;
    justify-content: center;
  }

  .mode-btn {
    flex: 1 1 0;
    padding: 0.52rem 0.72rem !important;
    font-size: 0.9rem;
  }
}

@media (max-width: 576px) {
  .ranking-page__container {
    padding-left: 0.8rem;
    padding-right: 0.8rem;
  }

  .ranking-page__title {
    font-size: 0.95rem;
  }
}
</style>

