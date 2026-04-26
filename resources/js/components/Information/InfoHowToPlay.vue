<template>
  <div class="info-main">
    <section class="card border-0 info-hero mb-4 fade-up">
      <div class="card-body p-4 p-lg-5">
        <p class="info-kicker mb-2">
          <i class="pi pi-compass me-2"></i>Información
        </p>
        <h1 class="info-title mb-3">¿Cómo se juega?</h1>
        <p class="info-lead mb-0">
          Sigue estos pasos y empieza a competir en tiempo real.
        </p>
      </div>
    </section>

    <div class="row g-4">
      <div class="col-lg-8">
        <section class="card border-0 info-card h-100 fade-up fade-up--delay">
          <div class="card-body p-4">
            <h2 class="info-subtitle mb-3">Pasos del juego</h2>

            <ol class="info-steps mb-0">
              <li v-for="(paso, index) in pasos" :key="`paso-${index}`" class="info-step-item">
                <span class="info-step-index">{{ index + 1 }}</span>
                <span>{{ paso }}</span>
              </li>
            </ol>
          </div>
        </section>
      </div>

      <div class="col-lg-4">
        <section class="card border-0 info-card h-100 fade-up fade-up--delay-2">
          <div class="card-body p-4">
            <h2 class="info-subtitle mb-3">Claves</h2>

            <div class="info-tip">
              <i class="pi pi-bolt"></i>
              <span>Velocidad + precisión = más puntos.</span>
            </div>
            <div class="info-tip">
              <i class="pi pi-users"></i>
              <span>Compites contra otros jugadores en tiempo real.</span>
            </div>
            <div class="info-tip">
              <i class="pi pi-chart-line"></i>
              <span>La constancia te hace subir posiciones.</span>
            </div>
          </div>
        </section>
      </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <RouterLink to="/mis-salas" class="home-btn-play info-cta">
        Ir a jugar
        <span>›</span>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
const pasos = [
  'Entra en una sala de cualquier categoría.',
  'Se mostrará una imagen en pantalla.',
  'Escribe lo que crees que representa lo más rápido posible.',
  'Cuanto antes aciertes, más puntos obtendrás.',
  'Compite contra otros jugadores en tiempo real.',
  'Sube posiciones en el ranking global y por categorías.'
]
</script>

<style scoped>
.info-main { padding: 1rem 0 3.5rem; }

.info-hero,
.info-card {
  background: rgba(116, 131, 171, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 18px;
  color: #f2f5ff;
  box-shadow: 0 10px 22px rgba(23, 33, 68, 0.24);
}

.info-kicker { color: #ffb58b; font-weight: 700; }
.info-title { font-size: clamp(1.9rem, 3vw, 2.5rem); font-weight: 800; }
.info-lead { color: rgba(242, 245, 255, 0.9); font-size: 1.05rem; }
.info-subtitle { color: #fff; font-weight: 800; font-size: 1.25rem; }

.info-steps {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 0.9rem;
}

.info-step-item {
  display: grid;
  grid-template-columns: 34px 1fr;
  gap: 0.8rem;
  align-items: start;
  color: #eef2ff;
}

.info-step-index {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.28);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}

.info-tip {
  display: flex;
  gap: 0.7rem;
  align-items: flex-start;
  padding: 0.75rem 0.85rem;
  border-radius: 12px;
  background: rgba(63, 77, 123, 0.45);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #eef2ff;
  margin-bottom: 0.75rem;
}
.info-tip:last-child { margin-bottom: 0; }
.info-tip i { color: #ff9e6a; margin-top: 2px; }

.info-cta { text-decoration: none; }

.fade-up { animation: fadeUp 0.35s ease both; }
.fade-up--delay { animation-delay: 0.05s; }
.fade-up--delay-2 { animation-delay: 0.1s; }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 991.98px) { .info-main { padding-top: 0.5rem; } }
</style>