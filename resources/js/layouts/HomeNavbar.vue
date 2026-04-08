<template>
  <header class="home-header">
    <div class="container-home home-header__inner">

      <RouterLink to="/" class="home-logo" aria-label="Whatizit - Inicio">
        <img src="/images/logowhatizit.svg" alt="Whatizit" class="home-logo-img" />
      </RouterLink>

      <nav class="home-nav">
        <div
          v-for="item in navItems"
          :key="item.label"
          class="home-nav-dropdown"
          @mouseenter="openDropdown(item.label)"
          @mouseleave="closeDropdown()"
        >
          <a href="#" class="home-nav-item" @click.prevent="toggleDropdown(item.label)">{{ item.label }}</a>
          <div v-if="activeDropdown === item.label" class="home-nav-dropdown-menu">
            <router-link
              v-for="sub in item.children"
              :key="sub.label"
              :to="sub.route"
              class="home-nav-dropdown-item"
              @click="closeDropdown"
            >{{ sub.label }}</router-link>
          </div>
        </div>
      </nav>

      <div class="home-auth">
        <template v-if="!store.authenticated">
          <router-link :to="{ name: 'auth.login' }" class="home-link">Login</router-link>
          <router-link :to="{ name: 'auth.register' }" class="home-btn-register">Registrarse <span>&gt;</span></router-link>
        </template>
        <template v-else>
          <div class="home-user-dropdown" @mouseenter="userMenuOpen = true" @mouseleave="userMenuOpen = false">
            <button class="home-user-btn" @click="userMenuOpen = !userMenuOpen">
              <span class="home-user-avatar">{{ store.user?.name?.[0]?.toUpperCase() }}</span>
              <span class="home-user-name">{{ store.user?.name }}</span>
              <span class="home-user-chevron" :class="{ 'open': userMenuOpen }">▾</span>
            </button>
            <div v-if="userMenuOpen" class="home-user-menu">
              <router-link :to="{ name: userPanelRoute }" class="home-user-menu-item" @click="userMenuOpen = false">
                <span class="home-user-menu-icon">👤</span> Mi perfil
              </router-link>
              <div class="home-user-menu-divider"></div>
              <button class="home-user-menu-item home-user-menu-logout" @click="handleLogout">
                <span class="home-user-menu-icon">🚪</span> Cerrar sesión
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { authStore } from '@/store/auth';
import useAuth from '@/composables/auth';

const store = authStore();
const router = useRouter();
const { logout } = useAuth();

const activeDropdown = ref(null);
const userMenuOpen = ref(false);

const navItems = [
  {
    label: 'Juegos',
    children: [
      { label: 'Todas las salas', route: '/' },
      { label: 'Categorías', route: '/categorias' },
    ]
  },
  {
    label: 'Rankings',
    children: [
      { label: 'Ranking global', route: '/' },
      { label: 'Por categoría', route: '/' },
    ]
  },
  {
    label: 'Información',
    children: [
      { label: 'Cómo jugar', route: '/' },
      { label: 'Contacto', route: '/' },
    ]
  },
];

const userPanelRoute = computed(() => {
  const roles = store.user?.roles ?? [];
  const isAdmin = roles.some(role => role?.name?.toLowerCase().includes('admin'));
  return isAdmin ? 'admin.index' : 'app';
});

const openDropdown = (label) => { activeDropdown.value = label; };
const closeDropdown = () => { activeDropdown.value = null; };
const toggleDropdown = (label) => {
  activeDropdown.value = activeDropdown.value === label ? null : label;
};

const handleLogout = () => {
  userMenuOpen.value = false;
  logout();
};
</script>

