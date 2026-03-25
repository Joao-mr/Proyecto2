<!-- filepath: c:\xampp\htdocs\Proyecto2\Laravel-VUE-API-Base-Clase\resources\js\views\public\home\index.vue -->
<template>
  <div class="home-page">
    

    <section class="container-home py-10">
      <h1>Home</h1>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { authStore } from '@/store/auth'

const store = authStore()
const isLogged = computed(() => !!store.user?.id)
</script>