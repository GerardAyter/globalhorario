<template>
  <div>
    <!-- ── Capçalera ──────────────────────────────────────────────────────────── -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-base font-medium text-gray-900">Calendari</h2>
        <p class="text-sm text-gray-400 mt-0.5">Vacances, absències i dies personals</p>
      </div>
      <button @click="openModal()"
              class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-colors">
        <IconPlus class="w-4 h-4" />Nova sol·licitud
      </button>
    </div>

    <!-- ── Comptadors de balanç ──────────────────────────────────────────────── -->
    <div v-if="balance" class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
      <!-- Vacances disponibles -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Vacances disponibles</p>
        <p class="text-2xl font-bold text-blue-600">{{ balance.vacation.available }}<span class="text-sm font-normal text-gray-400 ml-1">dies</span></p>
        <div class="mt-2 space-y-0.5 text-xs text-gray-400">
          <p>{{ balance.vacation.generated }} generats · {{ balance.vacation.carried }} traspassats</p>
          <p v-if="balance.vacation.extra > 0" class="text-green-600 font-medium">+{{ balance.vacation.extra }} dies extra</p>
        </div>
      </div>

      <!-- Vacances gaudides -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Vacances gaudides</p>
        <p class="text-2xl font-bold text-gray-700">{{ balance.vacation.taken }}<span class="text-sm font-normal text-gray-400 ml-1">dies</span></p>
        <div class="mt-2 text-xs text-gray-400">
          <p v-if="balance.vacation.pending > 0" class="text-amber-600">{{ balance.vacation.pending }} pendents d'aprovació</p>
          <p v-else>de {{ balance.vacation.total }} totals</p>
        </div>
        <!-- Barra de progrés -->
        <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full bg-blue-400 rounded-full transition-all"
               :style="{ width: balance.vacation.total > 0 ? `${Math.min(100, (balance.vacation.taken / balance.vacation.total) * 100)}%` : '0%' }" />
        </div>
      </div>

      <!-- Dies personals disponibles -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dies personals disp.</p>
        <p class="text-2xl font-bold text-purple-600">{{ balance.personal.available }}<span class="text-sm font-normal text-gray-400 ml-1">dies</span></p>
        <div class="mt-2 text-xs text-gray-400">
          <p v-if="balance.personal.pending > 0" class="text-amber-600">{{ balance.personal.pending }} pendent</p>
          <p v-else>de {{ balance.personal.total }} totals</p>
        </div>
      </div>

      <!-- Dies personals gaudits -->
      <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-1">Dies personals gaudits</p>
        <p class="text-2xl font-bold text-gray-700">{{ balance.personal.taken }}<span class="text-sm font-normal text-gray-400 ml-1">dies</span></p>
        <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full bg-purple-400 rounded-full transition-all"
               :style="{ width: balance.personal.total > 0 ? `${Math.min(100, (balance.personal.taken / balance.personal.total) * 100)}%` : '0%' }" />
        </div>
      </div>
    </div>
    <!-- Skeleton balanç -->
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
      <div v-for="i in 4" :key="i" class="bg-white border border-gray-200 rounded-xl p-4">
        <div class="h-3 w-24 bg-gray-100 animate-pulse rounded mb-2" />
        <div class="h-7 w-16 bg-gray-100 animate-pulse rounded" />
      </div>
    </div>

    <!-- ── Cos principal: calendari + llista ──────────────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

      <!-- Calendari (2/3) -->
      <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <!-- Navegació mes -->
        <div class="px-5 py-3 border-b flex items-center justify-between">
          <button @click="prevMonth" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition-colors">
            <IconChevronLeft class="w-4 h-4 text-gray-500" />
          </button>
          <h3 class="text-sm font-medium text-gray-900 capitalize">{{ monthLabel }}</h3>
          <button @click="nextMonth" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition-colors">
            <IconChevronRight class="w-4 h-4 text-gray-500" />
          </button>
        </div>

        <!-- Capçalera dies -->
        <div class="grid grid-cols-7 border-b bg-gray-50">
          <div v-for="d in ['Dl','Dt','Dc','Dj','Dv','Ds','Dg']" :key="d"
               class="py-2 text-center text-[10px] font-medium text-gray-400 uppercase">{{ d }}</div>
        </div>

        <!-- Quadrícula dies -->
        <div class="grid grid-cols-7">
          <div v-for="(cell, i) in calendarCells" :key="i"
               class="min-h-[72px] border-b border-r border-gray-100 p-1 relative"
               :class="cell.isToday
                 ? 'bg-blue-50'
                 : cell.isHoliday
                   ? 'bg-red-50'
                   : cell.isWeekend
                     ? 'bg-slate-50'
                     : cell.currentMonth ? 'bg-white' : 'bg-gray-50/60'">
            <!-- Número dia -->
            <div class="flex justify-end mb-1">
              <span class="w-6 h-6 flex items-center justify-center text-xs rounded-full"
                    :class="cell.isToday
                      ? 'bg-blue-600 text-white font-semibold'
                      : cell.isWeekend && cell.currentMonth
                        ? 'text-slate-400 font-medium'
                        : cell.currentMonth ? 'text-gray-700' : 'text-gray-300'">
                {{ cell.day }}
              </span>
            </div>
            <!-- Festius del dia -->
            <div class="space-y-0.5">
              <div v-for="h in cell.holidays" :key="`h-${h.id}`"
                   class="text-[9px] font-semibold px-1 py-0.5 rounded truncate leading-tight text-white"
                   :style="{ backgroundColor: h.color || '#EF4444' }"
                   :title="`${h.name} (${TYPE_LABELS[h.type] || h.type})`">
                🏖 {{ h.name }}
              </div>
            </div>
            <!-- Events d'absències -->
            <div class="space-y-0.5">
              <div v-for="ev in cell.events" :key="ev.id"
                   class="text-[9px] font-medium px-1 py-0.5 rounded truncate leading-tight"
                   :class="[eventClass(ev), ev.isOwn ? 'ring-1 ring-inset ring-current/30' : '']"
                   :title="isHrPlus && ev.firstName ? `${ev.firstName} · ${ev.label} · ${ev.statusLabel}` : `${ev.label} · ${ev.statusLabel}`">
                <template v-if="isHrPlus && ev.firstName">
                  <span class="opacity-75">{{ ev.firstName }}</span> · {{ ev.label }}
                </template>
                <template v-else>{{ ev.label }}</template>
              </div>
            </div>
          </div>
        </div>

        <!-- Llegenda -->
        <div class="px-5 py-3 border-t bg-gray-50 flex items-center gap-4 flex-wrap">
          <div v-for="type in usedTypes" :key="type.id" class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-sm" :style="{ backgroundColor: type.calendar_color || '#94a3b8' }" />
            <span class="text-xs text-gray-500">{{ type.name }}</span>
          </div>
          <div class="flex items-center gap-3 ml-auto text-xs text-gray-400">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-slate-100 border border-slate-300" />Cap de setmana</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-100 border border-red-300" />Festiu</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-amber-200 border border-amber-400" />Pendent</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-blue-200 border border-blue-400" />Aprovat</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-gray-200 border border-gray-400" />Denegat</span>
          </div>
        </div>
      </div>

      <!-- Llista sol·licituds (1/3) -->
      <div class="bg-white border border-gray-200 rounded-xl overflow-hidden flex flex-col">
        <div class="px-4 py-3 border-b">
          <h3 class="text-sm font-medium text-gray-900">Les meves sol·licituds</h3>
        </div>

        <div v-if="loading" class="p-4 space-y-3">
          <div v-for="i in 3" :key="i" class="h-16 bg-gray-100 animate-pulse rounded-xl" />
        </div>

        <div v-else-if="myRequests.length === 0"
             class="flex-1 flex flex-col items-center justify-center py-10 text-center px-4">
          <IconCalendarOff class="w-8 h-8 text-gray-200 mb-2" />
          <p class="text-sm text-gray-400">Sense sol·licituds</p>
          <p class="text-xs text-gray-300 mt-1">Crea-ne una amb el botó superior</p>
        </div>

        <div v-else class="divide-y divide-gray-100 overflow-y-auto flex-1">
          <div v-for="req in myRequests" :key="req.id" class="px-4 py-3 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between gap-2 mb-1">
              <div class="flex items-center gap-1.5 min-w-0">
                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                      :style="{ backgroundColor: req.type?.calendar_color || '#94a3b8' }" />
                <span class="text-xs font-medium text-gray-900 truncate">{{ req.type?.name }}</span>
              </div>
              <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full flex-shrink-0" :class="statusBadge(req.status)">
                {{ statusLabel(req.status) }}
              </span>
            </div>
            <p class="text-xs text-gray-500 pl-4">
              {{ formatDate(req.start_date) }}
              <template v-if="req.start_date !== req.end_date"> → {{ formatDate(req.end_date) }}</template>
              · {{ req.working_days }} {{ req.working_days === 1 ? 'dia' : 'dies' }}
            </p>
            <p v-if="req.manager_comment" class="text-xs text-gray-400 pl-4 mt-0.5 italic">
              "{{ req.manager_comment }}"
            </p>
            <!-- Cancel·lar (només pendents) -->
            <button v-if="req.status === 'pending'"
                    @click="doCancel(req.id)"
                    :disabled="acting"
                    class="mt-1.5 ml-4 text-[10px] text-red-500 hover:underline disabled:opacity-40">
              Cancel·lar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Panell admin: sol·licituds pendents (HR+) ───────────────────────── -->
    <div v-if="isHrPlus" class="mt-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-5 py-3 border-b flex items-center justify-between">
        <div class="flex items-center gap-3">
          <h3 class="text-sm font-medium text-gray-900">Sol·licituds de l'empresa</h3>
          <span v-if="pendingCount > 0"
                class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
            {{ pendingCount }} pendents
          </span>
        </div>
        <div class="flex gap-1">
          <button v-for="f in statusFilters" :key="f.value"
                  @click="companyFilter = f.value"
                  class="text-xs px-3 py-1 rounded-lg transition-colors"
                  :class="companyFilter === f.value
                    ? 'bg-gray-900 text-white'
                    : 'text-gray-500 hover:bg-gray-100'">
            {{ f.label }}
          </button>
        </div>
      </div>

      <div v-if="companyLoading" class="divide-y divide-gray-100">
        <div v-for="i in 3" :key="i" class="px-5 py-4 flex items-center gap-4">
          <div class="w-8 h-8 bg-gray-100 animate-pulse rounded-full" />
          <div class="flex-1 h-4 bg-gray-100 animate-pulse rounded" />
          <div class="w-24 h-6 bg-gray-100 animate-pulse rounded" />
        </div>
      </div>

      <div v-else-if="filteredCompanyRequests.length === 0"
           class="py-8 text-center text-sm text-gray-400">
        Sense sol·licituds {{ companyFilter !== 'all' ? statusLabel(companyFilter).toLowerCase() + 's' : '' }}
      </div>

      <div v-else class="divide-y divide-gray-100">
        <div v-for="req in filteredCompanyRequests" :key="req.id"
             :id="`req-${req.id}`"
             class="px-5 py-3 flex items-center gap-4 transition-colors"
             :class="highlightedRequestId === req.id ? 'bg-blue-50 ring-2 ring-blue-300 ring-inset' : 'hover:bg-gray-50'">

          <!-- Avatar -->
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white flex-shrink-0"
               :style="{ backgroundColor: avatarColor(req.employee?.nom + req.employee?.cognoms) }">
            {{ (req.employee?.nom?.[0] || '').toUpperCase() }}{{ (req.employee?.cognoms?.[0] || '').toUpperCase() }}
          </div>

          <!-- Empleat + info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ req.employee?.nom }} {{ req.employee?.cognoms }}
              </p>
              <span class="text-xs text-gray-400 truncate">{{ req.employee?.department?.name }}</span>
            </div>
            <div class="flex items-center gap-2 mt-0.5">
              <span class="w-2 h-2 rounded-full flex-shrink-0"
                    :style="{ backgroundColor: req.type?.calendar_color || '#94a3b8' }" />
              <p class="text-xs text-gray-500">
                {{ req.type?.name }} · {{ formatDate(req.start_date) }}
                <template v-if="req.start_date !== req.end_date"> → {{ formatDate(req.end_date) }}</template>
                · <strong>{{ req.working_days }} dies</strong>
              </p>
            </div>
            <p v-if="req.employee_comment" class="text-xs text-gray-400 mt-0.5 italic">"{{ req.employee_comment }}"</p>
          </div>

          <!-- Estat / Accions -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <template v-if="req.status === 'pending'">
              <button @click="doApprove(req.id)" :disabled="acting"
                      class="text-xs bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-lg transition-colors disabled:opacity-40 font-medium">
                Aprovar
              </button>
              <button @click="openDenyModal(req)" :disabled="acting"
                      class="text-xs bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-3 py-1.5 rounded-lg transition-colors disabled:opacity-40 font-medium">
                Denegar
              </button>
            </template>
            <span v-else class="text-[10px] font-medium px-2 py-0.5 rounded-full" :class="statusBadge(req.status)">
              {{ statusLabel(req.status) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Panell gestió de festius (Admin+) ─────────────────────────────────── -->
    <div v-if="isAdmin" class="mt-4 bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-5 py-3 border-b flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-900">Festius de l'empresa</h3>
        <button @click="openHolidayForm"
                class="flex items-center gap-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1.5 rounded-lg transition-colors">
          <IconPlus class="w-3.5 h-3.5" />Afegir festiu
        </button>
      </div>

      <!-- Llista de festius -->
      <div v-if="holidays.length === 0"
           class="py-8 text-center text-sm text-gray-400">
        Sense festius per a l'any {{ viewYear }}
      </div>
      <div v-else class="divide-y divide-gray-100">
        <div v-for="h in holidays" :key="h.id"
             class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50 transition-colors">
          <span class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: h.color }" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">{{ h.name }}</p>
            <p class="text-xs text-gray-400">
              {{ new Date(h.date).toLocaleDateString('ca-ES', { day: 'numeric', month: 'long' }) }}
              <template v-if="h.recurring"> · Recurrent</template>
              · <span class="capitalize">{{ TYPE_LABELS[h.type] || h.type }}</span>
            </p>
          </div>
          <button @click="doRemoveHoliday(h.id)" :disabled="holidayActing"
                  class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors disabled:opacity-40">
            <IconTrash class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- ── Modal nova sol·licitud ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showModal"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
           @click.self="showModal = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
          <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Nova sol·licitud</h3>
            <button @click="showModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
              <IconX class="w-4 h-4 text-gray-500" />
            </button>
          </div>

          <form @submit.prevent="doCreate" class="px-6 py-5 space-y-4">
            <!-- Tipus -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1.5">Tipus d'absència</label>
              <div class="grid grid-cols-2 gap-2">
                <button v-for="t in absenceTypes" :key="t.id" type="button"
                        @click="form.absence_type_id = t.id"
                        class="flex items-center gap-2 border rounded-xl px-3 py-2.5 text-left transition-colors text-sm"
                        :class="form.absence_type_id === t.id
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-200 hover:border-gray-300 text-gray-700'">
                  <span class="w-3 h-3 rounded-full flex-shrink-0"
                        :style="{ backgroundColor: t.calendar_color || '#94a3b8' }" />
                  <span class="truncate">{{ t.name }}</span>
                </button>
              </div>
              <p v-if="absenceTypes.length === 0" class="text-xs text-gray-400 mt-1">
                L'empresa no té tipus d'absència configurats.
              </p>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Inici</label>
                <input v-model="form.start_date" type="date" required
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Fi</label>
                <input v-model="form.end_date" type="date" required :min="form.start_date"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
            </div>

            <!-- Mig dia -->
            <div class="flex gap-4">
              <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input v-model="form.half_day_start" type="checkbox" class="rounded" />
                Mig dia inicial
              </label>
              <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input v-model="form.half_day_end" type="checkbox" class="rounded" />
                Mig dia final
              </label>
            </div>

            <!-- Resum dies laborables -->
            <div v-if="form.start_date && form.end_date"
                 class="bg-blue-50 rounded-xl px-4 py-3 flex items-center justify-between">
              <span class="text-sm text-blue-700">Dies laborables sol·licitats</span>
              <span class="font-bold text-blue-800 text-lg">{{ previewWorkingDays }}</span>
            </div>

            <!-- Comentari -->
            <div>
              <label class="block text-xs font-medium mb-1.5"
                     :class="selectedType?.requires_comment ? 'text-gray-900' : 'text-gray-700'">
                Comentari
                <span v-if="selectedType?.requires_comment" class="text-red-500 ml-0.5">*</span>
                <span v-else class="text-gray-400 font-normal ml-1">(opcional)</span>
              </label>
              <textarea v-model="form.employee_comment" rows="2" maxlength="1000"
                        :required="selectedType?.requires_comment"
                        :placeholder="selectedType?.requires_comment
                          ? 'Descriu el motiu de la sol·licitud...'
                          : 'Motiu o informació addicional...'"
                        class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none resize-none transition-colors"
                        :class="selectedType?.requires_comment
                          ? 'border-red-200 focus:ring-2 focus:ring-red-400'
                          : 'border-gray-200 focus:ring-2 focus:ring-blue-500'" />
              <p v-if="selectedType?.requires_comment" class="text-[10px] text-red-500 mt-1">
                Aquest tipus d'absència requereix un comentari obligatòriament.
              </p>
            </div>

            <p v-if="error" class="text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ error }}</p>

            <div class="flex gap-3 pt-1">
              <button type="button" @click="showModal = false"
                      class="flex-1 border border-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                Cancel·lar
              </button>
              <button type="submit" :disabled="acting || !form.absence_type_id"
                      class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
                <span v-if="acting">Enviant...</span>
                <span v-else>Sol·licitar</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal nou festiu -->
      <div v-if="showHolidayForm"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
           @click.self="showHolidayForm = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
          <!-- Capçalera -->
          <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Nou festiu</h3>
            <button @click="showHolidayForm = false"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
              <IconX class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="doCreateHoliday" class="px-6 py-5 space-y-4">
            <!-- Nom -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
              <input v-model="holidayForm.name" type="text" placeholder="Ej: Nadal, Festa Major..."
                     class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <!-- Data -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Data *</label>
              <input v-model="holidayForm.date" type="date"
                     class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <!-- Tipus -->
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-2">Tipus</label>
              <div class="grid grid-cols-3 gap-2">
                <button v-for="o in holidayTypeOptions" :key="o.value" type="button"
                        @click="holidayForm.type = o.value; holidayForm.color = o.color"
                        class="flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 transition-all text-sm font-medium"
                        :class="holidayForm.type === o.value
                          ? 'border-current text-white'
                          : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                        :style="holidayForm.type === o.value ? { backgroundColor: o.color, borderColor: o.color } : {}">
                  <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: o.color }" />
                  {{ o.label }}
                </button>
              </div>
            </div>

            <!-- Recurrent -->
            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
              <input v-model="holidayForm.recurring" type="checkbox"
                     class="w-4 h-4 rounded text-blue-600" />
              <div>
                <p class="text-sm font-medium text-gray-900">Recurrent cada any</p>
                <p class="text-xs text-gray-400">El festiu es repetirà cada any en la mateixa data</p>
              </div>
            </label>

            <p v-if="holidayError" class="text-xs text-red-600 bg-red-50 rounded-lg px-3 py-2">{{ holidayError }}</p>

            <!-- Botons -->
            <div class="flex gap-3 pt-1">
              <button type="button" @click="showHolidayForm = false"
                      class="flex-1 border border-gray-200 text-gray-700 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                Cancel·lar
              </button>
              <button type="submit" :disabled="holidayActing"
                      class="flex-1 text-white text-sm font-medium py-2.5 rounded-xl transition-colors disabled:opacity-50"
                      :style="{ backgroundColor: holidayForm.color || '#EF4444' }">
                <span v-if="holidayActing">Desant...</span>
                <span v-else>Desar festiu</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal denegar -->
      <div v-if="denyTarget"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
           @click.self="denyTarget = null">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
          <h3 class="font-semibold text-gray-900 mb-1">Denegar sol·licitud</h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ denyTarget.employee?.nom }} {{ denyTarget.employee?.cognoms }} —
            {{ denyTarget.type?.name }} ({{ denyTarget.working_days }} dies)
          </p>
          <textarea v-model="denyComment" rows="3" placeholder="Motiu de la denegació *"
                    class="w-full border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 resize-none mb-1"
                    :class="denyError ? 'border-red-400 focus:ring-red-400' : 'border-gray-200 focus:ring-red-400'" />
          <p v-if="denyError" class="text-xs text-red-600 mb-3">{{ denyError }}</p>
          <div v-else class="mb-4" />
          <div class="flex gap-3">
            <button @click="denyTarget = null"
                    class="flex-1 border border-gray-200 text-gray-700 text-sm font-medium py-2 rounded-xl hover:bg-gray-50">
              Cancel·lar
            </button>
            <button @click="doDeny" :disabled="acting"
                    class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white text-sm font-medium py-2 rounded-xl">
              Denegar
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  IconPlus, IconChevronLeft, IconChevronRight,
  IconCalendarOff, IconX, IconTrash,
} from '@tabler/icons-vue'
import { useAuthStore } from '../stores/auth'
import { useAbsences }  from '../composables/useAbsences'
import { useHolidays }  from '../composables/useHolidays'

