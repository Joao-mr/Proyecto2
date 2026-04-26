<template>
  <div class="categorias-edit-page container py-4">
    <h2 class="fs-4 fw-bold mb-4">Editar Categoría</h2>
    <div v-if="isLoading" class="mb-4">Cargando...</div>
    <form v-else @submit.prevent="submitUpdate">
      <div class="mb-4">
        <label for="categoria-nombre" class="block font-medium mb-1">Nombre de la categoría</label>
        <InputText
          v-model="categoria.nombre"
          id="categoria-nombre"
          class="w-100"
          :class="{ 'p-invalid': hasError('nombre') }"
          placeholder="Ej: Famosos"
        />
        <small v-if="hasError('nombre')" class="text-red-500">{{ getError('nombre') }}</small>
      </div>
      <div class="d-flex gap-2 justify-content-end">
        <Button label="Cancelar" severity="secondary" @click="goBack" />
        <Button label="Guardar" type="submit" :loading="isSubmitting" :disabled="isSubmitting" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import useCategorias from '@/composables/categorias';

const route = useRoute();
const router = useRouter();
const id = route.params.id;

const {
  categorias,
  categoria,
  getCategorias,
  updateCategoria,
  setCategoria,
  hasError,
  getError,
  isLoading
} = useCategorias();

const isSubmitting = computed(() => isLoading.value);

onMounted(async () => {
  await getCategorias();
  const currentCategoria = categorias.value.find(c => c.id == id);
  if (currentCategoria) setCategoria(currentCategoria);
});

const submitUpdate = async () => {
  if (isSubmitting.value) return;
  await updateCategoria();
  router.push('/admin/categorias');
};

const goBack = () => {
  router.push('/admin/categorias');
};
</script>

