<template>
    <MainLayout :menuItems="items" />
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MainLayout from './MainLayout.vue';

const route = useRoute();
const router = useRouter();

const ensureProfileRoute = () => {
    if (route.path === '/app') {
        router.replace('/app/profile');
    }
};

onMounted(() => {
    ensureProfileRoute();
});

watch(
    () => route.path,
    () => {
        ensureProfileRoute();
    }
);

const items = ref([
   
    {
        label: 'Contenido',
        items: [
            {
                label: 'Posts',
                icon: 'pi pi-th-large',
                route: '/app/posts'
            },
        ]
    },
    {
        label: 'Cuenta',
        items: [
            {
                label: 'Perfil',
                icon: 'pi pi-user',
                route: '/app/profile'
            }
        ]
    }
]);
</script>