const route = useRoute()
const auth  = useAuthStore()
const ROLE_HIERARCHY = { user: 0, hr: 1, admin: 2, superadmin: 3, founder: 4 }
const isHrPlus = computed(() => (ROLE_HIERARCHY[auth.user?.role] ?? -1) >= ROLE_HIERARCHY.hr)

const {
  myRequests, companyRequests, balance, absenceTypes,
  loading, acting, error,
  loadMyRequests, loadBalance, loadAbsenceTypes, loadCompanyRequests,
  createRequest, cancelRequest, approveRequest, denyRequest,
} = useAbsences()

const isAdmin = computed(() => (ROLE_HIERARCHY[auth.user?.role] ?? -1) >= ROLE_HIERARCHY.admin)

const {
  holidays, acting: holidayActing,
  load: loadHolidays, create: createHoliday, remove: removeHoliday,
  TYPE_LABELS, holidaysForDate, defaultColor,
} = useHolidays()

// ── Calendari ─────────────────────────────────────────────────────────────────
const viewYear  = ref(new Date().getFullYear())
const viewMonth = ref(new Date().getMonth()) // 0-indexed

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1)
    .toLocaleDateString('ca-ES', { month: 'long', year: 'numeric' })
)

function prevMonth() {
  if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value-- }
  else viewMonth.value--
}
function nextMonth() {
  if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++ }
  else viewMonth.value++
}

