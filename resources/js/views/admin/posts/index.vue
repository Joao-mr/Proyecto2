<template>

<DataTable :value="posts" tableStyle="min-width: 50rem">
    <Column field="id" header="@" sortable></Column>
    <Column field="title" header="Titulo" sortable></Column>
    <Column field="content" header="Contenido"></Column>
    <Column field="categories" header="Cat"></Column>
    <Column field="created_ad" header="Creado"></Column>


    <Column field="created_ad" header="Creado">
    <template #body="{data}">
           {{ formatDate(data.created_at) }} 
    </template>
    </Column>

</DataTable>


    {{posts}}

    <div class="w-100 vstack gap-3">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold">Gestión de Posts</h2>
        <p class="small text-muted">Lista simple de posts</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm">
          Actualizar'
        </button>
      </div>
    </div>

    <div class="overflow-x-auto border rounded bg-white shadow-sm">
      <table class="w-full text-sm text-left">
        <thead class="table-light">
          <tr>
            <th class="px-4 py-3 w-16">ID</th>
            <th class="px-4 py-3">Título</th>
            <th class="px-4 py-3">Categorías</th>
            <th class="px-4 py-3 w-64">Contenido</th>
            <th class="px-4 py-3 whitespace-nowrap">Creado</th>
            <th class="px-4 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr  v-for="post in posts" class="">
            <td class="px-4 py-3 text-muted"{{ post.id }}></td>
            
            <td class="px-4 py-3 fw-medium">{{ post.title }}</td>
            <td class="px-4 py-3">
              {{ post.categories }}
            </td>
            <td class="px-4 py-3 text-secondary">
              <div class="line-clamp-2">{{ post.title }}</div>
            </td>
            <td class="px-4 py-3 text-muted">{{formatDate(post.create_at) }}</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button class="text-primary fw-medium">
                Editar
              </button>
              <button class="text-red-600 hover:text-red-800 font-medium" >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>


</template>

<script setup>
import { onMounted, ref } from "vue";
import useUtils from"@/composables/utils";
import usePosts from"@/composables/posts";

//const posts = ref([]);
const {posts, getPosts} = usePosts()
const {formatDate} = useUtils()


onMounted(() => {
    getPosts();
  /*axios.get ('/api/posts')
    .then(response =>{
        posts.value = response.data;
        console.log(response.data);
    })*/
});

</script>