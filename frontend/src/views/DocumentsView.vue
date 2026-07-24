<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">{{ $t('documents_page.title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t('documents_page.subtitle') }}</p>
      </div>
      <button v-if="isManager" @click="openUpload"
              class="w-1/2 sm:w-auto flex items-center justify-center sm:justify-start gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
        <IconUpload class="w-4 h-4" />{{ $t('documents_page.upload_btn') }}
      </button>
    </div>

    <!-- ── Gestió (HR+) ─────────────────────────────────────────────────────── -->
    <div v-if="isManager" class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-6">
      <div class="px-5 py-3 border-b flex items-center justify-between gap-3 flex-wrap">
        <h3 class="text-sm font-medium text-gray-900">{{ $t('documents_page.all_documents') }}</h3>
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select v-model="filterEmployee" @change="fetchAll"
                  class="flex-1 sm:flex-initial border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">{{ $t('documents_page.filter_employee') }}</option>
            <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.nom }} {{ e.cognoms }}</option>
          </select>
          <select v-model="filterType" @change="fetchAll"
                  class="flex-1 sm:flex-initial border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">{{ $t('documents_page.filter_type') }}</option>
            <option value="payroll">{{ $t('documents_page.type_payroll') }}</option>
            <option value="medical_certificate">{{ $t('documents_page.type_medical_certificate') }}</option>
            <option value="other">{{ $t('documents_page.type_other') }}</option>
          </select>
        </div>
      </div>

      <div v-if="loadingAll" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-9 h-9 rounded-lg bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-100 animate-pulse rounded w-40" />
            <div class="h-3 bg-gray-100 animate-pulse rounded w-24" />
          </div>
        </div>
      </div>

      <div v-else-if="allDocuments.length === 0" class="flex flex-col items-center justify-center py-14 text-center">
        <IconInbox class="w-9 h-9 text-gray-300 mb-2" />
        <p class="text-sm text-gray-500">{{ $t('documents_page.all_documents_empty') }}</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-[11px] text-gray-400 uppercase tracking-wider border-b">
              <th class="px-5 py-2.5 font-medium">{{ $t('documents_page.col_actions') }}</th>
              <th class="px-5 py-2.5 font-medium">{{ $t('documents_page.col_employee') }}</th>
              <th class="px-5 py-2.5 font-medium">{{ $t('documents_page.col_title') }}</th>
              <th class="px-5 py-2.5 font-medium">{{ $t('documents_page.col_type') }}</th>
              <th class="px-5 py-2.5 font-medium">{{ $t('documents_page.col_date') }}</th>
              <th class="px-5 py-2.5 font-medium">{{ $t('documents_page.col_size') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="d in allDocuments" :key="d.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3 whitespace-nowrap">
                <button @click="doDownload(d)" class="w-7 h-7 inline-flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" :title="$t('documents_page.download')">
                  <IconDownload class="w-4 h-4" />
                </button>
                <button @click="askDelete(d)" class="w-7 h-7 inline-flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" :title="$t('documents_page.delete')">
                  <IconTrash class="w-4 h-4" />
                </button>
              </td>
              <td class="px-5 py-3 whitespace-nowrap">{{ d.employee?.nom }} {{ d.employee?.cognoms }}</td>
              <td class="px-5 py-3">
                <div class="font-medium text-gray-900">{{ d.title }}</div>
                <div v-if="d.description" class="text-xs text-gray-400 truncate max-w-xs">{{ d.description }}</div>
              </td>
              <td class="px-5 py-3 whitespace-nowrap">
                <span :class="typeBadgeClass(d.type)" class="text-[11px] font-medium px-2 py-0.5 rounded-full">{{ typeLabel(d.type) }}</span>
              </td>
              <td class="px-5 py-3 whitespace-nowrap text-gray-500">{{ formatDate(d.created_at) }}</td>
              <td class="px-5 py-3 whitespace-nowrap text-gray-500">{{ formatSize(d.file_size) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Els meus documents ───────────────────────────────────────────────── -->
    <div v-if="hasEmployee" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-5 py-3 border-b flex items-center justify-between gap-3">
        <h3 class="text-sm font-medium text-gray-900">{{ $t('documents_page.my_documents') }}</h3>
        <button @click="openSelfUpload"
                class="flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 border border-blue-200 hover:border-blue-300 px-2.5 py-1.5 rounded-lg transition-colors flex-shrink-0">
          <IconUpload class="w-3.5 h-3.5" />{{ $t('documents_page.upload_self_btn') }}
        </button>
      </div>

      <div v-if="loadingMine" class="divide-y divide-gray-100">
        <div v-for="i in 2" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-9 h-9 rounded-lg bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-100 animate-pulse rounded w-40" />
          </div>
        </div>
      </div>

      <div v-else-if="myDocuments.length === 0" class="flex flex-col items-center justify-center py-14 text-center">
        <IconInbox class="w-9 h-9 text-gray-300 mb-2" />
        <p class="text-sm text-gray-500">{{ $t('documents_page.my_documents_empty') }}</p>
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div v-for="d in myDocuments" :key="d.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
          <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" :class="typeIconBgClass(d.type)">
            <component :is="typeIcon(d.type)" class="w-4 h-4" :class="typeIconClass(d.type)" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="font-medium text-gray-900 text-sm truncate">{{ d.title }}</span>
              <span v-if="!d.read_at" class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-600 flex-shrink-0">{{ $t('documents_page.unread_badge') }}</span>
            </div>
            <p v-if="d.description" class="text-xs text-gray-400 truncate mt-0.5">{{ d.description }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">{{ formatDate(d.created_at) }} · {{ formatSize(d.file_size) }}</p>
          </div>
          <button @click="doDownload(d)" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors flex-shrink-0" :title="$t('documents_page.download')">
            <IconDownload class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- ── Modal pujar document ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="uploadModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeUpload">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">{{ $t(isSelfUpload ? 'documents_page.modal_self_title' : 'documents_page.modal_title') }}</h3>
            <button @click="closeUpload" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <form @submit.prevent="submitUpload" class="overflow-y-auto flex-1 px-6 py-5 space-y-4">
            <p v-if="isSelfUpload" class="text-xs text-gray-400 -mt-1">{{ $t('documents_page.self_upload_hint') }}</p>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('documents_page.form_title_label') }}</label>
              <input v-model="form.title" type="text" :placeholder="$t('documents_page.form_title_placeholder')" required
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('documents_page.form_description_label') }}</label>
              <textarea v-model="form.description" rows="2" :placeholder="$t('documents_page.form_description_placeholder')"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('documents_page.form_type_label') }}</label>
              <select v-model="form.type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option v-if="!isSelfUpload" value="payroll">{{ $t('documents_page.type_payroll') }}</option>
                <option value="medical_certificate">{{ $t('documents_page.type_medical_certificate') }}</option>
                <option value="other">{{ $t('documents_page.type_other') }}</option>
              </select>
            </div>

            <div v-if="!isSelfUpload">
              <label class="block text-xs font-medium text-gray-600 mb-1">
                {{ $t('documents_page.form_employees_label') }}
                <span v-if="form.employee_ids.length" class="text-gray-400 font-normal">— {{ $t('documents_page.form_employees_selected', { n: form.employee_ids.length }) }}</span>
              </label>
              <input v-model="employeeSearch" type="text" :placeholder="$t('documents_page.form_employees_search')"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <div class="border border-gray-200 rounded-lg max-h-40 overflow-y-auto divide-y divide-gray-50">
                <label v-for="e in filteredEmployees" :key="e.id"
                       class="flex items-center gap-2.5 px-3 py-2 cursor-pointer hover:bg-gray-50 transition-colors select-none">
                  <input type="checkbox" :value="e.id" v-model="form.employee_ids" class="accent-blue-600" />
                  <span class="text-sm text-gray-700">{{ e.nom }} {{ e.cognoms }}</span>
                </label>
                <p v-if="filteredEmployees.length === 0" class="px-3 py-3 text-xs text-gray-400 text-center">{{ $t('documents_page.form_employees_empty') }}</p>
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('documents_page.form_file_label') }}</label>
              <input type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" @change="onFileChange" required
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-3 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-100 file:text-xs" />
              <p class="text-[11px] text-gray-400 mt-1">{{ $t('documents_page.form_file_hint') }}</p>
            </div>

            <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t">
              <button type="button" @click="closeUpload" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                {{ $t('common.cancel') }}
              </button>
              <button type="submit" :disabled="saving"
                      class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
                <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ saving ? $t('documents_page.sending_btn') : $t('documents_page.send_btn') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal confirmar eliminació ────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="deleteTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <IconAlertTriangle class="w-5 h-5 text-red-600" />
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ $t('documents_page.delete_title') }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ $t('documents_page.delete_desc', { title: deleteTarget.title }) }}</p>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">{{ $t('common.cancel') }}</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 transition-colors">
              {{ deleting ? $t('common.deleting') : $t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../stores/auth'
import { useDocuments } from '../composables/useDocuments'
import api from '../services/api'
import {
  IconUpload, IconDownload, IconTrash, IconX, IconAlertTriangle,
  IconInbox, IconReceipt2, IconStethoscope, IconFileDescription,
} from '@tabler/icons-vue'

const { t, locale } = useI18n()
const auth = useAuthStore()
const dateLocale = computed(() => ({ ca: 'ca-ES', es: 'es-ES', en: 'en-GB' }[locale.value] || 'ca-ES'))

const ROLE_HIERARCHY = { user: 0, hr: 1, admin: 2, superadmin: 3, founder: 4 }
const isManager   = computed(() => (ROLE_HIERARCHY[auth.user?.role] ?? -1) >= ROLE_HIERARCHY.hr)
const hasEmployee  = computed(() => !!auth.user?.employee?.id)

const { documents: allDocuments, loading: loadingAll, saving: savingAll, load: loadAllRaw, upload, remove, download } = useDocuments()
const { documents: myDocuments, loading: loadingMine, saving: savingMine, load: loadMineRaw, uploadSelf } = useDocuments()
const saving = computed(() => isSelfUpload.value ? savingMine.value : savingAll.value)

const filterEmployee = ref('')
const filterType     = ref('')

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString(dateLocale.value, { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatSize(bytes) {
  if (!bytes) return '—'
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const TYPE_LABELS = { payroll: 'type_payroll', medical_certificate: 'type_medical_certificate', other: 'type_other' }
function typeLabel(type) { return t('documents_page.' + (TYPE_LABELS[type] || 'type_other')) }

const TYPE_BADGE_CLASS = {
  payroll: 'bg-green-50 text-green-700',
  medical_certificate: 'bg-red-50 text-red-700',
  other: 'bg-gray-100 text-gray-600',
}
function typeBadgeClass(type) { return TYPE_BADGE_CLASS[type] || TYPE_BADGE_CLASS.other }

const TYPE_ICONS = { payroll: IconReceipt2, medical_certificate: IconStethoscope, other: IconFileDescription }
function typeIcon(type) { return TYPE_ICONS[type] || IconFileDescription }

const TYPE_ICON_BG = { payroll: 'bg-green-50', medical_certificate: 'bg-red-50', other: 'bg-gray-100' }
function typeIconBgClass(type) { return TYPE_ICON_BG[type] || TYPE_ICON_BG.other }

const TYPE_ICON_COLOR = { payroll: 'text-green-600', medical_certificate: 'text-red-600', other: 'text-gray-500' }
function typeIconClass(type) { return TYPE_ICON_COLOR[type] || TYPE_ICON_COLOR.other }

async function doDownload(doc) {
  try {
    await download(doc)
    if (!doc.read_at && doc.employee_id === auth.user?.employee?.id) doc.read_at = new Date().toISOString()
  } catch {
    // silent — les descàrregues fallides es poden reintentar
  }
}

// ── Llista d'empleats (per al selector del formulari) ──────────────────────────
const employees = ref([])
async function loadEmployees() {
  const all = []
  let page = 1
  let lastPage = 1
  do {
    const res = await api.get(`/v1/employees?page=${page}`)
    const d = res.data.data
    all.push(...(d.data || []))
    lastPage = d.last_page || 1
    page++
  } while (page <= lastPage)
  employees.value = all
}

const employeeSearch = ref('')
const filteredEmployees = computed(() => {
  const q = employeeSearch.value.trim().toLowerCase()
  if (!q) return employees.value
  return employees.value.filter(e => `${e.nom} ${e.cognoms}`.toLowerCase().includes(q))
})

// ── Formulari de pujada ─────────────────────────────────────────────────────────
const uploadModal  = ref(false)
const isSelfUpload  = ref(false)
const formError     = ref('')
const selectedFile  = ref(null)
const form = reactive({ title: '', description: '', type: 'payroll', employee_ids: [] })

function resetForm(defaultType = 'payroll') {
  Object.assign(form, { title: '', description: '', type: defaultType, employee_ids: [] })
  employeeSearch.value = ''
  selectedFile.value   = null
  formError.value      = ''
}

function openUpload() {
  isSelfUpload.value = false
  resetForm('payroll')
  uploadModal.value = true
}

function openSelfUpload() {
  isSelfUpload.value = true
  resetForm('medical_certificate')
  uploadModal.value = true
}

function closeUpload() { uploadModal.value = false }

function onFileChange(e) {
  selectedFile.value = e.target.files?.[0] || null
}

async function submitUpload() {
  formError.value = ''
  if (!isSelfUpload.value && form.employee_ids.length === 0) {
    formError.value = t('documents_page.select_at_least_one')
    return
  }
  if (!selectedFile.value) return

  const fd = new FormData()
  fd.append('title', form.title)
  fd.append('description', form.description || '')
  fd.append('type', form.type)
  fd.append('file', selectedFile.value)

  let result
  if (isSelfUpload.value) {
    result = await uploadSelf(fd)
  } else {
    form.employee_ids.forEach(id => fd.append('employee_ids[]', id))
    result = await upload(fd)
  }

  if (result.ok) {
    closeUpload()
    if (isSelfUpload.value) loadMineRaw()
    else fetchAll()
  } else {
    formError.value = result.message || t('documents_page.error_upload')
  }
}

// ── Eliminar ────────────────────────────────────────────────────────────────────
const deleteTarget = ref(null)
const deleting      = ref(false)

function askDelete(d) { deleteTarget.value = d }

async function confirmDelete() {
  deleting.value = true
  try {
    await remove(deleteTarget.value.id)
    deleteTarget.value = null
    fetchAll()
  } catch {
    // l'usuari pot reintentar
  } finally {
    deleting.value = false
  }
}

// ── Càrrega ─────────────────────────────────────────────────────────────────────
function fetchAll() {
  if (!isManager.value) return
  const filters = {}
  if (filterEmployee.value) filters.employee_id = filterEmployee.value
  if (filterType.value) filters.type = filterType.value
  loadAllRaw(filters)
}

onMounted(() => {
  if (isManager.value) {
    fetchAll()
    loadEmployees()
  }
  if (hasEmployee.value) {
    loadMineRaw()
  }
})
</script>
