<template>
  <header class="home-header" :class="{ 'home-header--scrolled': scrolled }">
    <div class="container-home home-header__inner">

      <RouterLink to="/" class="home-logo" aria-label="Whatizit - Inicio">
        <img src="/images/logowhatizit.svg" alt="Whatizit" class="home-logo-img" />
      </RouterLink>

      <!-- NAV DESKTOP (solo visible en desktop) -->
      <nav class="home-nav d-none d-lg-flex" :class="{ 'home-nav--open': mobileMenuOpen }">
        <div
          v-for="item in navItems"
          :key="item.label"
          class="home-nav-dropdown"
          @mouseenter="item.children?.length && openDropdown(item.label)"
          @mouseleave="item.children?.length && closeDropdown()"
        >
          <router-link
            v-if="item.route"
            :to="item.route"
            class="home-nav-item"
            :class="{ 'home-nav-item--direct': !item.children?.length }"
            @click="closeMobileMenu"
          >
            {{ item.label }}
          </router-link>

          <a v-else href="#" class="home-nav-item" @click.prevent="toggleDropdown(item.label)">
            {{ item.label }}
          </a>

          <div v-if="item.children?.length && activeDropdown === item.label" class="home-nav-dropdown-menu">
            <router-link
              v-for="sub in item.children"
              :key="sub.label"
              :to="sub.route"
              class="home-nav-dropdown-item"
              @click="closeMobileMenu"
            >{{ sub.label }}</router-link>
          </div>
        </div>
      </nav>

      <!-- AUTH DESKTOP (solo visible en desktop) -->
      <div class="home-auth d-none d-lg-flex" :class="{ 'home-auth--open': mobileMenuOpen }">
        <template v-if="!store.authenticated">
          <router-link :to="{ name: 'auth.login' }" class="home-link" @click="closeMobileMenu">Login</router-link>
          <router-link :to="{ name: 'auth.register' }" class="home-btn-register" @click="closeMobileMenu">Registrarse <span>›</span></router-link>
        </template>
        <template v-else>
          <div class="home-user-dropdown" @mouseenter="userMenuOpen = true" @mouseleave="userMenuOpen = false">
            <button class="home-user-btn" @click="userMenuOpen = !userMenuOpen">
              <span class="home-user-avatar">{{ store.user?.name?.[0]?.toUpperCase() }}</span>
              <span class="home-user-name">{{ store.user?.name }}</span>
              <span class="home-user-chevron" :class="{ 'open': userMenuOpen }">▾</span>
            </button>
            <div v-if="userMenuOpen" class="home-user-menu">
              <router-link :to="{ name: userPanelRoute }" class="home-user-menu-item" @click="userMenuOpen = false; closeMobileMenu()">
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

      <!-- MOBILE NAV MENU (solo visible en móvil) -->
      <nav class="home-nav-mobile d-lg-none" v-show="mobileMenuOpen">
        <div v-for="item in navItems" :key="`mobile-${item.label}`" class="home-nav-mobile__item">

          <router-link
            v-if="item.route && !item.children?.length"
            :to="item.route"
            class="home-nav-mobile__toggle home-nav-mobile__direct"
            @click="closeMobileMenu"
          >
            <span class="home-nav-mobile__label">{{ item.label }}</span>
          </router-link>

          <template v-else>
            <button
              class="home-nav-mobile__toggle"
              @click="toggleMobileDropdown(item.label)"
              :class="{ 'is-active': activeMobileDropdown === item.label }"
            >
              <span class="home-nav-mobile__label">{{ item.label }}</span>
              <span class="home-nav-mobile__icon">‹</span>
            </button>
            <transition name="expand">
              <div v-if="activeMobileDropdown === item.label" class="home-nav-mobile__submenu">
                <router-link
                  v-for="sub in item.children"
                  :key="`sub-${sub.label}`"
                  :to="sub.route"
                  class="home-nav-mobile__subitem"
                  @click="closeMobileMenu"
                >{{ sub.label }}</router-link>
              </div>
            </transition>
          </template>

        </div>
      </nav>

      <!-- MOBILE AUTH MENU (solo visible en móvil cuando está autenticado) -->
      <div v-if="mobileMenuOpen && store.authenticated" class="home-auth-mobile d-lg-none">
        <div class="home-auth-mobile__user">
          <span class="home-auth-mobile__avatar">{{ store.user?.name?.[0]?.toUpperCase() }}</span>
          <span class="home-auth-mobile__name">{{ store.user?.name }}</span>
        </div>
        <router-link :to="{ name: userPanelRoute }" class="home-auth-mobile__link" @click="closeMobileMenu">
          <span>👤</span> Mi perfil
        </router-link>
        <button class="home-auth-mobile__link home-auth-mobile__logout" @click="handleLogout">
          <span>🚪</span> Cerrar sesión
        </button>
      </div>

      <!-- MOBILE AUTH BUTTONS (solo visible en móvil cuando NO está autenticado) -->
      <div v-if="mobileMenuOpen && !store.authenticated" class="home-auth-mobile-buttons d-lg-none">
        <router-link :to="{ name: 'auth.login' }" class="home-auth-mobile-btn home-auth-mobile-btn--login" @click="closeMobileMenu">
          Login
        </router-link>
        <router-link :to="{ name: 'auth.register' }" class="home-auth-mobile-btn home-auth-mobile-btn--register" @click="closeMobileMenu">
          Registrarse ›
        </router-link>
      </div>

      <!-- Hamburger (solo móvil) -->
      <button
        class="home-hamburger d-lg-none"
        @click="mobileMenuOpen = !mobileMenuOpen"
        :aria-expanded="mobileMenuOpen"
        :aria-label="mobileMenuOpen ? 'Cerrar menú' : 'Abrir menú'"
      >
        <span></span>
        <span></span>
        <span></span>
      </button>

    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { authStore } from '@/store/auth';
import useAuth from '@/composables/auth';

const store = authStore();
const router = useRouter();
const route = useRoute();
const { logout } = useAuth();

const activeDropdown = ref(null);
const activeMobileDropdown = ref(null);
const userMenuOpen = ref(false);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);

// Close mobile menu on route change
watch(() => route.path, () => {
  mobileMenuOpen.value = false;
  activeDropdown.value = null;
  activeMobileDropdown.value = null;
});

// Scroll-aware navbar
const handleScroll = () => {
  scrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('resize', handleResize, { passive: true });
  handleResize();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('resize', handleResize);
});

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
  activeDropdown.value = null
  activeMobileDropdown.value = null
  userMenuOpen.value = false
}

const handleResize = () => {
  if (window.innerWidth >= 992) closeMobileMenu()
}

const toggleMobileDropdown = (label) => {
  activeMobileDropdown.value = activeMobileDropdown.value === label ? null : label
}

const navItems = [
  {
    label: 'Juegos',
    children: [
      { label: 'Tus salas', route: '/mis-salas' },
      { label: 'Categorías individuales', route: '/categorias' },
    ]
  },
  {
    label: 'Rankings',
    children: [
      { label: 'Ranking global', route: { name: 'public.rankings' } },
      { label: 'Por categoria', route: { name: 'ranking.category' } },
    ]
  },
  {
    label: 'Información',
    route: { name: 'info.index' } // <- directo a /info, sin dropdown
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
  closeMobileMenu();
  logout();
};
</script>