// Converteix un Date local a 'YYYY-MM-DD' sense passar per UTC
function localDateStr(d) {
  return d.getFullYear() + '-' +
    String(d.getMonth() + 1).padStart(2, '0') + '-' +
    String(d.getDate()).padStart(2, '0')
}
// Parseja un string 'YYYY-MM-DD' (o ISO) com a mitjanit LOCAL
function localMidnight(str) {
  return new Date(String(str).slice(0, 10) + 'T00:00:00')
}

const calendarCells = computed(() => {
  const year  = viewYear.value
  const month = viewMonth.value
  const today = new Date()

  const firstDay = new Date(year, month, 1)
  const lastDay  = new Date(year, month + 1, 0)

  // Dia de la setmana del primer dia (0=Dg → convertim a 0=Dl)
  const startDow = (firstDay.getDay() + 6) % 7
  const cells = []

  const isWeekend = (d) => { const dow = d.getDay(); return dow === 0 || dow === 6 }

  // Dies del mes anterior
  for (let i = startDow - 1; i >= 0; i--) {
    const d = new Date(year, month, -i)
    cells.push({ day: d.getDate(), currentMonth: false, isToday: false, isWeekend: isWeekend(d), date: d, events: [] })
  }

  // Dies del mes actual
  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(year, month, d)
    const isToday = date.toDateString() === today.toDateString()
    cells.push({ day: d, currentMonth: true, isToday, isWeekend: isWeekend(date), date, events: [] })
  }

  // Dies del mes següent per completar la quadrícula
  const remaining = (7 - (cells.length % 7)) % 7
  for (let i = 1; i <= remaining; i++) {
    const d = new Date(year, month + 1, i)
    cells.push({ day: d.getDate(), currentMonth: false, isToday: false, isWeekend: isWeekend(d), date: d, events: [] })
  }

  // Afegir festius per cel·la
  for (const cell of cells) {
    const dateStr = localDateStr(cell.date)
    cell.holidays = holidaysForDate(dateStr)
    if (cell.holidays.length) cell.isHoliday = true
  }

  // Afegir events de sol·licituds
  const allReqs = isHrPlus.value
    ? [...myRequests.value, ...companyRequests.value.filter(r => r.user_id !== auth.user?.id)]
    : myRequests.value

  const myId = auth.user?.id
  for (const req of allReqs) {
    if (req.status === 'cancelled') continue
    const start  = localMidnight(req.start_date)
    const end    = localMidnight(req.end_date)
    const label  = req.type?.name || 'Absència'
    const isOwn  = req.user_id === myId
    const firstName = req.employee?.nom ?? null
    for (const cell of cells) {
      if (cell.date >= start && cell.date <= end) {
        cell.events.push({
          id: req.id, label, status: req.status,
          color: req.type?.calendar_color,
          statusLabel: statusLabel(req.status),
          isOwn, firstName,
        })
      }
    }
  }

  return cells
})

