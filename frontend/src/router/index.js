import { createRouter, createWebHistory } from 'vue-router'
import DashboardLayout from '../layouts/DashboardLayout.vue'
import DashboardView from '../views/DashboardView.vue'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    component: DashboardLayout,
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard',    name: 'dashboard',    component: DashboardView },
      { path: 'employees',    name: 'employees',    component: () => import('../views/EmployeesView.vue'),    meta: { minRole: 'hr'         } },
      { path: 'departments',  name: 'departments',  component: () => import('../views/DepartmentsView.vue'),  meta: { minRole: 'admin'      } },
      { path: 'convenis',     name: 'convenis',     component: () => import('../views/ConvenisView.vue'),      meta: { minRole: 'admin'      } },
      { path: 'time-entries',         name: 'time-entries',         component: () => import('../views/TimeEntriesView.vue'),  meta: { minRole: 'user' } },
      { path: 'time-entries/history', name: 'time-entries-history', component: () => import('../views/TimeHistoryView.vue'),   meta: { minRole: 'user' } },
      { path: 'time-entry-edit-requests', name: 'time-entry-edit-requests', component: () => import('../views/TimeEditRequestsView.vue'), meta: { minRole: 'hr' } },
      { path: 'admin/time-entries', name: 'admin-time-entries', component: () => import('../views/AdminTimeEntriesView.vue'), meta: { minRole: 'hr' } },
      { path: 'shifts',       name: 'shifts',       component: () => import('../views/ShiftsView.vue'),       meta: { minRole: 'admin'      } },
      { path: 'absences',     name: 'absences',     component: () => import('../views/AbsencesView.vue'),    meta: { minRole: 'user'       } },
      { path: 'payrolls',     name: 'payrolls',     component: () => import('../views/PlaceholderView.vue'),  meta: { minRole: 'admin'      } },
      { path: 'reports',      name: 'reports',      component: () => import('../views/PlaceholderView.vue') },
      { path: 'settings',     name: 'settings',     component: () => import('../views/PlaceholderView.vue'),  meta: { minRole: 'admin'      } },
      { path: 'companies',    name: 'companies',    component: () => import('../views/CompaniesView.vue'),    meta: { minRole: 'superadmin' } },
      { path: 'tenants',      name: 'tenants',      component: () => import('../views/TenantsView.vue'),      meta: { minRole: 'founder'    } },
    ],
  },
  { path: '/login',        name: 'login',        component: () => import('../views/auth/LoginView.vue'),       meta: { requiresAuth: false } },
  { path: '/set-password', name: 'set-password', component: () => import('../views/auth/SetPasswordView.vue'), meta: { requiresAuth: false } },
]

const ROLE_HIERARCHY = { user: 0, hr: 1, admin: 2, superadmin: 3, founder: 4 }

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.name === 'login' && auth.isAuthenticated) return next({ name: 'dashboard' })

  if (to.meta?.requiresAuth !== false && !auth.isAuthenticated) {
    return next({ name: 'login' })
  }

  if (to.meta?.minRole) {
    const userLevel = ROLE_HIERARCHY[auth.user?.role] ?? -1
    const minLevel  = ROLE_HIERARCHY[to.meta.minRole] ?? 999
    if (userLevel < minLevel) return next({ name: 'dashboard' })
  }

  next()
})

export default router
