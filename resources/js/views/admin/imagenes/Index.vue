<template>
    <div class="imagenes-page">
        <Card>
            <template #title>
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>Gesti+¦n de Im+ígenes (Juego)</span>
                    <div class="d-flex align-items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getImagenes"
                        />
                        <Button
                            label="Nueva Imagen"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Administra las im+ígenes y su respuesta correcta.
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="imagenes || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :filter-delay="300"
                    :global-filter-fields="['id', 'respuesta_correcta', 'created_at']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No se encontraron im+ígenes</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable filter class="w-[80px]">
                        <template #body="slotProps">
                            <span class="table-cell-id">#{{ slotProps.data.id }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="ID" class="w-100" />
                        </template>
                    </Column>

                    <Column header="Imagen" class="w-[120px]">
                        <template #body="slotProps">
                            <img
                                v-if="slotProps.data.urls?.thumb || slotProps.data.urls?.original"
                                :src="slotProps.data.urls.thumb || slotProps.data.urls.original"
                                :alt="`Imagen #${slotProps.data.id}`"
                                class="rounded border object-fit-cover" style="width: 64px; height: 64px;"
                            />
                            <div v-else class="d-flex align-items-center justify-content-center rounded border bg-light" style="width: 64px; height: 64px;">
                                <i class="pi pi-image text-gray-400 text-xl"></i>
                            </div>
                        </template>
                    </Column>

                    <Column field="respuesta_correcta" header="Respuesta Correcta" sortable filter class="" style="min-width: 220px;">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.respuesta_correcta || '-'" severity="info" />
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar respuesta" />
                        </template>
                    </Column>

                    <Column field="categoria_nombre" header="Categoría" sortable class="" style="min-width: 160px;">
                        <template #body="slotProps">
                            <Tag v-if="slotProps.data.categoria_nombre" :value="slotProps.data.categoria_nombre" severity="secondary" />
                            <span v-else class="text-sm opacity-50">Sin categoría</span>
                        </template>
                    </Column>

                    <Column field="created_at" header="Fecha de Creaci+¦n" sortable class="" style="min-width: 180px;">
                        <template #body="slotProps">
                            <span class="text-sm table-cell-date">
                                <i class="pi pi-calendar mr-2 text-xs opacity-70"></i>
                                {{ formatDate(slotProps.data.created_at) }}
                            </span>
                        </template>
                    </Column>

                    <Column header="Acciones" class="w-[160px]">
                        <template #body="slotProps">
                            <div class="d-flex gap-2">
                                <Button
                                    v-tooltip.top="'Editar imagen'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button
                                    v-tooltip.top="'Eliminar imagen'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteImagen(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog
            v-model:visible="imagenDialog.open"
            modal
            :header="imagenDialog.type === 'create' ? 'Crear Imagen' : 'Editar Imagen'"
            :style="{ width: '600px' }"
            class="imagen-dialog"
        >
            <div class="vstack gap-3">
                <div>
                    <label for="imagen-respuesta" class="dialog-label">Respuesta correcta</label>
                    <InputText
                        v-model="imagen.respuesta_correcta"
                        id="imagen-respuesta"
                        class="w-100"
                        :class="{ 'p-invalid': hasError('respuesta_correcta') }"
                        placeholder="Ej: Cristiano Ronaldo"
                    />
                    <small v-if="hasError('respuesta_correcta')" class="dialog-error">
                        {{ getError('respuesta_correcta') }}
                    </small>
                </div>
                <div>
                    <label for="imagen-categoria" class="dialog-label">Categoría</label>
                    <select
                        v-model.number="imagen.categoria_id"
                        id="imagen-categoria"
                        class="w-full border rounded px-3 py-2 text-sm"
                    >
                        <option :value="null">Sin categoría</option>
                        <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                            {{ cat.nombre }}
                        </option>
                    </select>
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
                    v-if="imagenDialog.type === 'create'"
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
import { ref, reactive, computed, onMounted, inject } from 'vue';
import { useRouter } from 'vue-router';
import useImagen from '@/composables/useImagen';
import useCategorias from '@/composables/categorias';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';

const {
    imagenes,
    imagen,
    getImagenes,
    createImagen,
    updateImagen,
    deleteImagen,
    resetImagen,
    setImagen,
    hasError,
    getError,
    upsertImagenRecord,
    isLoading
} = useImagen();

const { categorias, getCategorias } = useCategorias();

const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    respuesta_correcta: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const imagenDialog = reactive({
    open: false,
    type: 'create'
});

const isSubmitting = computed(() => isLoading.value);

const router = useRouter();

const openCreateDialog = () => {
    router.push({ name: 'imagenes-juego.upload' });
};

const openEditDialog = (currentImagen) => {
    setImagen(currentImagen);
    imagenDialog.type = 'edit';
    imagenDialog.open = true;
};

const closeDialog = () => {
    imagenDialog.open = false;
    resetImagen();
};

const submitCreate = () => {
    if (isSubmitting.value) return;

    createImagen()
        .then(createdImagen => {
            if (createdImagen) {
                upsertImagenRecord(createdImagen);
                closeDialog();
            }
        });
};

const submitUpdate = () => {
    if (isSubmitting.value) return;

    updateImagen()
        .then(updatedImagen => {
            if (updatedImagen) {
                upsertImagenRecord(updatedImagen);
                closeDialog();
            }
        });
};

const performDelete = (id) => {
    deleteImagen(id);
};

const confirmDeleteImagen = (currentImagen) => {
    if (!swal) {
        performDelete(currentImagen.id);
        return;
    }

    swal({
        icon: 'warning',
        title: '-+Eliminar imagen?',
        text: `La imagen #${currentImagen.id} se eliminar+í de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'S+¡, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(currentImagen.id);
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
    getImagenes();
    getCategorias();
});
</script>

