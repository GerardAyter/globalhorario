<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">{{ $t('departments.title') }}</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ $t(pagination.total === 1 ? 'departments.count_one' : 'departments.count_other', { n: pagination.total }) }}</p>
      </div>
      <button @click="openCreate" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
        <IconPlus class="w-4 h-4" />{{ $t('departments.new') }}
      </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-8 h-8 rounded-lg bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-100 animate-pulse rounded w-32" />
            <div class="h-3 bg-gray-100 animate-pulse rounded w-20" />
          </div>
          <div class="flex gap-2">
            <div class="w-7 h-7 bg-gray-100 animate-pulse rounded" />
            <div class="w-7 h-7 bg-gray-100 animate-pulse rounded" />
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="flex flex-col items-center justify-center py-16 text-center">
        <IconAlertTriangle class="w-8 h-8 text-red-400 mb-2" />
        <p class="text-sm text-red-600">{{ error }}</p>
        <button @click="load()" class="mt-3 text-xs text-blue-600 hover:underline">{{ $t('common.retry') }}</button>
      </div>

      <!-- Buit -->
      <div v-else-if="departments.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
        <IconSitemap class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">{{ $t('departments.empty') }}</p>
        <button @click="openCreate" class="mt-3 text-sm text-blue-600 hover:underline">{{ $t('departments.create_first') }}</button>
      </div>

      <!-- Llista -->
      <div v-else class="divide-y divide-gray-100">
        <div v-for="d in departments" :key="d.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
          <!-- Icona -->
          <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
            <IconSitemap class="w-4 h-4 text-blue-600" />
          </div>

          <!-- Nom + ubicació -->
          <div class="flex-1 min-w-0">
            <div class="font-medium text-gray-900 text-sm truncate">{{ d.name }}</div>
            <div v-if="d.location" class="text-xs text-gray-400 truncate">{{ d.location }}</div>
          </div>

          <!-- Botons -->
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="openEdit(d)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" :title="$t('common.edit')">
              <IconEdit class="w-4 h-4" />
            </button>
            <button @click="askDelete(d)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" :title="$t('common.delete')">
              <IconTrash class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Paginació -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t bg-gray-50">
        <p class="text-xs text-gray-400">{{ $t('common.page_of', { current: pagination.current_page, total: pagination.last_page }) }}</p>
        <div class="flex gap-1">
          <button @click="load(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
            class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">{{ $t('common.previous') }}</button>
          <button @click="load(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
            class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">{{ $t('common.next') }}</button>
        </div>
      </div>
    </div>

    <!-- ── Modal crear / editar ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-medium text-gray-900">{{ modal.isEdit ? $t('departments.edit_title') : $t('departments.new_title') }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <form @submit.prevent="submitModal" class="px-6 py-5 space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('departments.name_label') }}</label>
              <input v-model="form.name" type="text" :placeholder="$t('departments.name_placeholder')" autofocus
                     class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="formErrors.name ? 'border-red-400' : 'border-gray-200'" />
              <p v-if="formErrors.name" class="text-xs text-red-600 mt-1">{{ formErrors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('departments.location_label') }}</label>
              <input v-model="form.location" type="text" :placeholder="$t('departments.location_placeholder')"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                {{ $t('common.cancel') }}
              </button>
              <button type="submit" :disabled="saving"
                      class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
                <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ saving ? $t('common.saving') : (modal.isEdit ? $t('common.save') : $t('departments.create_btn')) }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal confirmar eliminació ─────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="deleteTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
          <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
              <IconAlertTriangle class="w-5 h-5 text-red-600" />
            </div>
            <div>
              <p class="font-medium text-gray-900">{{ $t('departments.delete_title') }}</p>
              <p class="text-sm text-gray-500 mt-1">
                {{ $t('departments.delete_desc', { name: deleteTarget.name }) }}
                {{ $t('departments.delete_warning') }}
              </p>
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
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPlus, IconEdit, IconTrash, IconX, IconSitemap, IconAlertTriangle } from '@tabler/icons-vue'
import { useDepartments } from '../composables/useDepartments'

const { departments, loading, saving, error, pagination, load, create, update, remove } = useDepartments()
const { t } = useI18n()

// ── Formulari ─────────────────────────────────────────────────────────────────
const modal      = reactive({ open: false, isEdit: false, editId: null })
const formErrors = ref({})
const formError  = ref('')
const form       = reactive({ name: '', location: '' })

function resetForm() {
  form.name     = ''
  form.location = ''
  formErrors.value = {}
  formError.value  = ''
}

function openCreate() {
  resetForm()
  Object.assign(modal, { open: true, isEdit: false, editId: null })
}

function openEdit(d) {
  resetForm()
  form.name     = d.name     || ''
  form.location = d.location || ''
  Object.assign(modal, { open: true, isEdit: true, editId: d.id })
}

function closeModal() { modal.open = false }

async function submitModal() {
  formErrors.value = {}
  formError.value  = ''
  const payload = { name: form.name.trim(), location: form.location || null }
  const result  = modal.isEdit
    ? await update(modal.editId, payload)
    : await create(payload)
  if (result.ok) { closeModal(); load(pagination.value.current_page) }
  else { formErrors.value = result.errors || {}; formError.value = result.message || t('common.error_saving') }
}

// ── Eliminar ──────────────────────────────────────────────────────────────────
const deleteTarget = ref(null)
const deleting     = ref(false)

function askDelete(d) { deleteTarget.value = d }

async function confirmDelete() {
  deleting.value = true
  try {
    await remove(deleteTarget.value.id)
    deleteTarget.value = null
    load(pagination.value.current_page)
  } finally {
    deleting.value = false
  }
}

onMounted(() => load())
</script>
