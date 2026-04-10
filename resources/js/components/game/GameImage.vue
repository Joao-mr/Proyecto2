<template>
  <div class="game-image-panel">
    <div v-if="loading" class="game-image-panel__skeleton">
      <span>Cargando imagen…</span>
    </div>
    <template v-else>
      <img
        :src="imageSrc"
        alt="Imagen a adivinar"
        class="game-image-panel__img"
        @error="onImgError"
      />
      <div class="game-image-panel__badge">Ronda {{ round }} / {{ totalRounds }}</div>
    </template>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  imageSrc: {
    type: String,
    default: null,
  },
  round: {
    type: Number,
    default: 1,
  },
  totalRounds: {
    type: Number,
    default: 5,
  },
});

const loading = ref(!props.imageSrc);

watch(
  () => props.imageSrc,
  (val) => {
    loading.value = !val;
  },
);

function onImgError(e) {
  e.target.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 240"%3E%3Crect fill="%23334" width="320" height="240"/%3E%3Ctext x="50%25" y="50%25" fill="%23aaa" font-size="20" text-anchor="middle" dominant-baseline="middle"%3E%3F%3C/text%3E%3C/svg%3E';
}
</script>
