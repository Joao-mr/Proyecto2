<template>
  <section class="profile-surface p-3 p-md-4 h-100">
    <h3 class="h5 mb-3 text-white">{{ title }}</h3>

    <div class="row g-3 mb-3">
      <div class="col-6">
        <span class="profile-metric-label d-block">{{ averageLabel }}</span>
        <strong class="profile-metric-value">{{ resumen.promedio_puntos }}</strong>
      </div>
      <div class="col-6">
        <span class="profile-metric-label d-block">{{ bestLabel }}</span>
        <strong class="profile-metric-value">{{ resumen.mejor_puntuacion }}</strong>
      </div>
      <div class="col-6">
        <span class="profile-metric-label d-block">{{ lastLabel }}</span>
        <strong class="profile-metric-value">{{ resumen.ultima_puntuacion }}</strong>
      </div>
      <div class="col-6">
        <span class="profile-metric-label d-block">{{ consistencyLabel }}</span>
        <strong class="profile-metric-value">{{ resumen.consistencia_pct }}%</strong>
      </div>
    </div>

    <div class="profile-progress">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="profile-metric-label">{{ progressLabel }}</span>
        <strong class="text-white">{{ resumen.progreso_siguiente_titulo_pct }}%</strong>
      </div>
      <ProgressBar :value="resumen.progreso_siguiente_titulo_pct" />
    </div>
  </section>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  averageLabel: {
    type: String,
    default: ''
  },
  bestLabel: {
    type: String,
    default: ''
  },
  lastLabel: {
    type: String,
    default: ''
  },
  consistencyLabel: {
    type: String,
    default: ''
  },
  progressLabel: {
    type: String,
    default: ''
  },
  resumen: {
    type: Object,
    default: () => ({
      promedio_puntos: 0,
      mejor_puntuacion: 0,
      ultima_puntuacion: 0,
      consistencia_pct: 0,
      progreso_siguiente_titulo_pct: 0
    })
  }
})
</script>
