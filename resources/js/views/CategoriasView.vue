<template>
  <div class="categorias-page">
    <HomeNavbar />

    <main class="main-content">
      <div class="categorias-header row g-3 align-items-start mb-5">
        <div class="col-12 col-md">
          <h1 class="categorias-title mb-3">Categorías</h1>
          <p class="categorias-subtitle mb-0">Elige una categoría para jugar</p>
        </div>

        <div class="col-12 col-md-auto d-grid d-md-block">
          <button class="btn btn-create-room fw-bold" @click="irACrearSala">
            Crear tu sala
          </button>
        </div>
      </div>

      <div class="row g-4 mb-5">
        <div
          class="col-12 col-sm-6 col-lg-4 col-xl-3"
          v-for="categoria in categorias"
          :key="categoria.id"
        >
          <div
            class="categoria-card h-100"
            @click="jugarCategoria(categoria.id)"
            role="button"
            tabindex="0"
            @keydown.enter="jugarCategoria(categoria.id)"
          >
            <div class="card-image-wrapper">
              <div class="card-image placeholder">
                <img
                  v-if="categoria.imagen"
                  :src="categoria.imagen"
                  :alt="categoria.nombre"
                  class="card-image__img"
                />
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
      </div>

      <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
          <div class="info-card ranking-card">
            <i class="info-card-icon"></i>
            <div>
              <h4 class="info-title mb-1">Ranking Global</h4>
              <p class="info-subtitle mb-0">Compite y escala posiciones globalmente</p>
            </div>

            <button
              type="button"
              class="btn btn-ranking ms-auto"
              @click="irARankingGlobal"
            >
              Ver rankings
            </button>
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

function irACrearSala() {
  router.push({ name: 'mis-salas' });
}

function irARankingGlobal() {
  router.push({ name: 'public.rankings' });
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

.categorias-title {
  font-size: 3rem;
  font-weight: 800;
  color: #eef2ff;
}

.categorias-subtitle {
  font-size: 1.1rem;
  color: rgba(238, 242, 255, 0.7);
  font-weight: 300;
}

.btn-create-room {
  background: #ff724f;
  border-color: #ff724f;
  color: #fff;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
}

.btn-create-room:hover {
  background: #e05c38;
  border-color: #e05c38;
  color: #fff;
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
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
}

.card-image-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.75rem 1.5rem 1rem;
}

.card-image {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  color: #eef2ff;
  text-align: center;
  overflow: hidden;
}

.card-image__img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  object-position: center;
  display: block;
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

.info-card {
  background: rgba(255, 255, 255, 0.10);
  border: 1px solid rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(8px);
  border-radius: 14px;
  padding: 1.25rem 1.75rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.ranking-card {
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.ranking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
  background: rgba(255, 255, 255, 0.16);
}

.info-card-icon {
  font-size: 2rem;
  color: #ffc107;
  line-height: 1;
}

.info-title {
  color: #eef2ff;
  font-weight: 700;
  font-size: 1rem;
}

.info-subtitle {
  color: rgba(238, 242, 255, 0.65);
  font-size: 0.9rem;
}

.btn-ranking {
  background: #ff724f;
  border: 1px solid #ff724f;
  color: #fff;
  font-weight: 700;
  border-radius: 8px;
  padding: 0.5rem 1rem;
}

.btn-ranking:hover,
.btn-ranking:focus {
  background: #e05c38;
  border-color: #e05c38;
  color: #fff;
}

.btn-ranking:active {
  background: #cf4f2f !important;
  border-color: #cf4f2f !important;
  color: #fff !important;
}

@media (max-width: 575.98px) {
  .ranking-card {
    flex-wrap: wrap;
  }

  .btn-ranking {
    margin-left: 0 !important;
    width: 100%;
    margin-top: 0.75rem;
  }
}

@media (max-width: 767.98px) {
  .categorias-title { font-size: 2.3rem; }
}
</style>

