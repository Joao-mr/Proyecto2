<template>
    <div class="min-h-screen flex flex-col">
        <HomeNavbar/>
        <main class="flex-grow">
            <router-view></router-view>
        </main>
    </div>
</template>
<script setup>
import HomeNavbar from './HomeNavbar.vue'

</script>