// ── Tipus usats al mes visible (per a la llegenda) ────────────────────────────
const usedTypes = computed(() => {
  const ids = new Set(calendarCells.value.flatMap(c => c.events.map(e => e.id)))
  const typeMap = new Map()
  for (const req of [...myRequests.value, ...companyRequests.value]) {
    if (ids.has(req.id) && req.type && !typeMap.has(req.type.id)) {
      typeMap.set(req.type.id, req.type)
    }
  }
  return [...typeMap.values()]
})

function eventClass(ev) {
  if (ev.status === 'approved') return 'bg-blue-100 text-blue-800 border border-blue-200'
  if (ev.status === 'pending')  return 'bg-amber-100 text-amber-800 border border-amber-200'
  return 'bg-gray-100 text-gray-500 border border-gray-200 line-through'
}

// ── Panell admin ──────────────────────────────────────────────────────────────
const companyFilter  = ref('pending')
const companyLoading = ref(false)

const statusFilters = [
  { value: 'pending',  label: 'Pendents' },
  { value: 'approved', label: 'Aprovades' },
  { value: 'denied',   label: 'Denegades' },
  { value: 'all',      label: 'Totes' },
]

const filteredCompanyRequests = computed(() =>
  companyFilter.value === 'all'
    ? companyRequests.value
    : companyRequests.value.filter(r => r.status === companyFilter.value)
)

