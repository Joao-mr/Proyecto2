import { watch } from "vue";

export function useStoredTableFilters(storageKey, filters, mergeFilters) {
    const canUseBrowserStorage = typeof window !== "undefined";

    const restore = () => {
        if (!canUseBrowserStorage) {
            return;
        }

        try {
            const stored = window.localStorage.getItem(storageKey);
            if (!stored) {
                return;
            }

            mergeFilters(JSON.parse(stored));
        } catch (_) {
            return;
        }
    };

    const persist = () => {
        if (!canUseBrowserStorage) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(filters.value));
        } catch (_) {
            return;
        }
    };

    watch(filters, persist, { deep: true });

    return { restore };
}
