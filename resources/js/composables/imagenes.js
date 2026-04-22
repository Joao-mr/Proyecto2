import useImagen from './useImagen'

export default function useImagenes() {
  const core = useImagen()

  // Compatibilidad con imports legacy de '@/composables/imagenes'
  return {
    imagenes: core.imagenes,
    imagen: core.imagen,
    isLoading: core.isLoading,
    hasError: core.hasError,
    getError: core.getError,
    resetImagen: core.resetImagen,
    setImagen: core.setImagen,
    upsertImagenRecord: core.upsertImagenRecord,
    getImagenes: core.getImagenes,
    createImagen: core.createImagen,
    updateImagen: core.updateImagen,
    deleteImagen: core.deleteImagen
  }
}
