import { ref } from "vue";
import axios from "axios";
import useUsers from "./users";
import useCategorias from "./categorias";
import useRoles from "./roles";
import { useToast } from "./useToast";

export default function useAdminDashboardStats() {
    const stats = ref({
        users: 0,
        categories: 0,
        roles: 0
    });
    const isResettingStats = ref(false);

    const { categorias, getCategorias } = useCategorias();
    const { users, getUsers } = useUsers();
    const { roles, getRoles } = useRoles();
    const toast = useToast();

    const resolveCollectionCount = (collection) => {
        if (Array.isArray(collection)) {
            return collection.length;
        }

        return collection?.total || collection?.data?.length || 0;
    };

    const loadStats = async () => {
        await Promise.all([getUsers(), getCategorias(), getRoles()]);

        stats.value = {
            users: resolveCollectionCount(users.value),
            categories: resolveCollectionCount(categorias.value),
            roles: resolveCollectionCount(roles.value)
        };
    };

    const resetPlayerStats = async () => {
        if (isResettingStats.value) {
            return;
        }

        isResettingStats.value = true;

        try {
            const response = await axios.post("/api/admin/player-stats/reset");
            toast.success(
                "Estadisticas reiniciadas",
                response.data?.message || "Todos los jugadores vuelven a empezar desde cero."
            );
        } catch (error) {
            toast.error(
                "Error",
                error.response?.data?.message || "No se pudieron reiniciar las estadisticas."
            );
        } finally {
            isResettingStats.value = false;
        }
    };

    return {
        stats,
        isResettingStats,
        loadStats,
        resetPlayerStats
    };
}
