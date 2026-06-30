<template>
  <div class="min-h-screen flex">
    <!-- Left column -->
    <div class="hidden md:flex w-1/2 bg-primary text-primary-contrast items-center justify-center p-8 flex-col">
      <div class="text-2xl font-medium">HRSuite</div>
      <div class="text-primary-200 text-sm mt-2">La gestió de persones, sense complexitat.</div>
      <div class="mt-auto text-primary-400 text-xs">© 2026 HRSuite</div>
    </div>

    <!-- Right column -->
    <div class="flex-1 bg-white flex items-center justify-center p-6">
      <div class="max-w-sm w-full mx-auto px-6">

        <!-- Token invàlid / caducat -->
        <div v-if="tokenInvalid" class="text-center">
          <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <IconAlertCircle class="w-7 h-7 text-red-500" />
          </div>
          <h1 class="text-xl font-medium text-gray-900">Enllaç no vàlid</h1>
          <p class="text-sm text-gray-500 mt-2">Aquest enllaç ha caducat o no és vàlid. Contacta amb l'administrador perquè et reenvïi la invitació.</p>
          <router-link to="/login" class="inline-block mt-6 text-sm text-blue-600 hover:underline">Anar al login</router-link>
        </div>

        <!-- Contrasenya establerta correctament -->
        <div v-else-if="done" class="text-center">
          <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
            <IconCheck class="w-7 h-7 text-green-600" />
          </div>
          <h1 class="text-xl font-medium text-gray-900">Contrasenya creada</h1>
          <p class="text-sm text-gray-500 mt-2">Ja pots accedir al teu compte amb el correu i la contrasenya que acabes de crear.</p>
          <router-link to="/login" class="inline-block mt-6 btn-primary btn text-sm font-medium px-6 py-2.5 rounded-lg">
            Accedir
          </router-link>
        </div>

        <!-- Formulari -->
        <div v-else>
          <h1 class="text-2xl font-medium text-gray-900">Crea la teva contrasenya</h1>
          <p class="text-sm text-gray-400 mt-1">Estableix una contrasenya per accedir al teu compte.</p>
          <p v-if="email" class="text-xs text-gray-500 mt-2 bg-gray-50 rounded-lg px-3 py-2">{{ email }}</p>

          <form class="mt-8 flex flex-col gap-4" @submit.prevent="handleSubmit">
            <div class="relative">
              <label class="text-xs font-medium text-gray-600 mb-1 block">Nova contrasenya</label>
              <input :type="showPassword ? 'text' : 'password'" v-model="password"
                     placeholder="Mínim 8 caràcters"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="errors.password ? 'border-red-400' : ''" />
              <button type="button" class="absolute right-3 top-9 text-gray-400" @click="showPassword = !showPassword">
                <IconEye v-if="!showPassword" class="w-4 h-4" />
                <IconEyeOff v-else class="w-4 h-4" />
              </button>
              <p v-if="errors.password" class="text-xs text-red-600 mt-1">{{ errors.password }}</p>
            </div>

            <div class="relative">
              <label class="text-xs font-medium text-gray-600 mb-1 block">Confirmar contrasenya</label>
              <input :type="showConfirm ? 'text' : 'password'" v-model="passwordConfirmation"
                     placeholder="Repeteix la contrasenya"
                     class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="errors.password_confirmation ? 'border-red-400' : ''" />
              <button type="button" class="absolute right-3 top-9 text-gray-400" @click="showConfirm = !showConfirm">
                <IconEye v-if="!showConfirm" class="w-4 h-4" />
                <IconEyeOff v-else class="w-4 h-4" />
              </button>
              <p v-if="errors.password_confirmation" class="text-xs text-red-600 mt-1">{{ errors.password_confirmation }}</p>
            </div>

            <div v-if="errorMsg" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3 flex items-center gap-2">
              <IconAlertCircle class="w-4 h-4 flex-shrink-0" />
              <span>{{ errorMsg }}</span>
            </div>

            <button :disabled="loading" type="submit"
                    class="w-full btn-primary btn font-medium text-sm py-2.5 rounded-lg disabled:opacity-75 flex items-center justify-center gap-2">
              <svg v-if="loading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              {{ loading ? 'Desant...' : 'Crear contrasenya' }}
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { IconEye, IconEyeOff, IconAlertCircle, IconCheck } from '@tabler/icons-vue'
import api from '../../services/api'

const route = useRoute()

const token               = ref('')
const email               = ref('')
const password            = ref('')
const passwordConfirmation = ref('')
const showPassword        = ref(false)
const showConfirm         = ref(false)
const loading             = ref(false)
const done                = ref(false)
const tokenInvalid        = ref(false)
const errorMsg            = ref('')
const errors              = ref({})

onMounted(() => {
  token.value = route.query.token || ''
  email.value = route.query.email || ''
  if (! token.value || ! email.value) {
    tokenInvalid.value = true
  }
})

async function handleSubmit() {
  errorMsg.value = ''
  errors.value   = {}

  if (password.value.length < 8) {
    errors.value.password = 'La contrasenya ha de tenir almenys 8 caràcters'
    return
  }
  if (password.value !== passwordConfirmation.value) {
    errors.value.password_confirmation = 'Les contrasenyes no coincideixen'
    return
  }

  loading.value = true
  try {
    await api.post('/auth/set-password', {
      token:                 token.value,
      email:                 email.value,
      password:              password.value,
      password_confirmation: passwordConfirmation.value,
    })
    done.value = true
  } catch (e) {
    const status = e?.response?.status
    if (status === 422) {
      const data = e.response.data
      if (data.errors) {
        errors.value = data.errors
      }
      const msg = data.message || ''
      if (msg.includes('vàlid') || msg.includes('caducat')) {
        tokenInvalid.value = true
      } else {
        errorMsg.value = msg || 'Error en establir la contrasenya'
      }
    } else {
      errorMsg.value = e?.response?.data?.message || 'Error inesperat. Torna-ho a intentar.'
    }
  } finally {
    loading.value = false
  }
}
</script>
