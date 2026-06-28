<template>
  <div class="min-h-screen flex flex-col bg-white text-gray-900">
    <!-- Topbar -->
    <header class="h-12 flex items-center justify-between px-4 bg-white border-b">
      <div class="flex items-center gap-3">
        <img v-if="whitelabel?.logo_url" :src="whitelabel.logo_url" class="h-8 object-contain max-w-[160px]" />
        <div v-else class="text-lg font-medium">Global<span class="text-[#185FA5]">Horario</span></div>
        <div class="h-6 border-l" />
        <nav class="flex items-center gap-2">
          <div v-for="item in topNav" :key="item.name" @click="go(item)"
               :class="topActive(item) ? 'bg-blue-50 text-blue-800 font-medium rounded px-2 py-1 cursor-pointer' : 'text-gray-500 hover:text-gray-700 px-2 py-1 cursor-pointer'">
            {{ item.label }}
          </div>
        </nav>
      </div>

      <div class="flex items-center gap-3">
        <div class="relative">
          <input placeholder="Cerca..." class="w-48 h-8 pl-8 pr-2 bg-gray-50 border rounded-lg text-sm" />
          <span class="absolute left-2 top-1.5 text-gray-400">🔍</span>
        </div>
        <div class="relative">
          <button class="text-gray-600">🔔</button>
          <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] rounded-full px-1">3</span>
        </div>

        <!-- Avatar + menú -->
        <div class="relative">
          <button @click="userMenuOpen = !userMenuOpen"
                  class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-sm font-medium hover:ring-2 hover:ring-blue-300 transition-all focus:outline-none">
            {{ initials }}
          </button>

          <!-- Overlay transparent per tancar al clic exterior -->
          <div v-if="userMenuOpen" class="fixed inset-0 z-20" @click="userMenuOpen = false" />

          <!-- Menú desplegable -->
          <div v-if="userMenuOpen"
               class="absolute right-0 top-full mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-30 overflow-hidden">
            <!-- Capçalera del menú -->
            <div class="px-4 py-3 border-b bg-gray-50">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center text-sm font-semibold flex-shrink-0">
                  {{ initials }}
                </div>
                <div class="min-w-0">
                  <div class="font-medium text-gray-900 text-sm truncate">{{ auth.user?.name }}</div>
                  <div v-if="auth.companyName" class="text-xs text-gray-500 truncate">{{ auth.companyName }}</div>
                  <div class="text-xs text-gray-400 truncate">{{ auth.user?.email }}</div>
                </div>
              </div>
              <div class="mt-2">
                <span :class="roleBadgeClass(auth.user?.role)" class="text-[10px] font-medium px-2 py-0.5 rounded-full">
                  {{ roleLabel(auth.user?.role) }}
                </span>
              </div>
            </div>

            <!-- Opcions -->
            <div class="py-1">
              <button @click="openEditProfile"
                      class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                <IconUserEdit class="w-4 h-4 text-gray-400" />
                Editar perfil
              </button>
              <div class="border-t my-1" />
              <button @click="handleLogout"
                      class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                <IconLogout class="w-4 h-4" />
                Tancar sessió
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="flex flex-1">
      <!-- Sidebar -->
      <aside class="w-48 bg-gray-50 border-r h-[calc(100vh-3rem)] sticky top-12 overflow-y-auto">
        <div class="px-2 pb-4">
          <template v-if="hasCompany">
            <div class="text-[10px] text-gray-400 font-medium tracking-wider px-3 mt-4 mb-1">HORARI</div>
            <div v-for="item in sidebar.horari" :key="item.name"
                 @click="go(item)"
                 :class="sidebarActive(item)
                   ? 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 bg-blue-50 text-blue-800 font-medium'
                   : 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 text-gray-600 hover:bg-gray-100'">
              <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
              <span class="flex-1">{{ item.label }}</span>
              <span v-if="item.badge" class="bg-blue-600 text-white text-[10px] rounded-full px-1.5">{{ item.badge }}</span>
            </div>

            <div class="text-[10px] text-gray-400 font-medium tracking-wider px-3 mt-4 mb-1">PERSONAL</div>
            <div v-for="item in sidebar.personal" :key="item.name"
                 @click="go(item)"
                 :class="sidebarActive(item)
                   ? 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 bg-blue-50 text-blue-800 font-medium'
                   : 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 text-gray-600 hover:bg-gray-100'">
              <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
              <span class="flex-1">{{ item.label }}</span>
              <span v-if="item.badge" class="bg-blue-600 text-white text-[10px] rounded-full px-1.5">{{ item.badge }}</span>
            </div>
          </template>

          <div class="text-[10px] text-gray-400 font-medium tracking-wider px-3 mt-4 mb-1">GESTIÓ</div>
          <div v-for="item in sidebar.gestio" :key="item.name"
               @click="go(item)"
               :class="sidebarActive(item)
                 ? 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 bg-blue-50 text-blue-800 font-medium'
                 : 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 text-gray-600 hover:bg-gray-100'">
            <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
            <span class="flex-1">{{ item.label }}</span>
            <span v-if="item.badge" class="bg-blue-600 text-white text-[10px] rounded-full px-1.5">{{ item.badge }}</span>
          </div>

          <template v-if="isSuperadmin">
            <div class="text-[10px] text-gray-400 font-medium tracking-wider px-3 mt-4 mb-1">DISTRIBUCIÓ</div>
            <div v-for="item in sidebar.distribucio" :key="item.name"
                 @click="go(item)"
                 :class="sidebarActive(item)
                   ? 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 bg-blue-50 text-blue-800 font-medium'
                   : 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 text-gray-600 hover:bg-gray-100'">
              <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
              <span class="flex-1">{{ item.label }}</span>
              <span v-if="item.badge" class="bg-blue-600 text-white text-[10px] rounded-full px-1.5">{{ item.badge }}</span>
            </div>
          </template>

          <template v-if="isFounder">
            <div class="text-[10px] text-gray-400 font-medium tracking-wider px-3 mt-4 mb-1">SISTEMA</div>
            <div v-for="item in sidebar.sistema" :key="item.name"
                 @click="go(item)"
                 :class="sidebarActive(item)
                   ? 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 bg-blue-50 text-blue-800 font-medium'
                   : 'flex items-center gap-2 px-3 py-1.5 text-sm cursor-pointer rounded-lg mx-1 text-gray-600 hover:bg-gray-100'">
              <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
              <span class="flex-1">{{ item.label }}</span>
              <span v-if="item.badge" class="bg-blue-600 text-white text-[10px] rounded-full px-1.5">{{ item.badge }}</span>
            </div>
          </template>
        </div>
      </aside>

      <!-- Contingut principal -->
      <main class="flex-1 bg-gray-50 overflow-auto p-4">
        <router-view />
      </main>
    </div>

    <!-- Modal editar perfil -->
    <Teleport to="body">
      <div v-if="editProfileOpen" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="editProfileOpen = false">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-medium text-gray-900">Editar perfil</h3>
            <button @click="editProfileOpen = false" class="text-gray-400 hover:text-gray-600">
              <IconX class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitProfile" class="px-5 py-4 space-y-4">
            <!-- Nom -->
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Nom complet <span class="text-red-500">*</span></label>
              <input v-model="profileForm.name" type="text"
                     class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="profileErrors.name ? 'border-red-400' : 'border-gray-200'" />
              <p v-if="profileErrors.name" class="text-xs text-red-600 mt-1">{{ profileErrors.name[0] }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Correu electrònic <span class="text-red-500">*</span></label>
              <input v-model="profileForm.email" type="email"
                     class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="profileErrors.email ? 'border-red-400' : 'border-gray-200'" />
              <p v-if="profileErrors.email" class="text-xs text-red-600 mt-1">{{ profileErrors.email[0] }}</p>
            </div>

            <!-- Canvi de contrasenya -->
            <div class="border-t pt-4">
              <button type="button" @click="showPasswordFields = !showPasswordFields"
                      class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                <IconChevronDown class="w-3 h-3 transition-transform" :class="showPasswordFields ? 'rotate-180' : ''" />
                {{ showPasswordFields ? 'Amagar canvi de contrasenya' : 'Canviar contrasenya' }}
              </button>

              <div v-if="showPasswordFields" class="mt-3 space-y-3">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Contrasenya actual</label>
                  <input v-model="profileForm.current_password" type="password"
                         class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                         :class="profileErrors.current_password ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="profileErrors.current_password" class="text-xs text-red-600 mt-1">{{ profileErrors.current_password[0] }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Nova contrasenya</label>
                  <input v-model="profileForm.password" type="password"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                         :class="profileErrors.password ? 'border-red-400' : 'border-gray-200'" />
                  <p v-if="profileErrors.password" class="text-xs text-red-600 mt-1">{{ profileErrors.password[0] }}</p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Confirmar nova contrasenya</label>
                  <input v-model="profileForm.password_confirmation" type="password"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <!-- Error general -->
            <div v-if="profileError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ profileError }}</div>

            <!-- Missatge d'èxit -->
            <div v-if="profileSuccess" class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg p-3">
              Perfil actualitzat correctament.
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
              <button type="button" @click="editProfileOpen = false"
                      class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                Cancel·lar
              </button>
              <button type="submit" :disabled="profileSaving"
                      class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
                <svg v-if="profileSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ profileSaving ? 'Desant...' : 'Desar canvis' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import { computed, ref, reactive, onMounted, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import {
  IconClock, IconLayoutDashboard, IconArrowsExchange,
  IconUsers, IconCalendarOff, IconReceipt2,
  IconReportAnalytics, IconSettings, IconBuilding, IconBuildingSkyscraper,
  IconSitemap, IconUserEdit, IconLogout, IconX, IconChevronDown,
} from '@tabler/icons-vue'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()

