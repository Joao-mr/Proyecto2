<template>
  <div class="misalas-page">
    <HomeNavbar />

    <main class="main-content">
      <div class="misalas-header row g-3 align-items-start">
        <div class="col-12 col-lg">
          <h1 class="misalas-title mb-1">Tus salas</h1>
          <p class="misalas-subtitle mb-0">Crea, edita o elimina tus salas de juego</p>
        </div>

        <div class="col-12 col-lg-auto d-grid d-lg-block">
          <button class="btn btn-primary" @click="showForm('create')">+ Nueva sala</button>
        </div>
      </div>

      <!-- Formulario crear / editar -->
      <div v-if="formVisible" class="sala-form-card">
        <h2 class="form-title">{{ editingId ? 'Editar sala' : 'Nueva sala' }}</h2>

        <div class="form-group">
          <label>Nombre</label>
          <input v-model="sala.nombre" class="form-input" placeholder="Nombre de la sala" />
          <span v-if="hasError('nombre')" class="form-error">{{ getError('nombre') }}</span>
        </div>

        <div class="form-group">
          <label>Código</label>
          <input v-model="sala.codigo" class="form-input" placeholder="Código único" />
          <span v-if="hasError('codigo')" class="form-error">{{ getError('codigo') }}</span>
        </div>

        <div class="form-group">
          <label>Tiempo por respuesta (segundos)</label>
          <input v-model.number="sala.tiempo_respuesta" type="number" min="5" max="300" class="form-input" placeholder="30" />
          <span v-if="hasError('tiempo_respuesta')" class="form-error">{{ getError('tiempo_respuesta') }}</span>
        </div>

        <div class="form-group">
          <label>Categorías</label>
          <div class="categorias-check-list">
            <label
              v-for="cat in categorias"
              :key="cat.id"
              class="check-item"
            >
              <input
                type="checkbox"
                :value="cat.id"
                v-model="sala.categorias"
              />
              {{ cat.nombre }}
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-primary" :disabled="isLoading" @click="handleSubmit">
            {{ isLoading ? 'Guardando...' : (editingId ? 'Guardar cambios' : 'Crear sala') }}
          </button>
          <button class="btn-secondary" @click="cancelForm">Cancelar</button>
        </div>
      </div>

      <!-- Lista de salas -->
      <div v-if="isLoading && !formVisible" class="loading-text">Cargando salas...</div>

      <div v-else-if="misSalas.length === 0 && !formVisible" class="empty-state">
        <span class="empty-icon">🎮</span>
        <p>No tienes salas creadas todavía.</p>
      </div>

      <div v-else class="row g-4">
        <div v-for="s in misSalas" :key="s.id" class="col-12 col-md-6 col-xl-4">
          <div class="sala-card h-100">
            <div class="sala-card__header">
              <h3 class="sala-card__name">{{ s.nombre }}</h3>
              <span class="sala-card__code">{{ s.codigo }}</span>
            </div>
            <div class="sala-card__meta">
              <span class="sala-card__time">⏱ {{ s.tiempo_respuesta }}s por respuesta</span>
              <span class="sala-card__cats">
                {{ s.categorias?.length ? s.categorias.map(c => c.nombre).join(', ') : 'Sin categorías' }}
              </span>
            </div>
            <div class="sala-card__actions">
              <button class="btn-play" @click="jugarSala(s.id)">▶ Jugar</button>
              <button class="btn-edit" @click="showForm('edit', s)">Editar</button>
              <button class="btn-delete" @click="handleDelete(s.id)">Eliminar</button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import HomeNavbar from '@/layouts/HomeNavbar.vue';
import { authStore } from '@/store/auth';
import useSalas from '@/composables/salas';
import useCategorias from '@/composables/categorias';

const auth = authStore();
const router = useRouter();

const {
  salas,
  sala,
  isLoading,
  hasError,
  getError,
  resetSala,
  setSala,
  upsertSalaRecord,
  getSalas,
  createSala,
  updateSala,
  deleteSala,
} = useSalas();

const { categorias, getCategorias } = useCategorias();

const formVisible = ref(false);
const editingId = ref(null);

const misSalas = computed(() =>
  salas.value.filter(s => s.id_creador === auth.user?.id)
);

onMounted(async () => {
  await Promise.all([getSalas(), getCategorias()]);
});

function showForm(mode, salaData = null) {
  editingId.value = mode === 'edit' ? salaData.id : null;
  if (mode === 'edit') {
    setSala(salaData);
  } else {
    resetSala();
  }
  formVisible.value = true;
}

function cancelForm() {
  formVisible.value = false;
  editingId.value = null;
  resetSala();
}

