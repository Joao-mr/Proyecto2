<template>
  <div class="categorias-create-page container py-4" style="max-width: 576px;">
    <h2 class="fs-4 fw-bold mb-4">Crear Categoría</h2>
    <div v-if="isLoading" class="mb-4">Cargando...</div>
    <form v-else @submit.prevent="submitCreate">
      <div class="mb-4">
        <label for="categoria-nombre" class="d-block fw-medium mb-1">Nombre de la categoría</label>
        <InputText
          v-model="categoria.nombre"
          id="categoria-nombre"
          class="w-100"
          :class="{ 'p-invalid': hasError('nombre') }"
          placeholder="Ej: Famosos"
        />
        <small v-if="hasError('nombre')" class="text-danger">{{ getError('nombre') }}</small>
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <Button label="Cancelar" severity="secondary" @click="goBack" />
        <Button label="Crear" type="submit" :loading="isSubmitting" :disabled="isSubmitting" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import useCategorias from '@/composables/categorias';

const router = useRouter();

const {
  categoria,
  createCategoria,
  resetCategoria,
  hasError,
  getError,
  isLoading
} = useCategorias();

const isSubmitting = computed(() => isLoading.value);

onMounted(() => {
  resetCategoria();
});

const submitCreate = async () => {
  if (isSubmitting.value) return;
  const createdCategoria = await createCategoria();
  if (createdCategoria) {
    router.push('/admin/categorias');
  }
};

const goBack = () => {
  router.push('/admin/categorias');
};
</script>