const initials      = computed(() => auth.initials)
const isFounder     = computed(() => auth.user?.role === 'founder')
const isSuperadmin  = computed(() => auth.user?.role === 'superadmin')
const hasCompany    = computed(() => ['admin', 'hr', 'user'].includes(auth.user?.role))
const whitelabel    = computed(() => auth.whitelabel)

watch(whitelabel, (wb) => {
  if (!wb?.favicon_url) return
  let link = document.querySelector("link[rel~='icon']")
  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.head.appendChild(link)
  }
  link.href = wb.favicon_url
}, { immediate: true })

// ── Menú d'usuari ─────────────────────────────────────────────────────────────
const userMenuOpen = ref(false)

function handleLogout() {
  userMenuOpen.value = false
  auth.logout()
  router.push({ name: 'login' })
}

function roleLabel(role) {
  const map = { founder: 'Founder', superadmin: 'Superadmin', admin: 'Administrador', hr: 'Recursos Humans', user: 'Usuari' }
  return map[role] || role
}

function roleBadgeClass(role) {
  const map = {
    founder:    'bg-purple-100 text-purple-700',
    superadmin: 'bg-blue-100 text-blue-700',
    admin:      'bg-indigo-100 text-indigo-700',
    hr:         'bg-green-100 text-green-700',
    user:       'bg-gray-100 text-gray-600',
  }
  return map[role] || 'bg-gray-100 text-gray-600'
}

