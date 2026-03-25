<template>
  <div class="categorias-page">
    <!-- Header Navigation -->
    <header class="header-nav">
      <div class="nav-container">
        <router-link to="/" class="logo-wrap-header">
          <span class="logo-letter wh">W</span><span class="logo-letter wh">H</span><span class="logo-letter wh">A</span><span class="logo-letter wh">T</span><span class="logo-letter or">I</span><span class="logo-letter or">Z</span><span class="logo-letter or">I</span><span class="logo-letter or">T</span>
        </router-link>
        <nav class="nav-menu">
          <router-link to="/categorias" class="nav-link">JUGAR</router-link>
          <a href="#" class="nav-link">TIENDA</a>
          <a href="#" class="nav-link">GLOBAL</a>
        </nav>
        <div class="nav-actions">
          <router-link to="/login" class="nav-login-btn">Login</router-link>
          <router-link to="/register" class="nav-register-btn">Registrarse ›</router-link>
        </div>
      </div>
    </header>

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
export default {
  name: "CategoriasView",
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

/* Header Navigation */
.header-nav {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem 0;
  position: sticky;
  top: 0;
  z-index: 50;
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 3rem;
}

.logo-wrap-header {
  display: flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
  text-decoration: none;
  cursor: pointer;
}

.logo-letter {
  font-size: 1.5rem;
  font-weight: 900;
  letter-spacing: 2px;
}

.logo-letter.wh {
  color: #ffffff;
}

.logo-letter.or {
  color: #FACB99;
}

.nav-menu {
  display: flex;
  gap: 2rem;
  flex: 1;
  justify-content: center;
}

.nav-link {
  color: #ffffff;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 1px;
  transition: color 0.2s;
}

.nav-link:hover {
  color: #FACB99;
}

.nav-actions {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.nav-login-btn {
  color: #ffffff;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  transition: background 0.2s, color 0.2s;
}

.nav-login-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.nav-register-btn {
  background: linear-gradient(135deg, #FF8C42 0%, #FF7A30 100%);
  color: white;
  text-decoration: none;
  padding: 0.5rem 1.1rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 700;
  transition: transform 0.2s, box-shadow 0.2s;
}

.nav-register-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(255, 140, 66, 0.3);
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
  .nav-container {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .nav-menu {
    order: 3;
    width: 100%;
    justify-content: center;
    gap: 1rem;
  }

  .logo-wrap-header {
    order: 1;
  }

  .nav-actions {
    order: 2;
  }

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

  .nav-login-btn,
  .nav-register-btn {
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .nav-menu {
    gap: 0.5rem;
  }

  .nav-link {
    font-size: 0.75rem;
  }

  .categorias-title {
    font-size: 1.5rem;
  }

  .categorias-grid {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
  }
}
</style>
