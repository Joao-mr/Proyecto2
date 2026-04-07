<template>
  <header class="home-header">
    <div class="container-home home-header__inner">
      <RouterLink to="/" class="home-logo" aria-label="Whatizit - Inicio">
        <img src="/images/logowhatizit.svg" alt="Whatizit" class="home-logo-img" />
      </RouterLink>

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