<template>
  <div>
    <!-- Capçalera -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">Sol·licituds de fitxatges</h2>
        <p class="text-sm text-gray-400 mt-0.5">Totes les peticions de l'empresa — empleats i administradors</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="showAll = false"
                class="px-3 py-1.5 text-xs rounded-lg border transition-colors"
                :class="!showAll ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
          Pendents
          <span v-if="pendingCount > 0" class="ml-1.5 bg-white/20 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
            {{ pendingCount }}
          </span>
        </button>
        <button @click="showAll = true"
                class="px-3 py-1.5 text-xs rounded-lg border transition-colors"
                :class="showAll ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
          Historial
        </button>
      </div>
    </div>

    <!-- Taula -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 4" :key="i" class="px-5 py-4 flex items-start gap-4">
          <div class="w-32 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="flex-1 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-24 h-6 bg-gray-100 animate-pulse rounded-lg" />
        </div>
      </div>

      <!-- Buit -->
      <div v-else-if="requests.length === 0"
           class="flex flex-col items-center justify-center py-16 text-center">
        <IconClipboardCheck class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">
          {{ showAll ? 'Sense sol·licituds registrades' : 'No hi ha sol·licituds pendents' }}
        </p>
      </div>

      <template v-else>
        <!-- Cap -->
        <div class="px-5 py-2 bg-gray-50 border-b grid grid-cols-[1fr_1.2fr_1.4fr_auto] gap-4 text-[10px] font-medium text-gray-400 uppercase tracking-wider">
          <span>Empleat / Data</span>
          <span>Detalls del canvi</span>
          <span>Motiu</span>
          <span class="w-48 text-right">Acció</span>
        </div>

        <div v-for="req in requests" :key="req.id"
             class="px-5 py-4 border-b last:border-0 grid grid-cols-[1fr_1.2fr_1.4fr_auto] gap-4 items-start"
             :class="req.initiated_by === 'admin' ? 'bg-purple-50/30' : ''">

          <!-- Empleat + data + origen -->
          <div>
            <p class="text-sm font-medium text-gray-900">
              {{ req.employee?.nom }} {{ req.employee?.cognoms }}
            </p>
            <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
              <p class="text-xs text-gray-400 capitalize">{{ formatDateLong(req.time_entry?.date) }}</p>
              <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full" :class="typeBadge(req.type)">
                {{ typeLabel(req.type) }}
              </span>
              <span v-if="req.initiated_by === 'admin'"
                    class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700">
                per admin
              </span>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">{{ timeAgo(req.created_at) }}</p>
          </div>

          <!-- Canvis proposats -->
          <div class="space-y-1.5">
            <template v-if="req.type === 'delete'">
              <div class="text-xs text-gray-500">
                <span class="text-gray-400">Entrada: </span>
                <span class="font-mono">{{ formatTime(req.original_data?.clock_in_at) }}</span>
                <span v-if="req.original_data?.clock_out_at">
                  <span class="text-gray-400 mx-1">·</span>
                  <span class="text-gray-400">Sortida: </span>
                  <span class="font-mono">{{ formatTime(req.original_data?.clock_out_at) }}</span>
                </span>
              </div>
            </template>
            <template v-else-if="req.type === 'edit'">
              <div v-if="req.requested_data?.clock_in_at" class="text-xs">
                <span class="text-gray-400">Entrada: </span>
                <template v-if="isSameTime(req.original_data?.clock_in_at, req.requested_data?.clock_in_at)">
                  <span class="font-mono">{{ formatTime(req.original_data?.clock_in_at) }}</span>
                </template>
                <template v-else>
                  <span class="font-mono text-gray-500 line-through">{{ fmtEdit(req.original_data?.clock_in_at, req.requested_data?.clock_in_at) }}</span>
                  <span class="mx-1 text-gray-400">→</span>
                  <span class="font-mono font-medium text-blue-700">{{ fmtEdit(req.requested_data?.clock_in_at, req.original_data?.clock_in_at) }}</span>
                </template>
              </div>
              <div v-if="req.requested_data?.clock_out_at" class="text-xs">
                <span class="text-gray-400">Sortida: </span>
                <template v-if="isSameTime(req.original_data?.clock_out_at, req.requested_data?.clock_out_at)">
                  <span class="font-mono">{{ formatTime(req.original_data?.clock_out_at) }}</span>
                </template>
                <template v-else>
                  <span class="font-mono text-gray-500 line-through">{{ fmtEdit(req.original_data?.clock_out_at, req.requested_data?.clock_out_at) }}</span>
                  <span class="mx-1 text-gray-400">→</span>
                  <span class="font-mono font-medium text-blue-700">{{ fmtEdit(req.requested_data?.clock_out_at, req.original_data?.clock_out_at) }}</span>
                </template>
              </div>
            </template>
            <template v-else-if="req.type === 'break_delete'">
              <div class="text-xs text-gray-500">
                <span class="text-gray-400">Inici: </span>
                <span class="font-mono">{{ formatTime(req.original_data?.break_start_at) }}</span>
                <span class="text-gray-400 mx-1">·</span>
                <span class="text-gray-400">Final: </span>
                <span class="font-mono">{{ formatTime(req.original_data?.break_end_at) }}</span>
                <span class="text-gray-400 ml-1">({{ req.original_data?.duration_minutes }} min)</span>
              </div>
            </template>
            <template v-else-if="req.type === 'break_edit'">
              <div v-if="req.requested_data?.break_start_at" class="text-xs">
                <span class="text-gray-400">Inici: </span>
                <template v-if="isSameTime(req.original_data?.break_start_at, req.requested_data?.break_start_at)">
                  <span class="font-mono">{{ formatTime(req.original_data?.break_start_at) }}</span>
                </template>
                <template v-else>
                  <span class="font-mono text-gray-500 line-through">{{ fmtEdit(req.original_data?.break_start_at, req.requested_data?.break_start_at) }}</span>
                  <span class="mx-1 text-gray-400">→</span>
                  <span class="font-mono font-medium text-blue-700">{{ fmtEdit(req.requested_data?.break_start_at, req.original_data?.break_start_at) }}</span>
                </template>
              </div>
              <div v-if="req.requested_data?.break_end_at" class="text-xs">
                <span class="text-gray-400">Final: </span>
                <template v-if="isSameTime(req.original_data?.break_end_at, req.requested_data?.break_end_at)">
                  <span class="font-mono">{{ formatTime(req.original_data?.break_end_at) }}</span>
                </template>
                <template v-else>
                  <span class="font-mono text-gray-500 line-through">{{ fmtEdit(req.original_data?.break_end_at, req.requested_data?.break_end_at) }}</span>
                  <span class="mx-1 text-gray-400">→</span>
                  <span class="font-mono font-medium text-blue-700">{{ fmtEdit(req.requested_data?.break_end_at, req.original_data?.break_end_at) }}</span>
                </template>
              </div>
            </template>

            <div class="mt-1">
              <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full" :class="statusBadge(req.status)">
                {{ statusLabel(req.status) }}
              </span>
            </div>
          </div>

          <!-- Motiu + nota -->
          <div>
            <p class="text-xs text-gray-600 leading-relaxed">{{ req.reason }}</p>
            <p v-if="req.review_note" class="text-xs text-gray-400 mt-1 italic">
              Nota: "{{ req.review_note }}"
            </p>
          </div>

          <!-- Accions -->
          <div class="w-48 flex flex-col items-end gap-2">
            <template v-if="req.status === 'pending'">

              <!-- Sol·licitud d'empleat → admin pot revisar -->
              <template v-if="req.initiated_by === 'employee'">
                <template v-if="reviewing === req.id">
                  <textarea v-model="reviewNote" rows="2" placeholder="Nota opcional..."
                            class="w-full text-xs border border-gray-200 rounded-lg px-2 py-1.5 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  <div class="flex gap-1.5">
                    <button @click="cancelReview"
                            class="px-2.5 py-1 text-xs border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50">
                      Cancel·lar
                    </button>
                    <button @click="confirmDeny(req.id)" :disabled="actionSaving"
                            class="px-2.5 py-1 text-xs font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60">
                      {{ actionSaving ? '...' : 'Denegar' }}
                    </button>
                    <button @click="confirmApprove(req.id)" :disabled="actionSaving"
                            class="px-2.5 py-1 text-xs font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg disabled:opacity-60">
                      {{ actionSaving ? '...' : 'Aprovar' }}
                    </button>
                  </div>
                </template>
                <template v-else>
                  <button @click="startReview(req.id)"
                          class="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                    Revisar
                  </button>
                </template>
              </template>

              <!-- Sol·licitud d'admin → esperant resposta de l'empleat -->
              <template v-else>
                <span class="text-xs text-purple-600 font-medium">Pendent d'empleat</span>
              </template>

            </template>
            <template v-else>
              <div class="text-xs text-gray-400 text-right">
                <span v-if="req.reviewed_by">
                  {{ req.status === 'approved' ? 'Aprovat' : 'Denegat' }} per {{ req.reviewed_by?.name }}
                </span>
                <p v-if="req.reviewed_at" class="text-[10px] mt-0.5">{{ timeAgo(req.reviewed_at) }}</p>
              </div>
            </template>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { IconClipboardCheck } from '@tabler/icons-vue'
