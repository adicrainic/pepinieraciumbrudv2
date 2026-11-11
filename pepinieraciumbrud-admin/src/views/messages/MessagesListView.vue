<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { messagesService } from '@/services/messagesService'
import type { ContactMessage, MessageStatus } from '@/types/message'

const messages = ref<ContactMessage[]>([])
const loading = ref(true)
const total = ref(0)
const page = ref(1)
const perPage = ref(15)
const unreadCount = ref(0)

const activeTab = ref<string>('all')
const searchQuery = ref('')
const searchDebounce = ref<any>(null)

// Selected message for detail modal
const selectedMessage = ref<ContactMessage | null>(null)
const updatingStatus = ref(false)
const deleteConfirmId = ref<number | null>(null)

// In-app reply state
const replySubject = ref('')
const replyText = ref('')
const sendingReply = ref(false)
const replySuccessNotice = ref('')
const replyErrorNotice = ref('')

const statusTabs = computed(() => [
  { id: 'all', label: 'Toate', count: total.value },
  { id: 'unread', label: 'Necitite', count: unreadCount.value, highlight: unreadCount.value > 0 },
  { id: 'read', label: 'Citite' },
  { id: 'replied', label: 'Răspunse' },
  { id: 'archived', label: 'Arhivate' }
])

const fetchMessages = async () => {
  loading.value = true
  try {
    const res = await messagesService.list({
      page: page.value,
      per_page: perPage.value,
      status: activeTab.value === 'all' ? undefined : activeTab.value,
      search: searchQuery.value.trim() || undefined
    })
    messages.value = res.items
    total.value = res.total
    unreadCount.value = res.unread_count
  } catch (err) {
    console.error('Failed to load contact messages:', err)
  } finally {
    loading.value = false
  }
}

watch(activeTab, () => {
  page.value = 1
  fetchMessages()
})

const onSearchInput = () => {
  clearTimeout(searchDebounce.value)
  searchDebounce.value = setTimeout(() => {
    page.value = 1
    fetchMessages()
  }, 350)
}

const openMessage = async (msg: ContactMessage, shouldFocusReply = false) => {
  selectedMessage.value = { ...msg }
  replySubject.value = `Re: Solicitare informații – Pepiniera Ciumbrud`
  replyText.value = ''
  replySuccessNotice.value = ''
  replyErrorNotice.value = ''

  if (msg.status === 'unread') {
    try {
      const res = await messagesService.get(msg.id)
      selectedMessage.value = res
      const found = messages.value.find((m) => m.id === msg.id)
      if (found) {
        found.status = 'read'
      }
      if (unreadCount.value > 0) {
        unreadCount.value -= 1
      }
    } catch (err) {
      console.error('Failed to mark message as read:', err)
    }
  }

  if (shouldFocusReply) {
    setTimeout(() => {
      const el = document.getElementById('in-app-reply-textarea')
      if (el) el.focus()
    }, 150)
  }
}

const handleSendReply = async () => {
  if (!selectedMessage.value || !replyText.value.trim()) return

  sendingReply.value = true
  replySuccessNotice.value = ''
  replyErrorNotice.value = ''

  try {
    const res = await messagesService.reply(selectedMessage.value.id, {
      message: replyText.value.trim(),
      subject: replySubject.value.trim() || undefined
    })

    if (res.success) {
      replySuccessNotice.value = res.message || 'Răspunsul a fost expediat cu succes!'
      selectedMessage.value = res.item

      // Update in list
      const found = messages.value.find((m) => m.id === res.item.id)
      if (found) {
        found.status = 'replied'
        found.reply_message = res.item.reply_message
        found.replied_at = res.item.replied_at
        found.replied_by = res.item.replied_by
      }
      replyText.value = ''
      fetchMessages()
    } else {
      replyErrorNotice.value = res.message || 'A apărut o problemă la trimiterea răspunsului.'
    }
  } catch (err: any) {
    replyErrorNotice.value = err?.response?.data?.message || err?.message || 'Eroare la trimiterea răspunsului.'
  } finally {
    sendingReply.value = false
  }
}

