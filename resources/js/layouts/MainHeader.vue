<template>
    <header class="tail-admin-header sticky-top z-999 d-flex w-100">
        <div class="d-flex flex-grow-1 align-items-center justify-content-between p-1 px-md-4">
            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <!-- Toggle Button - Mobile -->
                <button 
                    @click="emit('toggleSidebar')" 
                    class="d-flex d-lg-none align-items-center justify-content-center header-icon-button rounded"
                    style="width: 36px; height: 36px;"
                    aria-label="Toggle sidebar"
                >
                    <i class="pi pi-bars"></i>
                </button>

                <!-- Toggle Button - Desktop -->
                <button 
                    @click="emit('toggleCollapse')" 
                    class="d-none d-lg-flex align-items-center justify-content-center header-icon-button rounded"
                    style="width: 36px; height: 36px;"
                    :title="props.isCollapsed ? 'Expandir sidebar' : 'Colapsar sidebar'"
                    aria-label="Toggle sidebar"
                >
                    <i :class="props.isCollapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <ul class="list-unstyled d-flex align-items-center gap-2 mb-0">
                    <!-- Dark Mode Toggle -->
                    <li>
                        <button @click="toggleDarkMode" class="header-icon-button d-flex align-items-center justify-content-center rounded" style="width: 40px; height: 40px;" title="Cambiar tema">
                            <i :class="isDarkTheme ? 'pi pi-sun' : 'pi pi-moon'"></i>
                        </button>
                    </li>

                    <!-- User Dropdown -->
                    <li>
                        <div ref="dropdownRef" class="position-relative">
                            <button @click="toggleDropdown" class="header-user-button d-flex align-items-center gap-2 rounded px-2 py-1">
                                <span class="d-none d-lg-block text-end" style="min-width: 80px;">
                                    <span class="d-block small fw-semibold lh-sm user-name">{{ user?.name || 'Usuario' }}</span>
                                    <span class="d-block lh-sm user-role" style="font-size: 0.75rem;">{{ user?.roles?.[0]?.name || 'Rol' }}</span>
                                </span>
                                <div class="header-avatar position-relative flex-shrink-0 rounded-circle overflow-hidden" style="width: 40px; height: 40px;">
                                    <img v-if="user?.avatar" :src="user.avatar" alt="User" class="w-100 h-100 object-fit-cover"/>
                                    <div v-else class="d-flex h-100 w-100 align-items-center justify-content-center small fw-semibold avatar-initials">
                                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                                    </div>
                                </div>
                                <i class="pi pi-chevron-down" style="font-size: 0.75rem; transition: transform 0.2s;" :style="dropdownOpen ? 'transform: rotate(180deg)' : ''"></i>
                            </button>

                            <transition name="dropdown-fade">
                                <div v-show="dropdownOpen" class="header-dropdown position-absolute end-0 mt-2" style="z-index: 50;">
                                    <div class="header-dropdown-header">
                                        <p class="user-dropdown-name mb-0">{{ user?.name || 'Usuario' }}</p>
                                        <p class="user-dropdown-email mb-0">{{ user?.email || '' }}</p>
                                    </div>
                                    <ul class="list-unstyled mb-0 p-2">
                                        <li>
                                            <router-link :to="route.path.startsWith('/admin') ? '/admin/profile' : '/app/profile'" class="dropdown-menu-item">
                                                <i class="pi pi-user"></i>
                                                <span>Mi Perfil</span>
                                            </router-link>
                                        </li>
                                        <li>
                                            <router-link v-if="canAccessDashboard" to="/admin" class="dropdown-menu-item">
                                                <i class="pi pi-shield"></i>
                                                <span>Panel Admin</span>
                                            </router-link>
                                        </li>
                                    </ul>
                                    <div style="border-top: 1px solid;" class="p-2">
                                        <button @click="logout" class="dropdown-menu-item logout-button w-100">
                                            <i class="pi pi-sign-out"></i>
                                            <span>Cerrar Sesión</span>
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useLayout } from '../composables/layout';
import useAuth from '../composables/auth';
import { authStore } from '../store/auth';

const route = useRoute();

