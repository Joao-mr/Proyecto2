<template>
    <div class="categories-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Categorías (Juego)</span>
                    <div class="flex items-center gap-2">
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
                            @click="openCreateDialog"
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

                    <Column header="Salas vinculadas" class="min-w-[180px]">
                        <template #body="slotProps">
                            <Tag :value="String(slotProps.data.salas?.length ?? 0)" severity="info" />
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
                                    v-tooltip.top="'Editar categoría'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
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

        <Dialog
            v-model:visible="categoriaDialog.open"
            modal
            :header="categoriaDialog.type === 'create' ? 'Crear Categoría' : 'Editar Categoría'"
            :style="{ width: '500px' }"
            class="category-dialog"
        >
            <div class="flex flex-col gap-4">
                <div>
                    <label for="categoria-nombre" class="dialog-label">Nombre de la categoría</label>
                    <InputText
                        v-model="categoria.nombre"
                        id="categoria-nombre"
                        class="w-full"
                        :class="{ 'p-invalid': hasError('nombre') }"
                        placeholder="Ej: Famosos"
                    />
                    <small v-if="hasError('nombre')" class="dialog-error">
                        {{ getError('nombre') }}
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
                    v-if="categoriaDialog.type === 'create'"
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
import { ref, reactive, computed, onMounted, inject } from "vue";
import useCategorias from "@/composables/categorias";
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";

const { categorias, categoria, getCategorias, createCategoria, updateCategoria, deleteCategoria, resetCategoria, setCategoria, hasError, getError, upsertCategoriaRecord, isLoading } = useCategorias();

const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    nombre: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
});

const categoriaDialog = reactive({
    open: false,
    type: 'create'
});

const isSubmitting = computed(() => isLoading.value);

const openCreateDialog = () => {
    resetCategoria();
    categoriaDialog.type = 'create';
    categoriaDialog.open = true;
};

const openEditDialog = (currentCategoria) => {
    setCategoria(currentCategoria);
    categoriaDialog.type = 'edit';
    categoriaDialog.open = true;
};

const closeDialog = () => {
    categoriaDialog.open = false;
    resetCategoria();
};

const submitCreate = () => {
    if (isSubmitting.value) return;

    createCategoria()
        .then(createdCategoria => {
            if (createdCategoria) {
                upsertCategoriaRecord(createdCategoria);
                closeDialog();
            }
        });
};

const submitUpdate = () => {
    if (isSubmitting.value) return;

    updateCategoria()
        .then(updatedCategoria => {
            if (updatedCategoria) {
                upsertCategoriaRecord(updatedCategoria);
                closeDialog();
            }
        });
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
