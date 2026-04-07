<template>
  <div class="home-page">
    <HomeNavbar />
    <HeroSection />
    <HowToSection />
    <RankingSection />
    <CategoriesSection />
    <HomeFooter />
  </div>
</template>

<script setup>
import HomeNavbar from '@/layouts/HomeNavbar.vue'
import HeroSection from '@/components/home/HeroSection.vue'
</script>