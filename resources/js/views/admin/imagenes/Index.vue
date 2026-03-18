<template>
    <div class="imagenes-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Imágenes (Juego)</span>
                    <div class="flex items-center gap-2">
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
                Administra las imágenes y su respuesta correcta.
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
                    :global-filter-fields="['id', 'url', 'respuesta_correcta', 'created_at']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No se encontraron imágenes</p>
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

                    <Column field="url" header="URL" sortable filter class="min-w-[320px]">
                        <template #body="slotProps">
                            <a
                                :href="slotProps.data.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-blue-600 hover:underline break-all"
                            >
                                {{ slotProps.data.url || '-' }}
                            </a>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar por URL" />
                        </template>
                    </Column>

                    <Column field="respuesta_correcta" header="Respuesta Correcta" sortable filter class="min-w-[220px]">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.respuesta_correcta || '-'" severity="info" />
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" type="text" placeholder="Buscar respuesta" />
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
            <div class="flex flex-col gap-4">
                <div>
                    <label for="imagen-url" class="dialog-label">URL de la imagen</label>
                    <InputText
                        v-model="imagen.url"
                        id="imagen-url"
                        class="w-full"
                        :class="{ 'p-invalid': hasError('url') }"
                        placeholder="Ej: https://example.com/imagen.jpg"
                    />
                    <small v-if="hasError('url')" class="dialog-error">
                        {{ getError('url') }}
                    </small>
                </div>

                <div>
                    <label for="imagen-respuesta" class="dialog-label">Respuesta correcta</label>
                    <InputText
                        v-model="imagen.respuesta_correcta"
                        id="imagen-respuesta"
                        class="w-full"
                        :class="{ 'p-invalid': hasError('respuesta_correcta') }"
                        placeholder="Ej: Cristiano Ronaldo"
                    />
                    <small v-if="hasError('respuesta_correcta')" class="dialog-error">
                        {{ getError('respuesta_correcta') }}
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
import useImagenes from '@/composables/imagenes';
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
} = useImagenes();

const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    url: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
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
        title: '¿Eliminar imagen?',
        text: `La imagen #${currentImagen.id} se eliminará de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
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
});
</script>
