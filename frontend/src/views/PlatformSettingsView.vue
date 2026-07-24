<template>
  <div>
    <div class="mb-5">
      <h2 class="text-base font-medium text-gray-900">{{ $t('platform_settings.title') }}</h2>
      <p class="text-sm text-gray-400 mt-0.5">{{ $t('platform_settings.subtitle') }}</p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="bg-white border border-gray-200 rounded-xl p-6 space-y-4 max-w-2xl">
      <div class="h-4 bg-gray-100 animate-pulse rounded w-40" />
      <div class="h-10 bg-gray-100 animate-pulse rounded" />
      <div class="h-4 bg-gray-100 animate-pulse rounded w-40" />
      <div class="h-10 bg-gray-100 animate-pulse rounded" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-white border border-gray-200 rounded-xl p-10 flex flex-col items-center text-center max-w-2xl">
      <IconAlertTriangle class="w-8 h-8 text-red-400 mb-2" />
      <p class="text-sm text-red-600">{{ error }}</p>
      <button @click="load" class="mt-3 text-xs text-blue-600 hover:underline">{{ $t('common.retry') }}</button>
    </div>

    <form v-else @submit.prevent="submit" class="bg-white border border-gray-200 rounded-xl overflow-hidden max-w-2xl">
      <div class="px-6 py-5 space-y-6">

        <!-- Imatges -->
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('platform_settings.section_images') }}</p>
          <div class="grid grid-cols-2 gap-6">
            <!-- Logo -->
            <div>
              <p class="text-xs text-gray-500 mb-2">{{ $t('platform_settings.logo') }}</p>
              <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                  <img v-if="logoPreview || form.logo_url" :src="logoPreview || form.logo_url" class="w-full h-full object-cover" />
                  <IconPhoto v-else class="w-5 h-5 text-gray-300" />
                </div>
                <div>
                  <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                    <IconUpload class="w-3.5 h-3.5" />{{ $t('platform_settings.select_file') }}
                    <input type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                  </label>
                  <p class="text-[10px] text-gray-400 mt-1">{{ $t('platform_settings.logo_hint') }}</p>
                  <button v-if="logoPreview || form.logo_url" type="button" @click="clearLogo" class="text-[10px] text-red-500 hover:underline mt-0.5 block">{{ $t('common.delete') }}</button>
                </div>
              </div>
            </div>
            <!-- Favicon -->
            <div>
              <p class="text-xs text-gray-500 mb-2">{{ $t('platform_settings.favicon') }}</p>
              <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50">
                  <img v-if="faviconPreview || form.favicon_url" :src="faviconPreview || form.favicon_url" class="w-full h-full object-contain p-2" />
                  <IconPhoto v-else class="w-5 h-5 text-gray-300" />
                </div>
                <div>
                  <label class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-gray-600">
                    <IconUpload class="w-3.5 h-3.5" />{{ $t('platform_settings.select_file') }}
                    <input type="file" accept="image/*" class="hidden" @change="onFaviconChange" />
                  </label>
                  <p class="text-[10px] text-gray-400 mt-1">{{ $t('platform_settings.favicon_hint') }}</p>
                  <button v-if="faviconPreview || form.favicon_url" type="button" @click="clearFavicon" class="text-[10px] text-red-500 hover:underline mt-0.5 block">{{ $t('common.delete') }}</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Branding -->
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('platform_settings.section_branding') }}</p>
          <div class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('platform_settings.product_name') }} <span class="text-red-500">*</span></label>
                <input v-model="form.nom_producte" type="text" :placeholder="$t('platform_settings.product_name_placeholder')"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       :class="formErrors.nom_producte ? 'border-red-400' : 'border-gray-200'" />
                <p v-if="formErrors.nom_producte" class="text-xs text-red-600 mt-1">{{ formErrors.nom_producte[0] }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('platform_settings.primary_color') }}</label>
                <div class="flex items-center gap-2">
                  <input v-model="form.color_primari" type="color"
                         class="w-10 h-9 border border-gray-200 rounded-lg cursor-pointer flex-shrink-0 p-0.5" />
                  <input v-model="form.color_primari" type="text" placeholder="#2563eb"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contacte i legal -->
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">{{ $t('platform_settings.section_contact') }}</p>
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('platform_settings.support_email') }}</label>
              <input v-model="form.email_suport" type="email" :placeholder="$t('platform_settings.support_email_placeholder')"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="formErrors.email_suport ? 'border-red-400' : 'border-gray-200'" />
              <p v-if="formErrors.email_suport" class="text-xs text-red-600 mt-1">{{ formErrors.email_suport[0] }}</p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('platform_settings.legal_footer') }}</label>
              <textarea v-model="form.peu_legal" rows="3" :placeholder="$t('platform_settings.legal_footer_placeholder')"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
            </div>
          </div>
        </div>

        <p v-if="successMsg" class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg p-3">{{ successMsg }}</p>
        <p v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</p>
      </div>

      <div class="px-6 py-4 border-t bg-gray-50 flex items-center justify-end">
        <button type="submit" :disabled="saving"
                class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
          <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
          </svg>
          {{ saving ? $t('common.saving') : $t('platform_settings.save_btn') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { IconPhoto, IconUpload, IconAlertTriangle } from '@tabler/icons-vue'
import { usePlatformSettings } from '../composables/usePlatformSettings'

const { t } = useI18n()
const { settings, loading, saving, error, load: loadSettings, save } = usePlatformSettings()

const formErrors = ref({})
const formError   = ref('')
const successMsg  = ref('')

const form = reactive({
  nom_producte: '', color_primari: '', email_suport: '', peu_legal: '',
  logo_url: '', favicon_url: '',
})

// ── Imatges ───────────────────────────────────────────────────────────────────
const logoPreview    = ref('')
const logoBase64     = ref('')
const faviconPreview = ref('')
const faviconBase64  = ref('')

function readImageFile(file, onResult) {
  const reader = new FileReader()
  reader.onload = ev => onResult(ev.target.result)
  reader.readAsDataURL(file)
}
function onLogoChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  readImageFile(file, r => { logoPreview.value = r; logoBase64.value = r })
}
function clearLogo() { logoPreview.value = ''; logoBase64.value = ''; form.logo_url = '' }
function onFaviconChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  readImageFile(file, r => { faviconPreview.value = r; faviconBase64.value = r })
}
function clearFavicon() { faviconPreview.value = ''; faviconBase64.value = ''; form.favicon_url = '' }

