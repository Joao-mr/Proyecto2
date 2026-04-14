<template>
  <div class="salas-create-page max-w-xl mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Crear Sala</h2>
    <div v-if="isLoading" class="mb-4">Cargando...</div>
    <form v-else @submit.prevent="submitCreate">
      <div class="mb-4">
        <label for="sala-nombre" class="block font-medium mb-1">Nombre de la sala</label>
        <InputText
          v-model="sala.nombre"
          id="sala-nombre"
          class="w-full"
          :class="{ 'p-invalid': hasError('nombre') }"
          placeholder="Ej: Sala Pop"
        />
        <small v-if="hasError('nombre')" class="text-red-500">{{ getError('nombre') }}</small>
      </div>
      <div class="mb-4">
        <label for="sala-codigo" class="block font-medium mb-1">Código</label>
        <InputText
          v-model="sala.codigo"
          id="sala-codigo"
          class="w-full"
          :class="{ 'p-invalid': hasError('codigo') }"
          placeholder="Ej: ABC123"
        />
        <small v-if="hasError('codigo')" class="text-red-500">{{ getError('codigo') }}</small>
      </div>
      <div class="mb-4">
        <label for="sala-categorias" class="block font-medium mb-1">Categorías</label>
        <MultiSelect
          v-model="sala.categorias"
          input-id="sala-categorias"
          :options="categoriasDisponibles"
          option-label="nombre"
          option-value="id"
          placeholder="Selecciona categorías"
          class="w-full"
          display="chip"
        />
      </div>
      <div class="flex gap-2 justify-end">
        <Button label="Cancelar" severity="secondary" @click="goBack" />
        <Button label="Crear" type="submit" :loading="isSubmitting" :disabled="isSubmitting" />
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import useSalas from '@/composables/salas';

const router = useRouter();

const {
  sala,
  categoriasDisponibles,
  getSalas,
  getCategoriasDisponibles,
  createSala,
  resetSala,
  hasError,
  getError,
  isLoading
} = useSalas();

const isSubmitting = computed(() => isLoading.value);

onMounted(async () => {
  resetSala();
  await getCategoriasDisponibles();
});

const submitCreate = async () => {
  if (isSubmitting.value) return;
  const createdSala = await createSala();
  if (createdSala) {
    router.push('/admin/salas');
  }
};

const goBack = () => {
  router.push('/admin/salas');
};
</script>