import api from '../services/api'

const showAll  = ref(false)
const loading  = ref(false)
const requests = ref([])

// Només compta pendents que l'admin pot resoldre (employee-initiated)
const pendingCount = computed(() =>
  requests.value.filter(r => r.status === 'pending' && r.initiated_by === 'employee').length
)

async function fetchRequests() {
  loading.value = true
  try {
    const res = await api.get('/v1/time-entry-edit-requests', {
      params: showAll.value ? { all: 1 } : {}
    })
    requests.value = res.data.data || []
  } catch {
    requests.value = []
  } finally {
    loading.value = false
  }
}

watch(showAll, fetchRequests)
onMounted(fetchRequests)

// ── Revisió inline ────────────────────────────────────────────────────────────
const reviewing    = ref(null)
const reviewNote   = ref('')
const actionSaving = ref(false)

function startReview(id) { reviewing.value = id; reviewNote.value = '' }
function cancelReview()  { reviewing.value = null; reviewNote.value = '' }

async function confirmApprove(id) {
  actionSaving.value = true
  try {
    await api.post(`/v1/time-entry-edit-requests/${id}/approve`, { note: reviewNote.value || null })
    cancelReview()
    await fetchRequests()
  } catch (e) {
    alert(e?.response?.data?.message || 'Error en aprovar la sol·licitud.')
  } finally { actionSaving.value = false }
}

