<template>
  <div class="normas-main">
    <section class="card border-0 normas-hero mb-4 slide-in">
      <div class="card-body p-4 p-lg-5">
        <p class="normas-kicker mb-2">
          <i class="pi pi-shield me-2"></i>Información
        </p>
        <h1 class="normas-title mb-3">Normas del juego</h1>
        <p class="normas-lead mb-0">
          Juego limpio para garantizar una experiencia justa para todos.
        </p>
      </div>
    </section>

    <div class="row g-4">
      <div class="col-lg-8">
        <section class="card border-0 normas-card h-100 slide-in slide-in--delay">
          <div class="card-body p-4">
            <h2 class="normas-subtitle mb-3">Reglas principales</h2>

            <ul class="normas-list mb-0">
              <li v-for="(norma, index) in normas" :key="`norma-${index}`" class="normas-item">
                <i class="pi pi-check-circle"></i>
                <span>{{ norma }}</span>
              </li>
            </ul>
          </div>
        </section>
      </div>

      <div class="col-lg-4">
        <section class="card border-0 normas-card h-100 slide-in slide-in--delay-2">
          <div class="card-body p-4">
            <h2 class="normas-subtitle mb-3">Aviso</h2>

            <div class="normas-alert">
              <i class="pi pi-exclamation-triangle"></i>
              <p class="mb-0">
                El incumplimiento puede suponer penalizaciones o bloqueo.
              </p>
            </div>

            <div class="normas-mini mt-3">
              <i class="pi pi-users me-2"></i>
              Respeta a los demás jugadores.
            </div>
            <div class="normas-mini">
              <i class="pi pi-ban me-2"></i>
              No se permiten respuestas ofensivas.
            </div>
          </div>
        </section>
      </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <RouterLink to="/categorias" class="home-btn-play normas-cta">
        Ver salas disponibles
        <span>›</span>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
const normas = [
  'No hacer trampas ni usar herramientas externas.',
  'Respetar a los demás jugadores.',
  'No enviar respuestas ofensivas o inapropiadas.',
  'Jugar de forma limpia y competitiva.',
  'El incumplimiento puede suponer penalizaciones o bloqueo.'
]
</script>

<style scoped>
.normas-main { padding: 1rem 0 3.5rem; }

.normas-hero,
.normas-card {
  background: rgba(116, 131, 171, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 18px;
  color: #f2f5ff;
  box-shadow: 0 10px 22px rgba(23, 33, 68, 0.24);
}

.normas-kicker { color: #ffb58b; font-weight: 700; }
.normas-title { font-size: clamp(1.9rem, 3vw, 2.5rem); font-weight: 800; }
.normas-lead { color: rgba(242, 245, 255, 0.9); font-size: 1.05rem; }
.normas-subtitle { color: #fff; font-weight: 800; font-size: 1.2rem; }

.normas-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 0.75rem;
}

.normas-item {
  display: grid;
  grid-template-columns: 22px 1fr;
  gap: 0.7rem;
  align-items: start;
  padding: 0.75rem 0.8rem;
  border-radius: 12px;
  background: rgba(63, 77, 123, 0.45);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #eef2ff;
}
.normas-item i { color: #6ee7b7; margin-top: 2px; }

.normas-alert {
  display: grid;
  grid-template-columns: 22px 1fr;
  gap: 0.65rem;
  padding: 0.9rem;
  border-radius: 12px;
  background: rgba(255, 114, 79, 0.16);
  border: 1px solid rgba(255, 158, 123, 0.45);
  color: #fff3ed;
}
.normas-alert i { color: #ffb58b; margin-top: 2px; }

.normas-mini {
  color: #eef2ff;
  background: rgba(63, 77, 123, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 0.65rem 0.75rem;
  margin-bottom: 0.55rem;
}
.normas-mini:last-child { margin-bottom: 0; }

.normas-cta { text-decoration: none; }

.slide-in { animation: slideIn 0.35s ease both; }
.slide-in--delay { animation-delay: 0.05s; }
.slide-in--delay-2 { animation-delay: 0.1s; }

@keyframes slideIn {
  from { opacity: 0; transform: translateY(7px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 991.98px) { .normas-main { padding-top: 0.5rem; } }
</style>