<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { mediaService, type MediaItem } from '@/services/mediaService'
import { useUiStore } from '@/stores/ui'

interface ImageValue {
  id?: number
  src: string
  title?: string
  alt?: string
}

const props = withDefaults(
  defineProps<{
    modelValue?: ImageValue | null
    label?: string
    helperText?: string
    emptyTitle?: string
    emptySubtitle?: string
    compact?: boolean
  }>(),
  {
    modelValue: () => ({ src: '' }),
    label: 'Imagine Produs',
    helperText: 'Imaginile sunt stocate local pe server. Formate acceptate: JPG, PNG, GIF, WEBP.',
    emptyTitle: 'Încarcă imagine produs',
    emptySubtitle: 'Trage fișierul aici sau apasă pentru a deschide formularul de încărcare',
    compact: false
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: ImageValue): void
}>()

const uiStore = useUiStore()

// State
const isModalOpen = ref(false)
const activeTab = ref<'upload' | 'library' | 'url'>('upload')
const isDragging = ref(false)
const selectedFile = ref<File | null>(null)
const localPreviewUrl = ref<string>('')
const uploadProgress = ref<number>(0)
const isUploading = ref(false)
const uploadError = ref<string>('')

// Media library state
const libraryLoading = ref(false)
const libraryItems = ref<MediaItem[]>([])
const librarySearch = ref('')
const libraryPage = ref(1)
const libraryTotalPages = ref(1)
const selectedLibraryItem = ref<MediaItem | null>(null)

// Direct URL input state
const customUrlInput = ref('')

// Lightbox full-size preview
const isLightboxOpen = ref(false)

const currentImageSrc = computed(() => props.modelValue?.src || '')
const currentImageId = computed(() => props.modelValue?.id)

// Watch modelValue to sync customUrlInput
watch(
  () => props.modelValue?.src,
  (newSrc) => {
    if (newSrc) {
      customUrlInput.value = newSrc
    }
  },
  { immediate: true }
)

const openModal = (tab: 'upload' | 'library' | 'url' = 'upload') => {
  activeTab.value = tab
  resetUploadState()
  isModalOpen.value = true
  if (tab === 'library' && libraryItems.value.length === 0) {
    loadLibrary()
  }
}

const closeModal = () => {
  if (isUploading.value) return
  isModalOpen.value = false
  resetUploadState()
}

const resetUploadState = () => {
  selectedFile.value = null
  if (localPreviewUrl.value) {
    URL.revokeObjectURL(localPreviewUrl.value)
    localPreviewUrl.value = ''
  }
  uploadProgress.value = 0
  uploadError.value = ''
  selectedLibraryItem.value = null
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    processSelectedFile(target.files[0])
  }
}

const handleDrop = (event: DragEvent) => {
  isDragging.value = false
  if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
    processSelectedFile(event.dataTransfer.files[0])
  }
}

const processSelectedFile = (file: File) => {
  uploadError.value = ''
  const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']

  if (!validTypes.includes(file.type)) {
    uploadError.value = 'Fișierul selectat nu este o imagine validă (doar JPG, PNG, GIF, WEBP).'
    return
  }

  // Max 32MB
  if (file.size > 32 * 1024 * 1024) {
    uploadError.value = 'Imaginea depășește dimensiunea maximă de 32MB.'
    return
  }

  selectedFile.value = file
  if (localPreviewUrl.value) {
    URL.revokeObjectURL(localPreviewUrl.value)
  }
  localPreviewUrl.value = URL.createObjectURL(file)
}

const uploadSelectedFile = async () => {
  if (!selectedFile.value) return

  isUploading.value = true
  uploadProgress.value = 0
  uploadError.value = ''

  try {
    const uploaded = await mediaService.upload(selectedFile.value, (percent) => {
      uploadProgress.value = percent
    })

    emit('update:modelValue', {
      id: uploaded.id,
      src: uploaded.url,
      title: uploaded.title
    })

    uiStore.addNotification('success', 'Imaginea a fost încărcată pe server!')
    closeModal()
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Eroare la încărcarea imaginii pe server.'
    uploadError.value = msg
    uiStore.addNotification('error', msg)
  } finally {
    isUploading.value = false
  }
}

