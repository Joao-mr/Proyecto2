<template>
    <Card>
        <template #title>
            <div class="d-flex align-items-center justify-content-between w-100">
                <span>Gestión de Usuarios</span>
                <div class="d-flex align-items-center gap-2">
                    <Button 
                        label="Actualizar" 
                        icon="pi pi-refresh" 
                        size="small" 
                        outlined 
                        severity="secondary" 
                        :loading="loading" 
                        @click="refreshUsers" 
                    />
                    <Button 
                        v-if="can('usuarios-crear')"
                        label="Nuevo Usuario" 
                        icon="pi pi-plus" 
                        size="small" 
                        severity="primary" 
                        @click="router.push('/admin/users/create')" 
                    />
                </div>
            </div>
        </template>

        <template #subtitle>Administra y gestiona los usuarios del sistema. Crea, edita y elimina usuarios según tus permisos.</template>

        <template #content>
            <DataTable
                v-model:filters="userFilters"
                :value="users.data || []"
                :paginator="true"
                :rows="10"
                :rows-per-page-options="[10, 25, 50]"
                data-key="id"
                striped-rows
                size="small"
                :loading="loading"
                filter-display="menu"
                :filter-delay="300"
                :global-filter-fields="['alias', 'name', 'surname1', 'surname2', 'email']"

            >
                <Column field="id" header="ID" sortable class="w-[60px]">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="3rem" height="1rem" />
                        <span v-else class="table-cell-id">{{ slotProps.data.id }}</span>
                    </template>
                </Column>

                <Column field="name" header="Nombre" sortable filter :filter-placeholder="'Nombre'" class="" style="min-width: 200px;">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="10rem" height="1rem" />
                        <div v-else class="d-flex align-items-center gap-2">
                            <i class="pi pi-user text-primary" />
                            <span class="fw-medium table-cell-name">{{ slotProps.data.name || '-' }}</span>
                        </div>
                    </template>
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" placeholder="Nombre" class="w-100" />
                    </template>
                </Column>

                <Column field="alias" header="Alias" sortable filter :filter-placeholder="'Alias'" class="" style="min-width: 150px;">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="8rem" height="1rem" />
                        <span v-else class="table-cell-name">{{ slotProps.data.alias || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" placeholder="Alias" class="w-100" />
                    </template>
                </Column>

                <Column field="email" header="Email" sortable filter :filter-placeholder="'Email'" class="" style="min-width: 200px;">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="12rem" height="1rem" />
                        <span v-else class="text-sm table-cell-email">{{ slotProps.data.email || '-' }}</span>
                    </template>
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" placeholder="Email" class="w-100" />
                    </template>
                </Column>

                <Column field="roles" header="Roles" class="" filter :filter-function="filterRoles" style="min-width: 200px;">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="6rem" height="1.5rem" />
                        <div v-else class="d-flex flex-wrap gap-1">
                            <Tag
                                v-for="role in slotProps.data.roles || []"
                                :key="role.id"
                                :value="role.name"
                                :severity="getRoleSeverity(role.name)"
                                size="small"
                            />
                            <Tag
                                v-if="!slotProps.data.roles?.length"
                                value="Sin roles"
                                severity="secondary"
                                size="small"
                            />
                        </div>
                    </template>
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" placeholder="Buscar por nombre de rol" class="w-100" />
                    </template>
                </Column>

                <Column field="created_at" header="Fecha de Creación" sortable class="" style="min-width: 150px;">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="8rem" height="1rem" />
                        <span v-else class="text-sm table-cell-date">{{ formatDate(slotProps.data.created_at) }}</span>
                    </template>
                </Column>

                <Column header="Acciones" class="w-[150px]">
                    <template #body="slotProps">
                        <Skeleton v-if="loading" width="4rem" height="2rem" />
                        <div v-else class="d-flex gap-2">
                            <Button
                                v-if="can('usuarios-editar')"
                                v-tooltip.top="'Editar usuario'"
                                icon="pi pi-pencil"
                                rounded
                                text
                                severity="secondary"
                                size="small"
                                @click="router.push({ name: 'users.edit', params: { id: slotProps.data.id } })"
                            />
                            <Button
                                v-if="can('usuarios-eliminar')"
                                v-tooltip.top="'Eliminar usuario'"
                                icon="pi pi-trash"
                                rounded
                                text
                                severity="danger"
                                size="small"
                                @click="deleteUser(slotProps.data.id, slotProps.index)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import useUsers from "../../../composables/users";
import { useAbility } from '@casl/vue';
import { FilterMatchMode, FilterOperator } from "@primevue/core/api";

const router = useRouter();
const { users, getUsers, deleteUser } = useUsers();
const { can } = useAbility();
const loading = ref(false);

const userFilters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    alias: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    email: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    roles: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },
    created_at: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.STARTS_WITH }] },

    
});

const refreshUsers = () => {
    loading.value = true;
    getUsers().finally(() => {
        loading.value = false;
    });
};

const getRoleSeverity = (roleName) => {
    const roleMap = {
        'admin': 'danger',
        'alumne': 'info',
        'user': 'secondary'
    };
    return roleMap[roleName?.toLowerCase()] || 'secondary';
};

const filterRoles = (value, filter) => {
    if (!filter) return true;
    if (!value || !Array.isArray(value)) return false;
    const filterValue = filter.toString().toLowerCase();
    return value.some(role => 
        role.name && role.name.toLowerCase().includes(filterValue)
    );
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
    loading.value = true;
    getUsers().finally(() => {
        loading.value = false;
    });
});
</script>
