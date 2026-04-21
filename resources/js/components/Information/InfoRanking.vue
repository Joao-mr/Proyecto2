<template>
  <div class="ranking-info-main">
    <section class="card border-0 ranking-hero mb-4 rise-in">
      <div class="card-body p-4 p-lg-5">
        <p class="ranking-kicker mb-2">
          <i class="pi pi-chart-line me-2"></i>Información
        </p>
        <h1 class="ranking-title mb-3">¿Cómo funciona el ranking?</h1>
        <p class="ranking-lead mb-0">
          El ranking premia rapidez, precisión y constancia en cada partida.
        </p>
      </div>
    </section>

    <div class="row g-4 mb-4">
      <div class="col-md-4" v-for="(item, index) in resumen" :key="`resumen-${index}`">
        <article class="card border-0 ranking-card h-100 rise-in" :style="{ animationDelay: `${index * 0.05}s` }">
          <div class="card-body p-4">
            <h2 class="ranking-card-title">{{ item.title }}</h2>
            <p class="ranking-card-text mb-0">{{ item.text }}</p>
          </div>
        </article>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <section class="card border-0 ranking-card h-100 rise-in">
          <div class="card-body p-4">
            <h2 class="ranking-subtitle mb-3">Funcionamiento</h2>

            <ul class="ranking-list mb-3">
              <li v-for="(linea, index) in funcionamiento" :key="`linea-${index}`">
                <i class="pi pi-check-circle me-2"></i>{{ linea }}
              </li>
            </ul>

            <h3 class="ranking-subtitle ranking-subtitle--sm mb-2">Existen rankings:</h3>
            <div class="ranking-tags">
              <span class="ranking-tag">Global</span>
              <span class="ranking-tag">Por categoría</span>
            </div>
          </div>
        </section>
      </div>

      <div class="col-lg-5">
        <section class="card border-0 ranking-card h-100 rise-in" style="animation-delay:.08s;">
          <div class="card-body p-4">
            <h2 class="ranking-subtitle mb-3">Cómo subir posiciones</h2>
            <div class="ranking-tip" v-for="(tip, index) in tips" :key="`tip-${index}`">
              <i class="pi pi-star-fill"></i>
              <span>{{ tip }}</span>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <RouterLink to="/rankings" class="home-btn-play ranking-cta">
        Ver ranking
        <span>›</span>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
const resumen = [
  { title: 'Puntos por partida', text: 'Cada partida suma puntos según tu resultado.' },
  { title: 'Rapidez y precisión', text: 'Responder antes y acertar aumenta la puntuación.' },
  { title: 'Constancia', text: 'Jugar más te da más opciones de subir al Top.' }
]

const funcionamiento = [
  'Los jugadores obtienen puntos en cada partida.',
  'La puntuación depende de la rapidez y precisión.',
  'Cuantas más partidas juegues, más posibilidades de subir posiciones.',
  'Los mejores jugadores aparecerán en el Top del ranking.'
]

const tips = [
  'Juega de forma regular.',
  'Prioriza respuestas rápidas pero correctas.',
  'Participa en varias categorías para mejorar tu nivel global.'
]
</script>

<style scoped>
.ranking-info-main { padding: 1rem 0 3.5rem; }

.ranking-hero,
.ranking-card {
  background: rgba(116, 131, 171, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 18px;
  color: #f2f5ff;
  box-shadow: 0 10px 22px rgba(23, 33, 68, 0.24);
}

.ranking-kicker { color: #ffb58b; font-weight: 700; }
.ranking-title { font-size: clamp(1.9rem, 3vw, 2.5rem); font-weight: 800; }
.ranking-lead { color: rgba(242, 245, 255, 0.9); font-size: 1.05rem; }

.ranking-card-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 0.45rem;
}
.ranking-card-text { color: rgba(242, 245, 255, 0.9); }

.ranking-subtitle {
  color: #fff;
  font-weight: 800;
  font-size: 1.25rem;
}
.ranking-subtitle--sm { font-size: 1rem; }

.ranking-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 0.65rem;
}
.ranking-list li {
  background: rgba(63, 77, 123, 0.45);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 10px;
  padding: 0.65rem 0.75rem;
}
.ranking-list i { color: #6ee7b7; }

.ranking-tags { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.ranking-tag {
  background: rgba(255, 122, 88, 0.2);
  color: #ffd3bf;
  border: 1px solid rgba(255, 122, 88, 0.35);
  border-radius: 999px;
  padding: 0.35rem 0.8rem;
  font-weight: 700;
  font-size: 0.88rem;
}

.ranking-tip {
  display: grid;
  grid-template-columns: 18px 1fr;
  gap: 0.65rem;
  align-items: start;
  padding: 0.75rem 0.8rem;
  border-radius: 10px;
  background: rgba(63, 77, 123, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 0.6rem;
}
.ranking-tip:last-child { margin-bottom: 0; }
.ranking-tip i { color: #ffb58b; margin-top: 3px; }

.ranking-cta { text-decoration: none; }

.rise-in { animation: riseIn 0.35s ease both; }

@keyframes riseIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 991.98px) {
  .ranking-info-main { padding-top: 0.5rem; }
}
</style>