const loadLibrary = async (page = 1) => {
  libraryLoading.value = true
  libraryPage.value = page
  try {
    const res = await mediaService.list({
      page,
      per_page: 18,
      search: librarySearch.value.trim() || undefined
    })
    libraryItems.value = res.items
    libraryTotalPages.value = res.total_pages
  } catch (err) {
    uiStore.addNotification('error', 'Nu s-a putut încărca galeria media de pe server.')
  } finally {
    libraryLoading.value = false
  }
}

const selectFromLibrary = (item: MediaItem) => {
  selectedLibraryItem.value = item
}

const confirmLibrarySelection = () => {
  if (!selectedLibraryItem.value) return
  emit('update:modelValue', {
    id: selectedLibraryItem.value.id,
    src: selectedLibraryItem.value.url,
    title: selectedLibraryItem.value.title
  })
  uiStore.addNotification('success', 'Imaginea a fost selectată din galerie.')
  closeModal()
}

const applyCustomUrl = () => {
  const url = customUrlInput.value.trim()
  if (!url) return
  emit('update:modelValue', {
    src: url
  })
  uiStore.addNotification('success', 'URL-ul imaginii a fost actualizat.')
  closeModal()
}

const removeImage = () => {
  emit('update:modelValue', {
    id: undefined,
    src: '',
    title: ''
  })
  customUrlInput.value = ''
  uiStore.addNotification('info', 'Imaginea a fost eliminată.')
}

const formatFileSize = (bytes: number): string => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

onMounted(() => {
  if (props.modelValue?.src) {
    customUrlInput.value = props.modelValue.src
  }
})
</script>

