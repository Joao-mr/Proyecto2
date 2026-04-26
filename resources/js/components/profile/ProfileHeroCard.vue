<template>
  <section class="profile-surface profile-hero p-3 p-md-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 gap-md-4 position-relative" style="z-index: 1;">
      <Avatar :image="avatar" style="width: 5.4rem; height: 5.4rem;" shape="circle" />

      <div class="flex-grow-1">
        <h2 class="fs-4 fw-bold mb-1 text-white">{{ name }}</h2>
        <p class="mb-2 text-white-50">{{ subtitle }}</p>
        <div class="profile-title-tag">
          <Tag :value="titleLabel" rounded />
        </div>
      </div>

      <div class="d-flex gap-4">
        <div>
          <small class="d-block profile-metric-label">{{ sessionsLabel }}</small>
          <strong class="profile-metric-value">{{ sessionsValue }}</strong>
        </div>
        <div>
          <small class="d-block profile-metric-label">{{ eloLabel }}</small>
          <strong class="profile-metric-value">{{ eloValue }}</strong>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  avatar: {
    type: String,
    default: ''
  },
  name: {
    type: String,
    default: '-'
  },
  subtitle: {
    type: String,
    default: ''
  },
  titleLabel: {
    type: String,
    default: '-'
  },
  sessionsLabel: {
    type: String,
    default: ''
  },
  sessionsValue: {
    type: [Number, String],
    default: 0
  },
  eloLabel: {
    type: String,
    default: ''
  },
  eloValue: {
    type: [Number, String],
    default: 0
  }
})
</script>
