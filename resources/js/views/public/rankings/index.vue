<template>
  <div class="home-page">
    <HomeNavbar />

    <main class="py-5">
      <RankingPageSection />
    </main>

    <HomeFooter />
  </div>
</template>

<script setup>
import HomeNavbar from '@/layouts/HomeNavbar.vue'
import HomeFooter from '@/layouts/HomeFooter.vue'
import RankingPageSection from '@/components/rankings/RankingPageSection.vue'
</script>