import axios from 'axios'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const rankingStore = defineStore('rankingStore', () => {

    const players = ref([])
    const order = ref('desc')
    const loading = ref(false)

    async function getRanking() {

        try {

            loading.value = true

            const response = await axios.get('/api/public/rankings')
            console.log(response.data)

            players.value =
                response.data.data
                ? response.data.data
                : response.data

        } catch (error) {

            console.log(error)

        } finally {

            loading.value = false
        }
    }

    const sortedPlayers = computed(() => {

        return [...players.value].sort((a, b) => {

            if (order.value === 'asc') {
                return a.elo - b.elo
            }

            return b.elo - a.elo
        })
    })

    function toggleOrder() {

        order.value =
            order.value === 'asc'
                ? 'desc'
                : 'asc'
    }

    return {

        players,
        sortedPlayers,
        order,
        loading,

        getRanking,
        toggleOrder
    }

}, { persist: true })