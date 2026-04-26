<template>
  <div class="partidas-page">
    <Card>
      <template #title>
        <div class="d-flex align-items-center justify-content-between w-100">
          <span>Gestión de Partidas</span>
          <div class="d-flex align-items-center gap-2">
            <Button
              label="Actualizar"
              icon="pi pi-refresh"
              size="small"
              outlined
              severity="secondary"
              :loading="isLoading"
              @click="getPartidas"
            />
            <Button
              label="Nueva Partida"
              icon="pi pi-plus"
              size="small"
              severity="primary"
              @click="goToCreatePartida"
            />
          </div>
        </div>
      </template>

      <template #content>
        <DataTable
          :value="partidas || []"
          data-key="id"
          :paginator="true"
          :rows="10"
          :rows-per-page-options="[10, 25, 50]"
          striped-rows
          size="small"
          :loading="isLoading"
        >
          <template #empty>
            <div class="table-empty-state">
              <i class="pi pi-inbox empty-state-icon"></i>
              <p class="empty-state-text">No se encontraron partidas</p>
            </div>
          </template>

          <Column field="id" header="ID" sortable class="w-[80px]" />
          <Column header="Sala" class="" style="min-width: 200px;">
            <template #body="slotProps">
              {{ slotProps.data.sala?.nombre || `Sala #${slotProps.data.id_sala}` }}
            </template>
          </Column>
          <Column field="fecha_inicio" header="Inicio" class="" style="min-width: 180px;">
            <template #body="slotProps">{{ formatDate(slotProps.data.fecha_inicio) }}</template>
          </Column>
          <Column field="fecha_fin" header="Fin" class="" style="min-width: 180px;">
            <template #body="slotProps">{{ formatDate(slotProps.data.fecha_fin) }}</template>
          </Column>

          <Column header="Acciones" class="w-[160px]">
            <template #body="slotProps">
              <div class="d-flex gap-2">
                <Button
                  icon="pi pi-pencil"
                  rounded
                  text
                  severity="secondary"
                  size="small"
                  @click="goToEditPartida(slotProps.data.id)"
                />
                <Button
                  icon="pi pi-trash"
                  rounded
                  text
                  severity="danger"
                  size="small"
                  @click="confirmDelete(slotProps.data.id)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import usePartidas from '@/composables/partidas'

const router = useRouter()

const {
  partidas,
  isLoading,
  getPartidas,
  getSalasDisponibles,
  deletePartida
} = usePartidas()

onMounted(async () => {
  await Promise.all([getPartidas(), getSalasDisponibles()])
})

const goToCreatePartida = () => {
  router.push({ name: 'partidas-juego.create' })
}

const goToEditPartida = (id) => {
  router.push({ name: 'partidas-juego.edit', params: { id } })
}

const confirmDelete = async (id) => {
  if (!window.confirm('¿Eliminar esta partida?')) return
  await deletePartida(id)
}

const formatDate = (value) =>
  value ? new Date(value).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' }) : '-'
</script>
