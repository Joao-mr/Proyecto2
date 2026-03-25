<template>
  <div class="partidas-edit-page max-w-xl mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Editar Partida</h2>

    <div v-if="isLoading" class="mb-4">Cargando...</div>

    <form v-else @submit.prevent="submitEdit">
      <div class="mb-4">
        <label class="block font-medium mb-1">Sala</label>
        <select
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
        <label class="block font-medium mb-1">Fecha inicio</label>
        <input v-model="partida.fecha_inicio" type="datetime-local" class="w-full p-inputtext p-component" />
      </div>

      <div class="mb-4">
        <label class="block font-medium mb-1">Fecha fin</label>
        <input
          v-model="partida.fecha_fin"
          type="datetime-local"
          class="w-full p-inputtext p-component"
          :class="{ 'p-invalid': hasError('fecha_fin') }"
        />
        <small v-if="hasError('fecha_fin')" class="text-red-500">{{ getError('fecha_fin') }}</small>
      </div>

      <div class="flex gap-2 justify-end">
        <Button label="Cancelar" severity="secondary" @click="goBack" />
        <Button label="Guardar" type="submit" :loading="isLoading" :disabled="isLoading" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import usePartidas from '@/composables/partidas'

const router = useRouter()
const route = useRoute()

const {
  partida,
  salasDisponibles,
  isLoading,
  hasError,
  getError,
  getSalasDisponibles,
  getPartida,
  updatePartida
} = usePartidas()

onMounted(async () => {
  await getSalasDisponibles()
  await getPartida(route.params.id)
})

const submitEdit = async () => {
  const updated = await updatePartida()
  if (updated) goBack()
}

const goBack = () => {
  router.push({ name: 'partidas-juego.index' })
}
</script>