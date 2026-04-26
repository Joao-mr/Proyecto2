<template>
    <div class="imagen-upload-container">
        <Card class="upload-card">
            <template #title>
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span><i class="pi pi-upload mr-2"></i>Subir Nueva Imagen</span>
                    <Button
                        icon="pi pi-times"
                        class="p-button-rounded p-button-text p-button-plain"
                        severity="secondary"
                        @click="goBackToIndex"
                    />
                </div>
            </template>

            <template #subtitle>
                Carga una imagen para agregar al banco de imagenes del juego
            </template>

            <template #content>
                <form @submit.prevent="handleUpload" class="vstack gap-4">
                    <!-- File Upload Area -->
                    <div class="upload-area" @dragover.prevent @drop.prevent="handleDrop">
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            @change="handleFileSelect"
                            class="d-none"
                            aria-label="Seleccionar imagen"
                        />

                        <div v-if="!selectedFile" class="upload-placeholder" @click="$refs.fileInput?.click()">
                            <i class="pi pi-cloud-upload"></i>
                            <p class="fs-5 fw-semibold">Arrastra o haz clic para seleccionar</p>
                            <p class="small text-muted">Formatos permitidos: JPG, PNG, GIF, WebP, SVG</p>
                            <p class="small text-muted">Tamaño máximo: 5MB</p>
                        </div>

                        <div v-else class="selected-file">
                            <img
                                v-if="previewUrl"
                                :src="previewUrl"
                                :alt="selectedFile.name"
                                class="preview-image img-frame img-frame--full"
                            />
                            <div class="file-info">
                                <p class="filename">{{ selectedFile.name }}</p>
                                <p class="filesize">{{ formatFileSize(selectedFile.size) }}</p>
                                <Button
                                    label="Cambiar archivo"
                                    icon="pi pi-refresh"
                                    class="p-button-text p-button-sm mt-2"
                                    @click="$refs.fileInput?.click()"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div v-if="uploadProgress > 0" class="progress-container">
                        <ProgressBar :value="uploadProgress" />
                        <p class="small text-center text-muted mt-2">{{ uploadProgress }}% completado</p>
                    </div>

                    <!-- Respuesta Correcta Input -->
                    <div>
                        <label for="respuesta" class="d-block small fw-medium text-dark mb-2">
                            Respuesta Correcta
                            <span class="text-danger">*</span>
                        </label>
                        <InputText
                            v-model="respuestaCorrecta"
                            id="respuesta"
                            type="text"
                            placeholder="Ej: Manzana, Gato, Capital de Francia"
                            class="w-100"
                            :disabled="isLoading || uploadProgress > 0"
                            maxlength="255"
                        />
                        <small class="text-muted d-block mt-1">
                            Ingresa la respuesta correcta asociada a esta imagen
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                        <Button
                            label="Cancelar"
                            icon="pi pi-times"
                            class="p-button-outlined"
                            severity="secondary"
                            :disabled="isLoading || uploadProgress > 0"
                            @click="goBackToIndex"
                        />
                        <Button
                            label="Subir Imagen"
                            icon="pi pi-upload"
                            :loading="isLoading"
                            :disabled="!selectedFile || isLoading"
                            type="submit"
                        />
                    </div>
                </form>
            </template>
        </Card>

        <!-- Recently Uploaded -->
        <Card v-if="imagenes.length > 0" class="mt-6">
            <template #title>
                <span><i class="pi pi-images mr-2"></i>Imagenes Recientes</span>
            </template>

            <template #content>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                    <div
                        v-for="imagen in imagenes.slice(0, 8)"
                        :key="imagen.id"
                        class="imagen-thumbnail"
                    >
                        <img
                            v-if="getImageUrl(imagen, 'thumb')"
                            :src="getImageUrl(imagen, 'thumb')"
                            :alt="`Imagen ${imagen.id}`"
                            class="thumbnail-image img-frame img-frame--full"
                        />
                        <div v-else class="thumbnail-placeholder">
                            <i class="pi pi-image"></i>
                        </div>
                        <div class="thumbnail-overlay">
                            <Button
                                icon="pi pi-eye"
                                class="p-button-rounded p-button-sm"
                                @click="viewImage(imagen)"
                            />
                            <Button
                                icon="pi pi-trash"
                                class="p-button-rounded p-button-sm p-button-danger"
                                @click="deleteImage(imagen.id)"
                            />
                        </div>
                        <p class="thumbnail-label">{{ imagen.id }}</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Image Viewer Dialog -->
        <Dialog
            v-model:visible="viewDialogVisible"
            header="Vista Previa"
            :modal="true"
            class="w-100"
        >
            <img
                v-if="selectedImageToView"
                :src="getImageUrl(selectedImageToView, 'preview')"
                :alt="`Imagen ${selectedImageToView.id}`"
                class="w-100 img-frame img-frame--full"
            />
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import useImagen from '@/composables/useImagen'