const pendingCount = computed(() =>
  companyRequests.value.filter(r => r.status === 'pending').length
)

// ── Modal nova sol·licitud ────────────────────────────────────────────────────
const showModal = ref(false)
const form = ref(resetForm())

function resetForm() {
  return { absence_type_id: null, start_date: '', end_date: '', half_day_start: false, half_day_end: false, employee_comment: '' }
}

const selectedType = computed(() =>
  absenceTypes.value.find(t => t.id === form.value.absence_type_id) ?? null
)

function openModal() {
  form.value = resetForm()
  showModal.value = true
}

const previewWorkingDays = computed(() => {
  if (!form.value.start_date || !form.value.end_date) return 0
  let days = 0
  const cur = new Date(form.value.start_date)
  const end = new Date(form.value.end_date)
  if (end < cur) return 0
  while (cur <= end) {
    const dow     = cur.getDay()
    if (dow !== 0 && dow !== 6 && holidaysForDate(localDateStr(cur)).length === 0) days++
    cur.setDate(cur.getDate() + 1)
  }
  if (form.value.half_day_start) days -= 0.5
  if (form.value.half_day_end)   days -= 0.5
  return Math.max(0.5, days)
})

async function doCreate() {
  if (selectedType.value?.requires_comment && !form.value.employee_comment?.trim()) {
    error.value = 'El comentari és obligatori per a aquest tipus d\'absència.'
    return
  }
  error.value = ''
  const r = await createRequest({ ...form.value })
  if (r.ok) {
    showModal.value = false
    if (isHrPlus.value) fetchCompanyRequests()
  }
}

