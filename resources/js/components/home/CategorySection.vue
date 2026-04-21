<template>
  <section class="categories-section">
    <div class="container-home">
      <h2 class="categories-title">Categorías</h2>

      <div class="categories-carousel">
        <button class="categories-arrow categories-arrow--left" @click="prev" aria-label="Anterior">←</button>
        <button class="categories-arrow categories-arrow--right" @click="next" aria-label="Siguiente">→</button>

        <div class="categories-slide">
          <div class="categories-main">
            <div class="categories-main__media">
              <div class="categories-image-box">
                <img :src="current.image" :alt="current.name" class="categories-image" />
              </div>
            </div>

            <!-- div info (derecha) -->
            <div class="categories-main__info">
              <div class="categories-content">
                <h3 class="categories-name">{{ current.name }}</h3>
                <p class="categories-description">{{ current.description }}</p>
                <div class="categories-divider"></div>

                <button class="categories-btn" @click="goToRooms">
                  <span>›</span> ¡Jugar ahora!
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const index = ref(0)

const categories = [
  {
    slug: 'naturaleza',
    name: 'NATURALEZA',
    description:
      'Animales, plantas, paisajes y fenómenos del mundo natural. Demuestra cuánto sabes sobre la naturaleza más salvaje y sorprendente del planeta.',
    image: '/images/categoria-placeholder.webp'
  },
  {
    slug: 'cine',
    name: 'CINE',
    description:
      'Preguntas sobre películas, actores y cultura cinematográfica. Compite por tiempo y precisión en cada ronda.',
    image: '/images/categoria-placeholder.webp'
  },
  {
    slug: 'historia',
    name: 'HISTORIA',
    description:
      'Retos sobre acontecimientos históricos y personajes clave de todos los tiempos. Suma puntos y escala en el ranking.',
    image: '/images/categoria-placeholder.webp'
  }
]

const current = computed(() => categories[index.value])

const next = () => {
  index.value = (index.value + 1) % categories.length
}

const prev = () => {
  index.value = (index.value - 1 + categories.length) % categories.length
}

const goToRooms = () => {
  router.push({ path: '/salas', query: { categoria: current.value.slug } })
}
</script>