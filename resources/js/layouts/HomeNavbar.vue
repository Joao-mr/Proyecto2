<template>
  <header class="home-header">
    <div class="container-home home-header__inner">

      <RouterLink to="/" class="home-logo" aria-label="Whatizit - Inicio">
        <img src="/images/logowhatizit.svg" alt="Whatizit" class="home-logo-img" />
      </RouterLink>


      <nav class="home-nav">
        <a href="#" class="home-nav-item">Juegos</a>
        <a href="#" class="home-nav-item">Rankings</a>
        <a href="#" class="home-nav-item">Información</a>
      </nav>

      <div class="home-auth">
        <template v-if="!store.authenticated">
          <router-link :to="{ name: 'auth.login' }" class="home-link">Login</router-link>
          <router-link :to="{ name: 'auth.register' }" class="home-btn-register">Registrarse <span>&gt;</span></router-link>
        </template>
        <template v-else>
          <router-link :to="{ name: userPanelRoute }" class="home-username">{{ store.user?.name }}</router-link>
          <button class="home-btn-logout" @click="handleLogout">Cerrar sesión</button>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { authStore } from '@/store/auth';
import useAuth from '@/composables/auth';

const store = authStore();
const router = useRouter();
const { logout } = useAuth();

const userPanelRoute = computed(() => {
  const roles = store.user?.roles ?? [];
  const isAdmin = roles.some(role => role?.name?.toLowerCase().includes('admin'));
  return isAdmin ? 'admin.index' : 'app';
});

const handleLogout = () => {
  logout();
};
</script>

