<template>
  <section class="container-home py-5 py-lg-6">
    <div class="row flex align-items-center g-4">
      <!-- Contenedor de contenido -->
      <div class="col-lg-6">
        <div class="hero-content-box mt-4">
          <img src="/images/logowhatizit.svg" alt="Whatizit" class="home-hero-logo mb-3" />

          <h2 class="home-hero-subtitle mb-3 mt-2">
            Adivina la imagen. Gana puntos. Sube en el ranking.
          </h2>

          <p class="home-hero-text mb-4 mt-4">
            Únete a salas temáticas, observa la imagen y escribe la respuesta correcta antes que los demás. Compite en tiempo real, gana puntos y escala posiciones en el ranking global.
          </p>

          <button class="home-btn-play mt-3" @click="goPlay">
            Jugar ahora <span>&gt;</span>
          </button>
        </div>
      </div>

      <!-- Contenedor de imagen -->
      <div class="col-lg-6">
        <div class="hero-image-box">
          <img
            src="/images/Mascota_Whatizit.webp"
            alt="Mascota Whatizit"
            class="img-fluid home-hero-image"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()
const goPlay = () => router.push('/login')
</script>