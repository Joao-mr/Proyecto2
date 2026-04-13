<template>
    <div class="row g-4">
        <!-- Avatar Section -->
        <div class="col-12 col-md-4 col-lg-3">
            <Card>
                <template #title>Avatar</template>
                <template #content>
                    <div class="d-flex flex-column align-items-center">
                        <!-- File Upload -->
                        <FileUpload
                            name="picture"
                            url="/api/users/updateimg"
                            @before-upload="onBeforeUpload"
                            @upload="onTemplatedUpload($event)"
                            accept="image/*"
                            :maxFileSize="1500000"
                            @select="onSelectedFiles"
                            mode="basic"
                            :auto="true"
                            chooseLabel="Cambiar Avatar"
                            class="w-100"
                        />
                        
                        <div class="mt-4 w-100 d-flex justify-content-center">
                            <Avatar 
                                :image="user.avatar || 'https://bootdey.com/img/Content/avatar/avatar7.png'" 
                                style="width: 8rem; height: 8rem;" 
                                size="xlarge" 
                                shape="circle"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Personal Data Section -->
        <div class="col-12 col-md-8 col-lg-9">
            <Card>
                <template #title>Datos Personales</template>
                <template #content>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="fw-bold d-block mb-2">Nombre</label>
                            <div class="p-3 bg-light rounded border">
                                {{ user.name }}
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-6">
                            <label class="fw-bold d-block mb-2">Email</label>
                            <div class="p-3 bg-light rounded border">
                                {{ user.email }}
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="fw-bold d-block mb-2">Primer Apellido</label>
                            <div class="p-3 bg-light rounded border">
                                {{ user.surname1 || '-' }}
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="fw-bold d-block mb-2">Segundo Apellido</label>
                            <div class="p-3 bg-light rounded border">
                                {{ user.surname2 || '-' }}
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { usePrimeVue } from 'primevue/config';
import useUsers from "@/composables/users";
import { authStore } from "@/store/auth";

const auth = authStore();
const $primevue = usePrimeVue();
const { getUser, user } = useUsers();

onMounted(() => {
    getUser(auth.user.id)
})

const onBeforeUpload = (event) => {
    event.formData.append('id', user.value.id)
};

const onTemplatedUpload = (event) => {
    // Recargar usuario para actualizar avatar
    getUser(auth.user.id);
};

const onSelectedFiles = (event) => {
    // Lógica adicional si es necesaria
};
</script>