const props = defineProps({
    sidebarOpen: {
        type: Boolean,
        default: false
    },
    isCollapsed: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['toggleSidebar', 'toggleCollapse']);

const { toggleDarkMode, isDarkTheme } = useLayout();
const { logout: logoutAuth } = useAuth();
const auth = authStore();
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const user = computed(() => auth.user);
const canAccessDashboard = computed(() => {
    return user.value?.roles?.some((role) => {
        const roleName = role?.name?.toLowerCase() || '';
        return roleName === 'admin';
    }) || false;
});


const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const logout = () => {
    dropdownOpen.value = false;
    logoutAuth();
};

const handleClickOutside = (event) => {
    if (!dropdownOpen.value) {
        return;
    }

    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Asegurar que los iconos PrimeIcons se muestren correctamente */
.pi,
[class^="pi-"],
[class*=" pi-"] {
    font-family: 'primeicons' !important;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
    display: inline-block;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ============================================
   Estilos del Header - Modo Claro (Professional Design)
   ============================================ */
header {
    background-color: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px 0 rgba(0, 0, 0, 0.04);
    backdrop-filter: blur(8px);
}

/* Botones de iconos del header */
.header-icon-button {
    border: 1px solid #e5e7eb;
    background-color: #ffffff;
    color: #6b7280;
}

.header-icon-button:hover {
    background-color: #f8fafc;
    color: #1e293b;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

.header-icon-button:active {
    transform: translateY(0);
}

.header-icon-button i {
    color: inherit;
}

/* Input de búsqueda */
.header-search-input {
    background-color: #f8fafc;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    font-weight: 400;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.header-search-input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.header-search-input:focus {
    background-color: #ffffff;
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.header-search-wrapper .header-search-icon {
    color: #94a3b8;
    transition: color 0.2s ease-in-out;
}

.header-search-wrapper:focus-within .header-search-icon {
    color: #3b82f6;
}

/* Botón de usuario */
.header-user-button {
    border: none;
    background-color: transparent;
    cursor: pointer;
}

.header-user-button:hover {
    background-color: #f8fafc;
    border-radius: 0.5rem;
}

.user-name {
    color: #1e293b;
    font-weight: 600;
    letter-spacing: -0.01em;
}

.user-role {
    color: #64748b;
    font-weight: 400;
}

/* Avatar */
.header-avatar {
    border: none;
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}

.header-avatar.ring-2 {
    --tw-ring-offset-color: #ffffff;
    --tw-ring-color: #e2e8f0;
}

.avatar-initials {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Badge de notificación */
.notification-badge {
    background-color: #ef4444;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 2px #ffffff;
}

.notification-ping {
    background-color: #ef4444;
}

/* Dropdown - Diseño Profesional */
.header-dropdown {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.05);
    min-width: 280px;
}

.header-dropdown-header {
    border-bottom: 1px solid #f1f5f9;
    background: linear-gradient(to bottom, #fafbfc, #f8fafc);
    padding: 1rem 1.25rem;
}

.user-dropdown-name {
    color: #1e293b;
    font-weight: 600;
    font-size: 0.875rem;
    line-height: 1.25rem;
    margin-bottom: 0.25rem;
}

.user-dropdown-email {
    color: #64748b;
    font-weight: 400;
    font-size: 0.75rem;
    line-height: 1rem;
}

.header-dropdown ul {
    padding: 0.5rem;
}

.dropdown-menu-item {
    color: #475569;
    border: none;
    background: transparent;
    padding: 0.625rem 0.75rem;
    border-radius: 0.5rem;
    width: 100%;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.dropdown-menu-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background-color: #3b82f6;
    border-radius: 0 2px 2px 0;
    transition: height 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-menu-item:hover {
    color: #1e293b;
    background-color: #f1f5f9;
    padding-left: 1rem;
}

.dropdown-menu-item:hover::before {
    height: 60%;
}

.dropdown-menu-item i {
    color: #64748b;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    transition: color 0.15s ease-in-out;
}

.dropdown-menu-item:hover i {
    color: #3b82f6;
}

.dropdown-menu-item span {
    flex: 1;
}

.header-dropdown .border-t {
    border-top: 1px solid #f1f5f9;
    margin-top: 0.25rem;
}

.logout-button {
    color: #dc2626;
}

.logout-button:hover {
    color: #b91c1c;
    background-color: #fef2f2;
}

.logout-button:hover::before {
    background-color: #dc2626;
}

.logout-button:hover i {
    color: #dc2626;
}

/* Transiciones del dropdown */
.dropdown-fade-enter-active {
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dropdown-fade-leave-active {
    transition: all 0.15s cubic-bezier(0.4, 0, 1, 1);
}

.dropdown-fade-enter-from {
    opacity: 0;
    transform: translateY(-10px) scale(0.96);
}

.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-5px) scale(0.98);
}

</style>