const changeStatus = async (msg: ContactMessage, newStatus: MessageStatus) => {
  updatingStatus.value = true
  try {
    const res = await messagesService.updateStatus(msg.id, newStatus)
    msg.status = newStatus
    if (selectedMessage.value && selectedMessage.value.id === msg.id) {
      selectedMessage.value = res.item
    }
    fetchMessages()
  } catch (err) {
    console.error('Failed to update status:', err)
  } finally {
    updatingStatus.value = false
  }
}

const handleDelete = async (id: number) => {
  try {
    await messagesService.delete(id)
    deleteConfirmId.value = null
    if (selectedMessage.value && selectedMessage.value.id === id) {
      selectedMessage.value = null
    }
    fetchMessages()
  } catch (err) {
    console.error('Failed to delete message:', err)
  }
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleString('ro-RO', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateStr
  }
}

const getStatusBadge = (status: MessageStatus) => {
  switch (status) {
    case 'unread':
      return {
        label: 'Necitit',
        classes: 'bg-emerald-100 text-emerald-800 border-emerald-200 font-semibold'
      }
    case 'read':
      return {
        label: 'Citit',
        classes: 'bg-gray-100 text-gray-700 border-gray-200'
      }
    case 'replied':
      return {
        label: 'Răspuns',
        classes: 'bg-blue-100 text-blue-800 border-blue-200 font-semibold'
      }
    case 'archived':
      return {
        label: 'Arhivat',
        classes: 'bg-slate-100 text-slate-600 border-slate-200'
      }
    default:
      return { label: status, classes: 'bg-gray-100 text-gray-700' }
  }
}

