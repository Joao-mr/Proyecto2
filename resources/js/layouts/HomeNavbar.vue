<!-- filepath: c:\xampp\htdocs\Proyecto2\Laravel-VUE-API-Base-Clase\resources\js\layouts\HomeNavbar.vue -->
<template>
  <header class="home-header">
    <div class="container-home home-header__inner">
      <RouterLink to="/" class="home-logo">WHAT<span>IZIT</span></RouterLink>

      <nav class="home-nav">
        <a href="#" class="home-nav-item">Juegos</a>
        <a href="#" class="home-nav-item">Rankings</a>
        <a href="#" class="home-nav-item">Información</a>
      </nav>

      <div class="home-auth">
        <RouterLink to="/login" class="home-link">Login</RouterLink>
        <RouterLink to="/register" class="home-btn-register">Registrarse <span>&gt;</span></RouterLink>
      </div>
    </div>
  </header>
</template>