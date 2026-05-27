import { storeToRefs } from 'pinia'
import { useCategoriesStore } from '../store/categoriesStore'

export function usePublicCategories() {
  const store = useCategoriesStore()
  const { categories, current, index, loading, error } = storeToRefs(store)

  return {
    categories,
    current,
    index,
    loading,
    error,
    fetchCategories: store.fetchCategories,
    next: store.next,
    prev: store.prev,
    setIndex: store.setIndex,
    setActiveBySlug: store.setActiveBySlug,
    setActiveById: store.setActiveById,
    clearActiveCategory: store.clearActiveCategory
  }
}