onMounted(() => {
  fetchMessages()
})
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-serif font-bold text-gray-900">
              Mesaje Contact
            </h1>
            <span
              v-if="unreadCount > 0"
              class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white"
            >
              {{ unreadCount }} {{ unreadCount === 1 ? 'nou' : 'noi' }}
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-1">
            Vizualizați și răspundeți clienților direct din aplicație sau prin clientul dumneavoastră de email.
          </p>
        </div>

        <button
          @click="fetchMessages"
          :disabled="loading"
          class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-xl border border-border bg-white hover:bg-gray-50 text-gray-700 shadow-sm transition-colors"
        >
          <span :class="{ 'animate-spin': loading }">🔄</span>
          <span>Actualizează</span>
        </button>
      </div>

      <!-- Filters & Search bar -->
      <div class="bg-white p-4 rounded-2xl border border-border shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="flex flex-wrap items-center gap-1.5">
          <button
            v-for="tab in statusTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-colors flex items-center gap-2"
            :class="[
              activeTab === tab.id
                ? 'bg-primary text-white font-semibold shadow-sm'
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            <span>{{ tab.label }}</span>
            <span
              v-if="tab.count !== undefined && tab.count > 0"
              class="px-1.5 py-0.2 rounded-full text-[10px]"
              :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700'"
            >
              {{ tab.count }}
            </span>
          </button>
        </div>

        <!-- Search Input -->
        <div class="relative w-full md:w-72">
          <span class="absolute left-3 top-2.5 text-gray-400 text-sm">🔍</span>
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            type="text"
            placeholder="Caută după nume, email, text..."
            class="w-full pl-9 pr-4 py-2 bg-surface-warm border border-border rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-primary"
          />
        </div>
      </div>

      <!-- Messages Table -->
      <div class="bg-white rounded-2xl border border-border shadow-sm overflow-hidden">
        <!-- Loading skeleton -->
        <div v-if="loading" class="p-8 text-center text-gray-400 text-xs">
          <span class="inline-block animate-spin text-2xl mb-2">⏳</span>
          <p>Se încarcă mesajele...</p>
        </div>

        <!-- Empty state -->
        <div v-else-if="messages.length === 0" class="p-12 text-center">
          <div class="text-4xl mb-3">💬</div>
          <h3 class="font-serif font-bold text-gray-800 text-base mb-1">
            Niciun mesaj găsit
          </h3>
          <p class="text-xs text-gray-500 max-w-sm mx-auto">
            {{ searchQuery ? 'Nu există mesaje care să corespundă căutării dumneavoastră.' : 'Momentan nu există mesaje în această categorie.' }}
          </p>
        </div>

        <!-- Table view -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-border bg-gray-50/75 text-gray-500 uppercase tracking-wider font-semibold">
                <th class="py-3 px-4 w-12 text-center">Status</th>
                <th class="py-3 px-4">Expeditor</th>
                <th class="py-3 px-4">Contact</th>
                <th class="py-3 px-4">Mesaj</th>
                <th class="py-3 px-4">Data</th>
                <th class="py-3 px-4 text-right">Acțiuni</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr
                v-for="msg in messages"
                :key="msg.id"
                class="hover:bg-gray-50/80 transition-colors cursor-pointer"
                :class="{ 'bg-emerald-50/30 font-medium': msg.status === 'unread' }"
                @click="openMessage(msg)"
              >
                <!-- Status -->
                <td class="py-3.5 px-4 text-center" @click.stop>
                  <span
                    class="inline-block px-2 py-0.5 rounded-full border text-[10px]"
                    :class="getStatusBadge(msg.status).classes"
                  >
                    {{ getStatusBadge(msg.status).label }}
                  </span>
                </td>

                <!-- Sender name -->
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                    <span v-if="msg.status === 'unread'" class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span>{{ msg.name }}</span>
                  </div>
                </td>

                <!-- Contact info -->
                <td class="py-3.5 px-4 text-gray-600 space-y-0.5" @click.stop>
                  <div>
                    <a :href="`mailto:${msg.email}`" class="hover:text-primary hover:underline">
                      {{ msg.email }}
                    </a>
                  </div>
                  <div v-if="msg.phone" class="text-gray-400 text-[11px]">
                    <a :href="`tel:${msg.phone}`" class="hover:text-primary">
                      📞 {{ msg.phone }}
                    </a>
                  </div>
                </td>

                <!-- Message Preview -->
                <td class="py-3.5 px-4 max-w-md">
                  <p class="truncate text-gray-700">
                    {{ msg.message }}
                  </p>
                  <p v-if="msg.reply_message" class="text-[11px] text-emerald-700 truncate mt-0.5 font-medium">
                    ↳ Răspuns: {{ msg.reply_message }}
                  </p>
                </td>

                <!-- Date -->
                <td class="py-3.5 px-4 whitespace-nowrap text-gray-500 text-[11px]">
                  {{ formatDate(msg.created_at) }}
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-4 text-right whitespace-nowrap" @click.stop>
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      @click="openMessage(msg)"
                      title="Vizualizează mesaj"
                      class="px-2.5 py-1 rounded-lg border border-border text-gray-700 hover:bg-gray-100 transition-colors"
                    >
                      👁️ Citește
                    </button>

                    <button
                      @click="openMessage(msg, true)"
                      title="Răspunde direct din aplicație"
                      class="px-2.5 py-1 rounded-lg bg-primary text-white hover:bg-primary-dark transition-colors inline-flex items-center gap-1 font-semibold"
                    >
                      💬 Răspunde
                    </button>

                    <button
                      @click="deleteConfirmId = msg.id"
                      title="Șterge mesaj"
                      class="px-2 py-1 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors"
                    >
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="total > perPage" class="p-4 border-t border-border flex items-center justify-between text-xs text-gray-500">
          <div>
            Afișate {{ (page - 1) * perPage + 1 }} – {{ Math.min(page * perPage, total) }} din {{ total }} mesaje
          </div>
          <div class="flex items-center gap-2">
            <button
              :disabled="page <= 1"
              @click="page--; fetchMessages()"
              class="px-3 py-1.5 rounded-xl border border-border disabled:opacity-40 hover:bg-gray-50 font-medium"
            >
              Înapoi
            </button>
            <span class="px-2 font-semibold text-gray-700">Pagina {{ page }}</span>
            <button
              :disabled="page * perPage >= total"
              @click="page++; fetchMessages()"
              class="px-3 py-1.5 rounded-xl border border-border disabled:opacity-40 hover:bg-gray-50 font-medium"
            >
              Înainte
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Message Detail & In-App Reply Modal -->
    <div
      v-if="selectedMessage"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="selectedMessage = null"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full border border-border shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Header -->
        <div class="p-6 border-b border-border bg-gray-50/75 flex items-start justify-between">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span
                class="px-2 py-0.5 rounded-full text-[10px] border"
                :class="getStatusBadge(selectedMessage.status).classes"
              >
                {{ getStatusBadge(selectedMessage.status).label }}
              </span>
              <span class="text-xs text-gray-400">#{{ selectedMessage.id }}</span>
            </div>
            <h2 class="font-serif text-lg font-bold text-gray-900">
              {{ selectedMessage.name }}
            </h2>
            <p class="text-xs text-gray-500">
              Primit la {{ formatDate(selectedMessage.created_at) }}
            </p>
          </div>

          <button
            @click="selectedMessage = null"
            class="text-gray-400 hover:text-gray-600 text-lg w-8 h-8 rounded-full hover:bg-gray-200/60 flex items-center justify-center"
          >
            ✕
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
          <!-- Contact Details Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-surface-warm p-4 rounded-2xl border border-border">
            <div>
              <span class="text-gray-500 block mb-0.5">Email client:</span>
              <a :href="`mailto:${selectedMessage.email}`" class="font-semibold text-primary hover:underline break-all">
                {{ selectedMessage.email }}
              </a>
            </div>
            <div>
              <span class="text-gray-500 block mb-0.5">Telefon client:</span>
              <a v-if="selectedMessage.phone" :href="`tel:${selectedMessage.phone}`" class="font-semibold text-gray-800 hover:text-primary">
                {{ selectedMessage.phone }}
              </a>
              <span v-else class="text-gray-400 italic">Nespecificat</span>
            </div>
          </div>

          <!-- Customer's Original Message -->
          <div>
            <span class="text-gray-500 uppercase tracking-wider text-[10px] font-bold block mb-2">
              Mesajul Clientului
            </span>
            <div class="bg-gray-50 p-4 rounded-2xl border border-border text-gray-800 text-sm whitespace-pre-wrap leading-relaxed">
              {{ selectedMessage.message }}
            </div>
          </div>

          <!-- Already Replied History Card -->
          <div v-if="selectedMessage.reply_message" class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 space-y-2">
            <div class="flex items-center justify-between text-[11px] text-emerald-800 font-semibold">
              <span class="flex items-center gap-1.5">
                <span>✓</span> Răspuns trimis la {{ formatDate(selectedMessage.replied_at || '') }}
              </span>
              <span v-if="selectedMessage.replied_by" class="text-emerald-700">
                de către {{ selectedMessage.replied_by }}
              </span>
            </div>
            <div class="text-xs text-gray-800 whitespace-pre-wrap leading-relaxed bg-white/70 p-3 rounded-xl border border-emerald-100">
              {{ selectedMessage.reply_message }}
            </div>
          </div>

          <!-- In-App Reply Section -->
          <div class="border border-primary/20 bg-primary/5 rounded-2xl p-5 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-primary/10 pb-3">
              <div>
                <h4 class="font-bold text-xs text-primary-dark flex items-center gap-1.5">
                  <span>💬</span> Răspunde direct din aplicație
                </h4>
                <p class="text-[11px] text-gray-500">
                  Răspunsul va fi expediat direct către <strong class="text-primary-dark">{{ selectedMessage.email }}</strong>
                </p>
              </div>

              <!-- Fallback to mailto -->
              <a
                :href="`mailto:${selectedMessage.email}?subject=${encodeURIComponent(replySubject || 'Re: Mesaj Pepiniera Ciumbrud')}`"
                target="_blank"
                class="inline-flex items-center gap-1 text-[11px] font-semibold text-primary hover:text-primary-dark hover:underline flex-shrink-0"
              >
                <span>↗️</span> Deschide în client email
              </a>
            </div>

            <!-- Notice alerts -->
            <div v-if="replySuccessNotice" class="p-3 bg-emerald-100 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
              <span>✓</span>
              <span>{{ replySuccessNotice }}</span>
            </div>
            <div v-if="replyErrorNotice" class="p-3 bg-rose-100 text-rose-800 text-xs rounded-xl flex items-center gap-2">
              <span>⚠️</span>
              <span>{{ replyErrorNotice }}</span>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-gray-700 mb-1">Subiect email:</label>
              <input
                v-model="replySubject"
                type="text"
                placeholder="Subiectul email-ului..."
                class="w-full bg-white border border-border rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary shadow-xs"
              />
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-gray-700 mb-1">Mesaj de răspuns:</label>
              <textarea
                id="in-app-reply-textarea"
                v-model="replyText"
                rows="4"
                placeholder="Bună ziua, vă mulțumim pentru mesaj..."
                class="w-full bg-white border border-border rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-primary shadow-xs leading-relaxed"
              ></textarea>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
              <span class="text-[10px] text-gray-500">
                💡 Email-ul va include automat semnătura Pepiniera Ciumbrud și citatul mesajului original.
              </span>
              <button
                @click="handleSendReply"
                :disabled="sendingReply || !replyText.trim()"
                class="px-5 py-2 bg-primary hover:bg-primary-dark disabled:opacity-50 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors flex items-center justify-center gap-2 flex-shrink-0"
              >
                <span v-if="sendingReply" class="animate-spin inline-block w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                <span>{{ sendingReply ? 'Se trimite email...' : 'Trimite Răspunsul pe Email' }}</span>
              </button>
            </div>
          </div>

          <!-- Quick status update buttons -->
          <div class="pt-2 border-t border-border flex flex-wrap items-center justify-between gap-2">
            <span class="text-gray-500 text-[11px]">Modifică manual statusul:</span>
            <div class="flex flex-wrap items-center gap-1.5">
              <button
                v-if="selectedMessage.status !== 'unread'"
                @click="changeStatus(selectedMessage, 'unread')"
                class="px-2.5 py-1 rounded-lg border border-border hover:bg-gray-100 text-gray-700"
              >
                Marchează ca Necitit
              </button>
              <button
                v-if="selectedMessage.status !== 'read'"
                @click="changeStatus(selectedMessage, 'read')"
                class="px-2.5 py-1 rounded-lg border border-border hover:bg-gray-100 text-gray-700"
              >
                Marchează ca Citit
              </button>
              <button
                v-if="selectedMessage.status !== 'replied'"
                @click="changeStatus(selectedMessage, 'replied')"
                class="px-2.5 py-1 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100"
              >
                Marchează ca Răspuns
              </button>
              <button
                v-if="selectedMessage.status !== 'archived'"
                @click="changeStatus(selectedMessage, 'archived')"
                class="px-2.5 py-1 rounded-lg border border-gray-200 text-slate-600 hover:bg-gray-100"
              >
                Arhivează
              </button>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-border bg-gray-50/75 flex items-center justify-between">
          <button
            @click="deleteConfirmId = selectedMessage.id"
            class="px-3 py-1.5 rounded-xl text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-colors"
          >
            🗑️ Șterge mesajul
          </button>

          <button
            @click="selectedMessage = null"
            class="px-4 py-2 rounded-xl border border-border bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold"
          >
            Închide
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="deleteConfirmId !== null"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl max-w-sm w-full p-6 border border-border shadow-2xl text-center space-y-4">
        <div class="text-3xl text-rose-500">⚠️</div>
        <h3 class="font-serif font-bold text-gray-900 text-base">
          Sigur doriți să ștergeți acest mesaj?
        </h3>
        <p class="text-xs text-gray-500">
          Această acțiune este ireversibilă. Mesajul va fi șters definitiv din baza de date.
        </p>
        <div class="flex items-center justify-center gap-3 pt-2">
          <button
            @click="deleteConfirmId = null"
            class="px-4 py-2 rounded-xl border border-border text-xs font-semibold text-gray-700 hover:bg-gray-50"
          >
            Anulează
          </button>
          <button
            @click="handleDelete(deleteConfirmId)"
            class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-sm"
          >
            Da, șterge
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
