<template>
    <div class="user-profile-layout">
        <HomeNavbar />
        <main class="user-profile-layout__content">
            <div class="container-home">
                <router-view />
            </div>
        </main>
    </div>
</template>

<script setup>
import HomeNavbar from './HomeNavbar.vue';
</script>

<style scoped>
.user-profile-layout {
    min-height: 100vh;
    background: linear-gradient(190deg, #5f74b7 25%, #a6aec5 100%);
}

.user-profile-layout__content {
    padding-top: 110px;
    padding-bottom: 3rem;
}
</style>
