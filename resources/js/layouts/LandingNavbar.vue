<template>
    <nav 
        class="navbar navbar-expand-lg fixed-top border-bottom"
        :class="isDarkTheme ? 'navbar-dark bg-dark' : 'navbar-light bg-white'">
        <div class="container">
            <!-- Logo -->
            <router-link to="/" class="navbar-brand d-flex align-items-center gap-2">
                <img src="/images/logo.svg" alt="logo" style="height: 40px; width: auto;"/>
            </router-link>

            <!-- Mobile Toggle -->
            <button
                v-if="!isDesktop"
                @click="visibleMobileMenu = true"
                class="btn btn-outline-secondary">
                <i class="pi pi-bars"></i>
            </button>

            <!-- Desktop Menu -->
            <div v-if="isDesktop" class="d-flex align-items-center gap-4">
                <router-link 
                    v-for="link in navLinks" 
                    :key="link.route" 
                    :to="link.route" 
                    class="nav-link fw-medium"
                >
                    {{ link.label }}
                </router-link>
                
                <!-- Actions -->
                <div class="d-flex align-items-center gap-2 ps-3 border-start">
                    <LocaleSwitcher />
                    
                    <button 
                        type="button" 
                        @click="toggleDarkMode"
                        class="btn btn-outline-secondary btn-sm">
                        <i :class="isDarkTheme ? 'pi pi-moon' : 'pi pi-sun'"></i>
                    </button>

                    <template v-if="!authStore().user?.name">
                        <router-link :to="{ name: 'auth.login' }">
                            <Button label="Login" text size="small" />
                        </router-link>
                        <router-link :to="{ name: 'auth.register' }">
                            <Button label="Registro" severity="primary" size="small" />
                        </router-link>
                    </template>

                    <div v-else>
                        <button 
                            type="button" 
                            @click="toggle"
                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                            <Avatar :image="authStore().user.avatar" :label="authStore().user.name[0]" shape="circle" size="small" />
                            <span class="small fw-medium d-none d-xl-inline">{{ authStore().user?.name }}</span>
                            <i class="pi pi-chevron-down" style="font-size: 0.75rem;"></i>
                        </button>
                        <Menu ref="menu" :model="items" popup />
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Offcanvas -->
    <div v-if="visibleMobileMenu" class="d-lg-none">
        <!-- Backdrop -->
        <div class="position-fixed top-0 start-0 w-100 h-100" style="z-index: 1040; background: rgba(0,0,0,0.5);" @click="visibleMobileMenu = false"></div>
        
        <!-- Panel -->
        <div 
            class="position-fixed top-0 end-0 h-100 shadow"
            :class="isDarkTheme ? 'bg-dark text-white' : 'bg-white'"
            style="z-index: 1050; width: min(100%, 320px);"
            @click.stop>
            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <img src="/images/logo.svg" alt="logo" style="height: 32px;"/>
                    <span class="fw-bold fs-5">Menu</span>
                </div>
                <button 
                    @click="visibleMobileMenu = false"
                    class="btn btn-outline-secondary btn-sm">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="d-flex flex-column gap-3 p-3 overflow-y-auto" style="height: calc(100% - 5rem);">
                <!-- Nav Links -->
                <div class="d-flex flex-column gap-1">
                    <router-link 
                        v-for="link in navLinks"
                        :key="link.route"
                        :to="link.route" 
                        @click="visibleMobileMenu = false"
                        class="d-flex align-items-center gap-2 p-2 rounded text-decoration-none"
                        :class="isDarkTheme ? 'text-white' : 'text-dark'">
                        <i :class="link.icon"></i>
                        <span>{{ link.label }}</span>
                    </router-link>
                </div>

                <hr class="my-1"/>

                <!-- Auth -->
                <div class="d-flex flex-column gap-2">
                    <template v-if="!authStore().user?.name">
                        <router-link :to="{ name: 'auth.login' }" @click="visibleMobileMenu = false">
                            <Button label="Iniciar Sesión" outlined class="w-100" />
                        </router-link>
                        <router-link :to="{ name: 'auth.register' }" @click="visibleMobileMenu = false">
                            <Button label="Registrarse" class="w-100" />
                        </router-link>
                    </template>
                    <template v-else>
                        <div class="p-3 rounded" :class="isDarkTheme ? 'bg-secondary' : 'bg-light'">
                            <div class="fw-medium">{{ authStore().user.name }}</div>
                            <div class="small text-muted">{{ authStore().user.email }}</div>
                        </div>
                        <Button label="Ir al Dashboard" icon="pi pi-th-large" outlined @click="navigateToDashboard" />
                        <Button label="Cerrar Sesión" icon="pi pi-power-off" severity="danger" text @click="handleLogout" />
                    </template>
                </div>
                
                <!-- Theme Toggle -->
                <div 
                    class="mt-auto d-flex align-items-center justify-content-between p-2 rounded"
                    :class="isDarkTheme ? 'bg-secondary' : 'bg-light'">
                    <span class="small fw-medium">Tema</span>
                    <button 
                        @click="toggleDarkMode"
                        class="btn btn-sm"
                        :class="isDarkTheme ? 'btn-outline-light' : 'btn-outline-secondary'">
                        <i :class="isDarkTheme ? 'pi pi-moon' : 'pi pi-sun'" class="pi"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Spacer for fixed navbar -->
    <div style="height: 80px;"></div>
</template>

<script setup>
import { useLayout } from "@/composables/layout.js";
import useAuth from "@/composables/auth";
import { authStore } from "../store/auth";
import LocaleSwitcher from "../components/LocaleSwitcher.vue";
import { ref, computed, onBeforeMount, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const menu = ref();
const visibleMobileMenu = ref(false);
const isScrolled = ref(false);
const isDesktop = ref(window.innerWidth >= 992);

const { processing, logout } = useAuth();
const { toggleDarkMode, isDarkTheme, setDefaultMode } = useLayout();

const navLinks = [
    { label: 'Inicio', route: '/', icon: 'pi pi-home' }
];

const items = computed(() => [
    {
        items: [
            { label: 'Perfil', icon: 'pi pi-user', command: () => router.push('/app/profile') },
            { 
                label: 'Panel Admin', 
                icon: 'pi pi-cog', 
                route: '/admin', 
                visible: authStore().user?.roles?.some(r => r.name.includes('admin')) || false
            },
            { label: 'Mi Panel', icon: 'pi pi-th-large', route: '/app' },
            { separator: true },
            {
                label: 'Cerrar sesión',
                icon: 'pi pi-power-off',
                class: 'text-red-500',
                command: () => {
                    handleLogout()
                }
            }
        ]
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const navigateToDashboard = () => {
    visibleMobileMenu.value = false;
    router.push('/app');
}

const handleLogout = () => {
    visibleMobileMenu.value = false;
    logout();
}

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
}

const handleResize = () => {
    isDesktop.value = window.innerWidth >= 992;
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('resize', handleResize);
});

onBeforeMount(() => {
    setDefaultMode()
})
</script>

