<template>
  <section class="profile-surface p-3 p-md-4 h-100">
    <h3 class="h5 mb-3 text-white">{{ title }}</h3>

    <ul v-if="matches.length" class="list-unstyled mb-0">
      <li v-for="match in matches" :key="match.id_partida" class="profile-activity-item py-2">
        <div class="d-flex justify-content-between align-items-center gap-2">
          <div>
            <small class="profile-metric-label d-block">#{{ match.id_partida }}</small>
            <span class="text-white">{{ formatDate(match.fecha_inicio) }}</span>
          </div>
          <strong class="text-white">{{ match.puntuacion }} puntos</strong>
        </div>
      </li>
    </ul>

    <p v-else class="mb-0 text-white-50">{{ emptyLabel }}</p>
  </section>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  emptyLabel: {
    type: String,
    default: ''
  },
  matches: {
    type: Array,
    default: () => []
  }
})

const formatDate = (value) => {
  if (!value) return '-'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return '-'
  return parsed.toLocaleString('es-ES')
}
</script>
