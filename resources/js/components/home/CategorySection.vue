<template>
  <section class="categories-section">
    <div class="container-home">
      <h2 class="categories-title">Categorías</h2>

      <div class="categories-carousel">
        <button
          class="categories-arrow categories-arrow--left"
          @click="prev"
          aria-label="Anterior"
          :disabled="categories.length <= 1"
        >
          ←
        </button>

        <button
          class="categories-arrow categories-arrow--right"
          @click="next"
          aria-label="Siguiente"
          :disabled="categories.length <= 1"
        >
          →
        </button>

        <div class="categories-slide" v-if="current">
          <div class="categories-main">
            <div class="categories-main__media">
              <div class="categories-image-box">
                <img :src="current.image" :alt="current.name" class="categories-image" />
              </div>
            </div>

            <div class="categories-main__info">
              <div class="categories-content">
                <h3 class="categories-name">{{ current.name }}</h3>
                <p class="categories-description">{{ current.description }}</p>
                <div class="categories-divider"></div>

                <button class="categories-btn">
                  <a href="/categorias"><span>›</span> ¡Jugar ahora!</a>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="categories-slide" v-else>
          <div class="categories-main">
            <div class="categories-main__info">
              <div class="categories-content">
                <h3 class="categories-name">CATEGORÍAS</h3>
                <p class="categories-description">
                  {{ loading ? 'Cargando categorías...' : (error || 'No hay categorías disponibles.') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategories } from '../../composables/useCategories'

const router = useRouter()
const { categories, current, loading, error, fetchCategories, next, prev } = useCategories()

onMounted(() => {
  fetchCategories()
})

const goToRooms = () => {
  if (!current.value) return
  router.push({ path: '/salas', query: { categoria: current.value.slug } })
}
</script>