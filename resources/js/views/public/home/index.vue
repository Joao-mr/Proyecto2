<template>
  <div class="home-page">
    <HomeNavbar />
    <HeroSection />
    <InfoSection />
    <RankingSection />
    <CategoriesSection />
    <HomeFooter />
  </div>
</template>

<script setup>
import HomeNavbar from '@/layouts/HomeNavbar.vue'
import HeroSection from '@/components/home/HeroSection.vue'
import InfoSection from '../../../components/home/InfoSection.vue';
</script>