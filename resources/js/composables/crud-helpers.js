export const unwrapApiData = (response) => response.data?.data ?? response.data

export const createLoadingGuard = (isLoading) => async (fn) => {
  if (isLoading.value) {
    throw new Error('Operacion en curso')
  }

  isLoading.value = true

  try {
    return await fn()
  } finally {
    isLoading.value = false
  }
}

export const upsertById = (items, item) => {
  if (!item?.id) {
    return items
  }

  return [item, ...items.filter((currentItem) => currentItem.id !== item.id)]
}
