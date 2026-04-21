<template>
  <div class="home-page">
    <HomeNavbar />

    <main class="py-3 py-lg-4">
      <RankingCategorySection />
    </main>

    <HomeFooter />
  </div>
</template>

<script setup>
import HomeNavbar from '@/layouts/HomeNavbar.vue'
import HomeFooter from '@/layouts/HomeFooter.vue'
import RankingCategorySection from '@/components/RankingCategory/RankingCategorySection.vue'
</script>