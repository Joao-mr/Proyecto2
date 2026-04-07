<template>
  <div class="categorias-page">
    <HomeNavbar />

    <main class="main-content">

      <!-- ── Page Header ── -->
      <div class="page-header">
        <div class="header-text">
          <h1 class="page-title">Elige tu <span class="title-accent">categoría</span></h1>
          <p class="page-subtitle">Pon a prueba tus conocimientos en {{ categorias.length }} temáticas</p>
        </div>
        <div class="search-wrapper">
          <i class="pi pi-search search-icon"></i>
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Buscar categoría..."
          />
          <button v-if="searchQuery" class="search-clear" @click="searchQuery = ''">
            <i class="pi pi-times"></i>
          </button>
        </div>
      </div>

      <!-- ── Quick Actions ── -->
      <div class="quick-actions">
        <button class="action-card ranking-action">
          <div class="action-icon-wrap">
            <i class="pi pi-star-fill"></i>
          </div>
          <div class="action-text">
            <span class="action-title">Ranking Individual</span>
            <span class="action-desc">Consulta tu posición global</span>
          </div>
          <i class="pi pi-chevron-right action-arrow"></i>
        </button>
        <button class="action-card multi-action">
          <div class="action-icon-wrap">
            <i class="pi pi-users"></i>
          </div>
          <div class="action-text">
            <span class="action-title">Multijugador</span>
            <span class="action-desc">Juega con amigos en tiempo real</span>
          </div>
          <i class="pi pi-chevron-right action-arrow"></i>
        </button>
      </div>

      <!-- ── Section label ── -->
      <div class="section-label">
        <span>{{ filteredCategorias.length }} categoría{{ filteredCategorias.length !== 1 ? 's' : '' }} disponible{{ filteredCategorias.length !== 1 ? 's' : '' }}</span>
      </div>

      <!-- ── Loading skeletons ── -->
      <div v-if="isLoading" class="categorias-grid">
        <div v-for="n in 6" :key="n" class="categoria-card skeleton-card">
          <div class="sk sk-icon"></div>
          <div class="sk sk-title"></div>
          <div class="sk sk-btn"></div>
        </div>
      </div>

      <!-- ── Empty state ── -->
      <div v-else-if="filteredCategorias.length === 0" class="empty-state">
        <i class="pi pi-search empty-icon"></i>
        <p class="empty-msg">Sin resultados para <strong>"{{ searchQuery }}"</strong></p>
        <button class="empty-reset" @click="searchQuery = ''">Limpiar búsqueda</button>
      </div>

      <!-- ── Categories Grid ── -->
      <div v-else class="categorias-grid">
        <div
          class="categoria-card"
          v-for="categoria in filteredCategorias"
          :key="categoria.id"
          :style="{ '--cc': categoria.color }"
        >
          <div class="card-icon-circle">
            <img v-if="categoria.imagen" :src="categoria.imagen" :alt="categoria.nombre" class="card-img" />
            <i v-else :class="`pi ${categoria.icono}`"></i>
          </div>
          <h3 class="card-title">{{ categoria.nombre }}</h3>
          <button class="card-play-btn" @click="jugarCategoria(categoria.id)">
            <i class="pi pi-play"></i>
            <span>Jugar</span>
          </button>
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
      searchQuery: '',
      isLoading: false,
      categorias: [
        { id: 1, nombre: "Películas",  imagen: null, icono: "pi-video",      color: "#f97316" },
        { id: 2, nombre: "Música",     imagen: null, icono: "pi-volume-up",  color: "#a855f7" },
        { id: 3, nombre: "Deportes",   imagen: null, icono: "pi-bolt",       color: "#22c55e" },
        { id: 4, nombre: "Geografía",  imagen: null, icono: "pi-map",        color: "#06b6d4" },
        { id: 5, nombre: "Historia",   imagen: null, icono: "pi-book",       color: "#f59e0b" },
      ]
    };
  },
  computed: {
    filteredCategorias() {
      if (!this.searchQuery.trim()) return this.categorias;
      const q = this.searchQuery.toLowerCase();
      return this.categorias.filter(c => c.nombre.toLowerCase().includes(q));
    }
  },
  mounted() {
    this.fetchCategorias();
  },
  methods: {
    async fetchCategorias() {
      // this.isLoading = true;
      // try {
      //   const response = await fetch('/api/categorias');
      //   this.categorias = await response.json();
      // } catch (error) {
      //   console.error('Error al cargar categorías:', error);
      // } finally {
      //   this.isLoading = false;
      // }
    },
    jugarCategoria(categoriaId) {
      console.log('Jugando categoría:', categoriaId);
      // this.$router.push({ name: 'juego', params: { categoriaId } });
    }
  }
};
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

/* ── Page ── */
.categorias-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #5f74b7 0%, #a6aec5 100%);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.main-content {
  width: min(1200px, 92%);
  margin: 0 auto;
  padding: 2.5rem 0 4rem;
}

/* ── Page Header ── */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.page-title {
  font-size: 2rem;
  font-weight: 900;
  color: #fff;
  margin: 0 0 0.25rem;
  letter-spacing: -0.3px;
  text-shadow: 0 2px 12px rgba(0,0,0,0.18);
}

