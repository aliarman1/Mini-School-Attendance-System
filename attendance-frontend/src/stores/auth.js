import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    currentUser: (state) => state.user
  },

  actions: {
    async register(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/register', credentials)
        this.user = response.data.data.user
        this.token = response.data.data.token
        localStorage.setItem('auth_token', this.token)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/login', credentials)
        this.user = response.data.data.user
        this.token = response.data.data.token
        localStorage.setItem('auth_token', this.token)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      this.loading = true
      try {
        await api.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('auth_token')
        this.loading = false
      }
    },

    async fetchUser() {
      if (!this.token) return
      
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/user')
        this.user = response.data.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch user'
        // If token is invalid, clear auth state
        if (error.response?.status === 401) {
          this.user = null
          this.token = null
          localStorage.removeItem('auth_token')
        }
        throw error
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    }
  }
})