// ── Editar perfil ─────────────────────────────────────────────────────────────
const editProfileOpen    = ref(false)
const profileSaving      = ref(false)
const profileSuccess     = ref(false)
const profileError       = ref('')
const profileErrors      = ref({})
const showPasswordFields = ref(false)

const profileForm = reactive({
  name:                  '',
  email:                 '',
  current_password:      '',
  password:              '',
  password_confirmation: '',
})

function openEditProfile() {
  userMenuOpen.value       = false
  profileForm.name         = auth.user?.name || ''
  profileForm.email        = auth.user?.email || ''
  profileForm.current_password      = ''
  profileForm.password              = ''
  profileForm.password_confirmation = ''
  profileErrors.value      = {}
  profileError.value       = ''
  profileSuccess.value     = false
  showPasswordFields.value = false
  editProfileOpen.value    = true
}

async function submitProfile() {
  profileErrors.value  = {}
  profileError.value   = ''
  profileSuccess.value = false
  profileSaving.value  = true

  const payload = { name: profileForm.name, email: profileForm.email }
  if (showPasswordFields.value && profileForm.password) {
    payload.current_password      = profileForm.current_password
    payload.password              = profileForm.password
    payload.password_confirmation = profileForm.password_confirmation
  }

  try {
    await auth.updateProfile(payload)
    profileSuccess.value = true
    showPasswordFields.value = false
    profileForm.current_password      = ''
    profileForm.password              = ''
    profileForm.password_confirmation = ''
  } catch (e) {
    const status = e?.response?.status
    if (status === 422) {
      profileErrors.value = e.response.data.errors || {}
      profileError.value  = e.response.data.message || ''
    } else {
      profileError.value = e?.response?.data?.message || 'Error en desar els canvis.'
    }
  } finally {
    profileSaving.value = false
  }
}

// ── Navegació ─────────────────────────────────────────────────────────────────
const topNavAll = [
  { name: 'dashboard', label: 'Dashboard', path: '/dashboard' },
  { name: 'employees', label: 'Empleats',  path: '/employees', requiresCompany: true },
  { name: 'reports',   label: 'Informes',  path: '/reports'   },
]
const topNav = computed(() => topNavAll.filter(i => !i.requiresCompany || hasCompany.value))

const sidebar = {
  horari: [
    { name: 'control', label: 'Control horari', icon: IconClock,           path: '/time-entries' },
    { name: 'dash',    label: 'Dashboard',       icon: IconLayoutDashboard, path: '/dashboard', badge: 5 },
    { name: 'shifts',  label: 'Torns',           icon: IconArrowsExchange,  path: '/shifts' },
  ],
  personal: [
    { name: 'employees', label: 'Empleats',  icon: IconUsers,       path: '/employees' },
    { name: 'absences',  label: 'Absències', icon: IconCalendarOff, path: '/absences'  },
    { name: 'payrolls',  label: 'Nòmines',   icon: IconReceipt2,    path: '/payrolls'  },
  ],
  gestio: [
    { name: 'departments', label: 'Departaments', icon: IconSitemap,         path: '/departments' },
    { name: 'reports',     label: 'Informes',     icon: IconReportAnalytics, path: '/reports'     },
    { name: 'settings',    label: 'Configuració', icon: IconSettings,        path: '/settings'    },
  ],
  distribucio: [
    { name: 'companies', label: 'Empreses', icon: IconBuildingSkyscraper, path: '/companies' },
  ],
  sistema: [
    { name: 'tenants', label: 'Distribuidors', icon: IconBuilding, path: '/tenants' },
  ],
}

function go(item)            { router.push(item.path) }
function sidebarActive(item) { return route.path === item.path }
function topActive(item)     { return route.path === item.path }

// ── Init: carrega dades completes de l'usuari (empresa, etc.) ─────────────────
onMounted(() => { auth.fetchMe().catch(() => {}) })
</script>