<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between">
      <label class="block text-xs font-semibold text-gray-700">
        {{ label }}
      </label>
      <span v-if="currentImageSrc" class="text-[11px] text-gray-400 font-mono truncate max-w-xs">
        ID: {{ currentImageId || 'extern' }}
      </span>
    </div>

    <!-- MAIN DISPLAY CARD -->
    <div
      v-if="currentImageSrc"
      class="bg-gray-50 border border-gray-200 rounded-2xl transition-all hover:border-gray-300"
      :class="compact ? 'p-3 flex flex-col sm:flex-row items-center gap-3' : 'p-4 flex flex-col sm:flex-row items-center gap-4'"
    >
      <!-- Thumbnail Preview -->
      <div
        class="relative group flex-shrink-0 rounded-xl overflow-hidden bg-white border border-gray-200 shadow-sm flex items-center justify-center cursor-pointer"
        :class="compact ? 'w-24 h-24' : 'w-32 h-32'"
        @click="isLightboxOpen = true"
        title="Apasă pentru previzualizare mărită"
      >
        <img
          :src="currentImageSrc"
          :alt="label"
          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          @error="($event.target as HTMLImageElement).src = 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' fill=\'%23ccc\'><rect width=\'100%\' height=\'100%\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'12\' fill=\'%23666\'>Fără imagine</text></svg>'"
        />
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 text-white">
          <span class="text-[11px] font-semibold flex items-center gap-1 bg-black/50 px-2 py-1 rounded-md">
            🔍 Mărește
          </span>
        </div>
      </div>

      <!-- Info & Actions -->
      <div class="flex-1 min-w-0 w-full space-y-2">
        <div class="space-y-0.5">
          <p class="text-xs font-medium text-gray-800 truncate" :title="currentImageSrc">
            {{ currentImageSrc.split('/').pop() || 'Imagine curentă' }}
          </p>
          <p class="text-[11px] text-gray-400 font-mono truncate" :title="currentImageSrc">
            {{ currentImageSrc }}
          </p>
          <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            Stocată local pe server
          </span>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 pt-1">
          <button
            type="button"
            @click="openModal('upload')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 shadow-sm transition-colors"
          >
            <span>🔄</span>
            <span>Schimbă</span>
          </button>

          <button
            type="button"
            @click="openModal('library')"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl text-xs font-semibold text-gray-700 shadow-sm transition-colors"
          >
            <span>📁</span>
            <span>Galerie</span>
          </button>

          <button
            type="button"
            @click="removeImage"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 rounded-xl text-xs font-semibold text-rose-700 transition-colors"
          >
            <span>🗑️</span>
            <span>Șterge</span>
          </button>
        </div>
      </div>
    </div>

    <!-- DROPZONE EMPTY STATE -->
    <div
      v-else
      class="border-2 border-dashed rounded-2xl text-center transition-all cursor-pointer"
      :class="[
        compact ? 'p-4 sm:p-5' : 'p-6 sm:p-8',
        isDragging
          ? 'border-primary bg-primary/5 scale-[1.01]'
          : 'border-gray-300 hover:border-primary hover:bg-gray-50/50'
      ]"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="openModal('upload')"
    >
      <div class="flex flex-col items-center justify-center" :class="compact ? 'space-y-2' : 'space-y-3'">
        <div
          class="rounded-2xl bg-primary/10 text-primary flex items-center justify-center shadow-inner"
          :class="compact ? 'w-11 h-11 text-xl' : 'w-14 h-14 text-2xl'"
        >
          📸
        </div>

        <div>
          <p class="text-sm font-semibold text-gray-800">
            {{ emptyTitle }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ emptySubtitle }}
          </p>
        </div>

        <div class="flex items-center gap-2 pt-1">
          <button
            type="button"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm"
            @click.stop="openModal('upload')"
          >
            <span>⬆️</span>
            <span>Încarcă Imagine Nouă</span>
          </button>

          <button
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
            @click.stop="openModal('library')"
          >
            <span>📁 Galerie</span>
          </button>
        </div>

        <p class="text-[11px] text-gray-400">
          Format: JPG, PNG, GIF, WEBP • Salvare locală pe server
        </p>
      </div>
    </div>

    <!-- HELPER TEXT -->
    <p v-if="helperText" class="text-[11px] text-gray-400">
      {{ helperText }}
    </p>

    <!-- UPLOAD MODAL / FORM DIALOG -->
    <Teleport to="body">
      <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-200"
        @click.self="closeModal"
      >
        <div
          class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]"
          @click.stop
        >
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div>
              <h3 class="font-serif font-bold text-base text-gray-900 flex items-center gap-2">
                <span>🖼️</span>
                <span>Configurare Imagine Produs</span>
              </h3>
              <p class="text-xs text-gray-500 mt-0.5">
                Stocare locală pe server în directorul de fișiere multimedia
              </p>
            </div>
            <button
              type="button"
              @click="closeModal"
              class="w-8 h-8 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center text-lg transition-colors"
            >
              ✕
            </button>
          </div>

          <!-- Modal Tabs -->
          <div class="flex border-b border-gray-100 px-6 bg-white gap-4 text-xs font-semibold">
            <button
              type="button"
              @click="activeTab = 'upload'"
              class="py-3 border-b-2 transition-colors flex items-center gap-1.5"
              :class="activeTab === 'upload' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-800'"
            >
              <span>⬆️</span>
              <span>Încarcă fișier</span>
            </button>
            <button
              type="button"
              @click="openModal('library')"
              class="py-3 border-b-2 transition-colors flex items-center gap-1.5"
              :class="activeTab === 'library' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-800'"
            >
              <span>📁</span>
              <span>Galerie Media</span>
            </button>
            <button
              type="button"
              @click="activeTab = 'url'"
              class="py-3 border-b-2 transition-colors flex items-center gap-1.5"
              :class="activeTab === 'url' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-800'"
            >
              <span>🔗</span>
              <span>URL Manual</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto flex-1">
            <!-- TAB 1: UPLOAD FORM -->
            <div v-if="activeTab === 'upload'" class="space-y-4">
              <!-- Dropzone or Selected File Preview -->
              <div
                v-if="!selectedFile"
                class="border-2 border-dashed rounded-2xl p-8 text-center transition-all"
                :class="isDragging ? 'border-primary bg-primary/5' : 'border-gray-300 hover:border-primary hover:bg-gray-50'"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop"
              >
                <input
                  type="file"
                  id="image-file-input"
                  class="hidden"
                  accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                  @change="handleFileSelect"
                />

                <div class="flex flex-col items-center justify-center space-y-3">
                  <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-3xl">
                    📁
                  </div>

                  <div>
                    <label
                      for="image-file-input"
                      class="cursor-pointer text-sm font-semibold text-primary hover:underline"
                    >
                      Apasă pentru a alege un fișier
                    </label>
                    <span class="text-sm text-gray-600"> sau trage fișierul aici</span>
                    <p class="text-xs text-gray-400 mt-1">
                      Sunt permise doar fișiere de tip imagine: JPG, JPEG, PNG, GIF, WEBP
                    </p>
                    <p class="text-[11px] text-gray-400 mt-0.5">
                      Dimensiune maximă recomandată: 32 MB
                    </p>
                  </div>
                </div>
              </div>

              <!-- File Selected State -->
              <div v-else class="space-y-4">
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4">
                  <div class="w-32 h-32 rounded-xl overflow-hidden bg-white border border-gray-200 flex-shrink-0 flex items-center justify-center">
                    <img
                      :src="localPreviewUrl"
                      alt="Previzualizare locală"
                      class="w-full h-full object-cover"
                    />
                  </div>

                  <div class="flex-1 min-w-0 w-full space-y-1">
                    <p class="text-sm font-semibold text-gray-900 truncate">
                      {{ selectedFile.name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      Dimensiune: {{ formatFileSize(selectedFile.size) }} • Tip: {{ selectedFile.type }}
                    </p>

                    <!-- Upload Progress Bar -->
                    <div v-if="isUploading" class="pt-2 space-y-1">
                      <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div
                          class="bg-primary h-2 rounded-full transition-all duration-300"
                          :style="{ width: `${uploadProgress}%` }"
                        ></div>
                      </div>
                      <p class="text-[11px] text-primary font-semibold">
                        Se încarcă pe server... {{ uploadProgress }}%
                      </p>
                    </div>

                    <div v-if="!isUploading" class="pt-2 flex items-center gap-2">
                      <button
                        type="button"
                        @click="resetUploadState"
                        class="text-xs text-gray-600 hover:text-gray-900 underline"
                      >
                        Alege alt fișier
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Error Alert -->
              <div
                v-if="uploadError"
                class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-start gap-2"
              >
                <span class="text-sm">⚠️</span>
                <span>{{ uploadError }}</span>
              </div>
            </div>

            <!-- TAB 2: MEDIA GALLERY -->
            <div v-else-if="activeTab === 'library'" class="space-y-4">
              <!-- Search & Filter Bar -->
              <div class="flex items-center gap-3">
                <div class="relative flex-1">
                  <input
                    v-model="librarySearch"
                    @keyup.enter="loadLibrary(1)"
                    type="text"
                    placeholder="Caută în galeria de pe server..."
                    class="w-full bg-gray-50 border border-gray-300 rounded-xl pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                  />
                  <span class="absolute left-3 top-2.5 text-xs text-gray-400">🔍</span>
                </div>
                <button
                  type="button"
                  @click="loadLibrary(1)"
                  class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold"
                >
                  Caută
                </button>
              </div>

              <!-- Gallery Loading -->
              <div v-if="libraryLoading" class="py-12 text-center text-xs text-gray-400">
                Se încarcă imaginile de pe server...
              </div>

              <!-- Gallery Grid -->
              <div
                v-else-if="libraryItems.length"
                class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-72 overflow-y-auto p-1"
              >
                <div
                  v-for="item in libraryItems"
                  :key="item.id"
                  @click="selectFromLibrary(item)"
                  class="relative aspect-square rounded-xl overflow-hidden border-2 cursor-pointer transition-all bg-gray-100 flex items-center justify-center group"
                  :class="[
                    selectedLibraryItem?.id === item.id
                      ? 'border-primary ring-2 ring-primary/30 scale-95'
                      : 'border-transparent hover:border-gray-300'
                  ]"
                >
                  <img
                    :src="item.url"
                    :alt="item.title"
                    class="w-full h-full object-cover"
                    loading="lazy"
                  />
                  <div
                    v-if="selectedLibraryItem?.id === item.id"
                    class="absolute top-1 right-1 w-5 h-5 bg-primary text-white rounded-full flex items-center justify-center text-[10px] font-bold shadow"
                  >
                    ✓
                  </div>
                </div>
              </div>

              <!-- Gallery Empty -->
              <div v-else class="py-12 text-center text-xs text-gray-400">
                Nu au fost găsite imagini în galerie.
              </div>

              <!-- Pagination -->
              <div v-if="libraryTotalPages > 1" class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs">
                <span class="text-gray-500">
                  Pagina {{ libraryPage }} din {{ libraryTotalPages }}
                </span>
                <div class="flex gap-1.5">
                  <button
                    type="button"
                    :disabled="libraryPage <= 1"
                    @click="loadLibrary(libraryPage - 1)"
                    class="px-2.5 py-1 border border-gray-300 rounded-lg text-xs disabled:opacity-40"
                  >
                    Anterior
                  </button>
                  <button
                    type="button"
                    :disabled="libraryPage >= libraryTotalPages"
                    @click="loadLibrary(libraryPage + 1)"
                    class="px-2.5 py-1 border border-gray-300 rounded-lg text-xs disabled:opacity-40"
                  >
                    Următor
                  </button>
                </div>
              </div>
            </div>

            <!-- TAB 3: MANUAL URL -->
            <div v-else-if="activeTab === 'url'" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                  URL Direct către Imagine
                </label>
                <input
                  v-model="customUrlInput"
                  type="text"
                  placeholder="http://localhost/pepinieraciumbrudV2/pepinieraciumbrudshop/wp-content/uploads/..."
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white"
                />
                <p class="text-[11px] text-gray-400 mt-1">
                  Poți specifica un link direct către un fișier deja existent pe server.
                </p>
              </div>

              <div v-if="customUrlInput" class="pt-2">
                <p class="text-xs font-semibold text-gray-700 mb-2">Previzualizare:</p>
                <div class="w-32 h-32 rounded-xl overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center">
                  <img
                    :src="customUrlInput"
                    alt="Previzualizare URL"
                    class="w-full h-full object-cover"
                    @error="($event.target as HTMLImageElement).src = 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' fill=\'%23ccc\'><rect width=\'100%\' height=\'100%\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'10\' fill=\'%23666\'>Link invalid</text></svg>'"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <button
              type="button"
              @click="closeModal"
              :disabled="isUploading"
              class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50"
            >
              Anulează
            </button>

            <div>
              <button
                v-if="activeTab === 'upload'"
                type="button"
                :disabled="!selectedFile || isUploading"
                @click="uploadSelectedFile"
                class="px-5 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50 flex items-center gap-1.5"
              >
                <span v-if="isUploading">Se încarcă...</span>
                <span v-else>Încarcă pe Server</span>
              </button>

              <button
                v-else-if="activeTab === 'library'"
                type="button"
                :disabled="!selectedLibraryItem"
                @click="confirmLibrarySelection"
                class="px-5 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
              >
                Folosește Imaginea Selectată
              </button>

              <button
                v-else-if="activeTab === 'url'"
                type="button"
                :disabled="!customUrlInput.trim()"
                @click="applyCustomUrl"
                class="px-5 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition-colors shadow-sm disabled:opacity-50"
              >
                Salvează URL
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- LIGHTBOX PREVIEW -->
    <Teleport to="body">
      <div
        v-if="isLightboxOpen && currentImageSrc"
        class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4 cursor-pointer"
        @click="isLightboxOpen = false"
      >
        <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center" @click.stop>
          <img
            :src="currentImageSrc"
            alt="Imagine mărită"
            class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl border border-white/10"
          />
          <div class="mt-3 flex items-center justify-between w-full px-2 text-white/80 text-xs">
            <span class="truncate max-w-md font-mono">{{ currentImageSrc }}</span>
            <button
              type="button"
              @click="isLightboxOpen = false"
              class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-white font-semibold"
            >
              Închide (ESC)
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

