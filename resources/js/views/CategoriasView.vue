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
              <img v-if="categoria.imagen" :src="categoria.imagen" :alt="categoria.nombre" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
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
          <h4 class="info-title">Ranking Individual</h4>
        </div>
        <div class="info-card rewards-card">
          <h4 class="info-title">Recompensas</h4>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import HomeNavbar from '@/layouts/HomeNavbar.vue';

export default {
  name: "CategoriasView",
  components: { HomeNavbar },
  data() {
    return {
      categorias: [
        { id: 1, nombre: "Películas", imagen: null },
        { id: 2, nombre: "Música", imagen: null },
        { id: 3, nombre: "Deportes", imagen: null },
        { id: 4, nombre: "Geografía", imagen: null },
        { id: 5, nombre: "Historia", imagen: null },
      ]
    };
  },
  mounted() {
    this.fetchCategorias();
  },
  methods: {
    async fetchCategorias() {
      try {
        // Descomenta esto cuando tengas tu endpoint de categorías listo
        // const response = await fetch('/api/categorias');
        // this.categorias = await response.json();
        // Por ahora usamos los datos de ejemplo arriba
      } catch (error) {
        console.error('Error al cargar categorías:', error);
      }
    },
    jugarCategoria(categoriaId) {
      console.log('Jugando categoría:', categoriaId);
      // Aquí redirigirías a la vista de juego
      // this.$router.push({ name: 'juego', params: { categoriaId } });
    }
  }
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

/* ── Página: mismo gradiente que home ── */
.categorias-page {
  min-height: 100vh;
  background: linear-gradient(190deg, #5f74b7 25%, #a6aec5 100%);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  padding-top: 96px; /* mismo offset que .home-page */
}

/* ── Contenedor: mismo ancho que container-home ── */
.main-content {
  width: min(1200px, 92%);
  margin: 0 auto;
  padding: 3rem 0;
}

/* ── Cabecera de sección ── */
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

/* ── Grid de categorías ── */
.categorias-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

/* ── Cards: glass card igual que el estilo del home ── */
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

.categoria-card:hover .card-overlay {
  opacity: 1;
}

/* Overlay que aparece al hover */
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
  min-height: 150px;
}

.card-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  border: 2px solid rgba(255, 255, 255, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #eef2ff;
  font-weight: 600;
  text-align: center;
}

.card-image.placeholder {
  font-size: 0.9rem;
}

.card-content {
  padding: 1rem 1.5rem 1.5rem;
  text-align: center;
}

.card-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #eef2ff;
}

/* ── Info section (ranking / recompensas) ── */
.info-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.info-card {
  background: rgba(255, 255, 255, 0.10);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 70px;
  transition: background 0.3s;
}

.info-card:hover {
  background: rgba(255, 255, 255, 0.17);
}

.ranking-card,
.rewards-card {
  background: rgba(255, 114, 79, 0.55); /* mismo naranja del home con transparencia */
  border: 1px solid rgba(255, 180, 120, 0.35);
}

.ranking-card:hover,
.rewards-card:hover {
  background: rgba(255, 114, 79, 0.70);
}

.info-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #eef2ff;
  letter-spacing: 0.5px;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .categorias-title {
    font-size: 2rem;
  }

  .categorias-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
  }
}

@media (max-width: 480px) {
  .categorias-title {
    font-size: 1.5rem;
  }

  .categorias-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}
</style>
