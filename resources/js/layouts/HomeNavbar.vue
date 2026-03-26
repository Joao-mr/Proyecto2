<!-- filepath: c:\xampp\htdocs\Proyecto2\Laravel-VUE-API-Base-Clase\resources\js\layouts\HomeNavbar.vue -->
<template>
  <header class="home-header">
    <div class="container-home home-header__inner">
      <router-link to="/" class="home-logo">WHAT<span>IZIT</span></router-link>

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

<style scoped>
.home-username {
  color: #eef2ff;
  font-weight: 700;
  opacity: 0.95;
  font-size: 0.97rem;
  letter-spacing: 0.2px;
  text-decoration: none;
  cursor: pointer;
  transition: opacity 0.2s;
}

.home-username:hover {
  opacity: 0.75;
  text-decoration: underline;
}

.home-btn-logout {
  background: transparent;
  color: #eef2ff;
  border: 1px solid rgba(255, 255, 255, 0.35);
  padding: 0.55rem 1rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s, opacity 0.2s;
  font-family: inherit;
}

.home-btn-logout:hover {
  background: rgba(255, 100, 60, 0.25);
  border-color: #ff764f;
  color: #fff;
}
</style>