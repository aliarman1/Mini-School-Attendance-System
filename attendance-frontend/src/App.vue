<template>
  <div id="app">
    <nav v-if="authStore.isAuthenticated" class="navbar">
      <div class="nav-container">
        <div class="nav-brand">
          <div class="logo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
              <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
            </svg>
          </div>
          <div class="logo-text">
            <h1 class="logo-title">School Attendance</h1>
            <p class="logo-subtitle">Management System</p>
          </div>
        </div>
        
        <button class="mobile-menu-btn" @click="mobileMenuOpen = !mobileMenuOpen">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>

        <div class="nav-content" :class="{'mobile-open': mobileMenuOpen}">
          <div class="nav-links">
            <router-link to="/" class="nav-link" @click="mobileMenuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg>
              <span>Dashboard</span>
            </router-link>
            <router-link to="/students" class="nav-link" @click="mobileMenuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
              <span>Students</span>
            </router-link>
            <router-link to="/classes" class="nav-link" @click="mobileMenuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
              </svg>
              <span>Classes</span>
            </router-link>
            <router-link to="/sections" class="nav-link" @click="mobileMenuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
              </svg>
              <span>Sections</span>
            </router-link>
            <router-link to="/attendance" class="nav-link" @click="mobileMenuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
              </svg>
              <span>Attendance</span>
            </router-link>
            <router-link to="/reports" class="nav-link" @click="mobileMenuOpen = false">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
              </svg>
              <span>Reports</span>
            </router-link>
          </div>
          
          <div class="user-menu">
            <div class="user-info">
              <div class="user-avatar">{{ getUserInitials(authStore.user?.name) }}</div>
              <span class="user-name">{{ authStore.user?.name }}</span>
            </div>
            <button @click="handleLogout" class="btn-logout">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span>Logout</span>
            </button>
          </div>
        </div>
      </div>
    </nav>
    
    <main :class="{'main-content': authStore.isAuthenticated, 'main-content-full': !authStore.isAuthenticated}">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const getUserInitials = (name) => {
  if (!name) return 'U'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name[0].toUpperCase()
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}

#app {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  background: #f5f7fa;
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
}

/* Navigation Bar Styles */
.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%;
}

.nav-container {
  max-width: 100%;
  width: 100%;
  margin: 0 auto;
  padding: 0 1rem;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 1rem;
  min-height: 70px;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.logo-icon {
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  flex-shrink: 0;
}

.logo-icon svg {
  color: white;
  width: 24px;
  height: 24px;
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.logo-title {
  font-size: 1.1rem;
  font-weight: 700;
  line-height: 1.2;
  margin: 0;
  white-space: nowrap;
}

.logo-subtitle {
  font-size: 0.65rem;
  opacity: 0.9;
  margin: 0;
  font-weight: 400;
  white-space: nowrap;
}

.mobile-menu-btn {
  display: none;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.mobile-menu-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.nav-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  grid-column: 2 / 4;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  flex: 1;
}

.nav-link {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-weight: 500;
  font-size: 0.875rem;
  white-space: nowrap;
}

.nav-link svg {
  opacity: 0.8;
  flex-shrink: 0;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.15);
}

.nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.25);
  font-weight: 600;
}

.nav-link.router-link-active svg {
  opacity: 1;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50px;
  backdrop-filter: blur(10px);
  white-space: nowrap;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
  color: white;
  flex-shrink: 0;
}

.user-name {
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
}

.btn-logout {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  backdrop-filter: blur(10px);
  white-space: nowrap;
}

.btn-logout svg {
  flex-shrink: 0;
}

.btn-logout:hover {
  background: rgba(231, 76, 60, 0.9);
  border-color: rgba(231, 76, 60, 1);
  transform: translateY(-1px);
}

/* Main Content */
.main-content {
  flex: 1;
  width: 100%;
  max-width: 1600px;
  margin: 0 auto;
  padding: 2rem;
  min-height: calc(100vh - 70px);
}

