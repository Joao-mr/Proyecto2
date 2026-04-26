<template>
  <nav class="game-navbar">
    <div class="game-navbar__inner">
      <RouterLink to="/" class="game-navbar__logo-link" aria-label="Whatizit - Inicio">
        <img src="/images/logowhatizit.svg" alt="Whatizit" class="game-navbar__logo" />
      </RouterLink>

      <span class="game-navbar__sala-name">{{ salaName }}</span>

      <button
        type="button"
        class="game-navbar__exit"
        aria-label="Salir de la partida"
        @click="emit('exit')"
      >
        <i class="pi pi-sign-out"></i>
        Salir
      </button>
    </div>
  </nav>
</template>

<script setup>
defineProps({
  salaName: {
    type: String,
    default: 'Sala de Juego',
  },
});

const emit = defineEmits(['exit']);
</script>
