<template>
  <section class="row g-3">
    <div v-for="item in items" :key="item.key" class="col-12 col-sm-6 col-xl-3">
      <article class="profile-kpi p-3 h-100">
        <small class="d-block mb-1 profile-kpi__label">{{ item.label }}</small>
        <strong class="fs-4 profile-kpi__value">{{ item.value }}</strong>
      </article>
    </div>
  </section>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => []
  }
})
</script>
