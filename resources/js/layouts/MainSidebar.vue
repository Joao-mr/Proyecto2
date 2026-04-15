<template>
    <aside 
        :class="[
            props.sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            'sidebar-container'
        ]"
        :style="{ width: props.isCollapsed ? '70px' : '256px' }"
    >
        <!-- Sidebar Header -->
        <div class="d-flex align-items-center justify-content-center p-3 border-bottom flex-shrink-0"
             :style="{ height: props.isCollapsed ? '64px' : '96px' }">
            <div class="d-flex align-items-center gap-2 overflow-hidden w-100 justify-content-center">
                <img src="/images/logo.svg" alt="Logo" class="object-fit-contain"
                     :style="{ height: props.isCollapsed ? '32px' : '64px', width: 'auto', maxWidth: '100%' }"/>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <div class="d-flex flex-column overflow-y-auto overflow-x-hidden p-2 gap-1 flex-grow-1 scrollbar-hide" style="overflow-y: auto; overflow-x: hidden;">
            <template v-for="(item, index) in menuModel" :key="index">
                <!-- Group Label -->
                <div v-if="item.label && item.items && !props.isCollapsed"
                     class="px-3 mt-3 mb-1 sidebar-group-label">
                    {{ item.label }}
                </div>

                <template v-if="item.items">
                     <!-- Submenu Items -->
                     <template v-for="(subItem, subIndex) in item.items" :key="subItem.label">
                        <router-link :to="subItem.route" v-if="subItem.route" custom v-slot="{ href, navigate, isActive }">
                            <a :href="href" @click="navigate"
                               v-tooltip.right="props.isCollapsed ? subItem.label : ''"
                               class="sidebar-nav-item"
                               :class="isActive ? 'active' : ''"
                            >
                                <i :class="[subItem.icon, 'sidebar-nav-icon', isActive ? 'active' : '']"></i>
                                <span v-if="!props.isCollapsed" class="sidebar-nav-label">{{ subItem.label }}</span>
                            </a>
                        </router-link>
                     </template>
                </template>

                <!-- Single Item -->
                <template v-else-if="item.route">
                     <router-link :to="item.route" custom v-slot="{ href, navigate, isActive }">
                        <a :href="href" @click="navigate"
                           v-tooltip.right="props.isCollapsed ? item.label : ''"
                           class="sidebar-nav-item"
                           :class="isActive ? 'active' : ''"
                        >
                            <i :class="[item.icon, 'sidebar-nav-icon', isActive ? 'active' : '']"></i>
                            <span v-if="!props.isCollapsed" class="sidebar-nav-label">{{ item.label }}</span>
                        </a>
                     </router-link>
                </template>
            </template>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div v-if="props.sidebarOpen" @click="emit('toggleSidebar')" class="d-lg-none position-fixed top-0 start-0 w-100 h-100 sidebar-overlay" style="z-index: 40; background: rgba(17,24,39,0.5); backdrop-filter: blur(2px);"></div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAbility } from '@casl/vue';

const route = useRoute();
const router = useRouter();
const { can } = useAbility();

const props = defineProps({
    sidebarOpen: {
        type: Boolean,
        default: true
    },
    isCollapsed: {
        type: Boolean,
        default: false
    },
    menuItems: {
        type: Array,
        default: null
    }
});

const emit = defineEmits(['toggleSidebar', 'toggleCollapse']);

const menuModel = computed(() => {
    // Si se proporcionan items personalizados, usarlos
    if (props.menuItems) {
        return props.menuItems;
    }

    // Si no, usar los items por defecto del admin
    const items = [
        {
            icon: 'pi pi-home',
            label: 'Principal',
            // Used as header if items present
             items: [
                { label: 'Dashboard', icon: 'pi pi-compass', route: '/admin', permission: 'all' }
            ]
        },
        {
            label: 'Gestión',
            items: [
                { label: 'Usuarios', icon: 'pi pi-users', route: '/admin/users', permission: 'user-list' },
                { label: 'Roles', icon: 'pi pi-shield', route: '/admin/roles', permission: 'role-list' },
                { label: 'Permisos', icon: 'pi pi-key', route: '/admin/permissions', permission: 'permission-list' }
            ]
        },
        {
            label: 'Contenido',
            items: [
                { label: 'Categorías', icon: 'pi pi-tags', route: '/admin/categories', permission: 'category-list' },
                { label: 'Categorías', icon: 'pi pi-th-large', route: '/admin/categorias' },
                { label: 'Salas', icon: 'pi pi-users', route: '/admin/salas' },
                { label: 'Partidas', icon: 'pi pi-play-circle', route: '/admin/partidas' },
                { label: 'Imágenes', icon: 'pi pi-image', route: '/admin/imagenes' }
            ]
        }
    ];

    // Filtrar items según permisos
    return items.filter(item => {
        if (item.permission && item.permission !== 'all') {
            if (!can(item.permission)) return false;
        }
        if (item.items) {
            item.items = item.items.filter(child => {
                return !child.permission || can(child.permission);
            });
            return item.items.length > 0;
        }
        return true;
    });
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>