<template>
  <div class="partidas-create-page container py-4">
    <h2 class="fs-4 fw-bold mb-4">Crear Partida</h2>

    <div v-if="isLoading" class="mb-4">Cargando...</div>

    <form v-else @submit.prevent="submitCreate">
      <div class="mb-4">
        <label for="partida-sala" class="block font-medium mb-1">Sala</label>
        <select
          id="partida-sala"
          v-model.number="partida.id_sala"
          class="w-full p-inputtext p-component"
          :class="{ 'p-invalid': hasError('id_sala') }"
        >
          <option :value="null">Selecciona una sala</option>
          <option v-for="s in salasDisponibles" :key="s.id" :value="s.id">
            {{ s.nombre }} ({{ s.codigo }})
          </option>
        </select>
        <small v-if="hasError('id_sala')" class="text-red-500">{{ getError('id_sala') }}</small>
      </div>

      <div class="mb-4">
        <label for="partida-inicio" class="block font-medium mb-1">Fecha inicio</label>
        <input
          id="partida-inicio"
          v-model="partida.fecha_inicio"
          type="datetime-local"
          class="w-full p-inputtext p-component"
        />
      </div>

      <div class="mb-4">
        <label for="partida-fin" class="block font-medium mb-1">Fecha fin</label>
        <input
          id="partida-fin"
          v-model="partida.fecha_fin"
          type="datetime-local"
          class="w-full p-inputtext p-component"
          :class="{ 'p-invalid': hasError('fecha_fin') }"
        />
        <small v-if="hasError('fecha_fin')" class="text-red-500">{{ getError('fecha_fin') }}</small>
      </div>

      <div class="d-flex gap-2 justify-content-end">
        <Button label="Cancelar" severity="secondary" @click="goBack" />
        <Button label="Crear" type="submit" :loading="isSubmitting" :disabled="isSubmitting" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import usePartidas from '@/composables/partidas'

const router = useRouter()

const {
  partida,
  salasDisponibles,
  createPartida,
  resetPartida,
  getSalasDisponibles,
  hasError,
  getError,
  isLoading
} = usePartidas()

const isSubmitting = computed(() => isLoading.value)

onMounted(async () => {
  resetPartida()
  await getSalasDisponibles()
})

const submitCreate = async () => {
  if (isSubmitting.value) return
  const created = await createPartida()
  if (created) router.push('/admin/partidas')
}

const goBack = () => {
  router.push('/admin/partidas')
}
</script>