.main-content-full {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
  flex: 1;
}

/* Button Styles */
.btn {
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5568d3;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Card Styles */
.card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
  overflow-x: auto;
  width: 100%;
}

@media (max-width: 768px) {
  .card {
    padding: 1rem;
    border-radius: 8px;
  }
}

/* Form Styles */
.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 0.65rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Table Styles */
table {
  width: 100%;
  border-collapse: collapse;
  overflow-x: auto;
  display: table;
}

@media (max-width: 768px) {
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
  }
  
  table thead {
    display: table-header-group;
  }
  
  table tbody {
    display: table-row-group;
  }
}

table th,
table td {
  padding: 0.875rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
  table th,
  table td {
    padding: 0.5rem;
    font-size: 0.875rem;
  }
}

table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

table tr:hover {
  background: #f9fafb;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

/* Loading & Error States */
.loading {
  text-align: center;
  padding: 3rem;
  color: #6b7280;
}

.error {
  background: #fef2f2;
  color: #dc2626;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  border-left: 4px solid #dc2626;
}

/* Responsive Design */

/* Large Desktop - Show all nav items */
@media (min-width: 1400px) {
  .nav-container {
    max-width: 1400px;
    padding: 0 2rem;
  }
  
  .nav-link span {
    display: inline;
  }
}

/* Medium Desktop - Compact nav items */
@media (min-width: 1025px) and (max-width: 1399px) {
  .nav-container {
    padding: 0 1.5rem;
  }
  
  .nav-link {
    font-size: 0.8rem;
    padding: 0.5rem 0.6rem;
  }
  
  .user-name {
    display: none;
  }
}

/* Tablet - Hide some nav text, show icons only */
@media (min-width: 769px) and (max-width: 1024px) {
  .nav-link span {
    display: none;
  }
  
  .nav-link {
    padding: 0.5rem;
  }
  
  .user-name {
    display: none;
  }
  
  .btn-logout span {
    display: none;
  }
}

/* Mobile - Hamburger menu */
@media (max-width: 768px) {
  .nav-container {
    grid-template-columns: auto 1fr auto;
    padding: 0 1rem;
  }

  .logo-title {
    font-size: 0.95rem;
  }

  .logo-subtitle {
    font-size: 0.6rem;
  }

  .mobile-menu-btn {
    display: block;
    grid-column: 3;
  }

  .nav-content {
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    gap: 0;
    padding: 1rem;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    overflow-y: auto;
    grid-column: 1 / -1;
    z-index: 999;
  }

  .nav-content.mobile-open {
    transform: translateX(0);
  }

  .nav-links {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
    width: 100%;
  }

  .nav-link {
    padding: 1rem;
    font-size: 1rem;
    justify-content: flex-start;
  }

  .nav-link span {
    display: inline;
  }

  .user-menu {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    margin-top: 1rem;
    width: 100%;
  }

  .user-info {
    justify-content: center;
    padding: 0.75rem 1rem;
  }

  .user-name {
    display: inline;
  }

  .btn-logout {
    justify-content: center;
    padding: 0.75rem 1rem;
    width: 100%;
  }

  .btn-logout span {
    display: inline;
  }

  .main-content {
    padding: 1rem;
    margin: 0;
    width: 100%;
  }
  
  .card {
    border-radius: 8px;
    margin-bottom: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
    margin-bottom: 1.5rem;
  }
  
  .page-header h2 {
    font-size: 1.5rem;
  }
  
  .page-header .btn {
    width: 100%;
  }
}

/* Small Mobile */
@media (max-width: 480px) {
  .logo-text {
    display: none;
  }
  
  .nav-container {
    padding: 0 0.75rem;
  }
  
  .main-content {
    padding: 0.75rem;
  }
  
  .page-header h2 {
    font-size: 1.25rem;
  }
}
</style>
