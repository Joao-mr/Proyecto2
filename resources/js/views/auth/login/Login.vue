<template>
    <div class="login-page">
        <div class="page-wrapper">
            <div class="logo-wrap">
                <span class="logo-letter wh">W</span><span class="logo-letter wh">H</span><span class="logo-letter wh">A</span><span class="logo-letter wh">T</span><span class="logo-letter or">I</span><span class="logo-letter or">Z</span><span class="logo-letter or">I</span><span class="logo-letter or">T</span><span class="logo-cursor">|</span>
            </div>

            <div class="login-card">
                <form @submit.prevent="submitLogin" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico</label>
                        <input id="email" type="email" v-model="loginForm.email" placeholder="ejemplo@correo.com" class="form-input" :class="{ 'form-input--error': validationErrors?.email }" autocomplete="email" />
                        <div v-if="validationErrors?.email" class="form-error">
                            <span v-for="message in validationErrors.email" :key="message">{{ message }}</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña</label>
                        <div class="password-box" :class="{ 'password-box--active': passwordFocused, 'password-box--error': validationErrors?.password }">
                            <input id="password" :type="showPassword ? 'text' : 'password'" v-model="loginForm.password" placeholder="••••••••" class="password-input" @focus="passwordFocused = true" @blur="passwordFocused = false" autocomplete="current-password" />
                            <button type="button" class="eye-btn" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <div v-if="validationErrors?.password" class="form-error">
                            <span v-for="message in validationErrors.password" :key="message">{{ message }}</span>
                        </div>
                    </div>

                    <div class="form-controls">
                        <label class="remember-label">
                            <input type="checkbox" v-model="loginForm.remember" class="remember-check" /><span>Recordarme</span>
                        </label>
                        <router-link :to="{ name: 'auth.forgot-password' }" class="forgot-link">¿Olvidaste tu contraseña?</router-link>
                    </div>

                    <button type="submit" :disabled="processing" class="submit-btn">
                        <svg v-if="processing" class="btn-spinner" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" width="20" height="20">
                            <circle cx="12" cy="12" r="10" stroke-dasharray="31.4" stroke-dashoffset="10" stroke-linecap="round"/>
                        </svg>
                        <span v-else>Iniciar Sesión</span>
                    </button>

                    <p class="register-text">¿No tienes cuenta? <router-link :to="{ name: 'auth.register' }" class="register-link">Regístrate</router-link></p>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import useAuth from '@/composables/auth';

const { loginForm, validationErrors, processing, submitLogin } = useAuth();

const showPassword = ref(false);
const passwordFocused = ref(false);
</script>

<style scoped>
* { box-sizing: border-box; }

.login-page {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: #505c84;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.page-wrapper {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2.25rem;
    width: 100%;
    max-width: 390px;
}

.logo-wrap {
    display: flex;
    user-select: none;
}

.logo-letter {
    font-size: 2.85rem;
    font-weight: 900;
    letter-spacing: 5px;
}

.logo-letter.wh {
    color: #ffffff;
    text-shadow: 0 0 22px rgba(255, 255, 255, 0.28);
}

.logo-letter.or {
    color: #FACB99;
    text-shadow: 0 0 22px rgba(255, 140, 66, 0.45);
}

.logo-cursor {
    color: rgba(255, 255, 255, 0.88);
    font-size: 2.1rem;
    font-weight: 100;
    margin-left: 4px;
    animation: blink 1.2s step-end infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

.login-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 2.5rem 2.25rem;
    width: 100%;
    box-shadow: 0 30px 70px rgba(0, 0, 0, 0.45), 0 8px 24px rgba(0, 0, 0, 0.25);
}

.form-group { margin-bottom: 1.25rem; }

.form-label {
    display: block;
    font-size: 0.87rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #1f2937;
    background: #fff;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
    border-color: #FF8C42;
    box-shadow: 0 0 0 3px rgba(255, 140, 66, 0.16);
}

.form-input--error {
    border-color: #ef4444;
}

.form-input::placeholder {
    color: #9ca3af;
}

.password-box {
    display: flex;
    align-items: center;
    border: 1.5px solid #d1d5db;
    border-radius: 10px;
    background: #fff;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.password-box--active {
    border-color: #FF8C42;
    box-shadow: 0 0 0 3px rgba(255, 140, 66, 0.16);
}

.password-box--error {
    border-color: #ef4444;
}

.password-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    outline: none;
    font-size: 0.9rem;
    color: #1f2937;
    background: transparent;
}

.password-input::placeholder {
    color: #9ca3af;
}

.eye-btn {
    padding: 0.5rem 0.8rem;
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    display: flex;
    flex-shrink: 0;
    transition: color 0.2s;
}

.eye-btn:hover { color: #6b7280; }

.form-error {
    margin-top: 0.35rem;
    font-size: 0.8rem;
    color: #ef4444;
}

.form-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.6rem;
    gap: 1rem;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    user-select: none;
}

.remember-check {
    width: 15px;
    height: 15px;
    accent-color: #FF8C42;
    cursor: pointer;
}

.forgot-link {
    font-size: 0.875rem;
    color: #6b7280;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
    white-space: nowrap;
}

.forgot-link:hover { color: #FF8C42; }

.submit-btn {
    width: 100%;
    padding: 0.9rem;
    background: #FF7E5F;
    border: none;
    border-radius: 7px;
    color: white;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 8px 24px rgba(255, 126, 95, 0.35);
    transition: opacity 0.2s, transform 0.12s, box-shadow 0.2s;
    margin-bottom: 1.3rem;
}

.submit-btn:hover:not(:disabled) {
    opacity: 0.90;
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(255, 126, 95, 0.45);
}

.submit-btn:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 6px 16px rgba(255, 126, 95, 0.35);
}

.submit-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.btn-spinner { animation: spin 0.85s linear infinite; }

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.register-text {
    text-align: center;
    font-size: 0.88rem;
    color: #6b7280;
    margin: 0;
}

.register-link {
    color: #FF8C42;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.register-link:hover {
    color: #e06e25;
    text-decoration: underline;
}

@media (max-width: 660px) {
    .logo-letter { font-size: 2.1rem; letter-spacing: 3px; }
    .page-wrapper { gap: 1.75rem; }
}
</style>
