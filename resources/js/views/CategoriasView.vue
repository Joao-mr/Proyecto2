<template>
  <div class="categorias-page">
    <HomeNavbar />

    <main class="main-content">
      <div class="categorias-header">
        <h1 class="categorias-title">Categorías</h1>
        <p class="categorias-subtitle">Elige una categoría para jugar</p>
      </div>

      <div class="categorias-grid">
        <div
          class="categoria-card"
          v-for="categoria in categorias"
          :key="categoria.id"
          @click="jugarCategoria(categoria.id)"
          role="button"
          tabindex="0"
          @keydown.enter="jugarCategoria(categoria.id)"
        >
          <div class="card-image-wrapper">
            <div class="card-image placeholder">
              <img v-if="categoria.imagen" :src="categoria.imagen" :alt="categoria.nombre" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" />
              <span v-else>{{ categoria.nombre }}</span>
            </div>
          </div>
          <div class="card-content">
            <h3 class="card-title">{{ categoria.nombre }}</h3>
          </div>
          <div class="card-overlay">
            <span class="card-overlay-text">Jugar</span>
          </div>
        </div>
      </div>

      <div class="info-section">
        <div class="info-card ranking-card">
          <span class="info-card-icon">🏆</span>
          <div>
            <h4 class="info-title">Ranking Individual</h4>
            <p class="info-subtitle">Compite y escala posiciones globalmente</p>
          </div>
        </div>
        <div class="info-card rewards-card">
          <span class="info-card-icon">🎁</span>
          <div>
            <h4 class="info-title">Recompensas</h4>
            <p class="info-subtitle">Gana premios por tus logros en el juego</p>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import HomeNavbar from '@/layouts/HomeNavbar.vue';
import useCategorias from '@/composables/categorias';

const router = useRouter();
const { categorias, getCategorias } = useCategorias();

onMounted(() => getCategorias());

function jugarCategoria(id) {
  router.push({ name: 'game.categoria', params: { id } });
}
</script>

<style scoped>
* { box-sizing: border-box; }

.categorias-page {
  min-height: 100vh;
  background: linear-gradient(190deg, #5f74b7 25%, #a6aec5 100%);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  padding-top: 96px;
}

.main-content {
  width: min(1200px, 92%);
  margin: 0 auto;
  padding: 3rem 0;
}

.categorias-header {
  text-align: center;
  margin-bottom: 3rem;
}

.categorias-title {
  font-size: 3rem;
  font-weight: 900;
  color: #eef2ff;
  margin: 0 0 0.75rem 0;
  letter-spacing: 1px;
}

.categorias-subtitle {
  font-size: 1.1rem;
  color: rgba(238, 242, 255, 0.7);
  font-weight: 500;
  margin: 0;
}

.categorias-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.categoria-card {
  background: rgba(255, 255, 255, 0.10);
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
  transition: transform 0.3s, box-shadow 0.3s, background 0.3s;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  position: relative;
}

.categoria-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.28);
  background: rgba(255, 255, 255, 0.17);
}

.categoria-card:hover .card-overlay { opacity: 1; }

.card-overlay {
  position: absolute;
  inset: 0;
  background: rgba(95, 116, 183, 0.72);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.25s ease;
  border-radius: 16px;
}

.card-overlay-text {
  background: #ff724f;
  color: #fff;
  font-weight: 700;
  font-size: 1.1rem;
  padding: 0.6rem 2rem;
  border-radius: 8px;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.25);
}

.card-image-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.75rem 1.5rem 1rem;
}

.card-image {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  color: #eef2ff;
  text-align: center;
}

.card-content {
  padding: 0.75rem 1.25rem 1.25rem;
  text-align: center;
}

.card-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: #eef2ff;
  margin: 0;
}

/* Info section */
.info-section {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
  justify-content: center;
}

.info-card {
  background: rgba(255, 255, 255, 0.10);
  border: 1px solid rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(8px);
  border-radius: 14px;
  padding: 1.25rem 1.75rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 260px;
}

.info-card-icon { font-size: 2rem; }

.info-title {
  color: #eef2ff;
  font-weight: 700;
  margin: 0 0 0.2rem;
  font-size: 1rem;
}

.info-subtitle {
  color: rgba(238, 242, 255, 0.65);
  font-size: 0.85rem;
  margin: 0;
}
</style>
