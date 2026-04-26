<template>
    <div class="categories-page">
        <Card>
            <template #title>
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>Gestión de Categorías (Juego)</span>
                    <div class="d-flex align-items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getCategorias"
                        />
                        <Button
                            label="Nueva Categoría"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="goToCreateCategoria"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Administra las categorías de tu juego (famosos, cantantes, películas, etc.).
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="categorias || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :filter-delay="300"
                    :global-filter-fields="['id', 'nombre', 'created_at']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No se encontraron categorías</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable filter style="width: 80px;">
                        <template #body="slotProps">
                            <span class="table-cell-id">#{{ slotProps.data.id }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="ID" class="w-100" />
                        </template>
                    </Column>

                    <Column field="nombre" header="Nombre" sortable filter style="min-width: 220px;">
                        <template #body="slotProps">
                            <span class="table-cell-name">{{ slotProps.data.nombre || '-' }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar por nombre" />
                        </template>
                    </Column>

                    <Column header="Salas vinculadas" style="min-width: 180px;">
                        <template #body="slotProps">
                            <Tag :value="String(slotProps.data.salas?.length ?? 0)" severity="info" />
                        </template>
                    </Column>

                    <Column field="created_at" header="Fecha de Creación" sortable style="min-width: 180px;">
                        <template #body="slotProps">
                            <span class="small table-cell-date">
                                <i class="pi pi-calendar me-2" style="font-size: 0.7rem; opacity: 0.7;"></i>
                                {{ formatDate(slotProps.data.created_at) }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Acciones" style="width: 160px;">
                        <template #body="slotProps">
                            <div class="d-flex gap-2">
                                <Button
                                    v-tooltip.top="'Editar categoría'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="goToEditCategoria(slotProps.data.id)"
                                />
                                <Button
                                    v-tooltip.top="'Eliminar categoría'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteCategoria(slotProps.data)"
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
import { ref, onMounted, inject } from "vue";
import useCategorias from "@/composables/categorias";
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";
import { useRouter } from 'vue-router';

const { categorias, getCategorias, deleteCategoria, isLoading } = useCategorias();

const swal = inject('$swal');
const router = useRouter();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    nombre: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
});

const goToCreateCategoria = () => {
    router.push('/admin/categorias/create');
};

const goToEditCategoria = (id) => {
    router.push(`/admin/categorias/edit/${id}`);
};

const performDelete = (id) => {
    deleteCategoria(id);
};

const confirmDeleteCategoria = (currentCategoria) => {
    if (!swal) {
        performDelete(currentCategoria.id);
        return;
    }

    swal({
        icon: 'warning',
        title: '¿Eliminar categoría?',
        text: `La categoría "${currentCategoria.nombre}" se eliminará de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(currentCategoria.id);
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

onMounted(() => {
    getCategorias();
});
</script>