async function handleSubmit() {
  try {
    if (editingId.value) {
      const updated = await updateSala();
      if (updated) upsertSalaRecord(updated);
    } else {
      const created = await createSala();
      if (created) upsertSalaRecord(created);
    }
    cancelForm();
    await getSalas();
  } catch {
    // errors handled inside composable
  }
}

async function handleDelete(id) {
  if (!window.confirm('¿Seguro que quieres eliminar esta sala?')) return;
  await deleteSala(id);
  await getSalas();
}

function jugarSala(id) {
  router.push({ name: 'game.sala', params: { id } });
}
</script>

<style scoped>
*{box-sizing:border-box;}

.misalas-page {
  min-height: 100vh;
  background: linear-gradient(190deg, #5f74b7 25%, #a6aec5 100%);
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  padding-top: 96px;
}

.main-content {
  width: min(1100px, 92%);
  margin: 0 auto;
  padding: 3rem 0;
}

/* Header */
.misalas-header {
  margin-bottom: 2.5rem;
}
.misalas-title {
  font-size: 2.5rem;
  font-weight: 900;
  color: #eef2ff;
  margin: 0;
}
.misalas-subtitle {
  font-size: 1rem;
  color: rgba(238,242,255,0.7);
  margin: 0;
  flex: 1;
}

/* Buttons */
.btn-primary {
  background: #ff724f;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.6rem 1.4rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-primary:hover:not(:disabled) { background: #e05c38; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary {
  background: rgba(255,255,255,0.15);
  color: #eef2ff;
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 8px;
  padding: 0.6rem 1.2rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-secondary:hover { background: rgba(255,255,255,0.25); }

/* Form card */
.sala-form-card {
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.22);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2.5rem;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.form-title {
  color: #eef2ff;
  font-size: 1.4rem;
  font-weight: 800;
  margin: 0 0 1.5rem;
}
.form-group {
  margin-bottom: 1.2rem;
}
.form-group label {
  display: block;
  color: rgba(238,242,255,0.85);
  font-weight: 600;
  margin-bottom: 0.4rem;
  font-size: 0.9rem;
}
.form-input {
  width: 100%;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 8px;
  padding: 0.55rem 0.9rem;
  color: #eef2ff;
  font-size: 1rem;
  outline: none;
  transition: border-color 0.2s;
}
.form-input:focus { border-color: #ff724f; }
.form-input::placeholder { color: rgba(238,242,255,0.4); }
.form-error {
  color: #ffb3a0;
  font-size: 0.82rem;
  margin-top: 0.25rem;
  display: block;
}

/* Categorías checkboxes */
.categorias-check-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}
.check-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 6px;
  padding: 0.35rem 0.75rem;
  color: #eef2ff;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 0.15s;
}
.check-item:hover { background: rgba(255,255,255,0.15); }
.check-item input { accent-color: #ff724f; }

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
}

/* Grid de salas */
/* .salas-grid { ... } -> puedes eliminarlo si ya no se usa */

.sala-card {
  background: rgba(255,255,255,0.10);
  border: 1px solid rgba(255,255,255,0.22);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.sala-card__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}
.sala-card__name {
  color: #eef2ff;
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0;
}
.sala-card__code {
  background: rgba(255,114,79,0.25);
  color: #ffb3a0;
  border-radius: 6px;
  padding: 0.2rem 0.6rem;
  font-size: 0.8rem;
  font-weight: 700;
}
.sala-card__meta {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  margin-bottom: 1.2rem;
}
.sala-card__time, .sala-card__cats {
  color: rgba(238,242,255,0.7);
  font-size: 0.88rem;
}
.sala-card__actions {
  display: flex;
  gap: 0.75rem;
}
.btn-play {
  flex: 1;
  background: #ff724f;
  border: none;
  color: #fff;
  border-radius: 8px;
  padding: 0.5rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-play:hover { background: #e05c38; }
.btn-edit {
  flex: 1;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.25);
  color: #eef2ff;
  border-radius: 8px;
  padding: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-edit:hover { background: rgba(255,255,255,0.2); }
.btn-delete {
  flex: 1;
  background: rgba(220,38,38,0.15);
  border: 1px solid rgba(220,38,38,0.4);
  color: #fca5a5;
  border-radius: 8px;
  padding: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-delete:hover { background: rgba(220,38,38,0.28); }

/* States */
.loading-text {
  text-align: center;
  color: rgba(238,242,255,0.6);
  font-size: 1rem;
  padding: 3rem 0;
}
.empty-state {
  text-align: center;
  color: rgba(238,242,255,0.6);
  padding: 4rem 0;
}
.empty-icon { font-size: 3rem; display: block; margin-bottom: 1rem; }
</style>
