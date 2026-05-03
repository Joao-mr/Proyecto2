import axios from 'axios';
import { ref } from "vue";
import { defineStore } from "pinia";

export const authStore = defineStore("authStore", () => {

    const user = ref({name:''});
    const authenticated = ref(false);

    async function login() {
        await getUser()
    }

    async function getUser() {
        try {
            const response = await axios.get('/api/user')
            const payload = response.data?.data ?? response.data
            user.value = payload ?? {}
            authenticated.value = Boolean(payload?.id || payload?.email)
        } catch (error) {
            if ([401, 419].includes(error?.response?.status)) {
                user.value = {}
                authenticated.value = false
            }
        }
    }

    async function getUserSignIn() {
        return getUser()
    }

    function logout() {
        user.value = {}
        authenticated.value = false
    }

    function is(roleName) {
        return user.value.roles.some(role => role.name === roleName);
    }

    return { user, authenticated, login, is, getUser, getUserSignIn, logout};
}, {persist: true});