.title-accent { color: #f3b566; }

.page-subtitle {
  font-size: 0.93rem;
  color: rgba(255,255,255,0.65);
  margin: 0;
}

/* ── Search ── */
.search-wrapper {
  position: relative;
  width: 250px;
  flex-shrink: 0;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255,255,255,0.55);
  font-size: 0.82rem;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.65rem 2.4rem 0.65rem 2.5rem;
  background: rgba(255,255,255,0.13);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 999px;
  color: #fff;
  font-family: inherit;
  font-size: 0.87rem;
  outline: none;
  transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
  box-shadow: 0 0 0 2px rgba(244,244,244,0.07) inset;
}

.search-input::placeholder { color: rgba(255,255,255,0.4); }

.search-input:focus {
  background: rgba(255,255,255,0.2);
  border-color: rgba(255,255,255,0.45);
  box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
}

.search-clear {
  position: absolute;
  right: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: rgba(255,255,255,0.5);
  cursor: pointer;
  padding: 0;
  font-size: 0.72rem;
  line-height: 1;
  transition: color 0.2s;
}
.search-clear:hover { color: #fff; }

/* ── Quick Actions ── */
.quick-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 2rem;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  padding: 1rem 1.1rem;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 12px;
  cursor: pointer;
  font-family: inherit;
  text-align: left;
  box-shadow: 0 0 0 2px rgba(244,244,244,0.06) inset, 0 4px 14px rgba(0,0,0,0.12);
  transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
}

.action-card:hover {
  background: rgba(255,255,255,0.18);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.18);
}

.action-icon-wrap {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
  background: rgba(255,255,255,0.15);
  color: #fff;
}

.ranking-action .action-icon-wrap { background: rgba(249,168,37,0.25); color: #f3b566; }
.multi-action .action-icon-wrap   { background: rgba(255,118,79,0.25);  color: #ff8a4d; }

.action-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.action-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #fff;
}

.action-desc {
  font-size: 0.76rem;
  color: rgba(255,255,255,0.55);
}

.action-arrow {
  color: rgba(255,255,255,0.3);
  font-size: 0.72rem;
  flex-shrink: 0;
}

/* ── Section Label ── */
.section-label {
  font-size: 0.76rem;
  font-weight: 600;
  color: rgba(255,255,255,0.5);
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin-bottom: 1rem;
}

/* ── Grid ── */
.categorias-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

/* ── Category Card (white like login-card) ── */
.categoria-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2), 0 2px 8px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.75rem 1.25rem 1.4rem;
  gap: 0.85rem;
  transition: transform 0.25s, box-shadow 0.25s;
}

.categoria-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 45px rgba(0,0,0,0.25), 0 4px 12px rgba(0,0,0,0.12);
}

/* ── Icon Circle ── */
.card-icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: color-mix(in srgb, var(--cc, #ff764f) 12%, #f9f9f9);
  border: 2px solid color-mix(in srgb, var(--cc, #ff764f) 22%, transparent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  color: var(--cc, #ff764f);
  transition: transform 0.25s, background 0.25s;
}

.categoria-card:hover .card-icon-circle {
  transform: scale(1.08);
  background: color-mix(in srgb, var(--cc, #ff764f) 18%, #fff);
}

.card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

/* ── Card Text ── */
.card-title {
  margin: 0;
  font-size: 0.97rem;
  font-weight: 700;
  color: #1f2937;
  text-align: center;
}

/* ── Play Button (orange like submit-btn) ── */
.card-play-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1.2rem;
  background: #FF7E5F;
  border: none;
  border-radius: 7px;
  color: #fff;
  font-family: inherit;
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  letter-spacing: 0.3px;
  box-shadow: 0 6px 18px rgba(255,126,95,0.35);
  transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
}

.card-play-btn:hover {
  opacity: 0.9;
  transform: translateY(-2px);
  box-shadow: 0 10px 26px rgba(255,126,95,0.45);
}

.card-play-btn:active {
  transform: translateY(0);
  box-shadow: 0 4px 12px rgba(255,126,95,0.3);
}

.card-play-btn .pi { font-size: 0.72rem; }

/* ── Loading Skeleton ── */
.skeleton-card { pointer-events: none; }

.sk {
  border-radius: 8px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.sk-icon  { width: 72px; height: 72px; border-radius: 50%; }
.sk-title { height: 14px; width: 65%; }
.sk-btn   { height: 30px; width: 55%; border-radius: 7px; }

/* ── Empty State ── */
.empty-state {
  text-align: center;
  padding: 4rem 1rem;
}

.empty-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  display: block;
  color: rgba(255,255,255,0.45);
}

.empty-msg {
  font-size: 1rem;
  color: rgba(255,255,255,0.7);
  margin: 0 0 1.25rem;
}

.empty-reset {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 1.3rem;
  background: #FF7E5F;
  border: none;
  border-radius: 7px;
  color: #fff;
  font-family: inherit;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 6px 18px rgba(255,126,95,0.35);
  transition: opacity 0.2s, transform 0.15s;
}
.empty-reset:hover { opacity: 0.88; transform: translateY(-1px); }

/* ── Responsive ── */
@media (max-width: 1024px) {
  .categorias-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
  .page-header  { flex-direction: column; align-items: flex-start; }
  .search-wrapper { width: 100%; }
  .quick-actions  { grid-template-columns: 1fr; }
  .categorias-grid { grid-template-columns: repeat(2, 1fr); }
  .page-title   { font-size: 1.7rem; }
}

@media (max-width: 480px) {
  .main-content { padding: 1.5rem 0 3rem; }
  .categorias-grid { gap: 1rem; }
  .card-icon-circle { width: 60px; height: 60px; font-size: 1.3rem; }
}
</style>