const fileInput = ref(null)
const selectedFile = ref(null)
const previewUrl = ref(null)
const respuestaCorrecta = ref('')
const viewDialogVisible = ref(false)
const selectedImageToView = ref(null)
const router = useRouter()

const { imagenes, isLoading, uploadProgress, getImagenes, uploadImagenNew, deleteImagen, getImageUrl } = useImagen()

const goBackToIndex = () => {
    router.push({ name: 'imagenes-juego.index' })
}

/**
 * Manejador de seleccion de archivo
 */
const handleFileSelect = (event) => {
    const file = event.target.files?.[0]
    if (file) {
        selectedFile.value = file
        generatePreview(file)
    }
}

/**
 * Manejador de drag and drop
 */
const handleDrop = (event) => {
    const file = event.dataTransfer.files?.[0]
    if (file && file.type.startsWith('image/')) {
        selectedFile.value = file
        generatePreview(file)
    }
}

/**
 * Generar preview de la imagen
 */
const generatePreview = (file) => {
    const reader = new FileReader()
    reader.onload = (e) => {
        previewUrl.value = e.target.result
    }
    reader.readAsDataURL(file)
}

/**
 * Subir imagen
 */
const handleUpload = async () => {
    if (!selectedFile.value) return

    try {
        const result = await uploadImagenNew(selectedFile.value, respuestaCorrecta.value)
        if (result) {
            resetForm()
            await getImagenes()
        }
    } catch (error) {
        // El composable ya muestra el toast de error; evitamos romper el handler UI.
        console.error('Error al subir imagen en Upload.vue:', error)
    }
}

/**
 * Limpiar formulario
 */
const resetForm = () => {
    selectedFile.value = null
    previewUrl.value = null
    respuestaCorrecta.value = ''
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

/**
 * Ver imagen en modal
 */
const viewImage = (imagen) => {
    selectedImageToView.value = imagen
    viewDialogVisible.value = true
}

/**
 * Eliminar imagen
 */
const deleteImage = async (id) => {
    try {
        await deleteImagen(id)
        await getImagenes()
    } catch (error) {
        console.error('Error al eliminar:', error)
    }
}

/**
 * Formatear tamano de archivo
 */
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

// Cargar imagenes al montar
onMounted(() => {
    getImagenes()
})
</script>

<style scoped>
.imagen-upload-container {
    padding: 1rem;
}

.upload-card {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 0.5rem;
    background-color: #f8fafc;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: #0ea5e9;
    background-color: #f0f9ff;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.upload-placeholder i {
    font-size: 3rem;
    color: #64748b;
}

.selected-file {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.preview-image {
    width: 150px;
    height: 150px;
    border-radius: 0.375rem;
    object-fit: contain;
    object-position: center;
    background: #000;
    border: 1px solid #e2e8f0;
}

.file-info {
    flex: 1;
}

.filename {
    font-weight: 500;
    color: #1e293b;
    word-break: break-all;
}

.filesize {
    font-size: 0.875rem;
    color: #64748b;
}

.progress-container {
    width: 100%;
}

.imagen-thumbnail {
    position: relative;
    overflow: hidden;
    border-radius: 0.375rem;
    aspect-ratio: 1;
    background-color: #f1f5f9;
}

.thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    background: #000;
}

.thumbnail-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e2e8f0;
}

.thumbnail-placeholder i {
    font-size: 2rem;
    color: #94a3b8;
}

.thumbnail-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.imagen-thumbnail:hover .thumbnail-overlay {
    opacity: 1;
}

.thumbnail-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    padding: 0.25rem;
    font-size: 0.75rem;
    text-align: center;
}
</style>
