<template>
  <div class="categorias-page">
    <!-- Header Navigation -->
    <HomeNavbar />

    <!-- Main Content -->
    <main class="main-content">
      <div class="categorias-header">
        <h1 class="categorias-title">Categorías</h1>
        <p class="categorias-subtitle">Elige una categoría para jugar</p>
      </div>

      <div class="categorias-grid">
        <!-- Categoria Card v-for -->
        <div class="categoria-card" v-for="categoria in categorias" :key="categoria.id">
          <div class="card-image-wrapper">
            <div class="card-image placeholder">
              <img v-if="categoria.imagen" :src="categoria.imagen" :alt="categoria.nombre" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
              <span v-else>{{ categoria.nombre }}</span>
            </div>
          </div>
          <div class="card-content">
            <h3 class="card-title">{{ categoria.nombre }}</h3>
            <button class="card-play-btn" @click="jugarCategoria(categoria.id)">Jugar</button>
          </div>
        </div>
      </div>

      <!-- Bottom Info Cards -->
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

.categorias-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #505c84 0%, #3f4968 50%, #2e3548 100%);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

/* Main Content */
.main-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.categorias-header {
  text-align: center;
  margin-bottom: 3rem;
}

.categorias-title {
  font-size: 3rem;
  font-weight: 900;
  color: white;
  margin: 0 0 1rem 0;
  letter-spacing: 1px;
}

.categorias-subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

/* Categorias Grid */
.categorias-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  margin-bottom: 3rem;
}

.categoria-card {
  background: linear-gradient(180deg, #dce4ef 0%, #c9d6e8 50%, #b8c9de 100%);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s, box-shadow 0.3s;
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.categoria-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

.card-image-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: transparent;
  min-height: 150px;
}

.card-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: rgba(100, 120, 150, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #505c84;
  font-weight: 600;
  text-align: center;
}

.card-image.placeholder {
  font-size: 0.9rem;
}

.card-content {
  padding: 1.5rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: transparent;
}

.card-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
}

.card-play-btn {
  background: linear-gradient(135deg, #35C3FF 0%, #1BA8DE 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.card-play-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(53, 195, 255, 0.3);
}

.card-play-btn:active {
  transform: translateY(0);
}

/* Info Section */
.info-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.info-card {
  background: rgba(114, 112, 112, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 70px;
  max-width: 500px;
  transition: background 0.3s;
}

.info-card:hover {
  background: rgba(255, 255, 255, 0.15);
}

.ranking-card,
.rewards-card {
  background: linear-gradient(135deg, #35C3FF 0%, #1BA8DE 100%);
}

.info-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.5px;
}

/* Responsive */
@media (max-width: 768px) {
  .main-content {
    padding: 2rem 1rem;
  }

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
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
  }
}
</style>
