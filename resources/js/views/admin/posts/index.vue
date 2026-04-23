<template>
  <div class="w-100 vstack gap-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h2 class="h5 mb-1">Gestion de Posts</h2>
        <p class="small text-muted mb-0">Listado de posts registrados</p>
      </div>

      <Button
        type="button"
        label="Actualizar"
        icon="pi pi-refresh"
        size="small"
        outlined
        @click="refreshPosts"
      />
    </div>

    <DataTable :value="posts" tableStyle="min-width: 50rem">
      <Column field="id" header="ID" sortable />
      <Column field="title" header="Titulo" sortable />

      <Column header="Categorias">
        <template #body="{ data }">
          {{ formatCategories(data.categories) }}
        </template>
      </Column>

      <Column header="Contenido">
        <template #body="{ data }">
          <span class="line-clamp-2">{{ data.content || '-' }}</span>
        </template>
      </Column>

      <Column field="created_at" header="Creado" sortable>
        <template #body="{ data }">
          {{ formatDate(data.created_at) }}
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import useUtils from '@/composables/utils'
import usePosts from '@/composables/posts'

const { posts, getPosts } = usePosts()
const { formatDate } = useUtils()

const refreshPosts = () => {
  getPosts()
}

const formatCategories = (categories) => {
  if (!Array.isArray(categories) || categories.length === 0) {
    return '-'
  }

  return categories
    .map((category) => category?.name)
    .filter(Boolean)
    .join(', ')
}

onMounted(() => {
  refreshPosts()
})
</script>
