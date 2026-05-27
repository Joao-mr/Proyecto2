
import { storeToRefs } from 'pinia'
import { rankingStore } from '@/store/rankingStore'

export function useRanking() {

    const store = rankingStore()

    const {
        players,
        sortedPlayers,
        order,
        loading
    } = storeToRefs(store)

    return {

        players,
        sortedPlayers,
        order,
        loading,

        getRanking: store.getRanking,
        toggleOrder: store.toggleOrder
    }
}