function fillForm(s) {
  Object.assign(form, {
    nom_producte:  s.nom_producte  || '',
    color_primari: s.color_primari || '',
    email_suport:  s.email_suport  || '',
    peu_legal:     s.peu_legal     || '',
    logo_url:      s.logo_url      || '',
    favicon_url:   s.favicon_url   || '',
  })
  logoPreview.value = ''; logoBase64.value = ''
  faviconPreview.value = ''; faviconBase64.value = ''
}

async function load() {
  await loadSettings()
  if (settings.value) fillForm(settings.value)
}

async function submit() {
  formErrors.value = {}
  formError.value  = ''
  successMsg.value = ''
  const payload = {
    nom_producte: form.nom_producte.trim(), color_primari: form.color_primari,
    email_suport: form.email_suport, peu_legal: form.peu_legal,
  }
  if (logoBase64.value)    payload.logo_base64    = logoBase64.value
  if (faviconBase64.value) payload.favicon_base64 = faviconBase64.value

  const result = await save(payload)
  if (result.ok) {
    fillForm(result.data)
    successMsg.value = t('platform_settings.saved_success')
    setTimeout(() => { successMsg.value = '' }, 3000)
  } else {
    formErrors.value = result.errors || {}
    formError.value  = result.message || t('platform_settings.error_save')
  }
}

onMounted(load)
</script>
