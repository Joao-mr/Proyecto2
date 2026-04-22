<template>
  <section class="hero-section container-home">
    <div class="hero-inner">
      <!-- Contenido -->
      <div class="hero-content-box">
        <img src="/images/logowhatizit.svg" alt="Whatizit" class="home-hero-logo" />

        <h2 class="home-hero-subtitle">
          Adivina la imagen. Gana puntos. Sube en el ranking.
        </h2>

        <p class="home-hero-text">
          Únete a salas temáticas, observa la imagen y escribe la respuesta correcta antes que los demás. Compite en tiempo real, gana puntos y escala posiciones en el ranking global.
        </p>

        <button class="home-btn-play" @click="goPlay">
          <a href="/categorias">Jugar ahora <span>›</span></a>
        </button>
      </div>

      <!-- Imagen -->
      <div class="hero-image-box">
        <img
          src="/images/Mascota_Whatizit.webp"
          alt="Mascota Whatizit"
          class="home-hero-image"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()
const goPlay = () => router.push('/login')
</script>