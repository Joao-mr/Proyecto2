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
                  @click="changeMode('individual')"
                >
                  Individual
                </button>
                <button
                  type="button"
                  class="btn mode-btn px-4 py-2 rounded-pill fw-bold"
                  :class="{ active: mode === 'multijugador' }"
                  @click="changeMode('multijugador')"
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

                  <tbody v-if="currentRows.length">
                    <tr
                      v-for="(player, index) in currentRows"
                      :key="`${mode}-${player.name}-${index}`"
                      :class="{ 'ranking-row--top': index === 0 }"
                    >
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <span class="rank-num" :class="rankClass(index)">{{ index + 1 }}.</span>
                          <span class="fw-bold">{{ player.name }}</span>
                        </div>
                      </td>
                      <td class="fw-bold">
                        <span class="elo-dot me-2"></span>{{ formatElo(player.elo) }}
                      </td>
                      <td class="fw-bold">{{ player.matches }}</td>
                      <td class="fw-bold">{{ player.title }}</td>
                    </tr>
                  </tbody>

                  <tbody v-else>
                    <tr>
                      <td colspan="4" class="text-center py-4 text-white fw-semibold">
                        {{ loading ? 'Cargando ranking...' : (errorGlobal || 'No hay datos de ranking.') }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Mobile -->
              <div class="d-md-none p-3 ranking-mobile-list" v-if="currentRows.length">
                <article
                  v-for="(player, index) in currentRows"
                  :key="`mobile-${mode}-${player.name}-${index}`"
                  class="ranking-mobile-card"
                  :class="{ 'ranking-mobile-card--top': index === 0 }"
                >
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                      <span class="rank-num" :class="rankClass(index)">{{ index + 1 }}.</span>
                      <span class="fw-bold text-white">{{ player.name }}</span>
                    </div>
                    <span class="ranking-mobile-title">{{ player.title }}</span>
                  </div>

                  <div class="row g-2 mt-2">
                    <div class="col-6">
                      <div class="ranking-mobile-meta">ELO</div>
                      <div class="ranking-mobile-value fw-bold">
                        <span class="elo-dot me-2"></span>{{ formatElo(player.elo) }}
                      </div>
                    </div>
                    <div class="col-6 text-end">
                      <div class="ranking-mobile-meta">P. JUGADAS</div>
                      <div class="ranking-mobile-value fw-bold">{{ player.matches }}</div>
                    </div>
                  </div>
                </article>
              </div>

              <div v-else class="d-md-none p-3 text-center text-white fw-semibold">
                {{ loading ? 'Cargando ranking...' : (errorGlobal || 'No hay datos de ranking.') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRanking } from '@/composables/useRanking'

const { mode, currentRows, loading, errorGlobal, fetchRanking, formatElo } = useRanking()

const changeMode = async (nextMode) => {
  if (mode.value === nextMode) return
  mode.value = nextMode
  await fetchRanking(nextMode, { limit: 20 })
}

onMounted(async () => {
  await fetchRanking('individual', { limit: 20, force: true })
  fetchRanking('multijugador', { limit: 20 })
})

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
  border-radius: 15px;
  display: inline-flex;
  gap: 0.45rem;
}

.mode-btn {
  color: #eaf0ff;
  border: 0;
}

.mode-btn.active {
  background: #eef2ff;
  border-radius: 15px   ;
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


.ranking-table.table {
  --bs-table-bg: transparent;
  --bs-table-accent-bg: transparent;
  --bs-table-striped-bg: transparent;
  --bs-table-active-bg: transparent;
  --bs-table-hover-bg: rgba(255, 255, 255, 0.06);
  --bs-table-color: #f2f5ff;
  --bs-table-border-color: rgba(255, 255, 255, 0.12);
  margin-bottom: 0;
}

.ranking-table.table > :not(caption) > * > * {
  background-color: transparent !important;
  box-shadow: none !important;
}

.ranking-table thead th {
  background: #5f6d96 !important;
  color: #ffb58b !important;
}

.ranking-table tbody td {
  background: rgba(114, 130, 171, 0.40) !important;
  color: #f2f5ff !important;
}

.ranking-table tbody tr.top-1 td,
.ranking-table tbody tr.ranking-row--top td {
  background: rgba(255, 255, 255, 0.10) !important;
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

