<template>
  <div class="home-page">
    <HomeNavbar />
    <HeroSection />
    <InfoSection />
    <RankingSection />
    <CategorySection />
    <HomeFooter />
  </div>
</template>

<script setup>
import HomeNavbar from '@/layouts/HomeNavbar.vue'
import HeroSection from '@/components/home/HeroSection.vue'
import InfoSection from '@/components/home/InfoSection.vue'
import RankingSection from '@/components/home/RankingSection.vue'
import CategorySection from '@/components/home/CategorySection.vue'
//import HomeFooter from '@/components/home/HomeFooter.vue'
</script>