async function confirmDeny(id) {
  actionSaving.value = true
  try {
    await api.post(`/v1/time-entry-edit-requests/${id}/deny`, { note: reviewNote.value || null })
    cancelReview()
    await fetchRequests()
  } catch (e) {
    alert(e?.response?.data?.message || 'Error en denegar la sol·licitud.')
  } finally { actionSaving.value = false }
}

// ── Formats ───────────────────────────────────────────────────────────────────
function formatTime(iso) {
  if (!iso) return '—'
  return new Date(iso).toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
}
function isSameTime(iso1, iso2) {
  if (!iso1 || !iso2) return false
  return new Date(iso1).getTime() === new Date(iso2).getTime()
}
function fmtEdit(iso, peerIso) {
  if (!iso) return '—'
  const d = new Date(iso)
  const time = d.toLocaleTimeString('ca-ES', { hour: '2-digit', minute: '2-digit' })
  if (!peerIso) return time
  const p = new Date(peerIso)
  const sameDay = d.getFullYear() === p.getFullYear() && d.getMonth() === p.getMonth() && d.getDate() === p.getDate()
  if (sameDay) return time
  return d.toLocaleDateString('ca-ES', { weekday: 'short', day: 'numeric', month: 'short' }) + ' ' + time
}
function formatDateLong(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ca-ES', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}
function timeAgo(iso) {
  if (!iso) return ''
  const diff = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 2)   return 'ara mateix'
  if (mins < 60)  return `fa ${mins} min`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `fa ${hours}h`
  const days = Math.floor(hours / 24)
  return `fa ${days} ${days === 1 ? 'dia' : 'dies'}`
}
function typeBadge(t) {
  return {
    edit:         'bg-amber-50 text-amber-700',
    delete:       'bg-red-50 text-red-700',
    break_edit:   'bg-blue-50 text-blue-700',
    break_delete: 'bg-purple-50 text-purple-700',
  }[t] || 'bg-gray-100 text-gray-500'
}
function typeLabel(t) {
  return {
    edit:         'Edició fitxatge',
    delete:       'Eliminació fitxatge',
    break_edit:   'Edició pausa',
    break_delete: 'Eliminació pausa',
  }[t] || t
}
function statusBadge(s) {
  return {
    pending:  'bg-amber-50 text-amber-700',
    approved: 'bg-green-50 text-green-700',
    denied:   'bg-red-50 text-red-700',
  }[s] || 'bg-gray-100 text-gray-500'
}
function statusLabel(s) {
  return { pending: 'Pendent', approved: 'Aprovat', denied: 'Denegat' }[s] || '—'
}
</script>
