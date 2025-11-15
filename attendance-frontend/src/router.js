import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './stores/auth'
import Dashboard from './pages/Dashboard.vue'
import Students from './pages/Students.vue'
import Attendance from './pages/Attendance.vue'
import Login from './pages/Login.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/students',
    name: 'Students',
    component: Students,
    meta: { requiresAuth: true }
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: Attendance,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Redirect to login if not authenticated
      next({ name: 'Login' })
    } else {
      // Fetch user if not already loaded
      if (!authStore.user) {
        try {
          await authStore.fetchUser()
        } catch (error) {
          // If fetch fails, redirect to login
          next({ name: 'Login' })
          return
        }
      }
      next()
    }
  } else if (to.meta.guest && authStore.isAuthenticated) {
    // Redirect to dashboard if authenticated user tries to access login
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router
