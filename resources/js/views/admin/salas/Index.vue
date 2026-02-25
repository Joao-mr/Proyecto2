<template>
    <div class="salas-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Salas (Juego)</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getSalas"
                        />
                        <Button
                            label="Nueva Sala"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Administra las salas del juego y sus categorías asignadas.
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="salas || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :filter-delay="300"
                    :global-filter-fields="['id', 'nombre', 'codigo', 'created_at']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No se encontraron salas</p>
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

                    <Column field="nombre" header="Nombre" sortable filter class="min-w-[220px]">
                        <template #body="slotProps">
                            <span class="table-cell-name">{{ slotProps.data.nombre || '-' }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
                        </template>
                    </Column>

                    <Column field="codigo" header="Código" sortable filter class="min-w-[160px]">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.codigo || '-'" severity="contrast" />
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar por código" />
                        </template>
                    </Column>

                    <Column header="Categorías" class="min-w-[240px]">
                        <template #body="slotProps">
                            <div class="flex flex-wrap gap-1">
                                <Tag
                                    v-for="categoria in (slotProps.data.categorias || [])"
                                    :key="`${slotProps.data.id}-${categoria.id}`"
                                    :value="categoria.nombre"
                                    severity="info"
                                />
                                <span v-if="!(slotProps.data.categorias || []).length" class="text-gray-400 text-sm">Sin categorías</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="created_at" header="Fecha de Creación" sortable class="min-w-[180px]">
                        <template #body="slotProps">
                            <span class="text-sm table-cell-date">
                                <i class="pi pi-calendar mr-2 text-xs opacity-70"></i>
                                {{ formatDate(slotProps.data.created_at) }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Acciones" class="w-[160px]">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-tooltip.top="'Editar sala'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="goToEditSala(slotProps.data.id)"
                                />
                                <Button
                                    v-tooltip.top="'Eliminar sala'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteSala(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <!-- Diálogo eliminado: edición y creación ahora son navegación clásica -->
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
const router = useRouter();

const goToEditSala = (id) => {
    router.push(`/admin/salas/edit/${id}`);
};
import { ref, reactive, computed, onMounted, inject } from "vue";
import useSalas from "@/composables/salas";
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";

const {
    salas,
    sala,
    categoriasDisponibles,
    getSalas,
    getCategoriasDisponibles,
    createSala,
    updateSala,
    deleteSala,
    resetSala,
    setSala,
    hasError,
    getError,
    upsertSalaRecord,
    isLoading
} = useSalas();

const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    nombre: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    codigo: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
});

const salaDialog = reactive({
    open: false,
    type: 'create'
});

const isSubmitting = computed(() => isLoading.value);

const openCreateDialog = () => {
    resetSala();
    salaDialog.type = 'create';
    salaDialog.open = true;
};

const openEditDialog = (currentSala) => {
    setSala(currentSala);
    salaDialog.type = 'edit';
    salaDialog.open = true;
};

const closeDialog = () => {
    salaDialog.open = false;
    resetSala();
};

const submitCreate = () => {
    if (isSubmitting.value) return;

    createSala()
        .then(createdSala => {
            if (createdSala) {
                upsertSalaRecord(createdSala);
                closeDialog();
            }
        });
};

const submitUpdate = () => {
    if (isSubmitting.value) return;

    updateSala()
        .then(updatedSala => {
            if (updatedSala) {
                upsertSalaRecord(updatedSala);
                closeDialog();
            }
        });
};

const performDelete = (id) => {
    deleteSala(id);
};

const confirmDeleteSala = (currentSala) => {
    if (!swal) {
        performDelete(currentSala.id);
        return;
    }

    swal({
        icon: 'warning',
        title: '¿Eliminar sala?',
        text: `La sala "${currentSala.nombre}" se eliminará de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(currentSala.id);
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(async () => {
    await Promise.all([
        getCategoriasDisponibles(),
        getSalas()
    ]);
});
</script>
