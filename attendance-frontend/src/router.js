import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from './pages/Dashboard.vue'
import Students from './pages/Students.vue'
import Attendance from './pages/Attendance.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/students',
    name: 'Students',
    component: Students
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: Attendance
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
