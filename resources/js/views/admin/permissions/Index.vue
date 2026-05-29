<template>
    <div class="permissions-page">
        <Card>
            <template #title>
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span>Gestión de Permisos</span>
                    <div class="d-flex align-items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getPermissions"
                        />
                        <Button
                            v-if="can('permisos-crear')"
                            label="Nuevo Permiso"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Administra y gestiona los permisos del sistema. Crea, edita y elimina permisos según tus permisos.
            </template>

            <template #content>
                <div v-if="isLoading" class="table-loading-skeleton space-y-3">
                    <div
                        v-for="row in skeletonRows"
                        :key="row"
                        class="d-flex gap-3 align-items-center"
                    >
                        <Skeleton width="60px" height="1.25rem" />
                        <Skeleton width="200px" height="1.25rem" />
                        <Skeleton width="140px" height="1.25rem" />
                        <div class="d-flex gap-2 ms-auto">
                            <Skeleton width="2.5rem" height="2.5rem" shape="circle" />
                            <Skeleton width="2.5rem" height="2.5rem" shape="circle" />
                        </div>
                    </div>
                </div>
                <DataTable
                    v-else
                    v-model:filters="filters"
                    :value="permissions || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :filter-delay="300"
                    :global-filter-fields="['id', 'name', 'created_at']"
                >
                    <template #empty>
                        <div class="table-empty-state">
                            <i class="pi pi-inbox empty-state-icon"></i>
                            <p class="empty-state-text">No se encontraron permisos</p>
                            <p class="empty-state-subtext">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable filter class="w-[80px]">
                        <template #body="slotProps">
                            <Skeleton v-if="isLoading" width="3rem" height="1rem" />
                            <span v-else class="table-cell-id">#{{ slotProps.data.id }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="ID" class="w-100" />
                        </template>
                    </Column>

                    <Column field="name" header="Nombre" sortable filter class="" style="min-width: 200px;">
                        <template #body="slotProps">
                            <Skeleton v-if="isLoading" width="10rem" height="1rem" />
                            <span v-else class="table-cell-name">{{ slotProps.data.name || '-' }}</span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="Nombre" class="w-100" />
                        </template>
                    </Column>

                    <Column field="created_at" header="Fecha de Creación" sortable class="" style="min-width: 170px;">
                        <template #body="slotProps">
                            <Skeleton v-if="isLoading" width="8rem" height="1rem" />
                            <span v-else class="text-sm table-cell-date">
                                <i class="pi pi-calendar mr-2 text-xs opacity-70"></i>
                                {{ formatDate(slotProps.data.created_at) }}
                            </span>
                        </template>
                        <template #filter="{ filterModel }">
                            <InputText v-model="filterModel.value" placeholder="Nombre" class="w-100" />
                        </template>
                    </Column>

                    <Column header="Acciones" class="w-[150px]">
                        <template #body="slotProps">
                            <Skeleton v-if="isLoading" width="4rem" height="2rem" />
                            <div v-else class="d-flex gap-2">
                                <Button
                                    v-if="can('permisos-editar')"
                                    v-tooltip.top="'Editar permiso'"
                                    icon="pi pi-pencil"
                                    rounded
                                    text
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button
                                    v-if="can('permisos-eliminar')"
                                    v-tooltip.top="'Eliminar permiso'"
                                    icon="pi pi-trash"
                                    rounded
                                    text
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeletePermission(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog
            v-model:visible="permissionDialog.open"
            modal
            :header="permissionDialog.type === 'create' ? 'Crear Permiso' : 'Editar Permiso'"
            :style="{ width: '400px' }"
            class="permission-dialog"
        >
            <div class="vstack gap-3">
                <div>
                    <label for="permission-name" class="dialog-label">Nombre del permiso</label>
                    <InputText
                        id="permission-name"
                        v-model="permission.name"
                        placeholder="Nombre"
                        class="w-100"
                        :class="{ 'p-invalid': hasError('name') }"
                    />
                    <small v-if="hasError('name')" class="dialog-error">
                        {{ getError('name') }}
                    </small>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Cancelar"
                    severity="secondary"
                    @click="closeDialog"
                    :disabled="isSubmitting"
                />
                <Button
                    v-if="permissionDialog.type === 'create'"
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
import usePermissions from "@/composables/permissions";
import { useAbility } from '@casl/vue';
import {FilterMatchMode, FilterOperator} from "@primevue/core/api";
import { useStoredTableFilters } from "@/composables/useStoredTableFilters";

const FILTERS_STORAGE_KEY = 'admin_permissions_table_filters';
const {permissions, permission, getPermissions, createPermission, updatePermission, deletePermission, resetPermission, setPermission, hasError, getError, upsertPermissionRecord, isLoading} = usePermissions();
const { can } = useAbility();

const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    id: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    created_at: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
});

const permissionDialog = reactive({
    open: false,
    type: 'create'
});

const isSubmitting = computed(() => isLoading.value);
const skeletonRows = Array.from({ length: 5 }, (_, index) => index);

const { restore: restoreFiltersFromStorage } = useStoredTableFilters(
    FILTERS_STORAGE_KEY,
    filters,
    (storedFilters) => {
        filters.value = {
            global: { ...filters.value.global, ...storedFilters.global },
            id: { ...filters.value.id, ...storedFilters.id },
            name: { ...filters.value.name, ...storedFilters.name },
            created_at: { ...filters.value.created_at, ...storedFilters.created_at }
        };
    }
);

const openCreateDialog = () => {
    resetPermission();
    permissionDialog.type = 'create';
    permissionDialog.open = true;
};

const openEditDialog = (currentPermission) => {
    setPermission(currentPermission);
    permissionDialog.type = 'edit';
    permissionDialog.open = true;
};

const closeDialog = () => {
    permissionDialog.open = false;
    resetPermission();
};

const submitCreate = async () => {
    if (isSubmitting.value) return;

    const createdPermission = await createPermission();
    if (!createdPermission) return;

    upsertPermissionRecord(createdPermission);
    closeDialog();
};

const submitUpdate = async () => {
    if (isSubmitting.value) return;

    const updatedPermission = await updatePermission();
    if (!updatedPermission) return;

    upsertPermissionRecord(updatedPermission);
    closeDialog();
};

const confirmDeletePermission = async (currentPermission) => {
    if (!swal) {
        await deletePermission(currentPermission.id);
        return;
    }

    const result = await swal({
        icon: 'warning',
        title: '¿Eliminar permiso?',
        text: `El permiso "${currentPermission.name}" se eliminará de forma permanente.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    });

    if (!result.isConfirmed) return;

    await deletePermission(currentPermission.id);
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
    restoreFiltersFromStorage();
    getPermissions();
});
</script>
