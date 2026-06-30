<template>
  <div>
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">Empleats</h2>
        <p class="text-sm text-gray-400 mt-0.5">{{ pagination.total }} empleats registrats</p>
      </div>
      <button @click="openCreate" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
        <IconPlus class="w-4 h-4" />Nou empleat
      </button>
    </div>

    <div v-if="invitationError" class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 flex items-center gap-2">
      <IconAlertTriangle class="w-4 h-4 flex-shrink-0" />
      <span>{{ invitationError }}</span>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <!-- Skeleton -->
      <div v-if="loading" class="divide-y divide-gray-100">
        <div v-for="i in 4" :key="i" class="flex items-center gap-4 px-5 py-4">
          <div class="w-9 h-9 rounded-full bg-gray-100 animate-pulse flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-100 animate-pulse rounded w-36" />
            <div class="h-3 bg-gray-100 animate-pulse rounded w-24" />
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
        <button @click="load()" class="mt-3 text-xs text-blue-600 hover:underline">Tornar a intentar</button>
      </div>

      <!-- Buit -->
      <div v-else-if="employees.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
        <IconUsers class="w-10 h-10 text-gray-300 mb-3" />
        <p class="text-sm text-gray-500">Encara no hi ha empleats</p>
        <button @click="openCreate" class="mt-3 text-sm text-blue-600 hover:underline">Afegeix el primer empleat</button>
      </div>

      <!-- Llista -->
      <div v-else class="divide-y divide-gray-100">
        <div v-for="e in employees" :key="e.id" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
          <!-- #ID -->
          <div class="w-8 text-xs text-gray-400 font-mono flex-shrink-0 text-right">#{{ e.id }}</div>

          <!-- Avatar -->
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold text-white flex-shrink-0"
               :style="{ backgroundColor: avatarColor(e.nom + e.cognoms) }">
            {{ (e.nom[0] || '').toUpperCase() }}{{ (e.cognoms[0] || '').toUpperCase() }}
          </div>

          <!-- Nom + email + torn -->
          <div class="flex-1 min-w-0">
            <div class="font-medium text-gray-900 text-sm truncate">{{ e.nom }} {{ e.cognoms }}</div>
            <div class="flex items-center gap-2 flex-wrap">
              <span v-if="e.email" class="text-xs text-gray-400 truncate">{{ e.email }}</span>
              <span v-if="e.shift" class="flex items-center gap-1 text-xs text-gray-400">
                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: e.shift.color || '#94a3b8' }" />
                {{ e.shift.name }}
              </span>
            </div>
          </div>

          <!-- Empresa (superadmin) -->
          <div v-if="e.company" class="hidden lg:block w-36 text-xs text-gray-500 truncate">{{ e.company.name }}</div>

          <!-- Rol -->
          <div class="w-28 flex-shrink-0">
            <span v-if="e.user" :class="roleBadgeClass(e.user.role)" class="text-[10px] font-medium px-2 py-0.5 rounded-full">
              {{ roleLabel(e.user.role) }}
            </span>
            <span v-else class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">Sense accés</span>
          </div>

          <!-- Actiu -->
          <div class="w-16 flex-shrink-0">
            <span v-if="e.actiu" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-green-50 text-green-700">Actiu</span>
            <span v-else class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Baixa</span>
          </div>

          <!-- Botons -->
          <div class="flex items-center gap-1 flex-shrink-0">
            <button @click="viewTarget = e" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" title="Veure detalls">
              <IconEye class="w-4 h-4" />
            </button>
            <button @click="openEdit(e)" class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Editar">
              <IconEdit class="w-4 h-4" />
            </button>
            <button v-if="e.user"
                    @click="doSendInvitation(e)"
                    :disabled="sendingInvitationId === e.id"
                    :title="sentInvitations[e.id] ? 'Correu enviat!' : 'Enviar correu de recuperació de contrasenya'"
                    class="w-7 h-7 flex items-center justify-center rounded transition-colors"
                    :class="sentInvitations[e.id]
                      ? 'text-green-600 bg-green-50'
                      : 'text-gray-400 hover:text-blue-600 hover:bg-blue-50'">
              <IconCheck v-if="sentInvitations[e.id]" class="w-4 h-4" />
              <svg v-else-if="sendingInvitationId === e.id" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              <IconMail v-else class="w-4 h-4" />
            </button>
            <button @click="askDelete(e)"
                    :disabled="e.user?.role === 'admin'"
                    :title="e.user?.role === 'admin' ? 'No es pot eliminar l\'administrador de l\'empresa' : 'Eliminar'"
                    class="w-7 h-7 flex items-center justify-center rounded transition-colors"
                    :class="e.user?.role === 'admin'
                      ? 'text-gray-200 cursor-not-allowed'
                      : 'text-gray-400 hover:text-red-600 hover:bg-red-50'">
              <IconTrash class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Paginació -->
      <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t bg-gray-50">
        <p class="text-xs text-gray-400">Pàgina {{ pagination.current_page }} de {{ pagination.last_page }}</p>
        <div class="flex gap-1">
          <button @click="load(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                  class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">Anterior</button>
          <button @click="load(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
                  class="px-2.5 py-1 text-xs rounded border border-gray-200 disabled:opacity-40 hover:bg-white transition-colors">Següent</button>
        </div>
      </div>
    </div>

    <!-- ── Modal crear / editar ────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="modal.open" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="closeModal">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">{{ modal.isEdit ? 'Editar empleat' : 'Nou empleat' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <form @submit.prevent="submitModal" class="overflow-y-auto flex-1 px-6 py-5 space-y-6">

            <!-- Identitat -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Identitat</p>
              <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input v-model="form.nom" type="text" placeholder="Nom"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.nom ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.nom" class="text-xs text-red-600 mt-1">{{ formErrors.nom[0] }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cognoms <span class="text-red-500">*</span></label>
                    <input v-model="form.cognoms" type="text" placeholder="Cognoms"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="formErrors.cognoms ? 'border-red-400' : 'border-gray-200'" />
                    <p v-if="formErrors.cognoms" class="text-xs text-red-600 mt-1">{{ formErrors.cognoms[0] }}</p>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">DNI / NIE</label>
                    <input v-model="form.dni_nie" type="text" placeholder="12345678A"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Núm. Seguretat Social</label>
                    <input v-model="form.nss" type="text" placeholder="281234567890"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Data de naixement</label>
                  <input v-model="form.data_naixement" type="date"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <!-- Contacte -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Contacte</p>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                  <input v-model="form.email" type="email" placeholder="nom@empresa.com"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Telèfon</label>
                  <input v-model="form.telefon" type="tel" placeholder="+34 600 000 000"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
              </div>
            </div>

            <!-- Laboral -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Laboral</p>
              <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Departament</label>
                    <select v-model.number="form.department_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option :value="null">Sense departament</option>
                      <option v-for="dep in allDepartments" :key="dep.id" :value="dep.id">{{ dep.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Torn assignat</label>
                    <select v-model.number="form.torn_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option :value="null">Sense torn</option>
                      <option v-for="s in allShifts" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Conveni laboral</label>
                  <select v-model.number="form.conveni_id"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option :value="null">Sense conveni assignat</option>
                    <option v-for="c in allConvenis" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                  <p v-if="form.conveni_id" class="text-[11px] text-gray-400 mt-1">
                    {{ allConvenis.find(c => c.id === form.conveni_id)?.weekly_hours }}h/set. ·
                    {{ allConvenis.find(c => c.id === form.conveni_id)?.vacation_days }} dies vacances ·
                    {{ allConvenis.find(c => c.id === form.conveni_id)?.personal_days }} dies personals
                  </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Data d'alta</label>
                    <input v-model="form.data_alta" type="date"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Data de baixa</label>
                    <input v-model="form.data_baixa" type="date"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Percentatge de jornada (%)</label>
                  <input v-model.number="form.percentatge_jornada" type="number" min="0" max="100" step="0.5" placeholder="100"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="flex items-center gap-6">
                  <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.actiu ? 'bg-blue-600' : 'bg-gray-300'"
                         @click="form.actiu = !form.actiu">
                      <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                           :class="form.actiu ? 'left-4' : 'left-0.5'" />
                    </div>
                    <span class="text-sm text-gray-700">Actiu</span>
                  </label>
                  <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.geoloc_requerida ? 'bg-blue-600' : 'bg-gray-300'"
                         @click="form.geoloc_requerida = !form.geoloc_requerida">
                      <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                           :class="form.geoloc_requerida ? 'left-4' : 'left-0.5'" />
                    </div>
                    <span class="text-sm text-gray-700">Geolocalització requerida</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- WhatsApp -->
            <div>
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">WhatsApp</p>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-600 mb-1">Telèfon WhatsApp (hash)</label>
                  <input v-model="form.whatsapp_phone_hash" type="text" placeholder="Hash del número"
                         class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div class="flex items-end pb-2">
                  <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <div class="relative w-9 h-5 rounded-full transition-colors" :class="form.whatsapp_verificat ? 'bg-green-500' : 'bg-gray-300'"
                         @click="form.whatsapp_verificat = !form.whatsapp_verificat">
                      <div class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-all"
                           :class="form.whatsapp_verificat ? 'left-4' : 'left-0.5'" />
                    </div>
                    <span class="text-sm text-gray-700">Verificat</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Error general -->
            <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">{{ formError }}</div>

            <div class="flex items-center justify-end gap-2 pt-1 border-t">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel·lar
              </button>
              <button type="submit" :disabled="saving"
                      class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-60 flex items-center gap-2 transition-colors">
                <svg v-if="saving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                {{ saving ? 'Desant...' : (modal.isEdit ? 'Desar canvis' : 'Crear empleat') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- ── Modal veure detalls ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="viewTarget" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="viewTarget = null">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
          <div class="flex items-center justify-between px-6 py-4 border-b flex-shrink-0">
            <h3 class="font-medium text-gray-900">Detalls de l'empleat</h3>
            <button @click="viewTarget = null" class="text-gray-400 hover:text-gray-600"><IconX class="w-5 h-5" /></button>
          </div>

          <div class="overflow-y-auto flex-1 px-6 py-5 space-y-4">
            <!-- Capçalera -->
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold text-white flex-shrink-0"
                   :style="{ backgroundColor: avatarColor(viewTarget.nom + viewTarget.cognoms) }">
                {{ (viewTarget.nom[0] || '').toUpperCase() }}{{ (viewTarget.cognoms[0] || '').toUpperCase() }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ viewTarget.nom }} {{ viewTarget.cognoms }}</p>
                <p v-if="viewTarget.email" class="text-sm text-gray-500">{{ viewTarget.email }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span v-if="viewTarget.user" :class="roleBadgeClass(viewTarget.user.role)" class="text-[10px] font-medium px-2 py-0.5 rounded-full">
                    {{ roleLabel(viewTarget.user.role) }}
                  </span>
                  <span v-if="viewTarget.actiu" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-green-50 text-green-700">Actiu</span>
                  <span v-else class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Baixa</span>
                </div>
              </div>
            </div>

            <!-- Identitat -->
            <div class="border-t pt-4 space-y-2.5">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Identitat</p>
              <div v-if="viewTarget.dni_nie" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">DNI / NIE</span>
                <span class="text-gray-800">{{ viewTarget.dni_nie }}</span>
              </div>
              <div v-if="viewTarget.nss" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Núm. SS</span>
                <span class="text-gray-800">{{ viewTarget.nss }}</span>
              </div>
              <div v-if="viewTarget.data_naixement" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Data naixement</span>
                <span class="text-gray-800">{{ formatDate(viewTarget.data_naixement) }}</span>
              </div>
            </div>

            <!-- Contacte -->
            <div class="border-t pt-4 space-y-2.5">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Contacte</p>
              <div v-if="viewTarget.email" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Email</span>
                <a :href="`mailto:${viewTarget.email}`" class="text-blue-600 hover:underline">{{ viewTarget.email }}</a>
              </div>
              <div v-if="viewTarget.telefon" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Telèfon</span>
                <span class="text-gray-800">{{ viewTarget.telefon }}</span>
              </div>
            </div>

            <!-- Laboral -->
            <div class="border-t pt-4 space-y-2.5">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Laboral</p>
              <div v-if="viewTarget.company" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Empresa</span>
                <span class="text-gray-800">{{ viewTarget.company.name }}</span>
              </div>
              <div v-if="viewTarget.department" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Departament</span>
                <span class="text-gray-800">{{ viewTarget.department.name }}</span>
              </div>
              <div v-if="viewTarget.shift" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Torn</span>
                <span class="flex items-center gap-1.5">
                  <span class="w-2 h-2 rounded-full inline-block" :style="{ backgroundColor: viewTarget.shift.color || '#94a3b8' }" />
                  <span class="text-gray-800">{{ viewTarget.shift.name }}</span>
                </span>
              </div>
              <div v-if="viewTarget.conveni" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Conveni</span>
                <span class="text-gray-800">
                  {{ viewTarget.conveni.name }}
                  <span class="text-gray-400 text-xs ml-1">
                    ({{ viewTarget.conveni.weekly_hours }}h/set. · {{ viewTarget.conveni.vacation_days }}d vac. · {{ viewTarget.conveni.personal_days }}d pers.)
                  </span>
                </span>
              </div>
              <div v-if="viewTarget.data_alta" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Data alta</span>
                <span class="text-gray-800">{{ formatDate(viewTarget.data_alta) }}</span>
              </div>
              <div v-if="viewTarget.data_baixa" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Data baixa</span>
                <span class="text-gray-800">{{ formatDate(viewTarget.data_baixa) }}</span>
              </div>
              <div v-if="viewTarget.percentatge_jornada != null" class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">% Jornada</span>
                <span class="text-gray-800">{{ viewTarget.percentatge_jornada }}%</span>
              </div>
              <div class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Geoloc. requerida</span>
                <span class="text-gray-800">{{ viewTarget.geoloc_requerida ? 'Sí' : 'No' }}</span>
              </div>
            </div>

            <!-- WhatsApp -->
            <div v-if="viewTarget.whatsapp_phone_hash" class="border-t pt-4 space-y-2.5">
              <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">WhatsApp</p>
              <div class="flex gap-3 text-sm">
                <span class="w-40 text-gray-400 flex-shrink-0">Verificat</span>
                <span class="text-gray-800">{{ viewTarget.whatsapp_verificat ? 'Sí' : 'No' }}</span>
              </div>
            </div>
          </div>
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
              <p class="font-medium text-gray-900">Eliminar empleat</p>
              <p class="text-sm text-gray-500 mt-1">
                Estàs a punt d'eliminar <strong class="text-gray-800">{{ deleteTarget.nom }} {{ deleteTarget.cognoms }}</strong>.
                Totes les dades s'eliminaran permanentment.
              </p>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button @click="deleteTarget = null" class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Cancel·lar</button>
            <button @click="confirmDelete" :disabled="deleting" class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg disabled:opacity-60 transition-colors">
              {{ deleting ? 'Eliminant...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { IconPlus, IconEdit, IconTrash, IconX, IconEye, IconUsers, IconAlertTriangle, IconMail, IconCheck } from '@tabler/icons-vue'
import { useEmployees } from '../composables/useEmployees'
import { useDepartments } from '../composables/useDepartments'
import { useShifts } from '../composables/useShifts'
import api from '../services/api'

const { employees, loading, saving, error, pagination, load, create, update, remove, sendInvitation } = useEmployees()
const { loadAll: loadAllDepartments } = useDepartments()
const { loadAll: loadAllShifts }      = useShifts()

const allDepartments = ref([])
const allShifts      = ref([])
const allConvenis    = ref([])

// ── Helpers ───────────────────────────────────────────────────────────────────
const AVATAR_COLORS = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316']
function avatarColor(name) {
  let h = 0
  for (const c of (name || '')) h = (h * 31 + c.charCodeAt(0)) & 0xffffffff
  return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]
}

function roleLabel(role) {
  const map = { founder: 'Founder', superadmin: 'Superadmin', admin: 'Admin', hr: 'RRHH', user: 'Usuari' }
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

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('ca-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

// ── Formulari ─────────────────────────────────────────────────────────────────
const modal      = reactive({ open: false, isEdit: false, editId: null })
const formErrors = ref({})
const formError  = ref('')
const form = reactive({
  department_id: null,
  conveni_id: null,
  torn_id: null,
  nom: '', cognoms: '', dni_nie: '', nss: '', data_naixement: '',
  email: '', telefon: '',
  data_alta: '', data_baixa: '', percentatge_jornada: null,
  actiu: true, geoloc_requerida: false,
  whatsapp_phone_hash: '', whatsapp_verificat: false,
})

function resetForm() {
  Object.assign(form, {
    department_id: null,
    conveni_id: null,
    torn_id: null,
    nom: '', cognoms: '', dni_nie: '', nss: '', data_naixement: '',
    email: '', telefon: '',
    data_alta: '', data_baixa: '', percentatge_jornada: null,
    actiu: true, geoloc_requerida: false,
    whatsapp_phone_hash: '', whatsapp_verificat: false,
  })
  formErrors.value = {}
  formError.value  = ''
}

function openCreate() {
  resetForm()
  Object.assign(modal, { open: true, isEdit: false, editId: null })
}

function openEdit(e) {
  resetForm()
  Object.assign(form, {
    department_id:       e.department_id       || null,
    conveni_id:          e.conveni_id          || null,
    torn_id:             e.torn_id             || null,
    nom:                 e.nom                 || '',
    cognoms:             e.cognoms             || '',
    dni_nie:             e.dni_nie             || '',
    nss:                 e.nss                 || '',
    data_naixement:      e.data_naixement       ? e.data_naixement.substring(0, 10) : '',
    email:               e.email               || '',
    telefon:             e.telefon             || '',
    data_alta:           e.data_alta            ? e.data_alta.substring(0, 10) : '',
    data_baixa:          e.data_baixa           ? e.data_baixa.substring(0, 10) : '',
    percentatge_jornada: e.percentatge_jornada  != null ? e.percentatge_jornada : null,
    actiu:               e.actiu               ?? true,
    geoloc_requerida:    e.geoloc_requerida    ?? false,
    whatsapp_phone_hash: e.whatsapp_phone_hash  || '',
    whatsapp_verificat:  e.whatsapp_verificat  ?? false,
  })
  Object.assign(modal, { open: true, isEdit: true, editId: e.id })
}

function closeModal() { modal.open = false }

async function submitModal() {
  formErrors.value = {}
  formError.value  = ''
  const payload = {
    department_id:       form.department_id || null,
    conveni_id:          form.conveni_id    || null,
    torn_id:             form.torn_id       || null,
    nom:                 form.nom.trim(),
    cognoms:             form.cognoms.trim(),
    dni_nie:             form.dni_nie            || null,
    nss:                 form.nss               || null,
    data_naixement:      form.data_naixement     || null,
    email:               form.email             || null,
    telefon:             form.telefon           || null,
    data_alta:           form.data_alta         || null,
    data_baixa:          form.data_baixa        || null,
    percentatge_jornada: form.percentatge_jornada,
    actiu:               form.actiu,
    geoloc_requerida:    form.geoloc_requerida,
    whatsapp_phone_hash: form.whatsapp_phone_hash || null,
    whatsapp_verificat:  form.whatsapp_verificat,
  }
  const result = modal.isEdit
    ? await update(modal.editId, payload)
    : await create(payload)
  if (result.ok) { closeModal(); load(pagination.value.current_page) }
  else { formErrors.value = result.errors || {}; formError.value = result.message || 'Error en desar.' }
}

// ── Enviar invitació ──────────────────────────────────────────────────────────
const sendingInvitationId = ref(null)
const sentInvitations     = ref({})
const invitationError     = ref('')

async function doSendInvitation(e) {
  if (sendingInvitationId.value) return
  invitationError.value    = ''
  sendingInvitationId.value = e.id
  const result = await sendInvitation(e.id)
  sendingInvitationId.value = null
  if (result.ok) {
    sentInvitations.value = { ...sentInvitations.value, [e.id]: true }
    setTimeout(() => {
      const copy = { ...sentInvitations.value }
      delete copy[e.id]
      sentInvitations.value = copy
    }, 3000)
  } else {
    invitationError.value = result.message || "No s'ha pogut enviar el correu"
    setTimeout(() => { invitationError.value = '' }, 5000)
  }
}

// ── Veure / Eliminar ──────────────────────────────────────────────────────────
const viewTarget   = ref(null)
const deleteTarget = ref(null)
const deleting     = ref(false)

function askDelete(e) {
  if (e.user?.role === 'admin') return
  deleteTarget.value = e
}

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

async function loadAllConvenis() {
  try {
    const res = await api.get('/v1/convenis')
    return res.data.data || []
  } catch { return [] }
}

onMounted(async () => {
  load()
  ;[allDepartments.value, allShifts.value, allConvenis.value] = await Promise.all([
    loadAllDepartments(),
    loadAllShifts(),
    loadAllConvenis(),
  ])
})
</script>
