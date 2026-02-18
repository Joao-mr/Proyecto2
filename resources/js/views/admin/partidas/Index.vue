<template>
    <div class="partidas-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Partidas (Juego)</span>
                    <div class="flex items-center gap-2">
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
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Administra partidas por sala y horarios de inicio/fin.
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="partidas || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :filter-delay="300"
                    :global-filter-fields="['id', 'id_sala', 'sala.nombre', 'fecha_inicio', 'fecha_fin']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No se encontraron partidas</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable filter class="w-[80px]">
                        <template #body="slotProps">
                            <span class="table-cell-id">#{{ slotProps.data.id }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="ID" class="w-full" />
                        </template>
                    </Column>

                    <Column field="id_sala" header="Sala" sortable filter class="min-w-[220px]">
                        <template #body="slotProps">
                            <div class="flex flex-col gap-1">
                                <span class="table-cell-name">{{ slotProps.data.sala?.nombre || `Sala #${slotProps.data.id_sala}` }}</span>
                                <small class="text-gray-400">Código: {{ slotProps.data.sala?.codigo || '-' }}</small>
                            </div>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar sala" />
                        </template>
                    </Column>

                    <Column field="fecha_inicio" header="Fecha Inicio" sortable class="min-w-[180px]">
                        <template #body="slotProps">
                            <span class="text-sm table-cell-date">
                                <i class="pi pi-calendar mr-2 text-xs opacity-70"></i>
                                {{ formatDateTime(slotProps.data.fecha_inicio) }}
                            </span>
                        </template>
                    </Column>

                    <Column field="fecha_fin" header="Fecha Fin" sortable class="min-w-[180px]">
                        <template #body="slotProps">
                            <span class="text-sm table-cell-date">
                                <i class="pi pi-clock mr-2 text-xs opacity-70"></i>
                                {{ formatDateTime(slotProps.data.fecha_fin) }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Acciones" class="w-[160px]">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-tooltip.top="'Editar partida'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button
                                    v-tooltip.top="'Eliminar partida'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeletePartida(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog
            v-model:visible="partidaDialog.open"
            modal
            :header="partidaDialog.type === 'create' ? 'Crear Partida' : 'Editar Partida'"
            :style="{ width: '560px' }"
            class="partida-dialog"
        >
            <div class="flex flex-col gap-4">
                <div>
                    <label for="partida-sala" class="dialog-label">Sala</label>
                    <Select
                        v-model="partida.id_sala"
                        input-id="partida-sala"
                        :options="salasDisponibles"
                        option-label="nombre"
                        option-value="id"
                        placeholder="Selecciona una sala"
                        class="w-full"
                        :class="{ 'p-invalid': hasError('id_sala') }"
                    />
                    <small v-if="hasError('id_sala')" class="dialog-error">
                        {{ getError('id_sala') }}
                    </small>
                </div>

                <div>
                    <label for="partida-fecha-inicio" class="dialog-label">Fecha inicio</label>
                    <InputText
                        v-model="partida.fecha_inicio"
                        id="partida-fecha-inicio"
                        type="datetime-local"
                        class="w-full"
                        :class="{ 'p-invalid': hasError('fecha_inicio') }"
                    />
                    <small v-if="hasError('fecha_inicio')" class="dialog-error">
                        {{ getError('fecha_inicio') }}
                    </small>
                </div>

                <div>
                    <label for="partida-fecha-fin" class="dialog-label">Fecha fin</label>
                    <InputText
                        v-model="partida.fecha_fin"
                        id="partida-fecha-fin"
                        type="datetime-local"
                        class="w-full"
                        :class="{ 'p-invalid': hasError('fecha_fin') }"
                    />
                    <small v-if="hasError('fecha_fin')" class="dialog-error">
                        {{ getError('fecha_fin') }}
                    </small>
                </div>
            </div>

            <template #footer>
                <Button
                    severity="secondary"
                    label="Cancelar"
                    @click="closeDialog"
                    :disabled="isSubmitting"
                />
                <Button
                    v-if="partidaDialog.type === 'create'"
                    label="Crear"
                    @click="submitCreate"
                    :loading="isSubmitting"
                    :disabled="isSubmitting"
                />
                <Button
                    v-else
                    label="Guardar"
                    @click="submitUpdate"
                    :loading="isSubmitting"
                    :disabled="isSubmitting"
                />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, inject } from 'vue'
import usePartidas from '@/composables/partidas'
import { FilterMatchMode, FilterOperator } from '@primevue/core/api'

const {
    partidas,
    partida,
    salasDisponibles,
    getPartidas,
    getSalasDisponibles,
    createPartida,
    updatePartida,
    deletePartida,
    resetPartida,
    setPartida,
    hasError,
    getError,
    upsertPartidaRecord,
    isLoading
} = usePartidas()

const swal = inject('$swal')

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    id_sala: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] }
})

const partidaDialog = reactive({
    open: false,
    type: 'create'
})

const isSubmitting = computed(() => isLoading.value)

const openCreateDialog = () => {
    resetPartida()
    partidaDialog.type = 'create'
    partidaDialog.open = true
}

const openEditDialog = (currentPartida) => {
    setPartida(currentPartida)
    partidaDialog.type = 'edit'
    partidaDialog.open = true
}

const closeDialog = () => {
    partidaDialog.open = false
    resetPartida()
}

const submitCreate = () => {
    if (isSubmitting.value) return

    createPartida().then(createdPartida => {
        if (createdPartida) {
            upsertPartidaRecord(createdPartida)
            closeDialog()
        }
    })
}

const submitUpdate = () => {
    if (isSubmitting.value) return

    updatePartida().then(updatedPartida => {
        if (updatedPartida) {
            upsertPartidaRecord(updatedPartida)
            closeDialog()
        }
    })
}

const performDelete = (id) => {
    deletePartida(id)
}

const confirmDeletePartida = (currentPartida) => {
    if (!swal) {
        performDelete(currentPartida.id)
        return
    }

    swal({
        icon: 'warning',
        title: '¿Eliminar partida?',
        text: `La partida #${currentPartida.id} se eliminará de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(currentPartida.id)
        }
    })
}

const formatDateTime = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(async () => {
    await Promise.all([
        getSalasDisponibles(),
        getPartidas()
    ])
})
</script>