async function doCancel(id) {
  if (!confirm('Cancel·lar aquesta sol·licitud?')) return
  await cancelRequest(id)
}

// ── Accions admin ─────────────────────────────────────────────────────────────
const denyTarget   = ref(null)
const denyComment  = ref('')
const denyError    = ref('')

function openDenyModal(req) { denyTarget.value = req; denyComment.value = ''; denyError.value = '' }

async function doApprove(id) {
  const r = await approveRequest(id)
  if (r.ok) fetchCompanyRequests()
}

async function doDeny() {
  if (!denyComment.value.trim()) {
    denyError.value = 'El comentari és obligatori per denegar una sol·licitud.'
    return
  }
  denyError.value = ''
  const r = await denyRequest(denyTarget.value.id, denyComment.value)
  if (r.ok) { denyTarget.value = null; fetchCompanyRequests() }
}

// ── Deep link: ?request=ID des de notificació ─────────────────────────────────
const highlightedRequestId = ref(null)

async function handleDeepLink() {
  const rid = route.query.request ? Number(route.query.request) : null
  if (!rid) return
  highlightedRequestId.value = rid
  if (isHrPlus.value) {
    companyFilter.value = 'all' // mostra tots els estats
    await fetchCompanyRequests()
  }
  // Scroll fins a la fila
  setTimeout(() => {
    document.getElementById(`req-${rid}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }, 150)
}

async function fetchCompanyRequests() {
  companyLoading.value = true
  await loadCompanyRequests()
  companyLoading.value = false
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ca-ES', { day: 'numeric', month: 'short', year: 'numeric' })
}

function statusLabel(s) {
  return { pending: 'Pendent', approved: 'Aprovat', denied: 'Denegat', cancelled: 'Cancel·lat' }[s] || s
}

function statusBadge(s) {
  return {
    pending:   'bg-amber-50 text-amber-700',
    approved:  'bg-green-50 text-green-700',
    denied:    'bg-red-50 text-red-600',
    cancelled: 'bg-gray-100 text-gray-400',
  }[s] || 'bg-gray-100 text-gray-500'
}

const AVATAR_COLORS = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6','#F97316']
function avatarColor(name) {
  let h = 0
  for (const c of (name || '')) h = (h * 31 + c.charCodeAt(0)) & 0xffffffff
  return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length]
}

// ── Gestió festius (Admin+) ───────────────────────────────────────────────────
const showHolidayForm = ref(false)
const holidayForm     = ref({ name: '', date: '', type: 'national', color: '', recurring: false })
const holidayError    = ref('')

const holidayTypeOptions = [
  { value: 'national', label: 'Nacional', color: '#EF4444' },
  { value: 'local',    label: 'Local',    color: '#F97316' },
  { value: 'company',  label: 'Empresa',  color: '#8B5CF6' },
]

function openHolidayForm() {
  holidayForm.value  = { name: '', date: '', type: 'national', color: '', recurring: false }
  holidayError.value = ''
  showHolidayForm.value = true
}

async function doCreateHoliday() {
  if (!holidayForm.value.name.trim() || !holidayForm.value.date) {
    holidayError.value = 'El nom i la data són obligatoris.'
    return
  }
  const color = holidayForm.value.color || defaultColor(holidayForm.value.type)
  const r = await createHoliday({ ...holidayForm.value, color })
  if (r.ok) {
    showHolidayForm.value = false
  } else {
    holidayError.value = r.message
  }
}

async function doRemoveHoliday(id) {
  if (!confirm('Eliminar aquest festiu?')) return
  await removeHoliday(id)
}

// Recarrega festius quan canvia l'any del calendari
function reloadHolidays() { loadHolidays(viewYear.value) }
watch(viewYear, reloadHolidays)

// ── Muntatge ──────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([loadMyRequests(), loadBalance(), loadAbsenceTypes(), loadHolidays(viewYear.value)])
  if (isHrPlus.value) await fetchCompanyRequests()
  handleDeepLink()
})
</script>
