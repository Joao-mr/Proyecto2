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
            user.value = response.data.data
            authenticated.value = true
        } catch (error) {
            user.value = {}
            authenticated.value = false
        }
    }

    async function getUserSignIn() {
        try {
            const response = await axios.get('/api/user/signin')
            user.value = response.data.data
            authenticated.value = true
        } catch (error) {
            user.value = {}
            authenticated.value = false
        }
    }
    function logout() {
        user.value = {}
        authenticated.value = false
    }

    function is(roleName) {
        return user.value.roles.some(role => role.name === roleName);
    }

    return { user, authenticated, login, is, getUser,getUserSignIn, logout};
}, {persist: true});
